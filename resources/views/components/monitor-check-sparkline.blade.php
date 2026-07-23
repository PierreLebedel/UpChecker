@props([
    'results',
])

@php
    $points = collect($results)
        ->sortBy('checked_at')
        ->take(-30)
        ->values();

    $placeholderCount = max(0, 30 - $points->count());
    $maxResponseTime = max(1, (int) $points->pluck('response_time_ms')->filter()->max());
@endphp

<div {{ $attributes->merge(['class' => 'flex h-7 w-52 items-end justify-end gap-0.5']) }} aria-label="Historique des 30 dernières vérifications">
    @for ($index = 0; $index < $placeholderCount; $index++)
        <div
            class="aspect-square max-w-1.25 flex-1 shrink-0 rounded-full bg-zinc-300 dark:bg-zinc-600"
            style="height: 5px"
            title="Aucune vérification"
            data-monitor-check-placeholder
        ><span class="block w-full"></span></div>
    @endfor

    @foreach ($points as $checkResult)
        @php
            $isSuccess = ! $checkResult->status->isFailure();
            $height = $isSuccess
                ? max(7, (int) round(((int) $checkResult->response_time_ms / $maxResponseTime) * 28))
                : 5;
            $color = match ($checkResult->status->value) {
                'up' => 'bg-emerald-500',
                'down' => 'bg-rose-500',
                'timeout' => 'bg-orange-500',
                'invalid' => 'bg-violet-500',
                default => 'bg-zinc-400',
            };
            $title = $checkResult->checked_at->format('d/m/Y H:i:s').' - '.$checkResult->status->label();

            if ($checkResult->response_time_ms !== null) {
                $title .= ' - '.$checkResult->response_time_ms.' ms';
            }
        @endphp

        <div
            class="{{ $color }} flex-1 shrink-0 rounded-sm max-w-1.25 {{ $isSuccess ? '' : 'rounded-full aspect-square' }}"
            style="height: {{ $height }}px"
            title="{{ $title }}"
        ><span class="block w-full"></span></div>
    @endforeach
</div>
