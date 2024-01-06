<?php

namespace App\Livewire;

use App\Models\Action;
use App\Models\Behavior;
use Illuminate\Support\Collection;
use Livewire\Component;

class BehaviorActionsForm extends Component
{
    public $behavior;

    public Collection $actions;

    public function mount(Behavior $behavior)
    {
        $this->behavior = $behavior;

        $this->actions = collect();

        if ($this->behavior->actions->isEmpty()) {
            $this->addAction();
        } else {
            foreach ($this->behavior->actions as $action) {
                $this->addAction($action);
            }
        }
    }

    public function addAction(?Action $action = null)
    {
        if (! $action) {
            $action = Action::make();
        }

        $this->actions->push($action->toArray());
    }

    public function removeAction($key)
    {
        $this->actions->pull($key);
    }

    public function render()
    {
        return view('livewire.behavior-actions-form');
    }
}
