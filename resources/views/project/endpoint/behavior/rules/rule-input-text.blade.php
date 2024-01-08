<div class="flex w-full gap-8">

    @include('project.endpoint.behavior.rules.rule-sign-select', [
        'rule' => $rule,
        'customkey'  => $customkey,
    ])

    <div class="form-control flex-1">
        <x-text-input id="rule_{{$customkey}}_value" type="text" name="rules[{{$customkey}}][params][value]" :value="$rule->params['value'] ?? ''" required />
        <x-input-error :messages="$errors->get('rules.{{$customkey}}.params.value')" />
    </div>

</div>