<x-app-layout>

    <x-slot name="header_title">
        {{ __('Edit behavior') }}
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
            <a href="{{ route('endpoint.show', [$project, $endpoint]) }}">
                <span class="font-normal opacity-50">{{ __('Endpoint :') }}&nbsp;</span>
                <span class="font-semibold">{{ $endpoint->url }}</span>
            </a>
        </li>
        <li>{{ __('Edit behavior') }}</li>
    </x-slot>

    <form action="{{ route('behavior.update', [$project, $endpoint, $behavior]) }}" method="POST">
        @csrf
        @method('PUT')
        
        @include('project.endpoint.behavior.form')

        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('endpoint.show', [$project, $endpoint]) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
        </div>
    </form>

</x-app-layout>
