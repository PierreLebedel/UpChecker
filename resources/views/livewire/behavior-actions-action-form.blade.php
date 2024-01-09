<div class="grid grid-cols-3 gap-8">

    @use('App\Enums\BehaviorActionEnum', 'Actions')

    <x-text-input type="hidden" name="actions[{{$customkey}}][id]" value="{{ $action->id ?? '' }}" />

    <div class="form-control">
        <select class="select select-bordered w-full" id="action_{{$customkey}}_type" name="actions[{{$customkey}}][type]" wire:model.live="type" required>
            <option value="" selected>{{ __("Choose") }}</option>
            @foreach( Actions::cases() as $r )
            <option value="{{ $r->value }}" @selected($type === $r->value) >{{ $r->getDescription() }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('actions.{{$customkey}}.type')" />
    </div>

    <div class="col-span-2">

        @if( $type && $accountType = Actions::tryFrom($type)->getClassName()::needAccountType() )
            @include('project.endpoint.behavior.actions.action-account-select', [
                'action' => $action,
                'customkey'  => $customkey,
                'accountType' => $accountType,
            ])
        @endif

        @if($partialFormView)
            @include($partialFormView, [
                'action' => $action,
                'customkey'  => $customkey,
            ])
        @endif
    </div>
</div>