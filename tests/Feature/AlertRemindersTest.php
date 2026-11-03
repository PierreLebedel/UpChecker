<?php

use App\Actions\CheckMonitorAction;
use App\Enums\MonitorStatus;
use App\Jobs\CheckMonitorJob;
use App\Models\Monitor;
use App\Models\Project;
use App\Models\User;
use App\Notifications\MonitorAlertReminderNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->freezeSecond();
    config(['upchecker.notifications.channels.telegram.enabled' => true]);
    Notification::fake();
    Http::preventStrayRequests();
    Queue::fake([CheckMonitorJob::class]);
});

test('hourly reminders group confirmed failures by account for both mail and telegram', function () {
    $user = User::factory()->create([
        'notification_channels' => ['mail', 'telegram'],
        'next_alert_reminder_at' => now(),
    ]);
    $project = Project::factory()->for($user)->create(['name' => 'Alpha']);
    $otherProject = Project::factory()->for($user)->create(['name' => 'Beta']);
    Monitor::factory()->for($project)->confirmedFailure()->create(['name' => 'API']);
    Monitor::factory()->for($otherProject)->confirmedFailure()->create(['name' => 'Site']);
    Monitor::factory()->for($project)->down()->create(['name' => 'Pending', 'failure_detected_at' => now()]);
    Monitor::factory()->for($project)->confirmedFailure()->disabled()->create(['name' => 'Disabled']);
    $otherUser = User::factory()->create(['next_alert_reminder_at' => now()]);
    $privateProject = Project::factory()->for($otherUser)->create(['name' => 'Private']);
    Monitor::factory()->for($privateProject)->confirmedFailure()->create(['name' => 'Other account']);

    $this->artisan('monitors:send-alert-reminders')->assertSuccessful();

    Notification::assertSentToTimes($user, MonitorAlertReminderNotification::class, 1);
    Notification::assertSentToTimes($otherUser, MonitorAlertReminderNotification::class, 1);
    Notification::assertCount(2);
    Notification::assertSentTo($user, function (MonitorAlertReminderNotification $notification, array $channels) use ($user): bool {
        expect($channels)->toBe(['mail', 'telegram'])
            ->and($notification->toTelegram($user)->toArray()['text'])->toBe(
                "2 incidents en cours :\n🟨 Alpha / API\n🟨 Beta / Site",
            )
            ->and($notification->toMail($user)->subject)->toBe('Rappel UpChecker : services toujours en erreur')
            ->and($notification->toMail($user)->introLines)->toBe([
                'Rappel : les services suivants sont toujours en erreur.',
                '🟨 Alpha / API',
                '🟨 Beta / Site',
            ]);

        return true;
    });
    expect($user->refresh()->next_alert_reminder_at->equalTo(now()->addHour()))->toBeTrue();
});

test('reminders repeat once an hour without any new status change or duplicate dispatch', function () {
    $user = User::factory()->create(['next_alert_reminder_at' => now()]);
    $project = Project::factory()->for($user)->create();
    Monitor::factory()->for($project)->confirmedFailure()->create();

    $this->artisan('monitors:send-alert-reminders')->assertSuccessful();
    $this->artisan('monitors:send-alert-reminders')->assertSuccessful();
    $this->travel(59)->minutes();
    $this->artisan('monitors:send-alert-reminders')->assertSuccessful();
    Notification::assertSentToTimes($user, MonitorAlertReminderNotification::class, 1);

    $this->travel(1)->minutes();
    $this->artisan('monitors:send-alert-reminders')->assertSuccessful();
    Notification::assertSentToTimes($user, MonitorAlertReminderNotification::class, 2);
    expect($user->refresh()->next_alert_reminder_at->equalTo(now()->addHour()))->toBeTrue();
});

test('no reminder is sent without an active confirmed failure and its schedule is cleared', function (string $state) {
    $user = User::factory()->create(['next_alert_reminder_at' => now()]);
    $project = Project::factory()->for($user)->create();

    match ($state) {
        'pending' => Monitor::factory()->for($project)->down()->create(['failure_detected_at' => now()]),
        'disabled' => Monitor::factory()->for($project)->confirmedFailure()->disabled()->create(),
        'healthy' => Monitor::factory()->for($project)->up()->create(),
        'none' => null,
    };

    $this->artisan('monitors:send-alert-reminders')->assertSuccessful();

    Notification::assertNothingSent();
    expect($user->refresh()->next_alert_reminder_at)->toBeNull();
})->with(['pending', 'disabled', 'healthy', 'none']);

test('reminders wait until their scheduled deadline', function () {
    $user = User::factory()->create(['next_alert_reminder_at' => now()->addHour()]);
    $project = Project::factory()->for($user)->create();
    Monitor::factory()->for($project)->confirmedFailure()->create();

    $this->travel(3599)->seconds();
    $this->artisan('monitors:send-alert-reminders')->assertSuccessful();
    Notification::assertNothingSent();

    $this->travel(1)->seconds();
    $this->artisan('monitors:send-alert-reminders')->assertSuccessful();
    Notification::assertSentToTimes($user, MonitorAlertReminderNotification::class, 1);
});

test('new failures and partial recoveries do not postpone the grouped reminder', function () {
    Http::fake([
        'https://example.com/old' => Http::response('ok', 200),
        'https://example.com/new' => Http::response('error', 500),
    ]);
    $deadline = now()->addHour();
    $user = User::factory()->create(['next_alert_reminder_at' => $deadline]);
    $project = Project::factory()->for($user)->create();
    $oldMonitor = Monitor::factory()->for($project)->confirmedFailure()->create(['url' => 'https://example.com/old']);
    $newMonitor = Monitor::factory()->for($project)->up()->create(['url' => 'https://example.com/new']);
    $action = app(CheckMonitorAction::class);

    $this->travel(10)->minutes();
    $action->handle($newMonitor);
    $this->travel(1)->minutes();
    $action->handle($newMonitor);
    $this->travel(1)->minutes();
    $action->handle($oldMonitor);

    expect($user->refresh()->next_alert_reminder_at->equalTo($deadline))->toBeTrue();

    $this->travelTo($deadline);
    $this->artisan('monitors:send-alert-reminders')->assertSuccessful();
    Notification::assertSentToTimes($user, MonitorAlertReminderNotification::class, 1);
});

test('a full recovery cancels reminders and a new incident starts a new hourly deadline', function () {
    Http::fake([
        'https://example.com/health' => Http::sequence()->push('ok', 200)->push('error', 500)->push('error', 500),
    ]);
    $user = User::factory()->create(['next_alert_reminder_at' => now()->addMinutes(10)]);
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->confirmedFailure()->create(['url' => 'https://example.com/health']);
    $action = app(CheckMonitorAction::class);

    $action->handle($monitor);
    expect($user->refresh()->next_alert_reminder_at)->toBeNull();
    $this->travel(1)->hours();
    $this->artisan('monitors:send-alert-reminders')->assertSuccessful();
    Notification::assertNotSentTo($user, MonitorAlertReminderNotification::class);

    $action->handle($monitor);
    expect($user->refresh()->next_alert_reminder_at)->toBeNull();
    $this->travel(1)->minutes();
    $action->handle($monitor);
    expect($user->refresh()->next_alert_reminder_at->equalTo(now()->addHour()))->toBeTrue();
});

test('reminders use channels enabled for confirmed failure alerts', function () {
    $user = User::factory()->create([
        'notification_channels' => [
            'mail' => ['enabled' => true, 'transitions' => ['up_to_down']],
            'telegram' => ['enabled' => true, 'transitions' => ['down_to_up']],
        ],
        'next_alert_reminder_at' => now(),
    ]);
    $project = Project::factory()->for($user)->create();
    Monitor::factory()->for($project)->confirmedFailure()->create();

    $this->artisan('monitors:send-alert-reminders')->assertSuccessful();

    Notification::assertSentTo($user, fn (MonitorAlertReminderNotification $notification, array $channels): bool => $channels === ['mail']);
});

test('queued reminders use current failures and are cancelled if everything has recovered', function () {
    $user = User::factory()->create(['notification_channels' => ['mail', 'telegram']]);
    $project = Project::factory()->for($user)->create(['name' => 'Application']);
    $first = Monitor::factory()->for($project)->confirmedFailure()->create(['name' => 'API']);
    $second = Monitor::factory()->for($project)->confirmedFailure()->create(['name' => 'Site']);
    $notification = unserialize(serialize(new MonitorAlertReminderNotification));

    $first->update(['current_status' => MonitorStatus::Up, 'failure_confirmed_at' => null]);
    expect($notification->shouldSend($user, 'mail'))->toBeTrue()
        ->and($notification->shouldSend($user, 'telegram'))->toBeTrue()
        ->and($notification->toTelegram($user)->toArray()['text'])->toBe(
            "1 incident en cours :\n🟨 Application / Site",
        );

    $second->update(['current_status' => MonitorStatus::Up, 'failure_confirmed_at' => null]);
    expect($notification->shouldSend($user, 'mail'))->toBeFalse()
        ->and($notification->shouldSend($user, 'telegram'))->toBeFalse();
});
