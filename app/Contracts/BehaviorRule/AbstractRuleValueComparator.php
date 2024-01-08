<?php

namespace App\Contracts\BehaviorRule;

abstract class AbstractRuleValueComparator extends AbstractRule
{
    public function toString(): string
    {
        $string = $this->rule->type->value;
        if (array_key_exists('sign', $this->rule->params)) {
            $string .= ' '.$this->rule->params['sign'];
        }
        if (array_key_exists('value', $this->rule->params)) {
            $string .= ' '.$this->rule->params['value'];
        }

        return $string;
    }

    public function getSign(): ?string
    {
        return $this->rule->params['sign'] ?? null;
    }

    public function getValue(): ?string
    {
        return $this->rule->params['value'] ?? null;
    }

    public static function getSignsArray(): array
    {
        return ['='];
    }

    public static function getFormView(): ?string
    {
        return 'project.endpoint.behavior.rules.rule-input-text';
    }
}
