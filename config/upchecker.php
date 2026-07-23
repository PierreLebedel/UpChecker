<?php

use App\Enums\AlertTransition;

return [
    'registration_enabled' => env('UPCHECKER_REGISTRATION_ENABLED', true),

    'notifications' => [
        'channels' => [
            'mail' => [
                'enabled' => env('UPCHECKER_NOTIFICATIONS_MAIL_ENABLED', true),
                'testable' => true,
                'transitions' => [AlertTransition::UpToDown->value],
            ],

            'telegram' => [
                'enabled' => env('TELEGRAM_BOT_TOKEN') !== null
                    && env('TELEGRAM_BOT_TOKEN') !== ''
                    && env('TELEGRAM_CHAT_ID') !== null
                    && env('TELEGRAM_CHAT_ID') !== '',
                'testable' => true,
                'transitions' => [
                    AlertTransition::UpToDown->value,
                    AlertTransition::DownToUp->value,
                ],
                'bot_token' => env('TELEGRAM_BOT_TOKEN', null),
                'chat_id' => env('TELEGRAM_CHAT_ID', null),
            ],
        ],
    ],
];
