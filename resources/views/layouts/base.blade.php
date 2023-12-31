<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-base-300 min-h-screen">
        
        @include('layouts.navigation')

        {{ $slot }}

        @include('layouts.footer')

        <script>
            window.UpChecker = {!! json_encode([
                'user' => (auth()->check()) ? auth()->user()->toArray() : false,
            ]) !!};
        </script>

    </body>
</html>
