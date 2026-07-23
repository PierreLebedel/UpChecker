<?php

namespace App\Support;

use App\Enums\AlertChannel;
use App\Models\User;
use App\Notifications\NotificationChannelTestNotification;
use Throwable;

class NotificationChannelTester
{
    public function test(User $user, AlertChannel $channel): NotificationChannelTestResult
    {
        if (! $channel->isAvailable()) {
            return NotificationChannelTestResult::failed('Ce canal de notification n’est pas activé.');
        }

        if (! $channel->canTestConnection()) {
            return NotificationChannelTestResult::failed('Le test de connexion n’est pas encore disponible pour ce canal.');
        }

        return match ($channel) {
            AlertChannel::Mail, AlertChannel::Telegram => $this->testLaravelNotification($user, $channel),
            AlertChannel::Sms => NotificationChannelTestResult::failed('Le test SMS n’est pas encore disponible.'),
        };
    }

    private function testLaravelNotification(User $user, AlertChannel $channel): NotificationChannelTestResult
    {
        try {
            $user->notify(new NotificationChannelTestNotification($channel));
        } catch (Throwable $exception) {
            return NotificationChannelTestResult::failed($exception->getMessage());
        }

        return NotificationChannelTestResult::succeeded("Notification de test envoyée via {$channel->label()}.");
    }
}
