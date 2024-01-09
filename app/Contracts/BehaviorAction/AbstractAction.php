<?php

namespace App\Contracts\BehaviorAction;

use App\Models\Action;
use App\Models\Checkup;
use Illuminate\Support\Carbon;
use App\Contracts\AccountTypeInterface;
use App\Contracts\BehaviorActionInterface;

abstract class AbstractAction implements BehaviorActionInterface
{
    protected $action;
    protected $account = null;

    public function __construct(Action $action)
    {
        $this->action = $action;
    }

    public static function needAccountType(): ?string
    {
        return null;
    }

    public function toString(): string
    {
        $string = $this->action->type->value;
        if($this->needAccountType() && $this->action->account){
            $string .= ' ('.$this->action->account->name.')';
        }
        return $string;
    }

    public function getFormView(): ?string
    {
        return null;
    }

    public function afterRun(Checkup $checkup): void
    {
        $actions = $checkup->actions ?? [];

        $actions[ $this->action->id ] = Carbon::now()->toDateTimeString();

        $checkup->update([
            'actions' => $actions,
        ]);
    }
}
