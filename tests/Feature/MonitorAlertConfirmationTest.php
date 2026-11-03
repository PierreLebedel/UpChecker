<?php

use App\Actions\CheckMonitorAction;
use App\Enums\CheckStatus;
use App\Enums\MonitorStatus;
use App\Jobs\CheckMonitorJob;
use App\Models\Monitor;
use App\Models\Project;
use App\Models\User;
use App\Notifications\MonitorAlertNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->freezeSecond();
    config(['upchecker.notifications.channels.telegram.enabled' => true]);
    Http::preventStrayRequests();
    Notification::fake();
    Queue::fake([CheckMonitorJob::class]);
});

test('a first failure schedules a confirmation in one minute regardless of the regular interval', function (int $interval) {
    Http::fake(['https://example.com/health' => Http::response('error', 500)]);
    $monitor = Monitor::factory()->up()->create([
        'url' => 'https://example.com/health',
        'interval_minutes' => $interval,
    ]);

    $result = app(CheckMonitorAction::class)->handle($monitor);

    expect($result->status)->toBe(CheckStatus::Down)
        ->and($monitor->refresh()->current_status)->toBe(MonitorStatus::Down)
        ->and($monitor->failure_detected_at->equalTo(now()))->toBeTrue()
        ->and($monitor->failure_confirmed_at)->toBeNull()
        ->and($monitor->last_alerted_at)->toBeNull()
        ->and($monitor->next_check_at->equalTo(now()->addMinute()))->toBeTrue()
        ->and($monitor->project->user->next_alert_reminder_at)->toBeNull();
    Notification::assertNothingSent();
    Queue::assertPushed(CheckMonitorJob::class, function (CheckMonitorJob $job) use ($monitor): bool {
        return $job->monitorId === $monitor->id
            && $job->failureDetectedAt === $monitor->failure_detected_at->toIso8601String()
            && $job->delay->equalTo(now()->addMinute());
    });
    Queue::assertPushed(CheckMonitorJob::class, 1);
})->with([1, 15, 60]);

test('only a second failure after a full minute alerts both channels including for a new monitor', function (MonitorStatus $initialStatus) {
    Http::fake([
        'https://example.com/health' => Http::sequence()
            ->push('error', 500)
            ->push('error', 503)
            ->pushResponse(Http::failedConnection()),
    ]);
    $user = User::factory()->create(['notification_channels' => ['mail', 'telegram']]);
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'url' => 'https://example.com/health',
        'current_status' => $initialStatus,
        'interval_minutes' => 15,
    ]);
    $action = app(CheckMonitorAction::class);

    $action->handle($monitor);
    $detectedAt = $monitor->refresh()->failure_detected_at;
    $this->travel(59)->seconds();
    $action->handle($monitor);

    Notification::assertNothingSent();
    expect($monitor->refresh()->failure_detected_at->equalTo($detectedAt))->toBeTrue()
        ->and($monitor->next_check_at->equalTo($detectedAt->copy()->addMinute()))->toBeTrue();

    $this->travel(1)->seconds();
    $result = $action->handle($monitor);

    Notification::assertSentTo($user, function (MonitorAlertNotification $notification, array $channels) use ($result, $user): bool {
        expect($channels)->toBe(['mail', 'telegram'])
            ->and($notification->checkResult->is($result))->toBeTrue()
            ->and($notification->toMail($user)->subject)->toStartWith('Alerte UpChecker')
            ->and($notification->toTelegram($user)->toArray()['text'])->toStartWith('🟥');

        return true;
    });
    expect($result->status)->toBe(CheckStatus::Timeout)
        ->and($monitor->refresh()->failure_confirmed_at->equalTo(now()))->toBeTrue()
        ->and($monitor->next_check_at->equalTo(now()->addMinutes(15)))->toBeTrue()
        ->and($user->refresh()->next_alert_reminder_at->equalTo(now()->addHour()))->toBeTrue();
    Queue::assertPushed(CheckMonitorJob::class, 1);
})->with([MonitorStatus::Up, MonitorStatus::Unknown]);

test('a successful confirmation clears the signal silently and resumes the normal schedule', function () {
    Http::fake([
        'https://example.com/health' => Http::sequence()->push('error', 500)->push('ok', 200),
    ]);
    $monitor = Monitor::factory()->up()->create([
        'url' => 'https://example.com/health',
        'interval_minutes' => 60,
    ]);
    $action = app(CheckMonitorAction::class);
    $action->handle($monitor);
    $job = Queue::pushed(CheckMonitorJob::class)->first();

    $this->travel(1)->minutes();
    $job->handle($action);

    Notification::assertNothingSent();
    expect($monitor->refresh()->failure_detected_at)->toBeNull()
        ->and($monitor->failure_confirmed_at)->toBeNull()
        ->and($monitor->next_check_at->equalTo(now()->addHour()))->toBeTrue()
        ->and($monitor->project->user->next_alert_reminder_at)->toBeNull();
});

test('the confirmation delay starts when the first failed request finishes', function () {
    Http::fake(function () {
        $this->travel(30)->seconds();

        return Http::response('error', 500);
    });
    $monitor = Monitor::factory()->up()->create();
    $startedAt = now();

    $result = app(CheckMonitorAction::class)->handle($monitor);

    expect($result->checked_at->equalTo($startedAt))->toBeTrue()
        ->and($monitor->refresh()->failure_detected_at->equalTo($startedAt->addSeconds(30)))->toBeTrue()
        ->and($monitor->next_check_at->equalTo(now()->addMinute()))->toBeTrue();
    Notification::assertNothingSent();
});

test('a manual recovery cancels the pending confirmation without a recovery alert', function () {
    Http::fake([
        'https://example.com/health' => Http::sequence()->push('error', 500)->push('ok', 200),
    ]);
    $monitor = Monitor::factory()->up()->create(['url' => 'https://example.com/health']);
    $action = app(CheckMonitorAction::class);
    $action->handle($monitor);
    $job = Queue::pushed(CheckMonitorJob::class)->first();

    $this->travel(30)->seconds();
    $action->handle($monitor);
    $this->travel(30)->seconds();
    $job->handle($action);

    Http::assertSentCount(2);
    Notification::assertNothingSent();
    expect($monitor->refresh()->failure_detected_at)->toBeNull();
});

test('a queued confirmation cannot confirm a later incident or run before its deadline', function () {
    Http::fake([
        'https://example.com/health' => Http::sequence()
            ->push('error', 500)->push('ok', 200)->push('error', 500)->push('error', 500),
    ]);
    $monitor = Monitor::factory()->up()->create(['url' => 'https://example.com/health']);
    $action = app(CheckMonitorAction::class);
    $action->handle($monitor);
    $oldJob = Queue::pushed(CheckMonitorJob::class)->first();
    $this->travel(10)->seconds();
    $action->handle($monitor);
    $this->travel(10)->seconds();
    $action->handle($monitor);
    $newJob = Queue::pushed(CheckMonitorJob::class)->last();

    $this->travel(40)->seconds();
    $oldJob->handle($action);
    $newJob->handle($action);
    Http::assertSentCount(3);
    Notification::assertNothingSent();

    $this->travel(20)->seconds();
    $newJob->handle($action);
    $newJob->handle($action);

    Http::assertSentCount(4);
    Notification::assertSentToTimes($monitor->project->user, MonitorAlertNotification::class, 1);
});

test('the scheduler can recover a missed delayed confirmation', function () {
    Http::fake(['https://example.com/health' => Http::response('error', 500)]);
    $monitor = Monitor::factory()->up()->create([
        'url' => 'https://example.com/health',
        'interval_minutes' => 60,
    ]);
    $action = app(CheckMonitorAction::class);
    $action->handle($monitor);

    $this->travel(1)->minutes();
    $this->artisan('monitors:dispatch-due-checks')->assertSuccessful();
    Queue::assertPushed(CheckMonitorJob::class, 2);
    $scheduledJob = Queue::pushed(CheckMonitorJob::class)->last();
    $scheduledJob->handle($action);

    Notification::assertSentToTimes($monitor->project->user, MonitorAlertNotification::class, 1);
    expect($monitor->refresh()->failure_confirmed_at)->not->toBeNull();
});

test('pending and disabled services never enter another services alert', function () {
    Http::fake(['*' => Http::response('error', 500)]);
    $user = User::factory()->create(['notification_channels' => ['telegram']]);
    $project = Project::factory()->for($user)->create(['name' => 'Application']);
    $pending = Monitor::factory()->for($project)->up()->create(['name' => 'Pending']);
    $confirmed = Monitor::factory()->for($project)->up()->create(['name' => 'Confirmed']);
    Monitor::factory()->for($project)->confirmedFailure()->disabled()->create(['name' => 'Disabled']);
    $action = app(CheckMonitorAction::class);

    $action->handle($confirmed);
    $this->travel(40)->seconds();
    $action->handle($pending);
    $this->travel(20)->seconds();
    $action->handle($confirmed);

    Notification::assertSentTo($user, function (MonitorAlertNotification $notification) use ($user): bool {
        return $notification->toTelegram($user)->toArray()['text'] === '🟥 Application / Confirmed';
    });
    expect($pending->refresh()->failure_confirmed_at)->toBeNull();
});

test('recovering the last confirmed incident stops reminders even with an unconfirmed signal remaining', function () {
    Http::fake(['*' => Http::response('ok', 200)]);
    $user = User::factory()->create(['next_alert_reminder_at' => now()->addMinutes(30)]);
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->confirmedFailure()->create();
    Monitor::factory()->for($project)->down()->create(['failure_detected_at' => now()]);

    app(CheckMonitorAction::class)->handle($monitor);

    expect($user->refresh()->next_alert_reminder_at)->toBeNull()
        ->and($monitor->refresh()->failure_detected_at)->toBeNull()
        ->and($monitor->failure_confirmed_at)->toBeNull();
});
