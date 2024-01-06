<?php

namespace App\Contracts\BehaviorAction;

use App\Contracts\BehaviorActionInterface;
use App\Models\Action;

abstract class AbstractAction implements BehaviorActionInterface
{
    protected $action;

    public function __construct(Action $action)
    {
        $this->action = $action;
    }

    public function formView(): ?string
    {
        return null;
    }

    public function toString(): string
    {
        return $this->action->type->value;
    }
}
