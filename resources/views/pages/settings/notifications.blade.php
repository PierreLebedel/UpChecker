<?php

use App\Enums\AlertChannel;
use App\Enums\AlertTransition;
use App\Support\NotificationChannelTester;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Notification settings')] class extends Component {
    /**
     * @var array<string, array{enabled: bool, transitions: array<int, string>}>
     */
    public array $channels = [];

    public function mount(): void
    {
        $this->channels = Auth::user()->notificationChannelPreferences();
    }

    /**
     * @return array<int, AlertChannel>
     */
    #[Computed]
    public function availableChannels(): array
    {
        return AlertChannel::available();
    }

    /**
     * @return array<int, AlertTransition>
     */
    #[Computed]
    public function availableTransitions(): array
    {
        return AlertTransition::cases();
    }

    public function updateNotificationChannels(): void
    {
        $this->resetErrorBag();

        $preferences = [];
        $hasEnabledChannel = false;

        foreach ($this->availableChannels as $channel) {
            $channelPreferences = $this->channels[$channel->value] ?? [];
            $enabled = is_array($channelPreferences) && (bool) ($channelPreferences['enabled'] ?? false);
            $storedTransitions = is_array($channelPreferences) && is_array($channelPreferences['transitions'] ?? null)
                ? $channelPreferences['transitions']
                : [];
            $transitions = array_values(array_intersect(
                array_filter($storedTransitions, fn (mixed $transition): bool => is_string($transition)),
                $this->availableTransitionValues(),
            ));

            if ($enabled && $transitions === []) {
                $this->addError("channels.{$channel->value}.transitions", 'Choisissez au moins un déclencheur.');

                return;
            }

            $preferences[$channel->value] = [
                'enabled' => $enabled,
                'transitions' => $transitions === [] ? $channel->defaultTransitionValues() : $transitions,
            ];

            $hasEnabledChannel = $hasEnabledChannel || $enabled;
        }

        if (! $hasEnabledChannel) {
            $this->addError('channels', 'Choisissez au moins un canal de notification.');

            return;
        }

        Auth::user()->forceFill([
            'notification_channels' => $preferences,
        ])->save();

        Flux::toast(variant: 'success', text: 'Préférences de notification enregistrées.');
    }

    public function testChannel(string $channel): void
    {
        $alertChannel = AlertChannel::tryFrom($channel);

        if (! $alertChannel instanceof AlertChannel || ! $alertChannel->isAvailable()) {
            Flux::toast(variant: 'danger', text: 'Ce canal de notification n’est pas disponible.');

            return;
        }

        $result = app(NotificationChannelTester::class)->test(Auth::user(), $alertChannel);

        Flux::toast(
            variant: $result->successful ? 'success' : 'danger',
            text: $result->message,
        );
    }

    /**
     * @return array<int, string>
     */
    private function availableTransitionValues(): array
    {
        return AlertTransition::values();
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">Paramètres de notification</flux:heading>

    <x-pages::settings.layout heading="Notifications" subheading="Choisissez les canaux utilisés pour les alertes de vos projets">
        <form wire:submit="updateNotificationChannels" class="my-6 w-full space-y-4">
            @forelse ($this->availableChannels as $channel)
                <flux:card wire:key="notification-channel-{{ $channel->value }}" class="space-y-4">
                    <div class="flex items-start justify-between gap-4">
                        <flux:checkbox
                            wire:model="channels.{{ $channel->value }}.enabled"
                            :label="$channel->label()"
                            :description="$channel->description()"
                        />

                        <flux:button
                            type="button"
                            size="sm"
                            variant="filled"
                            icon="paper-airplane"
                            wire:click="testChannel('{{ $channel->value }}')"
                            wire:loading.attr="disabled"
                            :disabled="! $channel->canTestConnection()"
                        >
                            Tester
                        </flux:button>
                    </div>

                    <div class="grid gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                        <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                            Déclencheurs
                        </flux:text>

                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($this->availableTransitions as $transition)
                                <flux:checkbox
                                    wire:key="notification-channel-{{ $channel->value }}-transition-{{ $transition->value }}"
                                    wire:model="channels.{{ $channel->value }}.transitions"
                                    value="{{ $transition->value }}"
                                    :label="$transition->label()"
                                />
                            @endforeach
                        </div>

                        <flux:error name="channels.{{ $channel->value }}.transitions" />
                    </div>
                </flux:card>
            @empty
                <flux:callout variant="warning" icon="bell-slash">
                    Aucun canal de notification n’est activé.
                </flux:callout>
            @endforelse

            <flux:error name="channels" />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" data-test="update-notification-settings-button">
                    Enregistrer
                </flux:button>
            </div>
        </form>
    </x-pages::settings.layout>
</section>
