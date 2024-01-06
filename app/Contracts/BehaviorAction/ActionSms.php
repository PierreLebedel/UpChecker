<?php

namespace App\Contracts\BehaviorAction;

use App\Contracts\BehaviorActionInterface;

class ActionSms implements BehaviorActionInterface
{
    public function run(): void
    {
        dd('run sms');
    }
}
