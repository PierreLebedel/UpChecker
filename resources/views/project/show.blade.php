<x-app-layout>

    <x-slot name="header_title">
        <span class="font-normal opacity-50">{{ __('Project :') }}</span>
        <span class="font-semibold">{{ $project->name }}</span>
    </x-slot>

    <x-slot name="header_actions">
        <a href="{{ route('project.edit', $project) }}" class="btn btn-primary btn-sm">{{ __("Edit project") }}</a>
        <a href="{{ route('endpoint.create', $project) }}" class="btn btn-secondary btn-sm">{{ __("Add endpoint") }}</a>
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
                    <th>{{ __('Last check up') }}</th>
                    <th>{{ __('Status code') }}</th>
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
                        {{ $endpoint->lastCheckup->started_at->toDateTimeString() ?? 'N/A' }}
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            @if( !empty($endpoint->lastCheckup->status_code) )
                            <span>{{ $endpoint->lastCheckup->status_code }}</span>
                            @endif

                            @if( !empty($endpoint->lastCheckup->exception_message) )
                            <div class="tooltip" data-tip="{{ $endpoint->lastCheckup->exception_message }}">
                                <span class="text-error">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 1.67c.955 0 1.845 .467 2.39 1.247l.105 .16l8.114 13.548a2.914 2.914 0 0 1 -2.307 4.363l-.195 .008h-16.225a2.914 2.914 0 0 1 -2.582 -4.2l.099 -.185l8.11 -13.538a2.914 2.914 0 0 1 2.491 -1.403zm.01 13.33l-.127 .007a1 1 0 0 0 0 1.986l.117 .007l.127 -.007a1 1 0 0 0 0 -1.986l-.117 -.007zm-.01 -7a1 1 0 0 0 -.993 .883l-.007 .117v4l.007 .117a1 1 0 0 0 1.986 0l.007 -.117v-4l-.007 -.117a1 1 0 0 0 -.993 -.883z" stroke-width="0" fill="currentColor" /></svg>
                                </span>
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="w-0">
                        <div class="flex justify-center items-center gap-2">
                            <a href="{{ route('endpoint.show', [$project, $endpoint]) }}" class="btn btn-sm btn-primary">{{ __("Details") }}</a>

                            <livewire:endpoint-run-button :endpoint="$endpoint" :wire:key="$endpoint->id" />
                        </div>
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
