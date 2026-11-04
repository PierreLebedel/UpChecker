@props([
    'monitor',
    'dot' => true,
    'titlePrefix' => 'Statut',
])

@php
    $label = $monitor->enabled ? $monitor->current_status->label() : 'Désactivé';
@endphp

<flux:badge
    variant="solid"
    :color="$monitor->enabled ? $monitor->current_status->color() : 'zinc'"
    {{ $attributes->class([
        'size-3.5 rounded-full! p-0!' => $dot,
        'bg-zinc-300! text-zinc-700! dark:bg-zinc-300! dark:text-zinc-700!' => ! $monitor->enabled,
    ]) }}
    title="{{ $titlePrefix }} : {{ $label }}"
>
    @if ($dot)
        <span class="sr-only">{{ $label }}</span>
    @else
        {{ $label }}
    @endif
</flux:badge>
