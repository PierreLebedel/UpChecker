<x-app-layout>
    
    <x-slot name="header">
        <h1 class="text-2xl">
            {{ __('Profile') }}
        </h1>
    </x-slot>

    <x-slot name="breadcrumb">
        <li><a href="{{ route('home') }}">{{ config('app.name') }}</a></li> 
        <li><a href="#">{{ auth()->user()->name }}</a></li> 
        <li>{{ __('Dashboard') }}</li>
    </x-slot>

    <div class="flex flex-col gap-8">
        @include('profile.partials.update-profile-information-form')
        @include('profile.partials.update-password-form')
        @include('profile.partials.delete-user-form')
    </div>
    

</x-app-layout>
