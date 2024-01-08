<x-app-layout>

    <x-slot name="header_title">
        <span class="font-normal opacity-50">{{ __('Project :') }}</span>
        <span class="font-semibold">{{ $project->name }}</span>
    </x-slot>

    <x-slot name="header_actions">
        <a href="{{ route('endpoint.create', $project) }}" class="btn btn-secondary btn-sm">{{ __("Add endpoint") }}</a>

        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-sm">...</div>
            <ul tabindex="0" class="menu dropdown-content z-[1] p-2 shadow bg-base-100 rounded-box w-52 mt-4">
                <li><a href="{{ route('project.edit', $project) }}">{{ __("Edit project") }}</a></li>
                <li>@livewire('project-delete-link', ['project'=>$project])</li>
            </ul>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li><a href="{{ route('project.index') }}">{{ __('My projects') }}</a></li>
        <li>
            <span class="opacity-50">{{ __('Project :') }}&nbsp;</span>
            <span class="font-semibold">{{ $project->name }}</span>
        </li>
    </x-slot>

    <livewire:project-endpoints-index :project="$project" />

</x-app-layout>
