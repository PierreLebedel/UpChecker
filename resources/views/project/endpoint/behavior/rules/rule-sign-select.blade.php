<div>

    @php
    $signs = $rule->type->getSignsArray() ?? [];
    @endphp
    
    @if( is_array($signs) && !empty($signs) )
    <div class="form-control">
        <select class="select select-bordered w-full" id="rule_{{$customkey}}_sign" name="rules[{{$customkey}}][params][sign]" required>
            <option value="" selected>{{ __("Choose") }}</option>
            @foreach($signs as $sign)
            <option value="{{ $sign }}" @selected(isset($rule->params['sign']) && $rule->params['sign'] === $sign) >{{ $sign }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('rules.{{$customkey}}.params.sign')" />
    </div>
    @endif

</div>