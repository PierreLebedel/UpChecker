<div>

    @use('App\Enums\BehaviorActionEnum', 'Actions')

    @foreach($actions as $key => $action)
    <div class="mb-4">

        @livewire('behavior-actions-action-form', [
            'key'  => $key,
            'action' => $action
        ], key($action->temp_uniqid)) 

        @if($key > 0)
        <button type="button" wire:click="removeAction({{$key}})" class="btn btn-sm btn-danger mt-3">{{ __("Remove action") }}</button>
        @endif
    </div>
    @endforeach

    <button type="button" wire:click="addAction" class="btn btn-primary">{{ __("Add new action") }}</button>
</div>
