<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;

afterEach(function (): void {
    Carbon::setTestNow();
});

test('relative time renders very recent dates as now', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $html = Blade::render('<x-relative-time :date="$date" />', [
        'date' => now()->subSeconds(6),
    ]);

    expect($html)
        ->toContain('maintenant')
        ->not->toContain('il y a 6 secondes');
});
