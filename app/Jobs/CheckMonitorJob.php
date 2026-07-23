<?php

namespace App\Jobs;

use App\Actions\CheckMonitorAction;
use App\Models\Monitor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class CheckMonitorJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $monitorId)
    {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(CheckMonitorAction $checkMonitor): void
    {
        $monitor = Monitor::query()->find($this->monitorId);

        if (! $monitor instanceof Monitor || ! $monitor->enabled) {
            return;
        }

        $checkMonitor->handle($monitor);
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("monitor-check:{$this->monitorId}"))
                ->dontRelease()
                ->expireAfter(120),
        ];
    }
}
