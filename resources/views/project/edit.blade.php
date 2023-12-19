<x-app-layout>

    <x-slot name="header_title">
        {{ __('Modifier un projet') }}
    </x-slot>

    <x-slot name="breadcrumb">
        <li><a href="{{ route('home') }}">{{ config('app.name') }}</a></li> 
        <li><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li><a href="{{ route('project.index') }}">{{ __('Mes projets') }}</a></li>
        <li>
            <a href="{{ route('project.show', $project) }}">
                <span class="opacity-50">{{ __('Projet :') }}&nbsp;</span>
                <span class="font-semibold">{{ $project->name }}</span>
            </a>
        </li>
        <li>{{ __('Modifier') }}</li>
    </x-slot>

    <form action="{{ route('project.update', $project) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-control">
            <x-input-label for="project_name" :value="__('Nom du projet')" />
            <x-text-input id="project_name" type="text" name="name" :value="old('name', $project->name)" required autofocus />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('project.show', $project) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
        </div>
    </form>

</x-app-layout>
