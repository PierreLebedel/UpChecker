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

    public function label(): string
    {
        return match ($this) {
            self::Unknown => 'Nouveau',
            self::Up => 'OK',
            self::Down => 'Indisponible',
            self::Timeout => 'Timeout',
            self::Invalid => 'Invalide',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Unknown => 'zinc',
            self::Up => 'emerald',
            self::Down => 'rose',
            self::Timeout => 'orange',
            self::Invalid => 'violet',
        };
    }

    public function priority(): int
    {
        return match ($this) {
            self::Down => 50,
            self::Timeout => 40,
            self::Invalid => 30,
            self::Unknown => 20,
            self::Up => 10,
        };
    }
}
