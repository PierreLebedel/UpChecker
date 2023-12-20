<x-app-layout>

    <x-slot name="header_title">
        <span class="font-normal opacity-50">{{ __('Endpoint :') }}</span>
        <span class="font-semibold">{{ $endpoint->url }}</span>
    </x-slot>

    <x-slot name="header_actions">
        <a href="{{ route('endpoint.edit', [$project, $endpoint]) }}" class="btn btn-primary btn-sm">{{ __("Edit endpoint") }}</a>
        <livewire:endpoint-run-button :endpoint="$endpoint" />
    </x-slot>

    <x-slot name="breadcrumb">
        <li><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li><a href="{{ route('project.index') }}">{{ __('My projects') }}</a></li>
        <li>
            <a href="{{ route('project.show', $project) }}">
                <span class="opacity-50">{{ __('Project :') }}&nbsp;</span>
                <span class="font-semibold">{{ $project->name }}</span>
            </a>
        </li>
        <li>
            <span class="font-normal opacity-50">{{ __('Endpoint :') }}&nbsp;</span>
            <span class="font-semibold">{{ $endpoint->url }}</span>
        </li>
    </x-slot>

    <div class="grid grid-cols-3 gap-8">
        <div class="">
            @dump($endpoint->getAttributes())
        </div>
        <div class="col-span-2 flex flex-col gap-8">
            <livewire:endpoint-checkups-history :endpoint="$endpoint" />
        </div>
        
    </div>  

   

    <div class="mt-6 ">
        <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-endpoint-deletion')" class="btn-sm">{{ __('Delete endpoint') }}</x-danger-button>
    </div>

    <x-modal name="confirm-endpoint-deletion" focusable maxWidth="md">
        <form method="post" action="{{ route('endpoint.destroy', [$project, $endpoint]) }}">
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

</x-app-layout>
