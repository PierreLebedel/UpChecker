<div class="form-control">
    <x-input-label for="endpoint_url" :value="__('URL')" />
    <x-text-input id="endpoint_url" type="url" name="url" :value="old('url', $endpoint->url)" maxlength="255" required autofocus />
    <x-input-error :messages="$errors->get('url')" />
</div>

<div class="columns-3 mt-4">

    <div class="form-control">
        <x-input-label for="endpoint_expected_status_code" :value="__('Expected HTTP Status Code')" />
        <x-text-input id="endpoint_expected_status_code" type="text" name="expected_status_code" :value="old('expected_status_code', $endpoint->expected_status_code)" maxlength="10" required />
        <x-input-error :messages="$errors->get('expected_status_code')" />
    </div>

    <div class="form-control">
        <x-input-label for="endpoint_timeout" :value="__('Timeout (seconds)')" />
        <x-text-input id="endpoint_timeout" type="number" min="1" step="1" max="120" name="timeout" :value="old('timeout', $endpoint->timeout)" required />
        <x-input-error :messages="$errors->get('timeout')" />
    </div>

    <div class="form-control">
        <x-input-label for="endpoint_follow_redirects" :value="__('Follow redirects')" />
        <input type="checkbox" class="toggle toggle-primary mt-3" id="endpoint_follow_redirects" name="follow_redirects" value="1" {{ old('follow_redirects', $endpoint->follow_redirects) ? "checked" : "" }} />
    </div>
    
</div>

<div class="form-control">
    <x-input-label for="endpoint_checkup_delay" :value="__('Checkup delay')" />
    <x-text-input id="endpoint_checkup_delay" type="number" min="1" step="1" name="checkup_delay" :value="old('checkup_delay', $endpoint->checkup_delay)" required />
    <x-input-error :messages="$errors->get('checkup_delay')" />
</div>