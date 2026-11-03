<?php

use App\Actions\CheckMonitorAction;
use App\Enums\AlertChannel;
use App\Enums\AlertTransition;
use App\Enums\CheckStatus;
use App\Enums\MonitorCheckCriterionType;
use App\Enums\MonitorStatus;
use App\Events\MonitorCheckCompleted;
use App\Jobs\CheckMonitorJob;
use App\Models\Monitor;
use App\Models\Project;
use App\Models\User;
use App\Notifications\MonitorAlertNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Carbon::setTestNow(now()->startOfSecond());
    Http::preventStrayRequests();
    Queue::fake([CheckMonitorJob::class]);
});

afterEach(function () {
    Carbon::setTestNow();
});

test('it records an up result when the response matches the monitor expectations', function () {
    $checkedAt = Carbon::parse('2026-07-16 12:00:00');
    Carbon::setTestNow($checkedAt);
    Http::fake([
        'https://example.com/health' => Http::response('ok', 200),
    ]);
    $monitor = Monitor::factory()->create([
        'url' => 'https://example.com/health',
        'interval_minutes' => 15,
    ]);

    $result = app(CheckMonitorAction::class)->handle($monitor);
    $monitor->refresh();

    expect($result->status)->toBe(CheckStatus::Up)
        ->and($result->http_status)->toBe(200)
        ->and($result->response_time_ms)->toBeInt()
        ->and($result->error_message)->toBeNull()
        ->and($result->checked_url)->toBe('https://example.com/health')
        ->and($monitor->current_status)->toBe(MonitorStatus::Up)
        ->and($monitor->last_checked_at->equalTo($checkedAt))->toBeTrue()
        ->and($monitor->last_success_at->equalTo($checkedAt))->toBeTrue()
        ->and($monitor->last_failure_at)->toBeNull()
        ->and($monitor->next_check_at->equalTo($checkedAt->copy()->addMinutes(15)))->toBeTrue();
});

test('it broadcasts a monitor check completed event when the check is recorded', function () {
    $checkedAt = Carbon::parse('2026-07-16 12:00:00');
    Carbon::setTestNow($checkedAt);
    Event::fake([MonitorCheckCompleted::class]);
    Http::fake([
        'https://example.com/health' => Http::response('ok', 200),
    ]);
    $monitor = Monitor::factory()->create([
        'url' => 'https://example.com/health',
    ]);

    $result = app(CheckMonitorAction::class)->handle($monitor);

    Event::assertDispatched(
        MonitorCheckCompleted::class,
        fn (MonitorCheckCompleted $event): bool => $event->monitorId === $monitor->id
            && $event->userId === $monitor->project->user_id
            && $event->checkResultId === $result->id
            && $event->status === CheckStatus::Up->value
            && $event->checkedAt === $checkedAt->toIso8601String()
    );
});

test('it records a down result when the http status is unexpected', function () {
    Http::fake([
        'https://example.com/health' => Http::response('Server error', 500),
    ]);
    $monitor = Monitor::factory()->up()->create([
        'url' => 'https://example.com/health',
        'check_criteria' => [
            ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 200],
        ],
    ]);
    $previousSuccessAt = $monitor->last_success_at;

    $result = app(CheckMonitorAction::class)->handle($monitor);
    $monitor->refresh();

    expect($result->status)->toBe(CheckStatus::Down)
        ->and($result->http_status)->toBe(500)
        ->and($result->error_message)->toContain('500')
        ->and($result->response_excerpt)->toBe('Server error')
        ->and($monitor->current_status)->toBe(MonitorStatus::Down)
        ->and($monitor->last_success_at->equalTo($previousSuccessAt))->toBeTrue()
        ->and($monitor->last_failure_at)->not->toBeNull();
});

test('it sends an alert notification when a monitor failure is confirmed', function () {
    Notification::fake();
    Http::fake([
        'https://example.com/health' => Http::response('Server error', 500),
    ]);
    $monitor = Monitor::factory()->up()->create([
        'url' => 'https://example.com/health',
        'check_criteria' => [
            ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 200],
        ],
    ]);
    $user = $monitor->project->user;

    app(CheckMonitorAction::class)->handle($monitor);
    $this->travel(1)->minutes();
    $result = app(CheckMonitorAction::class)->handle($monitor);

    Notification::assertSentTo(
        $user,
        fn (MonitorAlertNotification $notification, array $channels): bool => $notification->checkResult->is($result)
            && $notification->transition === AlertTransition::UpToDown
            && $channels === ['mail']
    );

    expect($monitor->refresh()->last_alerted_at)->not->toBeNull();
});

test('it sends a recovery alert only to channels configured for down to up', function () {
    Notification::fake();
    config(['upchecker.notifications.channels.telegram.enabled' => true]);
    Http::fake([
        'https://example.com/health' => Http::response('ok', 200),
    ]);
    $monitor = Monitor::factory()->confirmedFailure()->create([
        'url' => 'https://example.com/health',
        'current_status' => MonitorStatus::Down,
        'last_failure_at' => now()->subMinute(),
        'check_criteria' => [
            ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 200],
        ],
    ]);
    $user = $monitor->project->user;
    $user->forceFill([
        'notification_channels' => [
            AlertChannel::Mail->value => [
                'enabled' => true,
                'transitions' => [AlertTransition::UpToDown->value],
            ],
            AlertChannel::Telegram->value => [
                'enabled' => true,
                'transitions' => [
                    AlertTransition::UpToDown->value,
                    AlertTransition::DownToUp->value,
                ],
            ],
        ],
    ])->save();

    $result = app(CheckMonitorAction::class)->handle($monitor);

    Notification::assertSentTo(
        $user,
        fn (MonitorAlertNotification $notification, array $channels): bool => $notification->checkResult->is($result)
            && $notification->transition === AlertTransition::DownToUp
            && $channels === ['telegram']
    );

    expect($monitor->refresh()->last_alerted_at)->not->toBeNull();
});

test('it sends a failure alert to every channel configured for up to down', function () {
    Notification::fake();
    config(['upchecker.notifications.channels.telegram.enabled' => true]);
    Http::fake([
        'https://example.com/health' => Http::response('Server error', 500),
    ]);
    $monitor = Monitor::factory()->up()->create([
        'url' => 'https://example.com/health',
        'check_criteria' => [
            ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 200],
        ],
    ]);
    $user = $monitor->project->user;
    $user->forceFill([
        'notification_channels' => [
            AlertChannel::Mail->value => [
                'enabled' => true,
                'transitions' => [AlertTransition::UpToDown->value],
            ],
            AlertChannel::Telegram->value => [
                'enabled' => true,
                'transitions' => [
                    AlertTransition::UpToDown->value,
                    AlertTransition::DownToUp->value,
                ],
            ],
        ],
    ])->save();

    app(CheckMonitorAction::class)->handle($monitor);
    $this->travel(1)->minutes();
    $result = app(CheckMonitorAction::class)->handle($monitor);

    Notification::assertSentTo(
        $user,
        fn (MonitorAlertNotification $notification, array $channels): bool => $notification->checkResult->is($result)
            && $notification->transition === AlertTransition::UpToDown
            && $channels === ['mail', 'telegram']
    );

    expect($monitor->refresh()->last_alerted_at)->not->toBeNull();
});

test('it does not mark an alert as sent when no channel is configured for the transition', function () {
    Notification::fake();
    config(['upchecker.notifications.channels.telegram.enabled' => true]);
    Http::fake([
        'https://example.com/health' => Http::response('ok', 200),
    ]);
    $monitor = Monitor::factory()->confirmedFailure()->create([
        'url' => 'https://example.com/health',
        'current_status' => MonitorStatus::Down,
        'last_failure_at' => now()->subMinute(),
        'check_criteria' => [
            ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 200],
        ],
    ]);
    $user = $monitor->project->user;
    $user->forceFill([
        'notification_channels' => [
            AlertChannel::Mail->value => [
                'enabled' => true,
                'transitions' => [AlertTransition::UpToDown->value],
            ],
            AlertChannel::Telegram->value => [
                'enabled' => false,
                'transitions' => [
                    AlertTransition::UpToDown->value,
                    AlertTransition::DownToUp->value,
                ],
            ],
        ],
    ])->save();

    app(CheckMonitorAction::class)->handle($monitor);

    Notification::assertNothingSent();

    expect($monitor->refresh()->last_alerted_at)->toBeNull();
});

test('telegram alerts summarize every failure after each outage and recovery', function () {
    Notification::fake();
    config([
        'upchecker.notifications.channels.telegram.enabled' => true,
        'upchecker.notifications.channels.telegram.chat_id' => 'test-chat',
    ]);
    Http::fake([
        'https://example.com/api' => Http::sequence()->push('error', 500)->push('error', 500)->push('ok', 200),
        'https://example.com/site' => Http::sequence()->push('error', 503)->push('error', 503)->push('ok', 200),
    ]);
    $user = User::factory()->create(['notification_channels' => ['telegram']]);
    $project = Project::factory()->for($user)->create(['name' => 'Application']);
    $api = Monitor::factory()->for($project)->up()->create([
        'name' => 'API',
        'url' => 'https://example.com/api',
    ]);
    $site = Monitor::factory()->for($project)->up()->create([
        'name' => 'Site',
        'url' => 'https://example.com/site',
    ]);

    $action = app(CheckMonitorAction::class);
    $action->handle($api);
    $this->travel(1)->minutes();
    $action->handle($api);
    $action->handle($site);
    $this->travel(1)->minutes();
    $action->handle($site);
    $action->handle($api);
    $action->handle($site);

    Notification::assertSentToTimes($user, MonitorAlertNotification::class, 4);

    $messages = Notification::sent($user, MonitorAlertNotification::class)
        ->map(function (MonitorAlertNotification $notification) use ($user): array {
            $queuedNotification = unserialize(serialize($notification));
            $message = $queuedNotification->toTelegram($user)->toArray();

            expect($message['chat_id'])->toBe('test-chat')
                ->and($message)->not->toHaveKey('reply_markup');

            return [$notification->transition, $message['text']];
        })
        ->all();

    expect($messages)->toBe([
        [AlertTransition::UpToDown, "1 incident en cours :\n🟥 Application / API"],
        [AlertTransition::UpToDown, "2 incidents en cours :\n🟥 Application / Site\n🟨 Application / API"],
        [AlertTransition::DownToUp, "1 incident en cours :\n🟨 Application / Site"],
        [AlertTransition::DownToUp, '✅ Aucun incident en cours'],
    ]);
});

test('telegram summaries include all failure types across the users active projects', function () {
    Notification::fake();
    config(['upchecker.notifications.channels.telegram.enabled' => true]);
    Http::fake(['https://example.com/health' => Http::response('error', 500)]);
    $user = User::factory()->create(['notification_channels' => ['telegram']]);
    $project = Project::factory()->for($user)->create(['name' => 'Alpha']);
    $otherProject = Project::factory()->for($user)->create(['name' => 'Beta']);
    $monitor = Monitor::factory()->for($project)->up()->create([
        'name' => 'API',
        'url' => 'https://example.com/health',
    ]);
    Monitor::factory()->for($otherProject)->confirmedFailure()->createMany([
        ['name' => 'Timeout', 'current_status' => MonitorStatus::Timeout],
        ['name' => 'Invalid', 'current_status' => MonitorStatus::Invalid],
        ['name' => 'Down', 'current_status' => MonitorStatus::Down],
        ['name' => 'Healthy', 'current_status' => MonitorStatus::Up],
        ['name' => 'New', 'current_status' => MonitorStatus::Unknown],
    ]);
    Monitor::factory()->for($project)->disabled()->down()->create(['name' => 'Paused']);
    Monitor::factory()->down()->create(['name' => 'Another users service']);
    Monitor::factory()->for($project)->down()->create(['name' => 'Unconfirmed']);

    app(CheckMonitorAction::class)->handle($monitor);
    $this->travel(1)->minutes();
    app(CheckMonitorAction::class)->handle($monitor);

    Notification::assertSentTo($user, function (MonitorAlertNotification $notification, array $channels) use ($user): bool {
        expect($channels)->toBe(['telegram'])
            ->and($notification->toTelegram($user)->toArray()['text'])->toBe(
                "4 incidents en cours :\n🟥 Alpha / API\n🟨 Beta / Down\n🟨 Beta / Invalid\n🟨 Beta / Timeout",
            );

        return true;
    });
});

test('telegram alerts are not repeated when availability stays unchanged', function () {
    Notification::fake();
    config(['upchecker.notifications.channels.telegram.enabled' => true]);
    Http::fake([
        'https://example.com/health' => Http::sequence()
            ->push('error', 500)
            ->push('error', 500)
            ->push('ok', 200)
            ->push('ok', 200),
    ]);
    $user = User::factory()->create(['notification_channels' => ['telegram']]);
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->up()->create(['url' => 'https://example.com/health']);

    $action = app(CheckMonitorAction::class);
    $action->handle($monitor);
    $this->travel(1)->minutes();
    $action->handle($monitor);
    $action->handle($monitor);
    $action->handle($monitor);

    Notification::assertSentToTimes($user, MonitorAlertNotification::class, 2);
});

test('it does not repeat alert notifications while the monitor is already failing', function () {
    Notification::fake();
    Http::fake([
        'https://example.com/health' => Http::response('Server error', 500),
    ]);
    $monitor = Monitor::factory()->confirmedFailure()->create([
        'url' => 'https://example.com/health',
        'current_status' => MonitorStatus::Down,
        'last_failure_at' => now()->subMinute(),
        'check_criteria' => [
            ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 200],
        ],
    ]);

    app(CheckMonitorAction::class)->handle($monitor);

    Notification::assertNothingSent();

    expect($monitor->refresh()->last_alerted_at)->toBeNull();
});

test('it records an up result for a matching json expectation', function () {
    Http::fake([
        'https://example.com/health' => Http::response(['status' => 'ok'], 200),
    ]);
    $monitor = Monitor::factory()->create([
        'url' => 'https://example.com/health',
        'check_criteria' => [
            ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 200],
            ['type' => MonitorCheckCriterionType::JsonPath->value, 'path' => 'status', 'expected' => 'ok'],
        ],
    ]);

    $result = app(CheckMonitorAction::class)->handle($monitor);

    expect($result->status)->toBe(CheckStatus::Up);
});

test('it records a down result for a mismatching json value', function () {
    Http::fake([
        'https://example.com/health' => Http::response(['status' => 'fail'], 200),
    ]);
    $monitor = Monitor::factory()->create([
        'url' => 'https://example.com/health',
        'check_criteria' => [
            ['type' => MonitorCheckCriterionType::JsonPath->value, 'path' => 'status', 'expected' => 'ok'],
        ],
    ]);

    $result = app(CheckMonitorAction::class)->handle($monitor);

    expect($result->status)->toBe(CheckStatus::Down)
        ->and($result->error_message)->toContain('status');
});

test('it records an invalid result when json is expected but the response is not json', function () {
    Http::fake([
        'https://example.com/health' => Http::response('not json', 200),
    ]);
    $monitor = Monitor::factory()->create([
        'url' => 'https://example.com/health',
        'check_criteria' => [
            ['type' => MonitorCheckCriterionType::JsonPath->value, 'path' => 'status', 'expected' => 'ok'],
        ],
    ]);

    $result = app(CheckMonitorAction::class)->handle($monitor);
    $monitor->refresh();

    expect($result->status)->toBe(CheckStatus::Invalid)
        ->and($monitor->current_status)->toBe(MonitorStatus::Invalid);
});

test('it records a down result when the expected body fragment is missing', function () {
    Http::fake([
        'https://example.com/health' => Http::response('not ready', 200),
    ]);
    $monitor = Monitor::factory()->create([
        'url' => 'https://example.com/health',
        'check_criteria' => [
            ['type' => MonitorCheckCriterionType::BodyContains->value, 'text' => 'ready:true'],
        ],
    ]);

    $result = app(CheckMonitorAction::class)->handle($monitor);

    expect($result->status)->toBe(CheckStatus::Down)
        ->and($result->error_message)->toContain('contenu attendu');
});

test('it records an up result when multiple json and body criteria match', function () {
    Http::fake([
        'https://example.com/health' => Http::response(['status' => 'ok', 'meta' => ['region' => 'eu']], 200),
    ]);
    $monitor = Monitor::factory()->create([
        'url' => 'https://example.com/health',
        'check_criteria' => [
            ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 200],
            ['type' => MonitorCheckCriterionType::JsonPath->value, 'path' => 'status', 'expected' => 'ok'],
            ['type' => MonitorCheckCriterionType::JsonPath->value, 'path' => 'meta.region', 'expected' => 'eu'],
            ['type' => MonitorCheckCriterionType::BodyContains->value, 'text' => '"region":"eu"'],
        ],
    ]);

    $result = app(CheckMonitorAction::class)->handle($monitor);

    expect($result->status)->toBe(CheckStatus::Up);
});

test('it records a timeout result when the request cannot connect', function () {
    Http::fake([
        'https://example.com/health' => Http::failedConnection(),
    ]);
    $monitor = Monitor::factory()->create([
        'url' => 'https://example.com/health',
    ]);

    $result = app(CheckMonitorAction::class)->handle($monitor);
    $monitor->refresh();

    expect($result->status)->toBe(CheckStatus::Timeout)
        ->and($result->http_status)->toBeNull()
        ->and($result->response_time_ms)->toBeNull()
        ->and($result->error_message)->not->toBeNull()
        ->and($monitor->current_status)->toBe(MonitorStatus::Timeout);
});
