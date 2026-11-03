<?php

namespace App\Notifications;

use App\Enums\AlertChannel;
use App\Enums\AlertTransition;
use App\Models\Monitor;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class MonitorAlertReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if (! $notifiable instanceof User) {
            return [];
        }

        return array_map(
            fn (AlertChannel $channel): string => $channel->value,
            $notifiable->alertChannels(AlertTransition::UpToDown),
        );
    }

    public function shouldSend(object $notifiable, string $channel): bool
    {
        return $notifiable instanceof User
            && in_array($channel, $this->via($notifiable), true)
            && Monitor::query()
                ->confirmedFailures()
                ->whereRelation('project', 'user_id', $notifiable->id)
                ->exists();
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Rappel UpChecker : services toujours en erreur')
            ->greeting('UpChecker')
            ->line('Rappel : les services suivants sont toujours en erreur.');

        foreach ($this->failingServices($notifiable) as $service) {
            $message->line($service);
        }

        return $message;
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        $services = $this->failingServices($notifiable);
        $incidentCount = count($services);
        $intro = $incidentCount === 1 ? '1 incident en cours :' : "{$incidentCount} incidents en cours :";

        return TelegramMessage::create()
            ->to((string) config('upchecker.notifications.channels.telegram.chat_id'))
            ->normal()
            ->content($intro."\n".implode("\n", $services))
            ->button('Consulter les incidents', route('dashboard'));
    }

    /**
     * @return list<string>
     */
    private function failingServices(object $notifiable): array
    {
        if (! $notifiable instanceof User) {
            return [];
        }

        return array_values(Monitor::query()
            ->confirmedFailures()
            ->with('project:id,name')
            ->whereRelation('project', 'user_id', $notifiable->id)
            ->get(['id', 'project_id', 'name'])
            ->map(fn (Monitor $monitor): string => "🟨 {$monitor->project->name} / {$monitor->name}")
            ->sort()
            ->all());
    }
}
