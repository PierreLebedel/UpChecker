<x-app-layout>

    <x-slot name="header_title">
        {{ __('404: Not found') }}
    </x-slot>

    <p class="pb-5">{{ __('The URL you requested was not found on the app.') }}</p>

    <p class=""><a href="{{ route('dashboard') }}" class="btn btn-sm btn-primary">{{ __('Back to dashboard') }}</a></p>
    

</x-app-layout>
