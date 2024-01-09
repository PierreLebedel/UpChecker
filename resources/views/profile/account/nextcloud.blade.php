<div>

    <div class="form-control mt-4">
        <x-input-label for="account_{{ $customkey }}_params_domain" :value="__('Server domain')" />
        <x-text-input type="url" id="account_{{ $customkey }}_params_domain" name="params[domain]" required wire:model='params.domain' />
        <x-input-error :messages="$errors->userAccount->get('params.domain')" />
    </div>

    <div class="form-control mt-4">
        <x-input-label for="account_{{ $customkey }}_params_username" :value="__('User name')" />
        <x-text-input type="text" id="account_{{ $customkey }}_params_username" name="params[username]" required wire:model='params.username' />
        <x-input-error :messages="$errors->userAccount->get('params.username')" />
    </div>

    <div class="form-control mt-4">
        <x-input-label for="account_{{ $customkey }}_params_password" :value="__('User password')" />
        <x-text-input type="password" id="account_{{ $customkey }}_params_password" name="params[password]" required wire:model='params.password' />
        <x-input-error :messages="$errors->userAccount->get('params.password')" />
    </div>

</div>