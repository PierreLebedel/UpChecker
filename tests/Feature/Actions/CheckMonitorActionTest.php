<?php

use App\Actions\CheckMonitorAction;
use App\Enums\CheckStatus;
use App\Enums\MonitorCheckCriterionType;
use App\Enums\MonitorStatus;
use App\Events\MonitorCheckCompleted;
use App\Models\Monitor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
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
