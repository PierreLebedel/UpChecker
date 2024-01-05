<?php

namespace App\Enums;

enum BehaviorRuleEnum: string
{
    case ResponseStatusCode = 'ResponseStatusCode';
    case ResponseDelay = 'ResponseDelay';

    public function getSignsArray(): array
    {
        return match ($this) {
            self::ResponseStatusCode => ['=', '!='],
            self::ResponseDelay      => ['<', '<=', '=', '!=', '>=', '>'],
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ResponseStatusCode => __('Response Status Code'),
            self::ResponseDelay      => __('Response Delay'),
        };
    }

    
}
