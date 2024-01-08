<div>

    @use('App\Enums\BehaviorActionEnum', 'Actions')

    @foreach($actions as $customkey => $action)
    <div class="mb-4">

        <livewire:behavior-actions-action-form :customkey="$customkey" :action="$action" :key="$customkey" />

        @if($loop->index > 0)
        <button type="button" wire:click="removeAction('{{$customkey}}')" class="btn btn-sm btn-danger mt-3">{{ __("Remove action") }}</button>
        @endif
    </div>
    @endforeach

    <button type="button" wire:click="addAction" class="btn btn-sm btn-primary">{{ __("Add new action") }}</button>
</div>
