<x-guest-layout>

    <x-slot name="header">
        <h1 class="text-2xl">{{ __('Verify your email') }}</h1>
        <div class="text-sm text-neutral-content mt-3">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </div>
    </x-slot>

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <x-primary-button>
                {{ __('Resend Verification Email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="btn btn-link">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
