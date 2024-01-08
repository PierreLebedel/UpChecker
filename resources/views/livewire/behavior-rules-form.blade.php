<div>

    @foreach($rules as $customkey => $rule)
    <div class="mb-4">

        @livewire('behavior-rules-rule-form', [
            'customkey'  => $customkey,
            'rule' => $rule
        ], key($customkey)) 

        @if($loop->index > 0)
        <button type="button" wire:click="removeRule('{{ $customkey }}')" class="btn btn-sm btn-danger mt-3">{{ __("Remove rule") }}</button>
        @endif
        
    </div>
    @endforeach

    <button type="button" wire:click="addRule" class="btn btn-sm btn-primary">{{ __("Add new rule") }}</button>
</div>
