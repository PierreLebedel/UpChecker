<?php

namespace App\Contracts\BehaviorRule;

abstract class AbstractRuleBoolean extends AbstractRule
{
    public function toString(): string
    {
        $inverseSign = '!';
        if (array_key_exists('boolean', $this->rule->params) && $this->rule->params['boolean']) {
            $inverseSign .= '';
        }
        return $inverseSign.$this->rule->type->value;
    }

    public function getBoolean(): bool
    {
        return $this->rule->params['boolean'] ?? true;
    }

    public static function getFormView(): ?string
    {
        return 'project.endpoint.behavior.rules.rule-select-boolean';
    }
}
