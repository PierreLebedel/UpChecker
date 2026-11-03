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
    public function __construct(
        public string $monitorId,
        public ?string $failureDetectedAt = null,
    ) {
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

        if (($this->failureDetectedAt ?? null) !== null && (
            $monitor->failure_detected_at?->toIso8601String() !== $this->failureDetectedAt
            || $monitor->failure_confirmed_at !== null
            || $monitor->failure_detected_at->copy()->addMinute()->isFuture()
        )) {
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
