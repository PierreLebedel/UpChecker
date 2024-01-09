<?php

namespace App\Enums;

use App\Contracts\AccountType\AccountTypeNexcloud;
use App\Contracts\AccountTypeInterface;
use App\Models\Rule;
use App\Contracts\BehaviorRuleInterface;
use App\Contracts\BehaviorRule\RuleResponseDelay;
use App\Contracts\BehaviorRule\RuleResponseIsJson;
use App\Contracts\BehaviorRule\RuleResponseStatusCode;
use App\Models\Account;

enum AccountTypeEnum: string
{
    case Nextcloud = 'Nextcloud';

    public function getDescription(): string
    {
        return match ($this) {
            default => $this->value,
        };
    }

    public function getClassName(): string
    {
        return match ($this) {
            self::Nextcloud => AccountTypeNexcloud::class,
        };
    }

    public function getInstance(Account $account): AccountTypeInterface
    {
        $className = $this->getClassName();
        return new $className($account);
    }
}
