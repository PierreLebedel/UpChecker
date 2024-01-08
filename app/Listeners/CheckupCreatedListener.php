<?php

namespace App\Listeners;

use App\Events\CheckupCreatedEvent;
use App\Jobs\CheckupBehaviorActionJob;
use App\Services\CheckupBehaviorRulesService;

class CheckupCreatedListener
{
    public function __construct()
    {
        //
    }

    public function handle(CheckupCreatedEvent $event): void
    {
        if ($event->endpoint->behaviors->isEmpty()) {
            return;
        }

        foreach ($event->endpoint->behaviors as $behavior) {

            if ($behavior->actions->isEmpty()) {
                continue;
            }

            $service = new CheckupBehaviorRulesService($behavior, $event->checkup);

            if (! $service->validateRules()) {
                continue;
            }

            foreach ($behavior->actions as $action) {
                CheckupBehaviorActionJob::dispatch($action, $event->checkup);
            }
        }
    }
}
