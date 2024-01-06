<?php

namespace App\Livewire;

use App\Enums\BehaviorRuleEnum;
use Livewire\Component;

class BehaviorRulesRuleForm extends Component
{
    public $key;

    public $rule;

    public array $signs;

    public function mount($key, $rule)
    {
        $this->key = $key;

        $this->rule = array_merge([
            'id'            => null,
            'compare_field' => null,
            'compare_sign'  => null,
            'compare_value' => null,
        ], $rule);

        $this->updateCompareSigns();
    }

    public function updated($property)
    {
        if ($property == 'rule.compare_field') {
            $this->updateCompareSigns();
        }
    }

    public function updateCompareSigns()
    {
        $this->signs = [];

        if ($this->rule['compare_field']) {
            $enumCase = BehaviorRuleEnum::tryFrom($this->rule['compare_field']);
            if ($enumCase) {
                $this->signs = $enumCase->getSignsArray();
            }
        }
    }

    public function render()
    {
        return view('livewire.behavior-rules-rule-form');
    }
}
