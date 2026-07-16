<?php

namespace App\Enums;

enum MonitorStatus: string
{
    case Unknown = 'unknown';
    case Up = 'up';
    case Down = 'down';
    case Timeout = 'timeout';
    case Invalid = 'invalid';

    public function isFailure(): bool
    {
        return in_array($this, [self::Down, self::Timeout, self::Invalid], true);
    }
}
