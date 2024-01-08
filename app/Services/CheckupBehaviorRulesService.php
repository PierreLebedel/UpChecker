<?php

namespace App\Services;

use App\Models\Behavior;
use App\Models\Checkup;
use App\Models\Endpoint;
use App\Models\Rule;
use Illuminate\Support\Collection;

class CheckupBehaviorRulesService
{
    public Checkup $checkup;

    public Endpoint $endpoint;

    public Behavior $behavior;

    public Collection $rules;

    public function __construct(Behavior $behavior, Checkup $checkup)
    {
        $this->checkup = $checkup;
        $this->behavior = $behavior;
        $this->rules = $this->behavior->rules;
    }

    public function validateRules(): bool
    {
        if ($this->rules->isEmpty()) {
            return true;
        }

        $invalidatedRules = collect();

        foreach ($this->rules as $rule) {

            if (! $this->validateRule($rule)) {
                $invalidatedRules->push($rule);
            }

        }

        return $invalidatedRules->isEmpty();
    }

    public function validateRule(Rule $rule): bool
    {
        return $rule->type->getInstance($rule)->check($this->checkup);
    }
}
