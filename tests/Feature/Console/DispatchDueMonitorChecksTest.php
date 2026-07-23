<?php

use App\Jobs\CheckMonitorJob;
use App\Models\Monitor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;

afterEach(function () {
    Carbon::setTestNow();
});

test('it dispatches queued checks for due enabled monitors', function () {
    $now = Carbon::parse('2026-07-16 12:00:00');
    Carbon::setTestNow($now);
    Queue::fake();

    $due = Monitor::factory()->due()->create(['interval_minutes' => 15]);
    Monitor::factory()->notDue()->create();
    Monitor::factory()->disabled()->due()->create();

    $this->artisan('monitors:dispatch-due-checks')
        ->assertSuccessful();

    Queue::assertPushed(CheckMonitorJob::class, fn (CheckMonitorJob $job): bool => $job->monitorId === $due->id);
    Queue::assertPushed(CheckMonitorJob::class, 1);

    expect($due->refresh()->next_check_at->lessThanOrEqualTo($now))->toBeTrue();
});

test('it limits the number of dispatched monitor checks', function () {
    Queue::fake();

    Monitor::factory()->count(3)->due()->create();

    $this->artisan('monitors:dispatch-due-checks --limit=2')
        ->assertSuccessful();

    Queue::assertPushed(CheckMonitorJob::class, 2);
});
