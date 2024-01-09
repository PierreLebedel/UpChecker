<div>

    @php
    $accounts = auth()->user()->accounts->filter(function($a) use ($accountType) {
        return $a->type->getClassName() === $accountType;
    })
    @endphp
    
    @if($accounts->isNotEmpty())
    <div class="form-control">
        <select class="select select-bordered" id="rule_{{$customkey}}_account_id" name="actions[{{$customkey}}][account_id]" required>
            <option value="" selected>{{ __("Choose") }}</option>
            @foreach($accounts as $account)
            <option value="{{ $account->id }}" @selected(($action->account_id === $account->id) || count($accounts)==1) >{{ $account->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('actions.{{$customkey}}.account_id')" />
    </div>
    @else
        {{ __("No ".$accountType." account") }}
        <a href="{{ route('profile.edit') }}" class="btn btn-sm ms-3">{{ __("Add account") }}</a>
    @endif

</div>