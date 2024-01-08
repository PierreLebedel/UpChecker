<?php

namespace App\Contracts\BehaviorRule;

use App\Models\Checkup;

class RuleResponseStatusCode extends AbstractRuleValueComparator
{
    public static function getSignsArray(): array
    {
        return ['=', '!='];
    }

    public function check(Checkup $checkup): bool
    {
        $diff = strcmp($checkup->status_code, $this->getValue());

        if ($this->getSign() == '!=' && $diff != 0) {
            return true;
        } elseif ($this->getSign() == '=' && $diff == 0) {
            return true;
        }

        return false;
    }
}
