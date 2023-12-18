<x-base-layout>

    @if (isset($header))
        <header class="bg-base-100 pt-5">
            <div class="container">
                {{ $header }}

                @if (isset($breadcrumb))
                <div class="text-sm breadcrumbs">
                    <ul>
                        {{ $breadcrumb }}
                    </ul>
                </div>
                @endif
            </div>
        </header>
        <main class="bg-base-100 pt-5 pb-10">

    @else
        <main class="bg-base-100 pt-10 pb-10">
    @endif

        <div class="container">

            <x-auth-session-status class="mb-4" :status="session('status')" />

            {{ $slot }}

        </div>
    </main>

</x-base-layout>