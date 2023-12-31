<section class="card bg-base-200 shadow-xl p-5">
    <header>
        <h2 class="card-title">
            {{ __('Profile Information') }}
        </h2>
        <p class="text-sm mt-3">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4">
        @csrf
        @method('patch')

        <div class="form-control">
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="form-control mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required />
            <x-input-error :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-6">
                    <p class="text-sm">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="btn btn-sm btn-secondary">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="form-control mt-4">
            <x-input-label for="locale" :value="__('Locale')" />
            <select class="select select-bordered w-1/2" name="locale" required>
                <option disabled selected>{{ __('Choose') }}</option>
                @foreach( config('app.locales') as $k=>$v )
                <option value="{{ $k }}" {{ old('locale', $user->locale) == $k ? "selected" : "" }}>{{ $v }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('locale')" />
        </div>

        <div class="mt-6 flex justify-end items-center">
            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-success me-4"
                >{{ __('Saved.') }}</p>
            @endif
            
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            
        </div>
    </form>
</section>
