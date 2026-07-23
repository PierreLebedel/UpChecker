@props([
    'results',
    'start' => null,
    'end' => null,
    'description' => 'Temps de réponse et erreurs sur les dernières exécutions',
])

@php
    $points = collect($results)
        ->filter(fn ($checkResult) => $checkResult->checked_at !== null)
        ->sortBy('checked_at')
        ->values();

    $windowStart = $start === null
        ? ($points->first()?->checked_at?->copy() ?? now())
        : ($start instanceof \DateTimeInterface
            ? \Illuminate\Support\Carbon::instance($start)
            : \Illuminate\Support\Carbon::parse($start));
    $windowEnd = $end === null
        ? ($points->last()?->checked_at?->copy() ?? now())
        : ($end instanceof \DateTimeInterface
            ? \Illuminate\Support\Carbon::instance($end)
            : \Illuminate\Support\Carbon::parse($end));

    if ($points->count() === 1 && $start === null && $end === null) {
        $windowStart = $points->first()->checked_at->copy()->subMinute();
        $windowEnd = $points->first()->checked_at->copy()->addMinute();
    }

    $points = $points
        ->filter(fn ($checkResult) => $checkResult->checked_at->betweenIncluded($windowStart, $windowEnd))
        ->values();
    $displayTimezone = config('app.timezone');

    $width = 720;
    $height = 220;
    $top = 16;
    $right = 16;
    $bottom = 28;
    $left = 16;
    $chartWidth = $width - $left - $right;
    $chartHeight = $height - $top - $bottom;
    $baseline = $top + $chartHeight;
    $duration = max(1, $windowEnd->getTimestamp() - $windowStart->getTimestamp());

    $maxResponseTime = max(1, (int) $points
        ->filter(fn ($checkResult) => ! $checkResult->status->isFailure() && $checkResult->response_time_ms !== null)
        ->pluck('response_time_ms')
        ->max());

    $statusColor = fn ($status) => match ($status->value) {
        'up' => '#10b981',
        'down' => '#f43f5e',
        'timeout' => '#f97316',
        'invalid' => '#8b5cf6',
        default => '#71717a',
    };

    $pointX = fn ($checkedAt) => $left + min(
        $chartWidth,
        max(0, (($checkedAt->getTimestamp() - $windowStart->getTimestamp()) / $duration) * $chartWidth),
    );

    $pointTitle = function ($checkResult) use ($displayTimezone): string {
        $titleParts = [
            $checkResult->checked_at->timezone($displayTimezone)->format('d/m/Y H:i:s'),
            $checkResult->status->label(),
        ];

        if ($checkResult->response_time_ms !== null) {
            $titleParts[] = $checkResult->response_time_ms.' ms';
        }

        if ($checkResult->http_status !== null) {
            $titleParts[] = 'HTTP '.$checkResult->http_status;
        }

        $message = $checkResult->status->isFailure()
            ? ($checkResult->error_message ?? $checkResult->response_excerpt)
            : null;

        if ($message !== null && $message !== '') {
            $titleParts[] = str($message)->limit(140)->toString();
        }

        return implode(' - ', $titleParts);
    };
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border border-zinc-200 bg-zinc-50/60 p-3 dark:border-zinc-700 dark:bg-zinc-900/40']) }} data-monitor-response-time-sparkline>
    @if ($points->isEmpty())
        <div class="flex h-56 items-center justify-center rounded border border-dashed border-zinc-200 text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
            Aucune vérification à afficher
        </div>
    @else
        <svg class="h-56 w-full overflow-visible" viewBox="0 0 {{ $width }} {{ $height }}" preserveAspectRatio="none" role="img" aria-label="{{ $description }}">
            @foreach ([0, 1, 2, 3] as $line)
                @php
                    $gridY = $top + (($chartHeight / 3) * $line);
                @endphp

                <line x1="{{ $left }}" y1="{{ $gridY }}" x2="{{ $width - $right }}" y2="{{ $gridY }}" stroke="currentColor" class="text-zinc-200 dark:text-zinc-800" stroke-width="1" vector-effect="non-scaling-stroke" />
            @endforeach

            <line x1="{{ $left }}" y1="{{ $baseline }}" x2="{{ $width - $right }}" y2="{{ $baseline }}" stroke="currentColor" class="text-zinc-300 dark:text-zinc-700" stroke-width="1" vector-effect="non-scaling-stroke" />

            @foreach ($points as $checkResult)
                @php
                    $x = $pointX($checkResult->checked_at);
                    $isSuccess = ! $checkResult->status->isFailure() && $checkResult->response_time_ms !== null;
                    $barHeight = $isSuccess
                        ? max(10, ((int) $checkResult->response_time_ms / $maxResponseTime) * $chartHeight)
                        : 14;
                    $y = $baseline - $barHeight;
                    $color = $statusColor($checkResult->status);
                    $title = $pointTitle($checkResult);
                @endphp

                <line
                    x1="{{ $x }}"
                    y1="{{ $baseline }}"
                    x2="{{ $x }}"
                    y2="{{ $y }}"
                    stroke="{{ $color }}"
                    stroke-width="{{ $isSuccess ? 6 : 8 }}"
                    stroke-linecap="round"
                    vector-effect="non-scaling-stroke"
                >
                    <title>{{ $title }}</title>
                </line>
            @endforeach
        </svg>

        <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs text-zinc-500 dark:text-zinc-400">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-full bg-emerald-500"></span>Succès</span>
                <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-full bg-rose-500"></span>Erreur</span>
                <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-full bg-orange-500"></span>Timeout</span>
                <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-full bg-violet-500"></span>Invalide</span>
            </div>
            <div class="flex items-center gap-2">
                <span>{{ $windowStart->timezone($displayTimezone)->format('H:i') }}</span>
                <span aria-hidden="true">-</span>
                <span>{{ $windowEnd->timezone($displayTimezone)->format('H:i') }}</span>
            </div>
        </div>
    @endif
</div>
