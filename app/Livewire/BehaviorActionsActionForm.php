<?php

namespace App\Livewire;

use Livewire\Component;

class BehaviorActionsActionForm extends Component
{
    public $key;

    public $action;

    public array $signs;

    public function mount($key, $action)
    {
        $this->key = $key;

        $this->action = array_merge([
            'id' => null,
            'type' => null,
        ], $action);
    }

    public function render()
    {
        return view('livewire.behavior-actions-action-form');
    }
}
