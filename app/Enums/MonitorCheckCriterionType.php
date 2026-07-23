<?php

namespace App\Enums;

enum MonitorCheckCriterionType: string
{
    case HttpStatus = 'http_status';
    case JsonPath = 'json_path';
    case BodyContains = 'body_contains';

    public function label(): string
    {
        return match ($this) {
            self::HttpStatus => 'Code HTTP',
            self::JsonPath => 'Champ JSON',
            self::BodyContains => 'Texte dans la réponse',
        };
    }
}
