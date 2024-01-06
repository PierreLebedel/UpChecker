<?php

namespace App\Contracts\BehaviorAction;

use App\Contracts\BehaviorAction\AbstractAction;

class ActionSms extends AbstractAction
{
    public function run(): void
    {
        dd('run sms');
    }
}
