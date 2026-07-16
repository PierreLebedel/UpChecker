<?php

namespace App\Enums;

enum CheckStatus: string
{
    case Up = 'up';
    case Down = 'down';
    case Timeout = 'timeout';
    case Invalid = 'invalid';

    public function isFailure(): bool
    {
        return $this !== self::Up;
    }
}
