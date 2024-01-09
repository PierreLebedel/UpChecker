<?php

namespace App\Contracts;

use App\Models\Action;
use App\Models\Checkup;

interface BehaviorActionInterface
{
    public function __construct(Action $action);

    public static function needAccountType(): ?string;

    public function run(Checkup $checkup): void;

    public function afterRun(Checkup $checkup): void;

    public function getFormView(): ?string;

    public function toString(): string;
}
