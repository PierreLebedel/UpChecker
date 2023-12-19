<x-app-layout>

    <x-slot name="header_title">
        {{ __('Create endpoint') }}
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
        <li>{{ __('Create endpoint') }}</li>
    </x-slot>

    <form action="{{ route('endpoint.store', $project) }}" method="POST">
        @csrf

        @include('project.endpoint.form')

        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('project.show', $project) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
        </div>
    </form>

</x-app-layout>
