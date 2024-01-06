<?php

namespace App\Enums;

enum EndpointCheckupDelayEnum: int
{
    case everyMinute = 1;
    case everyTwoMinutes = 2;
    case everyFiveMinutes = 5;
    case everyTenMinutes = 10;
    case everyFifteenMinutes = 15;
    case everyThirtyMinutes = 30;
    case everyHour = 60;

    public function getDescription(): string
    {
        return match ($this) {
            self::everyMinute         => __('Every minute'),
            self::everyTwoMinutes     => __('Every 2 minutes'),
            self::everyFiveMinutes    => __('Every 5 minutes'),
            self::everyTenMinutes     => __('Every 10 minutes'),
            self::everyFifteenMinutes => __('Every 15 minutes'),
            self::everyThirtyMinutes  => __('Every 30 minutes'),
            self::everyHour           => __('Every hour'),
        };
    }

    public function schedulingFrequencyMethod(): string
    {
        return match ($this) {
            self::everyMinute         => 'everyMinute',
            self::everyTwoMinutes     => 'everyTwoMinutes',
            self::everyFiveMinutes    => 'everyFiveMinutes',
            self::everyTenMinutes     => 'everyTenMinutes',
            self::everyFifteenMinutes => 'everyFifteenMinutes',
            self::everyThirtyMinutes  => 'everyThirtyMinutes',
            self::everyHour           => 'hourly',
        };
    }
}
