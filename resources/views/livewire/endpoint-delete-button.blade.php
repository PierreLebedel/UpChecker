<div>
    
    <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-endpoint-deletion')" class="btn-sm">{{ __('Delete endpoint') }}</x-danger-button>

    <x-modal name="confirm-endpoint-deletion" focusable maxWidth="md">
        <form method="post" action="{{ route('endpoint.destroy', [$endpoint->project, $endpoint]) }}" wire:submit="confirmDelete()">
            @csrf
            @method('delete')

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
</div>
