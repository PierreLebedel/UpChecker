<?php

namespace App\Console;

use App\Jobs\EndpointCheckJob;
use App\Models\Endpoint;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        /**
         * Warning: the schedule:work command dont execute job,
         * but only add it to the queue,
         * which be executed by queue:listen;
         */
        $endpoints = Endpoint::all();

        foreach ($endpoints as $endpoint) {
            $frequencyMethod = $endpoint->checkup_delay->schedulingFrequencyMethod();
            $schedule->job(new EndpointCheckJob($endpoint))->{$frequencyMethod}();
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
