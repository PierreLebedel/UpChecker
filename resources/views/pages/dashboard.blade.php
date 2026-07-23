<?php

use App\Models\Monitor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
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
     * @param  array<string, string>  $event
     */
    public function refreshAfterMonitorCheckCompleted(array $event = []): void
    {
        unset($this->monitors);
    }
}; ?>

<section class="flex w-full flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item separator="slash">Dashboard</flux:breadcrumbs.item>
    </flux:breadcrumbs>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-3">
            <flux:heading size="xl">
                URL surveillées
            </flux:heading>
            <flux:badge inset="top bottom">{{ $this->monitors->count() }}</flux:badge>
        </div>

        <flux:button icon="folder-plus" :href="route('projects.index')" wire:navigate>
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
</section>
