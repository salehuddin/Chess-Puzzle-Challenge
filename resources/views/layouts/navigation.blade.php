<nav x-data="{ open: false }" class="bg-white border-b border-amber-100 shadow-warm sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                <span class="text-3xl group-hover:animate-float inline-block transition-all">♟</span>
                <span class="font-display font-bold text-lg text-stone-900 leading-tight">
                    Chess Puzzle <span class="text-primary">Challenge</span>
                </span>
            </a>

            {{-- Desktop Nav Links --}}
            <div class="hidden sm:flex items-center gap-6">
                <a href="{{ route('dashboard') }}"
                   class="text-sm font-medium transition-colors duration-150
                          {{ request()->routeIs('dashboard') ? 'text-primary font-semibold' : 'text-stone-600 hover:text-primary' }}">
                    My Challenges
                </a>
                <a href="{{ url('/challenges') }}"
                   class="text-sm font-medium transition-colors duration-150
                          {{ request()->is('challenges*') ? 'text-primary font-semibold' : 'text-stone-600 hover:text-primary' }}">
                    Browse
                </a>
                <a href="{{ url('/hall-of-fame') }}"
                   class="text-sm font-medium transition-colors duration-150
                          {{ request()->is('hall-of-fame') ? 'text-primary font-semibold' : 'text-stone-600 hover:text-primary' }}">
                    🏆 Hall of Fame
                </a>
            </div>

            {{-- User Dropdown --}}
            <div class="hidden sm:flex items-center gap-4">
                {{-- Sticker count badge --}}
                <span class="badge badge-accent badge-sm gap-1 font-medium">
                    ✦ {{ auth()->user()->stickers()->count() }}
                </span>

                {{-- Dropdown --}}
                <div class="dropdown dropdown-end">
                    <div
                        tabindex="0"
                        role="button"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-full border border-stone-200 hover:border-primary/50 hover:bg-primary/5 transition-all cursor-pointer"
                    >
                        @if(Auth::user()->avatar)
                            <img src="{{ Storage::url(Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="w-7 h-7 rounded-full object-cover" />
                        @else
                            <div class="w-7 h-7 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <span class="text-sm font-medium text-stone-700 max-w-24 truncate">{{ Auth::user()->name }}</span>
                        <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>

                    <ul tabindex="0" class="dropdown-content menu menu-sm mt-2 w-52 rounded-xl shadow-warm-lg bg-white border border-amber-100 z-50 p-1.5">
                        <li class="px-3 py-2 border-b border-stone-100 mb-1">
                            <p class="text-xs text-stone-400 font-medium uppercase tracking-wider">Signed in as</p>
                            <p class="text-sm font-semibold text-stone-800 truncate">{{ Auth::user()->email }}</p>
                        </li>
                        <li>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 text-stone-700 hover:text-primary hover:bg-primary/5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Profile & Address
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-stone-700 hover:text-primary hover:bg-primary/5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                Dashboard
                            </a>
                        </li>
                        @if(Auth::user()->isPubliclyViewable())
                        <li>
                            <a href="{{ route('profile.show', Auth::user()->username) }}" target="_blank" class="flex items-center gap-2 text-stone-700 hover:text-primary hover:bg-primary/5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                Public Profile
                            </a>
                        </li>
                        @endif
                        <li class="border-t border-stone-100 mt-1 pt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2 text-error hover:bg-error/5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Log Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Mobile Hamburger --}}
            <button
                @click="open = !open"
                class="sm:hidden btn btn-ghost btn-sm btn-square"
                :aria-expanded="open"
                aria-label="Toggle menu"
            >
                <svg x-show="!open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="sm:hidden border-t border-amber-100 bg-white"
    >
        <div class="px-4 py-4 space-y-1">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg text-stone-700 hover:bg-amber-50 hover:text-primary font-medium text-sm">My Challenges</a>
            <a href="{{ url('/challenges') }}" class="block px-3 py-2 rounded-lg text-stone-700 hover:bg-amber-50 hover:text-primary font-medium text-sm">Browse</a>
            <a href="{{ url('/hall-of-fame') }}" class="block px-3 py-2 rounded-lg text-stone-700 hover:bg-amber-50 hover:text-primary font-medium text-sm">🏆 Hall of Fame</a>
        </div>
        <div class="px-4 py-4 border-t border-stone-100">
            <p class="text-xs text-stone-400 mb-1">{{ Auth::user()->name }}</p>
            <p class="text-sm text-stone-500 mb-3">{{ Auth::user()->email }}</p>
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg text-stone-700 hover:bg-amber-50 text-sm mb-1">Dashboard</a>
            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-lg text-stone-700 hover:bg-amber-50 text-sm mb-1">Profile & Address</a>
            @if(Auth::user()->isPubliclyViewable())
                <a href="{{ route('profile.show', Auth::user()->username) }}" target="_blank" class="block px-3 py-2 rounded-lg text-stone-700 hover:bg-amber-50 text-sm mb-1">Public Profile</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-error hover:bg-error/5 text-sm font-medium">
                    Log Out
                </button>
            </form>
        </div>
    </div>
</nav>
