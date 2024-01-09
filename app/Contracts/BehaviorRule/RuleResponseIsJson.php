<?php

namespace App\Contracts\BehaviorRule;

use App\Models\Checkup;

class RuleResponseIsJson extends AbstractRuleBoolean
{

    public function toString(): string
    {
        $negation = '';
        if (array_key_exists('boolean', $this->rule->params) && !$this->rule->params['boolean']) {
            $negation .= ' not';
        }
        return "Response is".$negation." JSON";
    }

    public function check(Checkup $checkup): bool
    {
        if(empty($checkup->response_body)) return false;
        json_decode($checkup->response_body);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
