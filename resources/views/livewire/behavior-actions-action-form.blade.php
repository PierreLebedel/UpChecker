<div class="grid grid-cols-3 gap-8">

    @use('App\Enums\BehaviorActionEnum', 'Actions')

    <x-text-input type="hidden" name="actions[{{$key}}][id]" wire:model.defer="action.id" />

    <div class="form-control">
        <select class="select select-bordered w-full" id="action_{{$key}}_type" name="actions[{{$key}}][type]" wire:model.live="action.type" required>
            <option value="" selected>{{ __("Choose") }}</option>
            @foreach( Actions::cases() as $r )
            <option value="{{ $r->value }}" @selected($action['type'] === $r->value) >{{ $r->getDescription() }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('actions.{{$key}}.type')" />
    </div>
</div>