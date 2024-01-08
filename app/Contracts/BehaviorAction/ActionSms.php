<?php

namespace App\Contracts\BehaviorAction;

use App\Models\Checkup;

class ActionSms extends AbstractAction
{
    public function run(Checkup $checkup): void
    {
        dd('@todo send SMS');
    }
}
