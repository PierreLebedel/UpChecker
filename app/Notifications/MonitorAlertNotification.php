<?php

namespace App\Notifications;

use App\Enums\AlertChannel;
use App\Enums\AlertTransition;
use App\Models\CheckResult;
use App\Models\Monitor;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class MonitorAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public CheckResult $checkResult,
        public AlertTransition $transition,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if (! $notifiable instanceof User) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (AlertChannel $channel): ?string => $this->notificationChannel($channel),
            $notifiable->alertChannels($this->transition),
        )));
    }

    public function toMail(object $notifiable): MailMessage
    {
        $monitor = $this->monitor();

        return (new MailMessage)
            ->subject($this->mailSubject($monitor))
            ->greeting('UpChecker')
            ->line($this->introLine($monitor))
            ->line("Projet : {$monitor->project->name}")
            ->line("URL : {$this->checkResult->checked_url}")
            ->line($this->failureSummary())
            ->action('Voir le contrôle', route('monitors.show', $monitor));
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        $monitor = $this->monitor();

        return TelegramMessage::create()
            ->to((string) config('upchecker.notifications.channels.telegram.chat_id'))
            ->normal()
            ->content($this->transition->label())
            ->line("{$monitor->project->name} / {$monitor->name}")
            ->line("Statut : {$this->checkResult->status->label()}")
            ->line("URL : {$this->checkResult->checked_url}")
            ->line($this->failureSummary())
            ->button('Voir le contrôle', route('monitors.show', $monitor));
    }

    private function monitor(): Monitor
    {
        $this->checkResult->loadMissing('monitor.project');

        return $this->checkResult->monitor;
    }

    private function mailSubject(Monitor $monitor): string
    {
        return match ($this->transition) {
            AlertTransition::UpToDown => "Alerte UpChecker : {$monitor->name}",
            AlertTransition::DownToUp => "Retour à la normale : {$monitor->name}",
        };
    }

    private function introLine(Monitor $monitor): string
    {
        return match ($this->transition) {
            AlertTransition::UpToDown => "Le contrôle {$monitor->name} est passé en {$this->checkResult->status->label()}.",
            AlertTransition::DownToUp => "Le contrôle {$monitor->name} est revenu en OK.",
        };
    }

    private function failureSummary(): string
    {
        $parts = [];

        if ($this->checkResult->http_status !== null) {
            $parts[] = "HTTP {$this->checkResult->http_status}";
        }

        if ($this->checkResult->response_time_ms !== null) {
            $parts[] = "{$this->checkResult->response_time_ms} ms";
        }

        if ($this->checkResult->error_message !== null && $this->checkResult->error_message !== '') {
            $parts[] = $this->checkResult->error_message;
        }

        return $parts === [] ? 'Aucun détail supplémentaire.' : implode(' - ', $parts);
    }

    private function notificationChannel(AlertChannel $channel): ?string
    {
        if (! $channel->isAvailable()) {
            return null;
        }

        return match ($channel) {
            AlertChannel::Mail => 'mail',
            AlertChannel::Telegram => 'telegram',
            AlertChannel::Sms => null,
        };
    }
}
