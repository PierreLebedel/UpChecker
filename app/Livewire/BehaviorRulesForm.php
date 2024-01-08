<?php

namespace App\Livewire;

use App\Models\Behavior;
use App\Models\Rule;
use Illuminate\Support\Collection;
use Livewire\Component;

class BehaviorRulesForm extends Component
{
    public $behavior;

    public Collection $rules;

    public function mount(Behavior $behavior)
    {
        $this->behavior = $behavior;

        $this->rules = collect();

        if ($this->behavior->rules->isEmpty()) {
            $this->addRule();
        } else {
            foreach ($this->behavior->rules as $rule) {
                $this->addRule($rule);
            }
        }
    }

    public function addRule(?Rule $rule = null)
    {
        if (! $rule) {
            $rule = Rule::make();
        }

        $this->rules->put(uniqid(), $rule);
    }

    public function removeRule($key)
    {
        dump($this->rules);

        $this->rules->pull($key);

        dd($this->rules);
    }

    public function render()
    {
        return view('livewire.behavior-rules-form');
    }
}
