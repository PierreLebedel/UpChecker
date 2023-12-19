<x-app-layout>

    <x-slot name="header_title">
        {{ __('Mes projets') }}
    </x-slot>

    <x-slot name="header_actions">
        <a href="{{ route('project.create') }}" class="btn btn-primary">{{ __("Créer un projet") }}</a>
    </x-slot>

    <x-slot name="breadcrumb">
        <li><a href="{{ route('home') }}">{{ config('app.name') }}</a></li>
        <li><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li>{{ __('Mes projets') }}</li>
    </x-slot>

    @if( $projects->isEmpty() )
    <div role="alert" class="alert alert-info">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>{{ __("Aucun projet pour le moment.") }}</span>
        <a href="{{ route('project.create') }}" class="btn btn-sm btn-primary">{{ __("Créer mon premier projet") }}</a>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="table">
            <!-- head -->
            <thead>
                <tr>
                    <th>{{ __("Name") }}</th>
                    <th>Job</th>
                    <th>Favorite Color</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($projects as $project)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div>
                                <div class="font-bold">{{ $project->name }}</div>
                                <div class="text-sm opacity-50">{{ $project->created_at->toDateTimeString() }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        Zemlak, Daniel and Leannon
                    </td>
                    <td>Purple</td>
                    <td class="w-0">
                        <a href="{{ route('project.show', $project) }}" class="btn btn-sm btn-secondary">{{ __("Détails") }}</a>
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