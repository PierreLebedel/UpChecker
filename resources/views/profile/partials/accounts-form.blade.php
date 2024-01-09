<section class="card bg-base-200 shadow-xl p-5">

    @use('App\Models\Account', 'AccountModel')

    <header>
        <h2 class="card-title">
            {{ __('Accounts') }}
        </h2>
        <p class="text-sm mt-3">
            {{ __("Set your external accounts to use it in behavior's actions.") }}
        </p>
    </header>

    <div class="mt-6">
        @forelse ($user->accounts as $account)
        <div class="card bg-base-300 mb-4">
            <h3 class="card-title p-4">
                <span class="opacity-50">{{ $account->type->getDescription() }}:</span> {{ $account->name }}
            </h3>
            <div class="card-body">

                {{-- @dump( $account->params ) --}}

                <div class="justify-end card-actions">
                    <a href="" x-data="" x-on:click.prevent="$dispatch('open-modal', 'edit-user-account-{{ $account->id }}')" class="btn btn-sm">{{ __('Edit account') }}</a>
                </div>
            </div>
        </div>

        <x-modal name="edit-user-account-{{ $account->id }}" focusable maxWidth="xl">
            <form method="post" action="{{ route('profile.updateAccount', $account) }}">
                @csrf
                @method('put')
    
                <h2 class="text-xl">{{ __('Add account') }}</h2>
    
                <div class="mt-4">
                    @livewire('account.account-form', [
                        'account' => $account
                    ])
                </div>
    
                <div class="mt-8 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                    <x-primary-button class="ms-3">{{ __('Save account') }}</x-primary-button>
                </div>
            </form>
        </x-modal>

        @empty
        <div role="alert" class="alert alert-info">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ __("No external account yet.") }}</span>
        </div>
        @endforelse
    </div>

    <div class="mt-4 flex justify-end items-center">
        <a href="" x-data="" x-on:click.prevent="$dispatch('open-modal', 'add-user-account')" class="btn btn-primary">
            @if($user->accounts->isEmpty())
                {{ __('Add first account') }}
            @else
                {{ __('Add account') }}
            @endif
        </a>
    </div>

    <x-modal name="add-user-account" focusable maxWidth="xl">
        <form method="post" action="{{ route('profile.storeAccount') }}">
            @csrf

            <h2 class="text-xl">{{ __('Add account') }}</h2>

            <div class="mt-4">
                @livewire('account.account-form', [
                    'account' => AccountModel::make()
                ])
            </div>

            <div class="mt-8 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                    <x-primary-button class="ms-3">{{ __('Add account') }}</x-primary-button>
            </div>
        </form>
    </x-modal>

</section>