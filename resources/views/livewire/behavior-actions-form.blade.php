<div>

    @use('App\Enums\BehaviorActionEnum', 'Actions')

    @foreach($actions as $key => $action)
    <div class="mb-4">

        <div class="grid grid-cols-3 gap-8">
            <div class="form-control">
                <select class="select select-bordered w-full" id="action_{{$key}}_type" name="actions[{{$key}}][type]" wire:model.defer="actions.{{$key}}.type">
                    <option value="" disabled @selected(is_null($actions[$key]['type']))>{{ __("Choose") }}</option>
                    @foreach( Actions::cases() as $action )
                    <option value="{{ $action->value }}" @selected($actions[$key]['type'] == $action->value))>{{ $action->value }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('actions.{{$key}}.type')" />
            </div>
        </div>

        @if($key > 0)
        <button type="button" wire:click="removeAction({{$key}})" class="btn btn-sm btn-danger mt-3">{{ __("Remove action") }}</button>
        @endif
    </div>
    @endforeach

    <button type="button" wire:click="addAction" class="btn btn-primary">{{ __("Add new action") }}</button>
</div>
