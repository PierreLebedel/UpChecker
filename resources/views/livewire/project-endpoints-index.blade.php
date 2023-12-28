<div class="card bg-base-200 shadow-xl pt-4">
    <header class="mb-4 px-4">
        <h2 class="card-title">
            <span>{{ __('Endpoints') }}</span>

            @if( $endpoints->isNotEmpty() )
            <span class="badge badge-primary">{{ $endpoints->count() }}</span>
            @endif
        </h2>
    </header>
    @if( $endpoints->isEmpty() )
    <div role="alert" class="alert alert-info mx-4 mb-4 w-auto">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>{{ __("No endpoint yet.") }}</span>
        <a href="{{ route('endpoint.create', $project) }}" class="btn btn-sm btn-primary">{{ __("Create first endpoint") }}</a>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="table border-t border-base-100">
            <!-- head -->
            <thead>
                <tr class="border-base-100">
                    <th>{{ __("URL") }}</th>
                    <th>{{ __('Delay') }}</th>
                    <th>{{ __('Last check up') }}</th>
                    <th>{{ __('Status code') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($endpoints as $endpoint)
                <tr class="border-base-100">
                    <td>
                        <a href="{{ route('endpoint.show', [$project, $endpoint]) }}" class="text-base font-semibold">{{ $endpoint->url }}</a><br>
                        <span class="font-mono opacity-50 text-sm">{{ __('ID:') }} {{ $endpoint->slug }}</span>
                    </td>
                    <td>
                        {{ trans_choice(':number minute|:number minutes', $endpoint->checkup_delay, ['number'=>$endpoint->checkup_delay]) }}
                    </td>
                    @if( $endpoint->lastCheckup )
                    <td>
                        @datetime($endpoint->lastCheckup->started_at)
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
                    @else
                    <td colspan="2">
                        <span class="opacity-50">N/A</span>
                    </td>
                    @endif
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
</div>
