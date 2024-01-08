<?php

namespace App\Jobs;

use App\Models\Action;
use App\Models\Checkup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckupBehaviorActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Action $action, public Checkup $checkup)
    {
        //
    }

    public function handle(): void
    {
        $this->action->type->getInstance($this->action)->run($this->checkup);
    }
}
