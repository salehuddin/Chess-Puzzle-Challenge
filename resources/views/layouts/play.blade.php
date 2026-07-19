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

        {{-- Brand accent --}}
        <div class="h-1 bg-brand"></div>

        {{-- Minimal escape hatch (no nav, no footer) --}}
        <div class="w-full max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 pt-4 flex justify-end">
            <a href="{{ route('dashboard') }}" class="text-xs sm:text-sm text-neutral-500 hover:text-neutral-900 transition-colors">
                ← Exit to Dashboard
            </a>
        </div>

        {{-- Page Content --}}
        <main class="w-full max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
            {{ $slot }}
        </main>

        @livewireScripts
    </body>
</html>
