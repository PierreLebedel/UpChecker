<?php

namespace App\Contracts\BehaviorAction;

use App\Mail\CheckupActionEmailMailable;
use App\Models\Checkup;
use Illuminate\Support\Facades\Mail;

class ActionEmail extends AbstractAction
{
    public function run(Checkup $checkup): void
    {
        Mail::send(new CheckupActionEmailMailable($this->action, $checkup));
        $this->afterRun($checkup);
    }

    public function getFormView(): ?string
    {
        return 'project.endpoint.behavior.actions.action-email';
    }

    public function toString(): string
    {
        return __('Email to ').$this->action->params['email_to'];
    }
}
