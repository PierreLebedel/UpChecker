@php
    use App\Enums\CheckStatus;

    $checks = collect([
        (object) ['checked_at' => now()->subMinutes(88), 'status' => CheckStatus::Up, 'response_time_ms' => 154, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(85), 'status' => CheckStatus::Up, 'response_time_ms' => 147, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(82), 'status' => CheckStatus::Up, 'response_time_ms' => 162, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(79), 'status' => CheckStatus::Up, 'response_time_ms' => 139, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(76), 'status' => CheckStatus::Up, 'response_time_ms' => 151, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(73), 'status' => CheckStatus::Timeout, 'response_time_ms' => null, 'http_status' => null, 'error_message' => 'Timeout après 10 secondes', 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(70), 'status' => CheckStatus::Up, 'response_time_ms' => 172, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(67), 'status' => CheckStatus::Up, 'response_time_ms' => 144, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(64), 'status' => CheckStatus::Up, 'response_time_ms' => 128, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(61), 'status' => CheckStatus::Up, 'response_time_ms' => 136, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(58), 'status' => CheckStatus::Up, 'response_time_ms' => 142, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(55), 'status' => CheckStatus::Up, 'response_time_ms' => 156, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(52), 'status' => CheckStatus::Up, 'response_time_ms' => 131, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(49), 'status' => CheckStatus::Up, 'response_time_ms' => 149, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(46), 'status' => CheckStatus::Up, 'response_time_ms' => 203, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(43), 'status' => CheckStatus::Up, 'response_time_ms' => 344, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(40), 'status' => CheckStatus::Invalid, 'response_time_ms' => 212, 'http_status' => 502, 'error_message' => 'Réponse JSON inattendue', 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(37), 'status' => CheckStatus::Up, 'response_time_ms' => 188, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(34), 'status' => CheckStatus::Up, 'response_time_ms' => 121, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(31), 'status' => CheckStatus::Up, 'response_time_ms' => 135, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(28), 'status' => CheckStatus::Up, 'response_time_ms' => 117, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(25), 'status' => CheckStatus::Up, 'response_time_ms' => 124, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(22), 'status' => CheckStatus::Up, 'response_time_ms' => 110, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(19), 'status' => CheckStatus::Up, 'response_time_ms' => 118, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(16), 'status' => CheckStatus::Up, 'response_time_ms' => 107, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(13), 'status' => CheckStatus::Up, 'response_time_ms' => 112, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(10), 'status' => CheckStatus::Up, 'response_time_ms' => 103, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(7), 'status' => CheckStatus::Up, 'response_time_ms' => 200, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinutes(4), 'status' => CheckStatus::Up, 'response_time_ms' => 96, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
        (object) ['checked_at' => now()->subMinute(), 'status' => CheckStatus::Up, 'response_time_ms' => 92, 'http_status' => 200, 'error_message' => null, 'response_excerpt' => null],
    ]);

    $components = [
        [
            'icon' => 'arrow-path', 
            'title' => 'Surveillez en continu', 
            'description' => 'Planifiez vos contrôles HTTP et gardez une lecture immédiate de ce qui répond, ralentit ou casse.'
        ], [
            'icon' => 'bell', 
            'title' => 'Choisissez vos canaux d\'alerte', 
            'description' => 'Recevez les rapport d\'incidents là où vous voulez : email, Telegram, SMS'
        ], [
            'icon' => 'folder-git-2', 
            'title' => 'Organisez vos projets', 
            'description' => 'Regroupez vos contrôles par produit, client ou environnement'
        ],
    ];

    $metrics = [
        ['label' => 'Disponibilité', 'value' => '99.94%'],
        ['label' => 'Temps de réponse', 'value' => '118 ms'],
        ['label' => 'Incidents ouverts', 'value' => '2'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head', ['title' => __('Bienvenue')])
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-800 antialiased dark:bg-zinc-900 dark:text-white">
        <header class="bg-white/90 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/90">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <x-app-logo href="{{ route('home') }}" />

                <nav class="flex items-center gap-3">
                    <flux:button
                        icon="arrow-top-right-on-square"
                        variant="subtle"
                        href="https://github.com/PierreLebedel/UpChecker"
                        target="_blank"
                    >
                        <span class="hidden sm:inline">GitHub</span>
                    </flux:button>

                    
                    <div>
                        <flux:button square x-data x-on:click="$flux.dark = ! $flux.dark" variant="subtle">
                            <flux:icon.sun x-show="($flux.appearance === 'dark' || $flux.appearance === 'system') && $flux.dark" variant="mini" />
                            <flux:icon.moon x-show="($flux.appearance === 'light' || $flux.appearance === 'system') && ! $flux.dark" x-cloak variant="mini" />
                        </flux:button>
                    </div>

                    @auth
                        @if (Route::has('dashboard'))
                            <flux:button variant="primary" :href="route('dashboard')" wire:navigate>
                                Dashboard
                            </flux:button>
                        @endif
                    @else
                        @if (Route::has('login') || true)
                            <flux:button :href="route('login')" wire:navigate>
                                Connexion
                            </flux:button>
                        @endif

                        @if (Route::has('register') || true)
                            <flux:button variant="primary" :href="route('register')" wire:navigate>
                                Créer un compte
                            </flux:button>
                        @endif
                    @endauth
                    
                </nav>
            </div>
        </header>
        <flux:separator />
        <div class="lg:mx-auto lg:max-w-7xl">
            

            <main class="flex flex-col gap-10 px-4 py-12 sm:px-6 lg:px-8 lg:py-12">
                <section class="grid items-center gap-8 lg:grid-cols-[1fr_33rem]">
                    <div class="max-w-3xl space-y-6 py-12">
                        <flux:heading level="1" class="text-4xl font-semibold leading-tight sm:text-5xl">
                            Détendez-vous, <br />
                            tout est <span class="text-accent">sous contrôle</span>
                        </flux:heading>
                        <flux:text class="max-w-2xl text-base text-zinc-600 dark:text-zinc-300 sm:text-lg">
                            UpChecker surveille en permanence l'état de vos serveurs et de vos sites web, <nobr>et vous</nobr> alerte en temps réel en cas d'incident.
                        </flux:text>
                        <div class="flex flex-wrap gap-3 pt-3">
                            @if (Route::has('dashboard'))
                                <flux:button variant="primary" icon="home" :href="route('dashboard')" wire:navigate>
                                    Ouvrir le dashboard
                                </flux:button>
                            @endif

                            <flux:button
                                icon="arrow-top-right-on-square"
                                href="https://github.com/PierreLebedel/UpChecker"
                                target="_blank"
                            >
                                Voir le code
                            </flux:button>
                        </div>
                    </div>

                    <flux:card class="overflow-hidden py-4">
                        <div class="flex items-center justify-start gap-3">
                            <flux:badge variant="solid" color="emerald" class="size-3.5"></flux:badge>
                            <div>
                                <flux:heading size="lg">
                                    <span class="opacity-50 text-sm">Projet /</span>
                                    Production API
                                </flux:heading>
                                <flux:text>https://api.example.com</flux:text>
                            </div>
                        </div>

                        <x-monitor-check-sparkline :results="$checks" class="mt-6 h-10 w-full justify-start gap-1" count="53" />

                        <div class="mt-6 flex gap-3">
                            @foreach ($metrics as $metric)
                                <div class="flex-1 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                    <div class="text-xl font-semibold">{{ $metric['value'] }}</div>
                                    <flux:text class="mt-1 text-xs">{{ $metric['label'] }}</flux:text>
                                </div>
                            @endforeach
                        </div>
                    </flux:card>
                </section>

                <section class="grid gap-8 lg:grid-cols-3">
                    @foreach ($components as $component)
                    <flux:card>
                        <div class="flex flex-col gap-6 text-center">
                            <div class="flex size-16 items-center justify-center rounded-lg bg-white text-accent shadow-sm dark:bg-zinc-800 mx-auto">
                                <flux:icon :name="$component['icon']" class="size-8" />
                            </div>
                            <div class="space-y-3">
                                <flux:heading size="lg">{{ $component['title'] }}</flux:heading>
                                <flux:text class="mt-2">{{ $component['description'] }}</flux:text>
                            </div>
                        </div>
                    </flux:card>
                    @endforeach
                </section>
            </main>
        </div>

        @fluxScripts
    </body>
</html>
