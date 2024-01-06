<?php

namespace App\Contracts\BehaviorAction;

use App\Contracts\BehaviorActionInterface;

abstract class AbstractAction implements BehaviorActionInterface
{

    public function formView(): ?string
    {
        return null;
    }

}
