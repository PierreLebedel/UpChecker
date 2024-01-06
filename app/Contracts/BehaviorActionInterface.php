<?php

namespace App\Contracts;

interface BehaviorActionInterface
{
    public function run(): void;

    public function formview(): ?string;
}
