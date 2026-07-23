@props([
    'results',
])

@php
    $points = collect($results)
        ->sortBy('checked_at')
        ->values();

    $maxResponseTime = max(1, (int) $points->pluck('response_time_ms')->filter()->max());
@endphp

@if ($points->isEmpty())
    <div {{ $attributes->merge(['class' => 'h-7 w-52 rounded border border-dashed border-zinc-200 dark:border-zinc-700']) }} title="Aucune vérification"></div>
@else
    <div {{ $attributes->merge(['class' => 'flex h-7 w-52 items-end justify-end gap-0.5']) }} aria-label="Historique des 30 dernières vérifications">
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
@endif
