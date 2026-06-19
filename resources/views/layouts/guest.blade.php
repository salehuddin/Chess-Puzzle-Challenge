<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="chess">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Chess Puzzle Challenge') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">

        {{-- Chess board pattern background --}}
        <div class="min-h-screen bg-chess-pattern-green flex flex-col items-center justify-center px-4 py-12">

            {{-- Frosted overlay for readability --}}
            <div class="absolute inset-0 bg-white/10 backdrop-blur-[1px]"></div>

            <div class="relative z-10 w-full max-w-md">

                {{-- Logo --}}
                <a href="{{ url('/') }}" class="flex flex-col items-center mb-8 group">
                    <div class="w-16 h-16 bg-white rounded-2xl shadow-warm-lg flex items-center justify-center mb-3 group-hover:scale-105 transition-transform duration-200">
                        <span class="text-4xl">♟</span>
                    </div>
                    <span class="font-display font-bold text-2xl text-white drop-shadow-lg">
                        Chess Puzzle <span class="text-accent">Challenge</span>
                    </span>
                    <span class="text-white/70 text-sm mt-1">Play. Complete. Earn your medal.</span>
                </a>

                {{-- Form Card --}}
                <div class="bg-white rounded-2xl shadow-warm-lg p-8">
                    {{ $slot }}
                </div>

                {{-- Back to home --}}
                <div class="text-center mt-6">
                    <a href="{{ url('/') }}" class="text-white/70 hover:text-white text-sm transition-colors duration-150">
                        ← Back to home
                    </a>
                </div>
            </div>
        </div>

    </body>
</html>
