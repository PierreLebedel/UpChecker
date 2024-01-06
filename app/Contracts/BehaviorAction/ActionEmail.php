<?php

namespace App\Contracts\BehaviorAction;

class ActionEmail extends AbstractAction
{
    public function run(): void
    {
        dd('run email');
    }

    public function formView(): ?string
    {
        return 'project.endpoint.behavior.actions.action-email';
    }

    public function toString(): string
    {
        return __('Email to ').$this->action->params['email_to'];
    }
}
