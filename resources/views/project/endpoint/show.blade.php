<x-app-layout>

    <x-slot name="header_title">
        <span class="font-normal opacity-50">{{ __('Endpoint :') }}</span>
        <span class="font-semibold">{{ $endpoint->url }}</span>
    </x-slot>

    <x-slot name="header_actions">
        <a href="{{ route('endpoint.edit', [$project, $endpoint]) }}" class="btn btn-primary btn-sm">{{ __("Edit endpoint") }}</a>
        <livewire:endpoint-run-button :endpoint="$endpoint" />
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
            <span class="font-normal opacity-50">{{ __('Endpoint :') }}&nbsp;</span>
            <span class="font-semibold">{{ $endpoint->url }}</span>
        </li>
    </x-slot>

    <div class="grid grid-cols-2 gap-8">
        <div class="">
            @if( $endpoint->behaviors->isEmpty() )
            <div role="alert" class="alert alert-info mb-4 w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ __("No behavior yet.") }}</span>
                <a href="{{ route('behavior.create', [$project, $endpoint]) }}" class="btn btn-sm btn-primary">{{ __("Create first behavior") }}</a>
            </div>
            @else
                {{-- <h2 class="text-2xl">{{ $endpoint->behaviors->count() }} {{ __('behaviors') }}</h2> --}}

                <div class="gap-4">
                @foreach( $endpoint->behaviors as $behavior )
                    <div class="card bg-base-200 shadow-xl pt-4">
                        <header class="mb-4 px-4">
                            <div class="flex justify-between items-center">
                                <h2 class="card-title">
                                    {{ __('Behavior') }} #{{ $loop->index+1 }}
                                </h2>
                                <div>
                                    <a href="{{ route('behavior.edit', [$project, $endpoint, $behavior]) }}" class="btn btn-primary btn-sm">{{ __("Edit behavior") }}</a>
                                    <a href="{{ route('behavior.destroy', [$project, $endpoint, $behavior]) }}" class="btn btn-error btn-sm">{{ __("Delete") }}</a>
                                </div>
                                
                            </div>
                            
                        </header>

                        <div class="px-4 pb-4">
                           <h3 class="text-lg">{{ __("Rules:") }}</h3>
                           <ul class="list-disc list-inside">
                                @foreach($behavior->rules as $rule)
                                <li>
                                    {{ $rule->compare_field }}
                                    {{ $rule->compare_sign }}
                                    {{ $rule->compare_value }}
                                </li>
                                @endforeach
                           </ul>

                            <hr class="my-3">

                            <h3 class="text-lg">{{ __("Actions:") }}</h3>
                            <ul class="list-disc list-inside">
                                @foreach($behavior->actions as $action)
                                <li>
                                    {{ $action->type }}
                                </li>
                                @endforeach
                           </ul>
                        </div>

                        {{-- @dump($behavior) --}}
                    </div>    
                
                @endforeach
                </div>
            @endif


            {{-- @dump($endpoint->getAttributes()) --}}

        </div>
        <div class="flex flex-col gap-8">
            <livewire:endpoint-checkups-history :endpoint="$endpoint" />
        </div>
        
    </div>  

    <div class="mt-6">
        <livewire:endpoint-delete-button :endpoint="$endpoint" />
    </div>

</x-app-layout>
