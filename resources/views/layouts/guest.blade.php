<x-base-layout>

    <main class="bg-base-100 pt-14 pb-14">

        <div class="container">

            <div class="w-full sm:w-2/3 md:w-1/2 lg:w-1/3 mx-auto">

                @if (isset($header))
                    <header class="mb-5">
                        {{ $header }}
                    </header>
                @endif
        
                @if (session('status') == 'verification-link-sent')
                <x-auth-session-status class="mb-4" :status="__('A new verification link has been sent to the email address you provided during registration.')" />
                @else        
                <x-auth-session-status class="mb-4" :status="session('status')" />
                @endif
                
                {{ $slot }}
        
            </div>
        

        </div>
    </main>

</x-base-layout>