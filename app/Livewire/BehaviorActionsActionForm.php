<?php

namespace App\Livewire;

use App\Enums\BehaviorActionEnum;
use App\Models\Action;
use Livewire\Component;

class BehaviorActionsActionForm extends Component
{
    public string $customkey;

    public Action $action;

    public ?string $type;

    public ?string $partialFormView = null;

    public function mount(string $customkey, Action $action)
    {
        $this->customkey = $customkey;

        $this->action = $action;

        $this->type = $this->action->type->value ?? null;

        $this->updateFormView();
    }

    public function updated($property)
    {
        if ($property == 'type') {
            $this->updateFormView();
        }
    }

    public function updateFormView()
    {
        $this->partialFormView = null;

        if ($this->type) {
            $enumCase = BehaviorActionEnum::tryFrom($this->type);
            if ($enumCase) {
                $this->partialFormView = $enumCase->getInstance($this->action)->getFormView();
            }
        }
    }

    public function render()
    {
        return view('livewire.behavior-actions-action-form');
    }
}
