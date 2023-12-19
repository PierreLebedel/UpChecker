<x-app-layout>

    <x-slot name="header_title">
        <span class="opacity-50">{{ __('Projet :') }}</span>
        <span class="font-semibold">{{ $project->name }}</span>
    </x-slot>

    <x-slot name="header_actions">
        <a href="{{ route('project.edit', $project) }}" class="btn btn-primary">{{ __("Modifier le projet") }}</a>
    </x-slot>

    <x-slot name="breadcrumb">
        <li><a href="{{ route('home') }}">{{ config('app.name') }}</a></li> 
        <li><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li><a href="{{ route('project.index') }}">{{ __('Mes projets') }}</a></li>
        <li>
            <span class="opacity-50">{{ __('Projet :') }}&nbsp;</span>
            <span class="font-semibold">{{ $project->name }}</span>
        </li>
    </x-slot>

    infos ici

    <div class="mt-6 ">
        <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-project-deletion')" class="btn-sm">{{ __('Supprimer le projet') }}</x-danger-button>
    </div>

    <x-modal name="confirm-project-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable maxWidth="md">
        <form method="post" action="{{ route('project.destroy', $project) }}">
            @csrf
            @method('delete')

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

</x-app-layout>
