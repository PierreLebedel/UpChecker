<?php

namespace App\Notifications;

use App\Enums\AlertChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class NotificationChannelTestNotification extends Notification
{
    use Queueable;

    public function __construct(
        public AlertChannel $channel,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return match ($this->channel) {
            AlertChannel::Mail => ['mail'],
            AlertChannel::Telegram => ['telegram'],
        };
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Test des notifications UpChecker')
            ->greeting('UpChecker')
            ->line('Ceci est un message de test pour vérifier la configuration des alertes par mail.')
            ->line('Si vous recevez ce message, le canal mail est opérationnel.');
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        return TelegramMessage::create()
            ->to((string) config('upchecker.notifications.channels.telegram.chat_id'))
            ->normal()
            ->content('Test des notifications UpChecker.');
    }
}
