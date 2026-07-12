<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="chess">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Chess Puzzle Challenge') }}</title>
        <meta name="description" content="{{ $description ?? 'Solve 100 chess puzzles. Earn a custom physical medal. Shipped to your door.' }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="antialiased bg-base-100 text-base-content">

        {{-- ── Sticky Navigation ──────────────────────────────────────────── --}}
        <header
            x-data="{ open: false, scrolled: false }"
            @scroll.window="scrolled = window.scrollY > 10"
            :class="scrolled ? 'shadow-warm-lg bg-white/95 backdrop-blur-sm' : 'bg-white'"
            class="sticky top-0 z-50 border-b border-amber-100 transition-all duration-300"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">

                    {{-- Logo --}}
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                        <span class="text-3xl group-hover:animate-float inline-block transition-all">♟</span>
                        <span class="font-display font-bold text-lg text-stone-900 leading-tight">
                            Chess Puzzle
                            <span class="text-primary">Challenge</span>
                        </span>
                    </a>

                    {{-- Desktop Nav Links --}}
                    <nav class="hidden md:flex items-center gap-8">
                        <a href="{{ url('/challenges') }}"
                           class="text-stone-600 hover:text-primary font-medium text-sm transition-colors duration-150
                                  {{ request()->is('challenges*') ? 'text-primary font-semibold' : '' }}">
                            Challenges
                        </a>
                        <a href="{{ url('/challenges#bundles') }}"
                           class="text-stone-600 hover:text-primary font-medium text-sm transition-colors duration-150">
                            Bundles
                        </a>
                        <a href="{{ url('/hall-of-fame') }}"
                           class="text-stone-600 hover:text-primary font-medium text-sm transition-colors duration-150">
                            🏆 Hall of Fame
                        </a>
                        <a href="{{ route('docs.index') }}"
                           class="text-stone-600 hover:text-primary font-medium text-sm transition-colors duration-150">
                            Docs
                        </a>
                    </nav>

                    {{-- Desktop CTA --}}
                    <div class="hidden md:flex items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm gap-1">
                                My Dashboard <span>→</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-stone-600 hover:text-primary font-medium text-sm transition-colors">
                                Login
                            </a>
                            <a href="{{ route('register') }}" class="btn btn-primary btn-sm">
                                Get Started →
                            </a>
                        @endauth
                    </div>

                    {{-- Mobile Hamburger --}}
                    <button
                        @click="open = !open"
                        class="md:hidden btn btn-ghost btn-sm btn-square"
                        :aria-expanded="open"
                        aria-label="Toggle navigation"
                    >
                        <svg x-show="!open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg x-show="open" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Mobile Menu --}}
                <div
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2"
                    class="md:hidden border-t border-amber-100 py-4 space-y-1"
                >
                    <a href="{{ url('/challenges') }}" class="block px-3 py-2 rounded-lg text-stone-700 hover:bg-amber-50 hover:text-primary font-medium text-sm transition-colors">Challenges</a>
                    <a href="{{ url('/challenges#bundles') }}" class="block px-3 py-2 rounded-lg text-stone-700 hover:bg-amber-50 hover:text-primary font-medium text-sm transition-colors">Bundles</a>
                    <a href="{{ url('/hall-of-fame') }}" class="block px-3 py-2 rounded-lg text-stone-700 hover:bg-amber-50 hover:text-primary font-medium text-sm transition-colors">🏆 Hall of Fame</a>
                    <a href="{{ route('docs.index') }}" class="block px-3 py-2 rounded-lg text-stone-700 hover:bg-amber-50 hover:text-primary font-medium text-sm transition-colors">Docs</a>
                    <div class="pt-3 border-t border-amber-100 flex flex-col gap-2">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm w-full">My Dashboard →</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-ghost btn-sm w-full">Login</a>
                            <a href="{{ route('register') }}" class="btn btn-primary btn-sm w-full">Get Started →</a>
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        {{-- ── Page Content ───────────────────────────────────────────────── --}}
        <main>
            {{ $slot }}
        </main>

        {{-- ── Footer ─────────────────────────────────────────────────────── --}}
        <footer class="bg-neutral text-neutral-content mt-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-10">

                    {{-- Brand --}}
                    <div>
                        <div class="flex items-center gap-2.5 mb-3">
                            <span class="text-4xl opacity-80">♟</span>
                            <span class="font-display font-bold text-xl opacity-90">Chess Puzzle Challenge</span>
                        </div>
                        <p class="text-sm opacity-50 leading-relaxed">
                            Solve 100 puzzles. Earn a custom-designed physical medal. Shipped to your door, anywhere in the world.
                        </p>
                    </div>

                    {{-- Links --}}
                    <div>
                        <h3 class="font-semibold text-sm uppercase tracking-wider opacity-50 mb-4">Platform</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="{{ url('/challenges') }}" class="opacity-70 hover:opacity-100 transition-opacity">Browse Challenges</a></li>
                            <li><a href="{{ url('/challenges#bundles') }}" class="opacity-70 hover:opacity-100 transition-opacity">Bundles</a></li>
                            <li><a href="{{ url('/hall-of-fame') }}" class="opacity-70 hover:opacity-100 transition-opacity">Hall of Fame</a></li>
                            <li><a href="{{ route('docs.index') }}" class="opacity-70 hover:opacity-100 transition-opacity">Documentation</a></li>
                        </ul>
                    </div>

                    {{-- Account --}}
                    <div>
                        <h3 class="font-semibold text-sm uppercase tracking-wider opacity-50 mb-4">Account</h3>
                        <ul class="space-y-2 text-sm">
                            @auth
                                <li><a href="{{ route('dashboard') }}" class="opacity-70 hover:opacity-100 transition-opacity">My Dashboard</a></li>
                                <li><a href="{{ route('profile.edit') }}" class="opacity-70 hover:opacity-100 transition-opacity">Profile & Address</a></li>
                            @else
                                <li><a href="{{ route('login') }}" class="opacity-70 hover:opacity-100 transition-opacity">Login</a></li>
                                <li><a href="{{ route('register') }}" class="opacity-70 hover:opacity-100 transition-opacity">Create Account</a></li>
                            @endauth
                        </ul>
                    </div>
                </div>

                <div class="border-t border-white/10 pt-8 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm opacity-40">
                    <p>© {{ date('Y') }} Chess Puzzle Challenge. All rights reserved.</p>
                    <p>Powering physical trophies for digital victories.</p>
                </div>
            </div>
        </footer>

        @livewireScripts
    </body>
</html>
