<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('monitors:dispatch-due-checks')
    ->everyMinute()
    ->withoutOverlapping(5);

Schedule::command('monitors:prune-check-results')
    ->daily()
    ->withoutOverlapping(30);
