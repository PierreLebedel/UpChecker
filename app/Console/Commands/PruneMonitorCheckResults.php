<?php

namespace App\Console\Commands;

use App\Models\CheckResult;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('monitors:prune-check-results')]
#[Description('Delete monitor check results older than one month')]
class PruneMonitorCheckResults extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $deleted = CheckResult::query()
            ->where('checked_at', '<', now()->subMonth())
            ->delete();

        $this->components->info("{$deleted} old check result(s) pruned.");

        return self::SUCCESS;
    }
}
