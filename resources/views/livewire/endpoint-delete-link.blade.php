<div>
    
    <a href="#" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-endpoint-deletion')">{{ __('Delete endpoint') }}</a>

    @teleport('body')
    <x-modal name="confirm-endpoint-deletion" focusable maxWidth="md">
        <form action="#" wire:submit="confirmDelete()">
            @csrf

            <h2 class="text-xl">
                {{ __('Are you sure you want to delete this endpoint?') }}
            </h2>

            <div class="mt-8 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete endpoint') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
    @endteleport
</div>
