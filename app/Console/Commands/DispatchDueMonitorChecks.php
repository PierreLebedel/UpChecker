<?php

namespace App\Console\Commands;

use App\Jobs\CheckMonitorJob;
use App\Models\Monitor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('monitors:dispatch-due-checks {--limit=100 : Maximum number of due monitors to dispatch}')]
#[Description('Dispatch queued checks for monitors that are due')]
class DispatchDueMonitorChecks extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $dispatched = 0;

        Monitor::query()
            ->due()
            ->orderBy('next_check_at')
            ->limit($limit)
            ->get()
            ->each(function (Monitor $monitor) use (&$dispatched): void {
                CheckMonitorJob::dispatch($monitor->id);

                $dispatched++;
            });

        $this->components->info("{$dispatched} monitor check(s) dispatched.");

        return self::SUCCESS;
    }
}
