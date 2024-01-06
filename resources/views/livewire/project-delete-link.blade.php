<div>
    
    <a href="#" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-project-deletion')">{{ __('Delete project') }}</a>

    @teleport('body')
    <x-modal name="confirm-project-deletion" focusable maxWidth="md">
        <form action="#" wire:submit="confirmDeleteProject()">
            @csrf

            <h2 class="text-xl">
                {{ __('Are you sure you want to delete this project?') }}
            </h2>

            <div class="mt-8 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete project') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
    @endteleport
</div>
