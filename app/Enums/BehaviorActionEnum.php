<?php

namespace App\Enums;

use App\Contracts\BehaviorAction\ActionEmail;
use App\Contracts\BehaviorAction\ActionSms;
use App\Contracts\BehaviorActionInterface;

enum BehaviorActionEnum: string
{
    case Email = 'Email';
    case Sms = 'SMS';

    public function getDescription(): string
    {
        return match ($this) {
            self::Email    => __('Send email'),
            self::Sms      => __('Send SMS'),
        };
    }

    public function getInstance(): BehaviorActionInterface
    {
        return match ($this) {
            self::Email    => new ActionEmail(),
            self::Sms      => new ActionSms(),
        };
    }
}
