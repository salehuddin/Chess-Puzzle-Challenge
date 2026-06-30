<x-marketing-layout>
    <x-slot name="title">Chess Puzzle Challenge — Play. Complete. Earn Your Medal.</x-slot>
    <x-slot name="description">Solve 100 curated chess puzzles from the Lichess database. Complete your series and earn a custom-designed physical medal shipped to your door.</x-slot>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- HERO                                                            --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden bg-base-100 pt-16 pb-24 lg:pt-24 lg:pb-32">

        {{-- Background decorations --}}
        <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
            <div class="absolute -top-16 -right-16 w-96 h-96 rounded-full bg-primary/5 blur-3xl"></div>
            <div class="absolute bottom-0 -left-16 w-80 h-80 rounded-full bg-accent/10 blur-3xl"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

                {{-- Left: Copy --}}
                <div>
                    <div class="inline-flex items-center gap-2 bg-primary/10 text-primary text-sm font-semibold px-4 py-1.5 rounded-full mb-6">
                        <span class="w-2 h-2 rounded-full bg-primary animate-pulse inline-block"></span>
                        Winter 2026 Series — Now Live
                    </div>

                    <h1 class="font-display text-5xl lg:text-6xl font-black text-stone-900 leading-tight mb-6">
                        Solve Puzzles.<br>
                        <span class="text-gradient-green">Earn Your</span><br>
                        <span class="text-gradient-gold">Physical Medal.</span>
                    </h1>

                    <p class="text-xl text-stone-600 leading-relaxed mb-8 max-w-lg">
                        Work through 100 curated chess puzzles from the Lichess database.
                        Complete the series and we'll ship you a <strong class="text-stone-900">custom-designed physical medal</strong> — plus a digital sticker for your Hall of Fame.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 mb-10">
                        <a href="{{ url('/challenges') }}" id="btn-hero-browse" class="btn btn-primary btn-lg shadow-warm-lg gap-2">
                            Browse Challenges
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                        <a href="#how-it-works" id="btn-hero-how" class="btn btn-outline btn-primary btn-lg gap-2">
                            How it works
                        </a>
                    </div>

                    {{-- Social proof --}}
                    <div class="flex items-center gap-6">
                        <div class="flex -space-x-2">
                            @foreach(['🟢','🔵','🟠','🟣','🔴'] as $color)
                                <div class="w-8 h-8 rounded-full border-2 border-white bg-base-200 flex items-center justify-center text-xs font-bold text-stone-600 shadow-sm">
                                    {{ chr(65 + $loop->index) }}
                                </div>
                            @endforeach
                        </div>
                        <p class="text-sm text-stone-500">
                            <span class="font-bold text-stone-900">1,200+</span> chess players have already joined
                        </p>
                    </div>
                </div>

                {{-- Right: Chess Board Illustration --}}
                <div class="flex justify-center lg:justify-end">
                    <div class="relative">
                        {{-- Main chess board --}}
                        <div class="relative rounded-2xl overflow-hidden shadow-[0_25px_60px_-10px_rgba(28,25,23,0.25)] rotate-3 transform hover:rotate-0 transition-transform duration-500">
                            <div class="grid grid-cols-8 w-72 h-72 lg:w-80 lg:h-80">
                                @php
                                    $pieces = [
                                        [0,0,'♜'],[0,1,'♞'],[0,2,'♝'],[0,3,'♛'],[0,4,'♚'],[0,5,'♝'],[0,6,'♞'],[0,7,'♜'],
                                        [1,0,'♟'],[1,1,'♟'],[1,2,'♟'],[1,3,'♟'],[1,4,'♟'],[1,5,'♟'],[1,6,'♟'],[1,7,'♟'],
                                        [6,0,'♙'],[6,1,'♙'],[6,2,'♙'],[6,3,'♙'],[6,4,'♙'],[6,5,'♙'],[6,6,'♙'],[6,7,'♙'],
                                        [7,0,'♖'],[7,1,'♘'],[7,2,'♗'],[7,3,'♕'],[7,4,'♔'],[7,5,'♗'],[7,6,'♘'],[7,7,'♖'],
                                    ];
                                    $pieceMap = [];
                                    foreach ($pieces as $p) { $pieceMap[$p[0].','.$p[1]] = $p[2]; }
                                @endphp
                                @for ($row = 0; $row < 8; $row++)
                                    @for ($col = 0; $col < 8; $col++)
                                        @php $isLight = ($row + $col) % 2 === 0; $piece = $pieceMap[$row.','.$col] ?? ''; @endphp
                                        <div class="aspect-square flex items-center justify-center text-lg select-none
                                            {{ $isLight ? 'bg-[#F0D9B5]' : 'bg-[#B58863]' }}">
                                            @if($piece)
                                                <span class="{{ $row < 2 ? 'text-stone-800' : 'text-white drop-shadow' }}">{{ $piece }}</span>
                                            @endif
                                        </div>
                                    @endfor
                                @endfor
                            </div>
                        </div>

                        {{-- Floating award badge --}}
                        <div class="absolute -bottom-6 -left-8 bg-white rounded-2xl shadow-warm-lg px-5 py-3 flex items-center gap-3 achievement-glow animate-float">
                            <span class="text-3xl">🏅</span>
                            <div>
                                <p class="text-xs text-stone-400 font-medium">Latest achievement</p>
                                <p class="text-sm font-bold text-stone-900">Winter Beginner Medal</p>
                            </div>
                        </div>

                        {{-- Floating puzzle count badge --}}
                        <div class="absolute -top-4 -right-6 bg-primary text-white rounded-2xl shadow-warm-lg px-4 py-2.5 text-center">
                            <p class="text-2xl font-black leading-none">100</p>
                            <p class="text-xs font-semibold opacity-80 mt-0.5">Puzzles</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- STATS BAR                                                       --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <section class="bg-primary text-white py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                @foreach([
                    ['300+', 'Puzzles Available'],
                    ['3', 'Active Challenges'],
                    ['2', 'Bundle Deals'],
                    ['🌍', 'Worldwide Shipping'],
                ] as [$value, $label])
                    <div>
                        <p class="font-display text-4xl font-black mb-1">{{ $value }}</p>
                        <p class="text-sm opacity-75 font-medium">{{ $label }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- HOW IT WORKS                                                    --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <section id="how-it-works" class="py-24 bg-base-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center mb-16">
                <span class="inline-block text-primary font-semibold text-sm uppercase tracking-widest mb-3">Simple Process</span>
                <h2 class="font-display text-4xl lg:text-5xl font-black text-stone-900">How It Works</h2>
                <p class="text-stone-500 mt-4 text-lg max-w-xl mx-auto">Three steps from sign-up to holding your medal in your hands.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
                {{-- Connecting line (desktop) --}}
                <div class="hidden md:block absolute top-12 left-1/6 right-1/6 h-0.5 bg-gradient-to-r from-primary/30 via-accent/50 to-primary/30 pointer-events-none" aria-hidden="true"></div>

                @foreach([
                    ['🎯', '01', 'Choose a Challenge', 'Browse our curated Puzzle Series. Each challenge has 100 Lichess-sourced puzzles filtered by difficulty and theme — from beginner forks to advanced endgames.', 'bg-primary/10 text-primary'],
                    ['♟', '02', 'Solve 100 Puzzles', 'Play at your own pace directly in your browser. Your progress is saved automatically — close the tab and pick up exactly where you left off, puzzle by puzzle.', 'bg-accent/10 text-amber-700'],
                    ['🏅', '03', 'Receive Your Medal', 'The moment you finish all 100 puzzles, a digital sticker is instantly unlocked in your Hall of Fame. Your physical medal is then custom-made and shipped to your door.', 'bg-secondary/10 text-secondary'],
                ] as [$icon, $step, $title, $desc, $iconBg])
                    <div class="relative bg-white rounded-2xl shadow-warm p-8 border border-stone-100 hover:shadow-warm-lg hover:-translate-y-1 transition-all duration-300">
                        {{-- Step number --}}
                        <div class="absolute -top-4 left-8 bg-white border border-stone-200 rounded-full w-8 h-8 flex items-center justify-center text-xs font-black text-stone-400 shadow-sm">
                            {{ $step }}
                        </div>
                        <div class="w-14 h-14 rounded-2xl {{ $iconBg }} flex items-center justify-center text-2xl mb-5">
                            {{ $icon }}
                        </div>
                        <h3 class="font-display text-xl font-bold text-stone-900 mb-3">{{ $title }}</h3>
                        <p class="text-stone-500 text-sm leading-relaxed">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- FEATURED CHALLENGES                                             --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <section id="challenges" class="py-24 bg-base-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-4">
                <div>
                    <span class="inline-block text-primary font-semibold text-sm uppercase tracking-widest mb-3">Winter 2026 Series</span>
                    <h2 class="font-display text-4xl lg:text-5xl font-black text-stone-900">Current Challenges</h2>
                </div>
                <a href="{{ url('/challenges') }}" class="btn btn-outline btn-primary shrink-0">
                    View All Challenges →
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @forelse($challenges as $challenge)
                    @php
                        $levelData = match(true) {
                            str_contains(strtolower($challenge->name), 'beginner') => ['🌱', 'Beginner', 'badge-success'],
                            str_contains(strtolower($challenge->name), 'intermediate') => ['⚡', 'Intermediate', 'badge-warning'],
                            str_contains(strtolower($challenge->name), 'advanced') => ['🔥', 'Advanced', 'badge-error'],
                            default => ['♟', 'Challenge', 'badge-primary'],
                        };
                        $rules = $challenge->rules ?? [];
                    @endphp
                    <div class="bg-white rounded-2xl shadow-warm overflow-hidden border border-stone-100 hover:shadow-warm-lg hover:-translate-y-1 transition-all duration-300 flex flex-col">
                        {{-- Card header with chess pattern --}}
                        <div class="bg-chess-pattern h-24 relative">
                            <div class="absolute inset-0 bg-gradient-to-b from-transparent to-white/80"></div>
                            <div class="absolute bottom-3 left-5">
                                <span class="badge {{ $levelData[2] }} badge-sm gap-1 font-semibold">
                                    {{ $levelData[0] }} {{ $levelData[1] }}
                                </span>
                            </div>
                        </div>

                        <div class="p-6 flex flex-col flex-1">
                            <h3 class="font-display text-xl font-bold text-stone-900 mb-2 leading-snug">
                                {{ $challenge->name }}
                            </h3>
                            <p class="text-stone-500 text-sm leading-relaxed mb-4 line-clamp-2 flex-1">
                                {{ $challenge->description }}
                            </p>

                            {{-- Details --}}
                            <div class="grid grid-cols-2 gap-3 mb-5 text-sm">
                                <div class="flex items-center gap-2 text-stone-600">
                                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    <span><strong>{{ $challenge->puzzles_count ?? $challenge->puzzle_count }}</strong> puzzles</span>
                                </div>
                                <div class="flex items-center gap-2 text-stone-600">
                                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>{{ ($rules['order'] ?? null) === 'sequential' ? 'Sequential' : 'Any order' }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-stone-600">
                                    <span>🏅</span>
                                    <span>Physical medal</span>
                                </div>
                                <div class="flex items-center gap-2 text-stone-600">
                                    <span>✦</span>
                                    <span>Digital sticker</span>
                                </div>
                            </div>

                            {{-- Pricing --}}
                            <div class="flex items-center justify-between mb-5 pt-4 border-t border-stone-100">
                                <div>
                                    <p class="text-2xl font-black text-stone-900">MYR {{ number_format($challenge->price_myr, 2) }}</p>
                                    <p class="text-xs text-stone-400">or USD {{ number_format($challenge->price_usd, 2) }}</p>
                                </div>
                                <span class="badge badge-outline badge-sm text-stone-400">one-time</span>
                            </div>

                            <a href="{{ url('/challenges/'.$challenge->slug) }}" id="btn-challenge-{{ $challenge->id }}" class="btn btn-primary w-full gap-2">
                                Start Challenge →
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-16 text-stone-400">
                        <p class="text-4xl mb-4">♟</p>
                        <p class="text-lg font-medium">No challenges yet — check back soon!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- BUNDLES                                                         --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <section id="bundles" class="py-24 bg-base-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center mb-12">
                <span class="inline-block text-accent font-semibold text-sm uppercase tracking-widest mb-3">Best Value</span>
                <h2 class="font-display text-4xl lg:text-5xl font-black text-stone-900">Bundle & Save</h2>
                <p class="text-stone-500 mt-4 text-lg max-w-xl mx-auto">
                    Get multiple challenges in one purchase. Multiple medals. One great price.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                @forelse($bundles as $bundle)
                    <div class="relative bg-white rounded-2xl border-2 border-accent/30 shadow-warm overflow-hidden hover:shadow-warm-lg hover:-translate-y-1 transition-all duration-300">

                        {{-- Best value ribbon --}}
                        <div class="absolute top-5 right-0 bg-accent text-stone-900 text-xs font-black px-4 py-1.5 rounded-l-full shadow-md">
                            🎉 SAVE MORE
                        </div>

                        <div class="p-8">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 rounded-xl bg-accent/10 flex items-center justify-center text-2xl">🎁</div>
                                <h3 class="font-display text-2xl font-bold text-stone-900">{{ $bundle->name }}</h3>
                            </div>

                            <p class="text-stone-500 text-sm leading-relaxed mb-6">{{ $bundle->description }}</p>

                            {{-- Included challenges --}}
                            @if($bundle->challenges->isNotEmpty())
                                <div class="space-y-2 mb-6">
                                    <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-2">Includes</p>
                                    @foreach($bundle->challenges as $c)
                                        <div class="flex items-center gap-2 text-sm text-stone-700">
                                            <svg class="w-4 h-4 text-primary shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            {{ $c->name }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex items-center justify-between pt-4 border-t border-stone-100">
                                <div>
                                    <p class="text-3xl font-black text-stone-900">MYR {{ number_format($bundle->price_myr, 2) }}</p>
                                    <p class="text-xs text-stone-400">or USD {{ number_format($bundle->price_usd, 2) }}</p>
                                </div>
                                <a href="{{ route('bundles.enroll', $bundle) }}" id="btn-bundle-{{ $bundle->id }}" class="btn btn-accent gap-2">
                                    Get Bundle →
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 text-center py-12 text-stone-400">
                        <p>No bundles available yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- HALL OF FAME TEASER                                             --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <section class="py-24 bg-base-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl shadow-warm-lg overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">

                    {{-- Left: Copy --}}
                    <div class="p-10 lg:p-14 flex flex-col justify-center">
                        <span class="inline-block text-accent font-semibold text-sm uppercase tracking-widest mb-3">Your Collection</span>
                        <h2 class="font-display text-3xl lg:text-4xl font-black text-stone-900 mb-4">
                            Build Your<br>Hall of Fame
                        </h2>
                        <p class="text-stone-500 leading-relaxed mb-8">
                            Every completed challenge earns you a unique digital sticker — displayed in your personal Hall of Fame. Collect them all, show them off, and track your chess journey.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3">
                            @auth
                                <a href="{{ url('/hall-of-fame') }}" class="btn btn-primary gap-2">View My Hall of Fame →</a>
                            @else
                                <a href="{{ route('register') }}" class="btn btn-primary gap-2">Start Collecting →</a>
                                <a href="{{ route('login') }}" class="btn btn-ghost">Already have an account</a>
                            @endauth
                        </div>
                    </div>

                    {{-- Right: Sticker grid preview --}}
                    <div class="bg-chess-pattern-green p-10 flex items-center justify-center">
                        <div class="grid grid-cols-3 gap-4">
                            @foreach(['♔','♕','♖','♗','♘','♟'] as $i => $piece)
                                <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-4xl shadow-md
                                    {{ $i < 3 ? 'bg-white achievement-glow animate-float' : 'bg-white/30 backdrop-blur-sm grayscale' }}"
                                    style="animation-delay: {{ $i * 0.3 }}s"
                                >
                                    {{ $piece }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- FINAL CTA BANNER                                                --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <section class="py-24 bg-neutral text-neutral-content relative overflow-hidden">

        {{-- Background chess pattern --}}
        <div class="absolute inset-0 bg-chess-pattern opacity-5 pointer-events-none" aria-hidden="true"></div>

        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 text-center">
            <div class="text-6xl mb-6 animate-float inline-block">♟</div>
            <h2 class="font-display text-4xl lg:text-5xl font-black mb-6">
                Ready to Earn Your<br>
                <span class="text-gradient-gold">First Medal?</span>
            </h2>
            <p class="text-white/70 text-xl leading-relaxed mb-10">
                Join over 1,200 chess enthusiasts who are solving puzzles and building their trophy case — one medal at a time.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @auth
                    <a href="{{ url('/challenges') }}" id="btn-cta-challenges" class="btn btn-accent btn-lg gap-2 shadow-warm-lg">
                        Browse Challenges →
                    </a>
                @else
                    <a href="{{ route('register') }}" id="btn-cta-register" class="btn btn-accent btn-lg gap-2 shadow-warm-lg">
                        Create Free Account →
                    </a>
                    <a href="{{ url('/challenges') }}" id="btn-cta-browse" class="btn btn-outline btn-lg border-white/30 text-white hover:bg-white/10">
                        Browse First
                    </a>
                @endauth
            </div>
        </div>
    </section>

</x-marketing-layout>
