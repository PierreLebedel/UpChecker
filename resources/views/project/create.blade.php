<x-app-layout>

    <x-slot name="header_title">
        {{ __('Créer un projet') }}
    </x-slot>

    <x-slot name="breadcrumb">
        <li><a href="{{ route('home') }}">{{ config('app.name') }}</a></li> 
        <li><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li><a href="{{ route('project.index') }}">{{ __('Mes projets') }}</a></li>
        <li>{{ __('Créer un projet') }}</li>
    </x-slot>

    <form action="{{ route('project.store') }}" method="POST">
        @csrf

        <div class="form-control">
            <x-input-label for="project_name" :value="__('Nom du projet')" />
            <x-text-input id="project_name" type="text" name="name" :value="old('name')" required autofocus />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('project.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
        </div>
    </form>

</x-app-layout>
