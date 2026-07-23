@props([
    'monitors',
    'showProjectColumn' => true,
    'showMonitorActions' => null,
])

@php
    $showMonitorActions ??= ! $showProjectColumn;
@endphp

<div class="overflow-x-auto">
    <flux:table>
        <flux:table.columns>
            <flux:table.column><span class="sr-only">Statut</span></flux:table.column>
            <flux:table.column>URL</flux:table.column>
            <flux:table.column>Historique</flux:table.column>
            <flux:table.column><flux:icon name="clock" /></flux:table.column>
            <flux:table.column>Dernière exec.</flux:table.column>
            <flux:table.column>Dernière erreur</flux:table.column>
            <flux:table.column>Prochaine exec.</flux:table.column>
            @if ($showMonitorActions)
                <flux:table.column></flux:table.column>
            @endif
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($monitors as $monitor)
                <flux:table.row :key="$monitor->id">

                    <flux:table.cell class="w-1 pe-1">
                        <flux:badge variant="solid" :color="$monitor->current_status->color()" class="size-3.5 rounded-full! p-0!" title="Statut : {{ $monitor->current_status->label() }}">
                            <span class="sr-only">{{ $monitor->current_status->label() }}</span>
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="flex flex-wrap gap-y-0 gap-x-2 items-baseline">
                            <div class="flex flex-wrap items-baseline gap-1">
                                @if ($showProjectColumn)
                                <a href="{{ route('projects.show', $monitor->project) }}" wire:navigate class="font-medium text-xs text-zinc-500 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-zinc-100">
                                    {{ $monitor->project->name }}
                                </a>
                                <span class="text-zinc-400 dark:text-zinc-500 text-xs">/</span>
                                @endif
                                <a href="{{ route('monitors.show', $monitor) }}" wire:navigate class="font-medium text-zinc-900 hover:text-zinc-700 dark:text-zinc-100 dark:hover:text-zinc-300">
                                    {{ $monitor->name }}
                                </a>
                            </div>
                            <div class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $monitor->url }}</div>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell class="w-1">
                        <x-monitor-check-sparkline :results="$monitor->checkResults" />
                    </flux:table.cell>

                    <flux:table.cell class="w-1">
                        {{ $monitor->interval_minutes }}&nbsp;min
                    </flux:table.cell>

                    <flux:table.cell class="w-1">
                        <x-relative-time :date="$monitor->last_checked_at" />
                    </flux:table.cell>

                    <flux:table.cell class="w-1">
                        <x-relative-time :date="$monitor->last_failure_at" fallback="aucune" />
                    </flux:table.cell>

                    <flux:table.cell class="w-1">
                        <x-relative-time :date="$monitor->next_check_at" fallback="non-planifiée" due />
                    </flux:table.cell>

                    @if ($showMonitorActions)
                        <flux:table.cell class="w-1">
                            <div class="flex justify-end">
                                <flux:dropdown align="end">
                                    <flux:button size="sm" variant="subtle" icon="ellipsis-horizontal" square aria-label="Actions du contrôle" />

                                    <flux:menu>
                                        <flux:menu.item icon="eye" :href="route('monitors.show', $monitor)" wire:navigate>
                                            Voir le détail
                                        </flux:menu.item>
                                        <flux:menu.item icon="arrow-path" wire:click="checkNow('{{ $monitor->id }}')">
                                            Vérifier maintenant
                                        </flux:menu.item>
                                        <flux:menu.item icon="pencil" wire:click="openEditMonitorModal('{{ $monitor->id }}')">
                                            Modifier l'URL
                                        </flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" class="text-rose-600 hover:bg-rose-50 hover:text-rose-700 dark:text-rose-400 dark:hover:bg-rose-950/40 dark:hover:text-rose-300" wire:click="openDeleteMonitorModal('{{ $monitor->id }}')">
                                            Supprimer l'URL
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </flux:table.cell>
                    @endif
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
