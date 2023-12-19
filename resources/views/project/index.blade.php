<x-app-layout>

    <x-slot name="header_title">
        <span>{{ __('My projects') }}</span>

        @if( $projects->isNotEmpty() )
        <span class="badge badge-primary">{{ $projects->count() }}</span>
        @endif
    </x-slot>

    <x-slot name="header_actions">
        <a href="{{ route('project.create') }}" class="btn btn-secondary btn-sm">{{ __("Create project") }}</a>
    </x-slot>

    <x-slot name="breadcrumb">
        <li><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li>{{ __('My projects') }}</li>
    </x-slot>

    @if( $projects->isEmpty() )
    <div role="alert" class="alert alert-info">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>{{ __("No project yet.") }}</span>
        <a href="{{ route('project.create') }}" class="btn btn-sm btn-primary">{{ __("Create first project") }}</a>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="table">
            <!-- head -->
            <thead>
                <tr>
                    <th>{{ __("Project name") }}</th>
                    <th>{{ __('Project id') }}</th>
                    <th>{{ __("Endpoints") }}</th>
                    <th>{{ __('Created date') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($projects as $project)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div>
                                <a href="{{ route('project.show', $project) }}" class="text-md font-semibold">{{ $project->name }}</a>
                            </div>
                        </div>
                    </td>
                    <td class="font-mono">
                        {{ $project->slug }}
                    </td>
                    <td>
                        <span class="badge badge-neutral">{{ $project->endpoints_count }}</span>
                    </td>
                    <td>
                        {{ $project->created_at->toDateTimeString() }}
                    </td>
                    <td class="w-0">
                        <a href="{{ route('project.show', $project) }}" class="btn btn-sm btn-primary">{{ __("Details") }}</a>
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

</x-app-layout>