<?php

namespace App\Enums;

enum AlertTransition: string
{
    case UpToDown = 'up_to_down';
    case DownToUp = 'down_to_up';

    public function label(): string
    {
        return match ($this) {
            self::UpToDown => 'Panne détectée',
            self::DownToUp => 'Retour à la normale',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::UpToDown => 'Quand une URL passe de OK vers un état en erreur.',
            self::DownToUp => 'Quand une URL revient en OK après une erreur.',
        };
    }

    /**
     * @return array<int, self>
     */
    public static function defaultsFor(AlertChannel $channel): array
    {
        $configuredTransitions = config("upchecker.notifications.channels.{$channel->value}.transitions", [
            self::UpToDown->value,
        ]);

        if (! is_array($configuredTransitions)) {
            return [self::UpToDown];
        }

        $transitions = array_values(array_filter(array_map(
            fn (mixed $transition): ?self => is_string($transition) ? self::tryFrom($transition) : null,
            $configuredTransitions,
        )));

        return $transitions === [] ? [self::UpToDown] : $transitions;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $transition): string => $transition->value,
            self::cases(),
        );
    }
}
