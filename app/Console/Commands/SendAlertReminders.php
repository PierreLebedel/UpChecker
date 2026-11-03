<?php

namespace App\Console\Commands;

use App\Models\Monitor;
use App\Models\User;
use App\Notifications\MonitorAlertReminderNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('monitors:send-alert-reminders')]
#[Description('Send hourly grouped reminders for confirmed monitor failures')]
class SendAlertReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        User::query()
            ->where('next_alert_reminder_at', '<=', now())
            ->eachById(function (User $user): void {
                DB::transaction(function () use ($user): void {
                    $user = User::query()->lockForUpdate()->findOrFail($user->id);

                    if ($user->next_alert_reminder_at === null || $user->next_alert_reminder_at->isFuture()) {
                        return;
                    }

                    $hasConfirmedFailures = Monitor::query()
                        ->confirmedFailures()
                        ->whereRelation('project', 'user_id', $user->id)
                        ->exists();

                    if ($hasConfirmedFailures) {
                        $user->notify((new MonitorAlertReminderNotification)->afterCommit());
                    }

                    $user->forceFill([
                        'next_alert_reminder_at' => $hasConfirmedFailures ? now()->addHour() : null,
                    ])->save();
                });
            });

        return self::SUCCESS;
    }
}
