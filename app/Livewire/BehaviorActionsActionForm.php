<?php

namespace App\Livewire;

use Livewire\Component;
use App\Enums\BehaviorActionEnum;

class BehaviorActionsActionForm extends Component
{
    public $key;

    public $action;

    public ?string $partialFormView = null;

    public function mount($key, $action)
    {
        $this->key = $key;

        $this->action = array_merge([
            'id'   => null,
            'type' => null,
        ], $action);

        $this->updateFormView();
    }

    public function updated($property)
    {
        if ($property == 'action.type') {
            $this->updateFormView();
        }
    }

    public function updateFormView()
    {
        $this->partialFormView = null;

        if ($this->action['type']) {
            $enumCase = BehaviorActionEnum::tryFrom($this->action['type']);
            if ($enumCase) {
                $this->partialFormView = $enumCase->getInstance()->formView();
            }
        }
    }

    public function render()
    {
        return view('livewire.behavior-actions-action-form');
    }
}
