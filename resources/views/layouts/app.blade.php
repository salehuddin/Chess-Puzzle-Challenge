<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="chess">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Chess Puzzle Challenge') }}</title>
        <meta name="description" content="{{ $description ?? 'Solve chess puzzles and earn physical medals.' }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('vite')
        @livewireStyles
    </head>
    <body class="antialiased bg-base-100 text-base-content min-h-screen">

        @include('layouts.navigation')

        {{-- Optional page header --}}
        @isset($header)
            <div class="bg-white border-b border-neutral-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    {{ $header }}
                </div>
            </div>
        @endisset

        {{-- Page Content --}}
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{ $slot }}
        </main>

        @livewireScripts
    </body>
</html>
