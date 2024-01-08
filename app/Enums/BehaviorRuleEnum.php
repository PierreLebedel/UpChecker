<?php

namespace App\Enums;

use App\Contracts\BehaviorRule\RuleResponseDelay;
use App\Contracts\BehaviorRule\RuleResponseStatusCode;
use App\Contracts\BehaviorRuleInterface;
use App\Models\Rule;

enum BehaviorRuleEnum: string
{
    case ResponseStatusCode = 'ResponseStatusCode';
    case ResponseDelay = 'ResponseDelay';

    public function getDescription(): string
    {
        return match ($this) {
            self::ResponseStatusCode => __('Response Status Code'),
            self::ResponseDelay      => __('Response Delay'),
        };
    }

    private function getClassName(): string
    {
        return match ($this) {
            self::ResponseStatusCode => RuleResponseStatusCode::class,
            self::ResponseDelay      => RuleResponseDelay::class,
        };
    }

    public function getSignsArray(): array
    {
        $className = $this->getClassName();

        return $className::getSignsArray();

        return match ($this) {
            self::ResponseStatusCode => ['=', '!='],
            self::ResponseDelay      => ['<', '<=', '=', '!=', '>=', '>'],
        };
    }

    public function getInstance(Rule $rule): BehaviorRuleInterface
    {
        $className = $this->getClassName();

        return new $className($rule);
    }
}
