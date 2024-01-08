<div>
    <x-text-input type="email" name="actions[{{ $customkey }}][params][email_to]" :value="old('actions.{{ $customkey }}.params.email_to', $action['params']['email_to'] ?? auth()->user()->email)" maxlength="255" required />
    <x-input-error :messages="$errors->get('actions.{{ $customkey }}.params.email_to')" />
</div>