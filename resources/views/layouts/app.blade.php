<x-base-layout>

    @if (isset($header_title))
    <header class="bg-base-200 py-3">
        <div class="container flex justify-between items-center">
            <h1 class="text-2xl font-semibold py-2 flex justify-start items-center gap-3">
                {{ $header_title }}
            </h1>

            @if( isset($header_actions) )
            <div class="flex gap-2">
                {{ $header_actions }}
            </div>
            @endif
        </div>
    </header>
    @endif

    <main class="bg-base-100 pt-2 pb-8">

        <div class="container">

            @if (isset($breadcrumb))
            <div class="text-sm breadcrumbs mt-0">
                <ul>
                    {{ $breadcrumb }}
                </ul>
            </div>
            @endif

            <div class="pt-5">

                <x-auth-session-status class="mb-4" :status="session('status')" />

                {{ $slot }}

            </div>

        </div>
    </main>

</x-base-layout>