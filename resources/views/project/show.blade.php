<x-app-layout>

    <x-slot name="header_title">
        <span class="font-normal opacity-50">{{ __('Project :') }}</span>
        <span class="font-semibold">{{ $project->name }}</span>
    </x-slot>

    <x-slot name="header_actions">
        <a href="{{ route('project.edit', $project) }}" class="btn btn-secondary btn-sm">{{ __("Edit project") }}</a>
        <a href="{{ route('endpoint.create', $project) }}" class="btn btn-primary btn-sm">{{ __("Add endpoint") }}</a>
    </x-slot>

    <x-slot name="breadcrumb">
        <li><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li><a href="{{ route('project.index') }}">{{ __('My projects') }}</a></li>
        <li>
            <span class="opacity-50">{{ __('Project :') }}&nbsp;</span>
            <span class="font-semibold">{{ $project->name }}</span>
        </li>
    </x-slot>

    @if( $project->endpoints->isEmpty() )
    <div role="alert" class="alert alert-info">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>{{ __("No endpoint yet.") }}</span>
        <a href="{{ route('endpoint.create', $project) }}" class="btn btn-sm btn-primary">{{ __("Create first endpoint") }}</a>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="table">
            <!-- head -->
            <thead>
                <tr>
                    <th>{{ __("URL") }}</th>
                    <th>{{ __('Created date') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($project->endpoints as $endpoint)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div>
                                <a href="{{ route('endpoint.show', [$project, $endpoint]) }}" class="text-md font-semibold">{{ $endpoint->url }}</a>
                            </div>
                        </div>
                    </td>
                    <td>
                        {{ $endpoint->created_at->toDateTimeString() }}
                    </td>
                    <td class="w-0">
                        <a href="{{ route('endpoint.show', [$project, $endpoint]) }}" class="btn btn-sm btn-primary">{{ __("Details") }}</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
            {{-- <tfoot>
                <tr>
                    <th>{{ __("Name") }}</th>
                    <th>Job</th>
                    <th>Favorite Color</th>
                    <th></th>
                </tr>
            </tfoot> --}}

        </table>
    </div>
    @endif

    <div class="mt-6 ">
        <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-project-deletion')" class="btn-sm">{{ __('Delete project') }}</x-danger-button>
    </div>

    <x-modal name="confirm-project-deletion" focusable maxWidth="md">
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
