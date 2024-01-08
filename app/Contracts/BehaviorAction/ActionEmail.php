<?php

namespace App\Contracts\BehaviorAction;

use App\Models\Checkup;
use Illuminate\Support\Facades\Mail;
use App\Mail\CheckupActionEmailMailable;

class ActionEmail extends AbstractAction
{
    public function run(Checkup $checkup): void
    {
        Mail::send( new CheckupActionEmailMailable($this->action, $checkup) );
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
