<?php

namespace App\Support;

readonly class NotificationChannelTestResult
{
    private function __construct(
        public bool $successful,
        public string $message,
    ) {}

    public static function succeeded(string $message): self
    {
        return new self(true, $message);
    }

    public static function failed(string $message): self
    {
        return new self(false, $message);
    }
}
