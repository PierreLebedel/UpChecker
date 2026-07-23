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

    public function label(): string
    {
        return match ($this) {
            self::Up => 'OK',
            self::Down => 'Indisponible',
            self::Timeout => 'Timeout',
            self::Invalid => 'Réponse invalide',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Up => 'emerald',
            self::Down => 'rose',
            self::Timeout => 'rose',
            self::Invalid => 'orange',
        };
    }
}
