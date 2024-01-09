<div class="flex w-full gap-8">

    <div class="form-control">
        <select class="select select-bordered w-full" id="rule_{{$customkey}}_boolean" name="rules[{{$customkey}}][params][boolean]" required>
            <option value="1" @selected(!isset($rule->params['boolean']) || $rule->params['boolean']) >{{ __("True") }}</option>
            <option value="0" @selected(isset($rule->params['boolean']) && !$rule->params['boolean']) >{{ __("False") }}</option>
        </select>
        <x-input-error :messages="$errors->get('rules.{{$customkey}}.params.boolean')" />
    </div>

</div>