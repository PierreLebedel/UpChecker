<?php

namespace App\Contracts\BehaviorRule;

use App\Models\Checkup;

class RuleResponseDelay extends AbstractRuleValueComparator
{
    public static function getSignsArray(): array
    {
        return ['<', '<=', '>=', '>'];
    }

    public function check(Checkup $checkup): bool
    {
        $microtime = floatval($checkup->microtime);
        $compare = floatval($this->getValue());

        switch ($this->getSign()) {
            case '<':
                if ($microtime < $compare) {
                    return true;
                }
                break;
            case '<=':
                if ($microtime <= $compare) {
                    return true;
                }
                break;
            case '>=':
                if ($microtime >= $compare) {
                    return true;
                }
                break;
            case '>':
                if ($microtime > $compare) {
                    return true;
                }
                break;
        }

        return false;
    }
}
