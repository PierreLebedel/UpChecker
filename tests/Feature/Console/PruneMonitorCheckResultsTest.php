<?php

use App\Models\CheckResult;
use App\Models\Monitor;
use Illuminate\Support\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

test('it prunes check results older than one month', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));
    $monitor = Monitor::factory()->create();
    $oldResult = CheckResult::factory()->for($monitor)->create([
        'checked_at' => now()->subMonth()->subSecond(),
    ]);
    $recentResult = CheckResult::factory()->for($monitor)->create([
        'checked_at' => now()->subMonth()->addSecond(),
    ]);

    $this->artisan('monitors:prune-check-results')
        ->assertSuccessful();

    expect($oldResult->fresh())->toBeNull()
        ->and($recentResult->fresh())->not->toBeNull();
});
