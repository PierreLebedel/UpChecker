<?php

use App\Enums\CheckStatus;
use App\Models\Monitor;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] class extends Component
{
    /**
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        return [
            'echo-private:users.'.Auth::id().',MonitorCheckCompleted' => 'refreshAfterMonitorCheckCompleted',
        ];
    }

    /**
     * @return Collection<int, Monitor>
     */
    #[Computed]
    public function monitors(): Collection
    {
        return Monitor::query()
            ->select([
                'id',
                'project_id',
                'name',
                'url',
                'enabled',
                'current_status',
                'last_checked_at',
                'last_failure_at',
                'next_check_at',
                'interval_minutes',
            ])
            ->whereHas('project', fn ($query) => $query->whereBelongsTo(Auth::user()))
            ->with([
                'project:id,user_id,name',
                'checkResults' => fn ($query) => $query
                    ->select([
                        'id',
                        'monitor_id',
                        'status',
                        'response_time_ms',
                        'checked_at',
                    ])
                    ->latest('checked_at')
                    ->limit(30),
            ])
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Monitor>
     */
    #[Computed]
    public function monitorsWithRecentFailures(): Collection
    {
        $recentFailuresSince = $this->recentFailuresSince();

        return Monitor::query()
            ->select([
                'id',
                'project_id',
                'name',
                'url',
                'current_status',
                'last_checked_at',
                'last_failure_at',
                'next_check_at',
            ])
            ->whereHas('project', fn ($query) => $query->whereBelongsTo(Auth::user()))
            ->whereHas('checkResults', fn ($query) => $query
                ->where('status', '!=', CheckStatus::Up->value)
                ->where('checked_at', '>=', $recentFailuresSince))
            ->with([
                'project:id,user_id,name',
                'checkResults' => fn ($query) => $query
                    ->select([
                        'id',
                        'monitor_id',
                        'status',
                        'http_status',
                        'response_time_ms',
                        'error_message',
                        'checked_at',
                    ])
                    ->where('status', '!=', CheckStatus::Up->value)
                    ->where('checked_at', '>=', $recentFailuresSince)
                    ->latest('checked_at')
                    ->limit(1),
            ])
            ->withCount([
                'checkResults as recent_failures_count' => fn ($query) => $query
                    ->where('status', '!=', CheckStatus::Up->value)
                    ->where('checked_at', '>=', $recentFailuresSince),
            ])
            ->orderBy('name')
            ->get();
    }

    public function forgetRecentFailures(): void
    {
        Cache::forever($this->recentFailuresForgottenAtCacheKey(), now()->toIso8601String());

        unset($this->monitorsWithRecentFailures);
    }

    /**
     * @param  array<string, string>  $event
     */
    public function refreshAfterMonitorCheckCompleted(array $event = []): void
    {
        unset($this->monitors, $this->monitorsWithRecentFailures);
    }

    private function recentFailuresSince(): CarbonInterface
    {
        $since = now()->subDay();
        $forgottenAt = Cache::get($this->recentFailuresForgottenAtCacheKey());

        if (! is_string($forgottenAt) || $forgottenAt === '') {
            return $since;
        }

        $forgottenSince = Carbon::parse($forgottenAt);

        return $forgottenSince->greaterThan($since) ? $forgottenSince : $since;
    }

    private function recentFailuresForgottenAtCacheKey(): string
    {
        return 'users:'.Auth::id().':dashboard:recent-failures-forgotten-at';
    }
}; ?>

<section class="flex w-full flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item separator="slash">Dashboard</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    @if ($this->monitorsWithRecentFailures->isNotEmpty())
    <div class="space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-3 min-h-10">
                <flux:heading size="xl">
                    Erreurs récentes
                </flux:heading>
                <flux:badge variant="solid" color="rose">{{ $this->monitorsWithRecentFailures->count() }}</flux:badge>
            </div>
            <flux:button size="sm" variant="filled" icon="x-mark" wire:click="forgetRecentFailures" wire:loading.attr="disabled">
                Oublier
            </flux:button>
        </div>
        <div class="grid gap-3">
            <div class="grid gap-3 lg:grid-cols-2 2xl:grid-cols-3">
                @foreach ($this->monitorsWithRecentFailures as $monitor)
                    @php
                        $latestFailure = $monitor->checkResults->first();
                    @endphp

                    <flux:card wire:key="recent-failure-{{ $monitor->id }}" class="flex flex-col gap-4 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <flux:heading size="lg">
                                    <a href="{{ route('monitors.show', $monitor) }}" wire:navigate class="hover:text-zinc-700 dark:hover:text-zinc-300">
                                        {{ $monitor->name }}
                                    </a>
                                </flux:heading>
                                <flux:text class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $monitor->url }}</flux:text>
                            </div>

                            @if ($latestFailure)
                                <flux:badge variant="solid" :color="$latestFailure->status->color()">
                                    {{ $latestFailure->status->label() }}
                                </flux:badge>
                            @endif
                        </div>

                        <div class="flex gap-3 items-start justify-between text-sm">
                            <div>
                                <div class="text-zinc-500 dark:text-zinc-400">Projet</div>
                                <a href="{{ route('projects.show', $monitor->project) }}" wire:navigate class="font-medium text-zinc-800 hover:text-zinc-950 dark:text-zinc-200 dark:hover:text-white">
                                    {{ $monitor->project->name }}
                                </a>
                            </div>
                            <div>
                                <div class="text-zinc-500 dark:text-zinc-400">Dernière erreur</div>
                                <div class="font-medium text-zinc-800 dark:text-zinc-200">
                                    <x-relative-time :date="$latestFailure?->checked_at ?? $monitor->last_failure_at" />
                                </div>
                            </div>
                            <div>
                                <div class="text-zinc-500 dark:text-zinc-400">Nb/24h</div>
                                <div class="font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $monitor->recent_failures_count }} erreur{{ $monitor->recent_failures_count > 1 ? 's' : '' }}
                                </div>
                            </div>
                        </div>

                        @if ($latestFailure?->error_message)
                            <flux:text class="line-clamp-2">{{ $latestFailure->error_message }}</flux:text>
                        @endif
                    </flux:card>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-3">
                <flux:heading size="xl">
                    URL surveillées
                </flux:heading>
                <flux:badge inset="top bottom">{{ $this->monitors->count() }}</flux:badge>
            </div>

            <flux:button icon="folder-git-2" :href="route('projects.index')" wire:navigate>
                Gérer les projets
            </flux:button>
        </div>
        @if ($this->monitors->isEmpty())
            <flux:card class="max-w-2xl">
                <div class="flex flex-col gap-4">
                    <div>
                        <flux:heading>Aucune URL pour le moment</flux:heading>
                        <flux:text class="mt-1">Créez votre premier projet avec une URL à surveiller depuis la page Projets.</flux:text>
                    </div>

                    <div>
                        <flux:button variant="primary" icon="plus" :href="route('projects.index')" wire:navigate>
                            Créer un projet
                        </flux:button>
                    </div>
                </div>
            </flux:card>
        @else
            <flux:card class="overflow-hidden py-3">
                <x-monitors-status-table :monitors="$this->monitors" />
            </flux:card>
        @endif
    </div>
</section>
