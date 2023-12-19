<x-app-layout>
    
    <x-slot name="header_title">
        {{ __('Profile') }}
    </x-slot>

    <div class="grid grid-cols-3 gap-8">
        <div class="col-span-2 flex flex-col gap-8">
            @include('profile.partials.update-profile-information-form')
            @include('profile.partials.update-password-form')
        </div>
        <div class="">
            @include('profile.partials.delete-user-form')
        </div>
    </div>    

</x-app-layout>
