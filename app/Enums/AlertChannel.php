<?php

namespace App\Enums;

enum AlertChannel: string
{
    case Mail = 'mail';
    case Telegram = 'telegram';
    case Sms = 'sms';

    public function label(): string
    {
        return match ($this) {
            self::Mail => 'Mail',
            self::Telegram => 'Telegram',
            self::Sms => 'SMS',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Mail => 'Recevoir les alertes sur votre adresse email.',
            self::Telegram => 'Recevoir les alertes dans un chat Telegram configuré côté serveur.',
            self::Sms => 'Recevoir les alertes par SMS lorsqu’un connecteur sera configuré.',
        };
    }

    public function isAvailable(): bool
    {
        return (bool) config("upchecker.notifications.channels.{$this->value}.enabled", false);
    }

    public function canTestConnection(): bool
    {
        return (bool) config("upchecker.notifications.channels.{$this->value}.testable", false);
    }

    /**
     * @return array<int, AlertTransition>
     */
    public function defaultTransitions(): array
    {
        return AlertTransition::defaultsFor($this);
    }

    /**
     * @return array<int, string>
     */
    public function defaultTransitionValues(): array
    {
        return array_map(
            fn (AlertTransition $transition): string => $transition->value,
            $this->defaultTransitions(),
        );
    }

    /**
     * @return array<int, self>
     */
    public static function available(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $channel): bool => $channel->isAvailable(),
        ));
    }
}
