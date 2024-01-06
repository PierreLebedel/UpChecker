<div class="grid grid-cols-3 gap-8">

    @use('App\Enums\BehaviorRuleEnum', 'Rules')

    <x-text-input type="hidden" name="rules[{{$key}}][id]" wire:model.defer="rule.id" />

    <div class="form-control">
        <select class="select select-bordered w-full" id="rule_{{$key}}_compare_field" name="rules[{{$key}}][compare_field]" wire:model.live="rule.compare_field" required>
            <option value="" selected>{{ __("Choose") }}</option>
            @foreach( Rules::cases() as $r )
            <option value="{{ $r->value }}" @selected($rule['compare_field'] === $r->value) >{{ $r->getDescription() }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('rules.{{$key}}.compare_field')" />
    </div>

    <div class="form-control">
        <select class="select select-bordered w-full" id="rule_{{$key}}_compare_sign" name="rules[{{$key}}][compare_sign]" wire:model.defer="rule.compare_sign" required>
            <option value="" selected>{{ __("Choose") }}</option>
            @foreach($signs as $sign)
            <option value="{{ $sign }}" @selected($rule['compare_sign'] === $sign) >{{ $sign }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('rules.{{$key}}.compare_sign')" />
    </div>

    <div class="form-control">
        <x-text-input id="rule_{{$key}}_compare_value" type="text" name="rules[{{$key}}][compare_value]" wire:model.defer="rule.compare_value" required />
        <x-input-error :messages="$errors->get('rules.{{$key}}.compare_value')" />
    </div>
</div>