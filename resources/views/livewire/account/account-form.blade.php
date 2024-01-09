<div>
    
    @use('App\Enums\AccountTypeEnum', 'Types')

    <x-input-label for="account_{{ $customkey }}_type" value="{{ __('Account type') }}" class="required" />

    <select id="account_{{ $customkey }}_type" class="select select-bordered w-full" required name="type" @disabled($account->type) wire:model.live='type'>
        <option value="" @selected(!$account->type)>{{ __("Choose") }}</option>
        @foreach( Types::cases() as $r )
        <option value="{{ $r->value }}" @selected($account->type && ($r->value === $account->type->value)) >{{ $r->getDescription() }}</option>
        @endforeach
    </select>

    @if($account->type)
    <input type="hidden" name="type" value="{{ $account->type }}" />
    @endif

    <x-input-error :messages="$errors->userAccount->get('type')" />

    @if($partialFormView)

        <hr class="mt-6" />

        @include($partialFormView, [
            'account' => $account,
            'customkey' => $customkey
        ])
    @endif

</div>
