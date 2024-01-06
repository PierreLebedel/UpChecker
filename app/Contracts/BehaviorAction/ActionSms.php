<?php

namespace App\Contracts\BehaviorAction;

class ActionSms extends AbstractAction
{
    public function run(): void
    {
        dd('run sms');
    }
}
