<?php

namespace App\Livewire;

use App\Enums\BehaviorRuleEnum;
use App\Models\Rule;
use Livewire\Component;

class BehaviorRulesRuleForm extends Component
{
    public $customkey;

    public $rule;

    public ?string $type;

    public ?array $params;

    public ?string $partialFormView = null;

    public function mount(string $customkey, Rule $rule)
    {
        $this->customkey = $customkey;

        $this->rule = $rule;

        $this->type = $this->rule->type->value ?? null;

        $this->updatedType();
    }

    public function updatedType()
    {
        $this->partialFormView = null;

        if ($this->type) {
            $enumCase = BehaviorRuleEnum::tryFrom($this->type);
            if ($enumCase) {
                $this->rule->type = $enumCase;
                $this->partialFormView = $enumCase->getInstance($this->rule)->getFormView();
            }
        }
    }

    public function render()
    {
        return view('livewire.behavior-rules-rule-form');
    }
}
