<?php

namespace App\Contracts\BehaviorAction;

use App\Contracts\BehaviorActionInterface;

class ActionEmail implements BehaviorActionInterface
{
    public function run(): void
    {
        dd('run email');
    }
}
