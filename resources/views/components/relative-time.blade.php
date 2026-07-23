@props([
    'date' => null,
    'fallback' => 'Jamais',
    'due' => false,
])

<div class="w-31 overflow-hidden truncate">
    @if ($date instanceof DateTimeInterface)
        @php
            $isDue = $due && $date <= now();
        @endphp

        <time
            datetime="{{ $date->toIso8601String() }}"
            data-relative-time="{{ $date->toIso8601String() }}"
            data-relative-time-mode="{{ $due ? 'due' : 'relative' }}"
            title="{{ $date->format('d/m/Y H:i:s') }}"
            {{ $attributes }}
        >{{ $isDue ? 'maintenant' : $date->diffForHumans() }}</time>
    @else
        <span {{ $attributes }}>{{ $fallback }}</span>
    @endif
</div>
