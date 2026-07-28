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

        {{-- Persistent mini-nav for the play mode --}}
        <div class="w-full max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 pt-3">
            <div class="flex items-center justify-between gap-3 py-2 border-b border-neutral-200">
                {{-- Left: challenge name + progress counter --}}
                <div class="min-w-0 flex-1 flex items-center gap-3">
                    @isset($challengeName)
                        <div class="min-w-0">
                            <div class="text-xs font-bold uppercase tracking-[0.15em] text-neutral-500">Playing</div>
                            <div class="text-sm font-display font-bold text-neutral-900 truncate">{{ $challengeName }}</div>
                        </div>
                        @isset($completedPuzzles, $totalPuzzles)
                            <div class="hidden sm:flex flex-col items-end flex-shrink-0 ml-2">
                                <span class="text-sm font-display font-black text-neutral-900 leading-none">{{ $completedPuzzles }}<span class="text-xs text-neutral-400">/{{ $totalPuzzles }}</span></span>
                                <span class="text-[10px] uppercase tracking-wider text-neutral-500 mt-0.5">Solved</span>
                            </div>
                        @endisset
                    @endisset
                </div>

                {{-- Right: status + dashboard links --}}
                <div class="flex items-center gap-1 sm:gap-2 flex-shrink-0">
                    @isset($enrollmentId)
                        <a href="{{ route('enrollments.show', $enrollmentId) }}"
                           class="btn btn-ghost btn-xs sm:btn-sm gap-1"
                           wire:navigate
                           title="Challenge status page">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            <span class="hidden sm:inline">Status</span>
                        </a>
                    @endisset
                    <a href="{{ route('dashboard') }}"
                       class="btn btn-ghost btn-xs sm:btn-sm gap-1"
                       wire:navigate
                       title="Back to dashboard">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span class="hidden sm:inline">Dashboard</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Page Content --}}
        <main class="w-full max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
            {{ $slot }}
        </main>

        @livewireScripts
    </body>
</html>
