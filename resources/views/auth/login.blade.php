<x-guest-layout>

    <x-slot name="header">
        <h1 class="text-2xl">{{ __('Log in') }}</h1>
    </x-slot>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="form-control">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div class="form-control mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="form-control mt-4">
            <label class="cursor-pointer justify-start">
                <input type="checkbox" name="remember" class="checkbox checkbox-primary" />
                <span class="label-text ms-2">{{ __('Remember me') }}</span> 
            </label>
        </div>

        <div class="flex items-center justify-end mt-8">
            @if (Route::has('password.request'))
                <a class="btn btn-link" href="{{ route('password.request') }}" tabindex="-1">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-2">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
    
</x-guest-layout>
