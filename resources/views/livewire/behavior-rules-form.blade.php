<div>

    @dump($rules)

    @foreach($rules as $key => $rule)
    <div class="mb-4">

        @livewire('behavior-rules-rule-form', [
            'key'  => $key,
            'rule' => $rule
        ], key($key)) 

        @if($key > 0)
        <button type="button" wire:click="removeRule({{$key}})" class="btn btn-sm btn-danger mt-3">{{ __("Remove rule") }}</button>
        @endif
        
    </div>
    @endforeach

    <button type="button" wire:click="addRule" class="btn btn-primary">{{ __("Add new rule") }}</button>
</div>
