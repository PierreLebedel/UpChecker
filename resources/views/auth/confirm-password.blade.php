<x-guest-layout>

    <x-slot name="header">
        <h1 class="text-2xl">{{ __('Confirm your password') }}</h1>
        <div class="text-sm text-neutral-content mt-3">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
        </div>
    </x-slot>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                {{ __('Confirm') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
