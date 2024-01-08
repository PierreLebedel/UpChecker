<?php

namespace App\Contracts\BehaviorRule;

use App\Contracts\BehaviorRuleInterface;
use App\Models\Rule;

abstract class AbstractRule implements BehaviorRuleInterface
{
    protected $rule;

    public function __construct(Rule $rule)
    {
        $this->rule = $rule;
    }

    public function toString(): string
    {
        return $this->rule->type->value;
    }

    public static function getSignsArray(): array
    {
        return [];
    }

    public static function getFormView(): ?string
    {
        return null;
    }
}
