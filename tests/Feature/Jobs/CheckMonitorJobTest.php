<?php

use App\Actions\CheckMonitorAction;
use App\Enums\MonitorStatus;
use App\Jobs\CheckMonitorJob;
use App\Models\CheckResult;
use App\Models\Monitor;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('it checks an enabled monitor', function () {
    Http::fake([
        'https://example.com/health' => Http::response('ok', 200),
    ]);
    $monitor = Monitor::factory()->create([
        'url' => 'https://example.com/health',
    ]);

    app(CheckMonitorJob::class, ['monitorId' => $monitor->id])->handle(app(CheckMonitorAction::class));

    expect(CheckResult::query()->whereBelongsTo($monitor)->count())->toBe(1)
        ->and($monitor->refresh()->current_status)->toBe(MonitorStatus::Up);
});

test('it ignores disabled monitors', function () {
    $monitor = Monitor::factory()->disabled()->create([
        'url' => 'https://example.com/health',
    ]);

    app(CheckMonitorJob::class, ['monitorId' => $monitor->id])->handle(app(CheckMonitorAction::class));

    expect(CheckResult::query()->whereBelongsTo($monitor)->count())->toBe(0);
});

test('it ignores missing monitors', function () {
    app(CheckMonitorJob::class, ['monitorId' => '01981e42-5f4d-70bf-9e4f-a1e88bd2bc66'])
        ->handle(app(CheckMonitorAction::class));

    expect(CheckResult::query()->count())->toBe(0);
});
