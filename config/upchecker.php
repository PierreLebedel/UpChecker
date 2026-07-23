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
                'enabled' => env('TELEGRAM_BOT_TOKEN', null),
                'testable' => true,
                'transitions' => [
                    AlertTransition::UpToDown->value,
                    AlertTransition::DownToUp->value,
                ],
                'bot_token' => env('TELEGRAM_BOT_TOKEN', null),
                'chat_id' => env('TELEGRAM_CHAT_ID', null),
            ],

            'sms' => [
                'enabled' => env('UPCHECKER_NOTIFICATIONS_SMS_ENABLED', false),
                'testable' => false,
                'transitions' => [AlertTransition::UpToDown->value],
            ],
        ],
    ],
];
