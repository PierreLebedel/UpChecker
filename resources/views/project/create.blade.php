<x-app-layout>

    <x-slot name="header_title">
        {{ __('Create project') }}
    </x-slot>

    <x-slot name="breadcrumb">
        <li><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li><a href="{{ route('project.index') }}">{{ __('My projects') }}</a></li>
        <li>{{ __('Create project') }}</li>
    </x-slot>

    <form action="{{ route('project.store') }}" method="POST">
        @csrf

        <div class="form-control">
            <x-input-label for="project_name" :value="__('Project name')" />
            <x-text-input id="project_name" type="text" name="name" :value="old('name')" required autofocus />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('project.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
        </div>
    </form>

</x-app-layout>
