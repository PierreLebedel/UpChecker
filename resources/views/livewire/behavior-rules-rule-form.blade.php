<div class="grid grid-cols-3 gap-8">

    @use('App\Enums\BehaviorRuleEnum', 'Rules')

    <x-text-input type="hidden" name="rules[{{$customkey}}][id]" :value="$rule->id" />

    <div class="form-control">
        <select class="select select-bordered w-full" id="rule_{{$customkey}}_type" name="rules[{{$customkey}}][type]" wire:model.live="type" required>
            <option value="" selected>{{ __("Choose") }}</option>
            @foreach( Rules::cases() as $r )
            <option value="{{ $r->value }}" @selected($type === $r->value) >{{ $r->getDescription() }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('rules.{{$customkey}}.type')" />
    </div>

    <div class="col-span-2">
        @if($partialFormView)
            @include($partialFormView, [
                'rule' => $rule,
                'customkey'  => $customkey,
            ])
        @endif
    </div>

    
</div>