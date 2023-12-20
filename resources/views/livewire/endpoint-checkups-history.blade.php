<div class="card bg-base-200 shadow-xl p-5">
    <header class="mb-6">
        <h2 class="card-title">
            {{ __('History') }}
        </h2>
    </header>

    @if( $checkups->isEmpty() )
    <div role="alert" class="alert bg-base-300">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>{{ __("No check up yet.") }}</span>
        <livewire:endpoint-run-button :endpoint="$endpoint" />
    </div>
    @else
    <table class="table table-xs">
        <thead>
            <tr>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Duration') }}</th>
                <th>{{ __('Status code') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($checkups as $checkup)
            <tr>
                <td>{{ $checkup->started_at }}</td>
                <td>{{ $checkup->microtime }}</td>
                <td>
                    <div class="flex items-center gap-2">
                        @if( !empty($checkup->status_code) )
                        <span>{{ $checkup->status_code }}</span>
                        @endif

                        @if( !empty($checkup->exception_message) )
                        <div class="tooltip" data-tip="{{ $checkup->exception_message }}">
                            <span class="text-error">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 1.67c.955 0 1.845 .467 2.39 1.247l.105 .16l8.114 13.548a2.914 2.914 0 0 1 -2.307 4.363l-.195 .008h-16.225a2.914 2.914 0 0 1 -2.582 -4.2l.099 -.185l8.11 -13.538a2.914 2.914 0 0 1 2.491 -1.403zm.01 13.33l-.127 .007a1 1 0 0 0 0 1.986l.117 .007l.127 -.007a1 1 0 0 0 0 -1.986l-.117 -.007zm-.01 -7a1 1 0 0 0 -.993 .883l-.007 .117v4l.007 .117a1 1 0 0 0 1.986 0l.007 -.117v-4l-.007 -.117a1 1 0 0 0 -.993 -.883z" stroke-width="0" fill="currentColor" /></svg>
                            </span>
                        </div>
                        @endif

                        @if( empty($checkup->status_code) && empty($checkup->exception_message) )
                        <span class="opacity-50">N/A</span>
                        @endif
                    </div>
                    
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
