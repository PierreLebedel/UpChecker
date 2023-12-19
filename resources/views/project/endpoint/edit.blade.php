<x-app-layout>

    <x-slot name="header_title">
        {{ __('Edit endpoint') }}
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
        <li>{{ __('Edit') }}</li>
    </x-slot>

    <form action="{{ route('endpoint.update', [$project, $endpoint]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-control">
            <x-input-label for="endpoint_url" :value="__('URL')" />
            <x-text-input id="endpoint_url" type="url" name="url" :value="old('url', $endpoint->url)" required autofocus />
            <x-input-error :messages="$errors->get('url')" />
        </div>

        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('project.show', $project) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
        </div>
    </form>

</x-app-layout>
