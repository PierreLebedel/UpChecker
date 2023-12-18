<x-app-layout>

    <x-slot name="header">
        <h1 class="text-2xl">
            {{ __('Dashboard') }}
        </h1>
    </x-slot>

    <x-slot name="breadcrumb">
        <li><a href="{{ route('home') }}">{{ config('app.name') }}</a></li> 
        <li>{{ __('Dashboard') }}</li>
    </x-slot>

    <div role="alert" class="alert alert-success">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span>{{ __("You're logged in!") }}</span>
    </div>

</x-app-layout>