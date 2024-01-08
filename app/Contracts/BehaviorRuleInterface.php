<?php

namespace App\Contracts;

use App\Models\Checkup;
use App\Models\Rule;

interface BehaviorRuleInterface
{
    public function __construct(Rule $rule);

    public function toString(): string;

    public static function getSignsArray(): array;

    public static function getFormView(): ?string;

    public function check(Checkup $checkup): bool;
}
