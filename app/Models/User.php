<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\AlertChannel;
use App\Enums\AlertTransition;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property array<mixed>|null $notification_channels
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'notification_channels'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable;

    /**
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notification_channels' => 'array',
        ];
    }

    /**
     * @return array<int, AlertChannel>
     */
    public function alertChannels(?AlertTransition $transition = null): array
    {
        $enabledChannelValues = [];

        foreach ($this->notificationChannelPreferences() as $channel => $preferences) {
            if ($preferences['enabled']) {
                $enabledChannelValues[] = $channel;
            }
        }

        return array_values(array_filter(array_map(
            function (string $channel) use ($transition): ?AlertChannel {
                $alertChannel = AlertChannel::tryFrom($channel);

                if (! $alertChannel instanceof AlertChannel || ! $alertChannel->isAvailable()) {
                    return null;
                }

                if ($transition === null) {
                    return $alertChannel;
                }

                $preferences = $this->notificationChannelPreferences()[$channel] ?? null;

                if (! is_array($preferences) || ! in_array($transition->value, $preferences['transitions'], true)) {
                    return null;
                }

                return $alertChannel;
            },
            $enabledChannelValues,
        )));
    }

    /**
     * @return array<string, array{enabled: bool, transitions: array<int, string>}>
     */
    public function notificationChannelPreferences(): array
    {
        $storedPreferences = $this->notification_channels;

        if (! is_array($storedPreferences) || $storedPreferences === []) {
            return $this->defaultNotificationChannelPreferences();
        }

        if (array_is_list($storedPreferences)) {
            return $this->legacyNotificationChannelPreferences($storedPreferences);
        }

        $preferences = [];

        foreach (AlertChannel::available() as $channel) {
            $channelPreferences = $storedPreferences[$channel->value] ?? null;
            $transitions = is_array($channelPreferences)
                ? ($channelPreferences['transitions'] ?? [])
                : [];

            $preferences[$channel->value] = [
                'enabled' => is_array($channelPreferences) && (bool) ($channelPreferences['enabled'] ?? false),
                'transitions' => $this->validTransitionValues($transitions, $channel->defaultTransitionValues()),
            ];
        }

        return $preferences;
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    /**
     * @return array<string, array{enabled: bool, transitions: array<int, string>}>
     */
    private function defaultNotificationChannelPreferences(): array
    {
        $preferences = [];

        foreach (AlertChannel::available() as $channel) {
            $preferences[$channel->value] = [
                'enabled' => $channel === AlertChannel::Mail,
                'transitions' => $channel->defaultTransitionValues(),
            ];
        }

        return $preferences;
    }

    /**
     * @param  array<int, mixed>  $storedChannels
     * @return array<string, array{enabled: bool, transitions: array<int, string>}>
     */
    private function legacyNotificationChannelPreferences(array $storedChannels): array
    {
        $enabledChannels = array_values(array_filter(
            $storedChannels,
            fn (mixed $channel): bool => is_string($channel),
        ));
        $preferences = [];

        foreach (AlertChannel::available() as $channel) {
            $preferences[$channel->value] = [
                'enabled' => in_array($channel->value, $enabledChannels, true),
                'transitions' => $channel->defaultTransitionValues(),
            ];
        }

        return $preferences;
    }

    /**
     * @param  array<int, string>  $fallback
     * @return array<int, string>
     */
    private function validTransitionValues(mixed $transitions, array $fallback): array
    {
        if (! is_array($transitions)) {
            return $fallback;
        }

        $values = array_values(array_intersect(
            array_filter($transitions, fn (mixed $transition): bool => is_string($transition)),
            AlertTransition::values(),
        ));

        return $values === [] ? $fallback : $values;
    }
}
