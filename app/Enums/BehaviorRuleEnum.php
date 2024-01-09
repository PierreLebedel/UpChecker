<?php

namespace App\Enums;

use App\Models\Rule;
use App\Contracts\BehaviorRuleInterface;
use App\Contracts\BehaviorRule\RuleResponseDelay;
use App\Contracts\BehaviorRule\RuleResponseIsJson;
use App\Contracts\BehaviorRule\RuleResponseStatusCode;

enum BehaviorRuleEnum: string
{
    case ResponseStatusCode = 'ResponseStatusCode';
    case ResponseDelay = 'ResponseDelay';
    case RuleResponseIsJson = 'RuleResponseIsJson';

    public function getDescription(): string
    {
        return match ($this) {
            self::ResponseStatusCode => __('Response Status Code'),
            self::ResponseDelay      => __('Response Delay'),
            self::RuleResponseIsJson => __('Response is JSON'),
        };
    }

    private function getClassName(): string
    {
        return match ($this) {
            self::ResponseStatusCode => RuleResponseStatusCode::class,
            self::ResponseDelay      => RuleResponseDelay::class,
            self::RuleResponseIsJson => RuleResponseIsJson::class,
        };
    }

    public function getSignsArray(): array
    {
        $className = $this->getClassName();
        return $className::getSignsArray();
    }

    public function getInstance(Rule $rule): BehaviorRuleInterface
    {
        $className = $this->getClassName();
        return new $className($rule);
    }
}
