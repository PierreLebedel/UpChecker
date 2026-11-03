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

    /**
     * @var list<string>
     */
    private array $telegramLines;

    public function __construct(
        public CheckResult $checkResult,
        public AlertTransition $transition,
    ) {
        $monitor = $this->monitor();

        $this->telegramLines = array_values(Monitor::query()
            ->confirmedFailures()
            ->with('project:id,name')
            ->whereRelation('project', 'user_id', $monitor->project->user_id)
            ->get(['id', 'project_id', 'name'])
            ->map(function (Monitor $failingMonitor) use ($monitor): string {
                $icon = $this->transition === AlertTransition::UpToDown && $failingMonitor->is($monitor)
                    ? '🟥'
                    : '🟨';

                return "{$icon} {$failingMonitor->project->name} / {$failingMonitor->name}";
            })
            ->sort()
            ->all());

        if ($this->telegramLines === []) {
            $this->telegramLines = ['✅ Aucun incident en cours'];
        } else {
            $incidentCount = count($this->telegramLines);

            array_unshift($this->telegramLines, $incidentCount === 1
                ? '1 incident en cours :'
                : "{$incidentCount} incidents en cours :");
        }
    }

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
        $message = TelegramMessage::create()
            ->to((string) config('upchecker.notifications.channels.telegram.chat_id'))
            ->normal()
            ->content(implode("\n", $this->telegramLines));

        if ($this->transition === AlertTransition::UpToDown) {
            $message->button('Consulter le nouvel incident', route('monitors.show', $this->checkResult->monitor_id));
        }

        return $message;
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
        };
    }
}
