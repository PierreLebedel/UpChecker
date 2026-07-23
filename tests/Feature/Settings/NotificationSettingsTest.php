<?php

use App\Enums\AlertChannel;
use App\Enums\AlertTransition;
use App\Models\User;
use App\Notifications\NotificationChannelTestNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('notification settings page lists channels enabled by configuration', function () {
    config([
        'upchecker.notifications.channels.mail.enabled' => true,
        'upchecker.notifications.channels.telegram.enabled' => true,
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('notifications.edit'))
        ->assertOk()
        ->assertSee('Notifications')
        ->assertSee('Mail')
        ->assertSee('Telegram');
});

test('notification settings default to mail', function () {
    config([
        'upchecker.notifications.channels.mail.enabled' => true,
        'upchecker.notifications.channels.telegram.enabled' => false,
    ]);

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::settings.notifications')
        ->assertSet('channels.'.AlertChannel::Mail->value.'.enabled', true)
        ->assertSet('channels.'.AlertChannel::Mail->value.'.transitions', [AlertTransition::UpToDown->value]);
});

test('notification settings can be updated', function () {
    config([
        'upchecker.notifications.channels.mail.enabled' => true,
        'upchecker.notifications.channels.telegram.enabled' => true,
    ]);

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::settings.notifications')
        ->set('channels.'.AlertChannel::Mail->value.'.enabled', true)
        ->set('channels.'.AlertChannel::Mail->value.'.transitions', [AlertTransition::UpToDown->value])
        ->set('channels.'.AlertChannel::Telegram->value.'.enabled', true)
        ->set('channels.'.AlertChannel::Telegram->value.'.transitions', [
            AlertTransition::UpToDown->value,
            AlertTransition::DownToUp->value,
        ])
        ->call('updateNotificationChannels')
        ->assertHasNoErrors();

    expect($user->refresh()->notification_channels)->toBe([
        AlertChannel::Mail->value => [
            'enabled' => true,
            'transitions' => [AlertTransition::UpToDown->value],
        ],
        AlertChannel::Telegram->value => [
            'enabled' => true,
            'transitions' => [
                AlertTransition::UpToDown->value,
                AlertTransition::DownToUp->value,
            ],
        ],
    ]);
});

test('notification settings reject unavailable channels', function () {
    config([
        'upchecker.notifications.channels.mail.enabled' => true,
        'upchecker.notifications.channels.telegram.enabled' => false,
    ]);

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::settings.notifications')
        ->set('channels.'.AlertChannel::Mail->value.'.enabled', false)
        ->call('updateNotificationChannels')
        ->assertHasErrors(['channels']);
});

test('notification settings reject enabled channels without transition', function () {
    config([
        'upchecker.notifications.channels.mail.enabled' => true,
    ]);

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::settings.notifications')
        ->set('channels.'.AlertChannel::Mail->value.'.enabled', true)
        ->set('channels.'.AlertChannel::Mail->value.'.transitions', [])
        ->call('updateNotificationChannels')
        ->assertHasErrors(['channels.'.AlertChannel::Mail->value.'.transitions']);
});

test('mail notification channel connection can be tested', function () {
    config(['upchecker.notifications.channels.mail.enabled' => true]);
    Notification::fake();

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::settings.notifications')
        ->call('testChannel', AlertChannel::Mail->value);

    Notification::assertSentTo($user, NotificationChannelTestNotification::class);
});

test('telegram notification channel connection can be tested', function () {
    config([
        'upchecker.notifications.channels.telegram.enabled' => true,
        'upchecker.notifications.channels.telegram.bot_token' => 'secret-token',
        'upchecker.notifications.channels.telegram.chat_id' => '123456',
    ]);
    Notification::fake();

    $user = User::factory()->create([
        'notification_channels' => [AlertChannel::Telegram->value],
    ]);

    $this->actingAs($user);

    Livewire::test('pages::settings.notifications')
        ->call('testChannel', AlertChannel::Telegram->value);

    Notification::assertSentTo(
        $user,
        fn (NotificationChannelTestNotification $notification, array $channels): bool => $notification->channel === AlertChannel::Telegram
            && $channels === ['telegram']
    );
});
