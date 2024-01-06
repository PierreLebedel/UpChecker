<?php

namespace App\Contracts;

use App\Models\Action;

interface BehaviorActionInterface
{
    public function __construct(Action $action);

    public function run(): void;

    public function formView(): ?string;

    public function toString(): string;
}
