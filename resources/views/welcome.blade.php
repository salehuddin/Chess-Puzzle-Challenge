<x-marketing-layout>
    <x-slot name="title">Chess Puzzle Challenge — Play. Complete. Earn Your Medal.</x-slot>
    <x-slot name="description">Solve 100 curated chess puzzles from the Lichess database. Complete your series and earn a custom-designed physical medal shipped to your door.</x-slot>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- HERO                                                            --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden bg-white pt-16 pb-20 lg:pt-24 lg:pb-28">

        {{-- Subtle dot pattern background --}}
        <div class="absolute inset-0 bg-dot-pattern-dark pointer-events-none" aria-hidden="true"></div>

        {{-- Chartreuse glow blob top-right --}}
        <div class="absolute -top-32 -right-32 w-[28rem] h-[28rem] rounded-full bg-brand/10 blur-[100px] pointer-events-none animate-pulse-soft" aria-hidden="true"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">

                {{-- Left: Copy --}}
                <div class="reveal-left">
                    <div class="inline-flex items-center gap-2 bg-brand text-neutral-900 text-xs font-black px-3 py-1.5 rounded-full mb-6 uppercase tracking-widest">
                        <span class="w-1.5 h-1.5 rounded-full bg-neutral-900 animate-pulse-soft"></span>
                        Winter 2026 Series
                    </div>

                    <h1 class="font-display text-5xl lg:text-7xl font-black text-neutral-900 leading-[1.05] tracking-tight mb-6">
                        Solve Puzzles.<br>
                        Earn Your<br>
                        <span class="relative inline-block">
                            <span class="relative z-10">Physical Medal.</span>
                            <span class="absolute inset-x-0 bottom-2 h-3 bg-brand -z-0 animate-pulse-soft"></span>
                        </span>
                    </h1>

                    <p class="text-lg lg:text-xl text-neutral-600 leading-relaxed mb-8 max-w-lg">
                        Work through 100 curated chess puzzles from the Lichess database.
                        Complete the series and we'll ship you a <strong class="text-neutral-900">custom-designed physical medal</strong> — plus a digital sticker for your Hall of Fame.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3 mb-10">
                        <a href="{{ url('/challenges') }}" id="btn-hero-browse" class="btn btn-primary btn-lg gap-2 group">
                            Browse Challenges
                            <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                        <a href="#how-it-works" id="btn-hero-how" class="btn btn-outline btn-lg gap-2">
                            How it works
                        </a>
                    </div>

                    {{-- Social proof --}}
                    <div class="flex items-center gap-4">
                        <div class="flex -space-x-2">
                            @foreach(['🟢','🔵','🟠','🟣','🔴'] as $color)
                                <div class="w-9 h-9 rounded-full border-2 border-white bg-neutral-200 flex items-center justify-center text-xs font-bold text-neutral-600 transition-transform hover:scale-125 hover:z-10 cursor-default">
                                    {{ chr(65 + $loop->index) }}
                                </div>
                            @endforeach
                        </div>
                        <p class="text-sm text-neutral-600">
                            <span class="font-bold text-neutral-900">1,200+</span> chess players have already joined
                        </p>
                    </div>
                </div>

                {{-- Right: Chess Board Illustration --}}
                <div class="flex justify-center lg:justify-end reveal-right" style="--reveal-delay: 200ms;">
                    <div class="relative">
                        {{-- Glow behind board --}}
                        <div class="absolute inset-0 bg-brand/15 blur-3xl scale-90 pointer-events-none animate-pulse-soft" aria-hidden="true"></div>

                        {{-- Main chess board --}}
                        <div class="relative rounded-3xl overflow-hidden shadow-warm-lg ring-1 ring-neutral-900/5 rotate-3 transform hover:rotate-0 transition-transform duration-700">
                            <div class="grid grid-cols-8 w-72 h-72 lg:w-96 lg:h-96">
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
                                        <div class="aspect-square flex items-center justify-center text-2xl select-none transition-colors duration-300
                                            {{ $isLight ? 'bg-[#F0D9B5]' : 'bg-[#B58863]' }}">
                                            @if($piece)
                                                <span class="{{ $row < 2 ? 'text-neutral-800' : 'text-white drop-shadow' }} transition-transform hover:scale-150 cursor-default">{{ $piece }}</span>
                                            @endif
                                        </div>
                                    @endfor
                                @endfor
                            </div>
                        </div>

                        {{-- Floating award badge --}}
                        <div class="absolute -bottom-6 -left-8 bg-white rounded-2xl shadow-warm-lg px-5 py-3 flex items-center gap-3 ring-1 ring-neutral-900/5 animate-float">
                            <span class="text-3xl">🏅</span>
                            <div>
                                <p class="text-xs text-neutral-500 font-medium uppercase tracking-wider">Latest achievement</p>
                                <p class="text-sm font-bold text-neutral-900">Winter Beginner Medal</p>
                            </div>
                        </div>

                        {{-- Floating puzzle count badge (chartreuse) --}}
                        <div class="absolute -top-4 -right-6 bg-brand text-neutral-900 rounded-2xl shadow-warm-lg px-4 py-2.5 text-center ring-1 ring-neutral-900/10 hover:scale-110 transition-transform duration-300 cursor-default">
                            <p class="text-2xl font-black leading-none">100</p>
                            <p class="text-xs font-bold mt-0.5">Puzzles</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- STATS BAR (black)                                               --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <section class="bg-neutral-900 text-white py-12 relative overflow-hidden">
        {{-- Grid pattern overlay --}}
        <div class="absolute inset-0 bg-grid-pattern pointer-events-none" aria-hidden="true"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                @foreach([
                    ['300+', 'Puzzles Available'],
                    ['3', 'Active Challenges'],
                    ['2', 'Bundle Deals'],
                    ['🌍', 'Worldwide Shipping'],
                ] as $i => [$value, $label])
                    <div class="reveal" style="--reveal-delay: {{ $i * 120 }}ms;">
                        <p class="font-display text-4xl lg:text-5xl font-black mb-1 transition-transform hover:scale-110 cursor-default">{{ $value }}</p>
                        <p class="text-sm text-white/60 font-medium">{{ $label }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- HOW IT WORKS                                                    --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <section id="how-it-works" class="py-20 lg:py-28 bg-white relative overflow-hidden">
        {{-- Subtle dot pattern --}}
        <div class="absolute inset-0 bg-dot-pattern-dark pointer-events-none" aria-hidden="true"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center mb-16 reveal">
                <span class="inline-block text-neutral-500 font-bold text-xs uppercase tracking-[0.2em] mb-3">Simple Process</span>
                <h2 class="font-display text-4xl lg:text-5xl font-black text-neutral-900 mb-4">How It Works</h2>
                <p class="text-neutral-500 mt-2 text-lg max-w-xl mx-auto">Three steps from sign-up to holding your medal in your hands.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 relative">
                {{-- Connecting line (desktop) --}}
                <div class="hidden md:block absolute top-12 left-1/6 right-1/6 h-0.5 bg-gradient-to-r from-brand/20 via-brand/50 to-brand/20 pointer-events-none" aria-hidden="true"></div>

                @foreach([
                    ['🎯', '01', 'Choose a Challenge', 'Browse our curated Puzzle Series. Each challenge has 100 Lichess-sourced puzzles filtered by difficulty and theme — from beginner forks to advanced endgames.'],
                    ['♟', '02', 'Solve 100 Puzzles', 'Play at your own pace directly in your browser. Your progress is saved automatically — close the tab and pick up exactly where you left off, puzzle by puzzle.'],
                    ['🕘', '03', 'Receive Your Medal', 'The moment you finish all 100 puzzles, a digital sticker is instantly unlocked in your Hall of Fame. Your physical medal is then custom-made and shipped to your door.'],
                ] as $i => [$icon, $step, $title, $desc])
                    <div class="reveal group relative bg-white rounded-2xl ring-1 ring-neutral-900/10 p-8 hover:ring-brand/40 hover:-translate-y-2 transition-all duration-400 hover:shadow-warm-lg"
                         style="--reveal-delay: {{ $i * 150 }}ms;">
                        {{-- Step number badge in chartreuse --}}
                        <div class="absolute -top-4 left-8 bg-brand text-neutral-900 rounded-full w-10 h-10 flex items-center justify-center text-sm font-black ring-4 ring-white transition-transform group-hover:scale-110 group-hover:rotate-12">
                            {{ $step }}
                        </div>
                        <div class="w-14 h-14 rounded-2xl bg-neutral-100 flex items-center justify-center text-2xl mb-5 mt-2 transition-transform group-hover:scale-110">
                            {{ $icon }}
                        </div>
                        <h3 class="font-display text-xl font-bold text-neutral-900 mb-3">{{ $title }}</h3>
                        <p class="text-neutral-600 text-sm leading-relaxed">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- FEATURED CHALLENGES (black bg, white cards)                     --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <section id="challenges" class="py-20 lg:py-28 bg-neutral-900 text-white relative overflow-hidden">
        {{-- Grid pattern overlay --}}
        <div class="absolute inset-0 bg-grid-pattern pointer-events-none" aria-hidden="true"></div>
        {{-- Chartreuse glow accent --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-brand/5 blur-[100px] pointer-events-none" aria-hidden="true"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-4 reveal">
                <div>
                    <span class="inline-block text-brand font-bold text-xs uppercase tracking-[0.2em] mb-3">Winter 2026 Series</span>
                    <h2 class="font-display text-4xl lg:text-5xl font-black text-white">Current Challenges</h2>
                </div>
                <a href="{{ url('/challenges') }}" class="btn bg-white text-neutral-900 hover:bg-brand hover:text-neutral-900 shrink-0 border-0 group transition-all duration-300">
                    View All Challenges
                    <span class="transition-transform group-hover:translate-x-1">→</span>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                @forelse($challenges as $i => $challenge)
                    @php
                        $levelData = match(true) {
                            str_contains(strtolower($challenge->name), 'beginner') => ['🌱', 'Beginner', 'bg-success/20 text-success'],
                            str_contains(strtolower($challenge->name), 'intermediate') => ['⚡', 'Intermediate', 'bg-warning/20 text-warning'],
                            str_contains(strtolower($challenge->name), 'advanced') => ['🔥', 'Advanced', 'bg-error/20 text-error'],
                            default => ['♟', 'Challenge', 'bg-white/10 text-white'],
                        };
                        $rules = $challenge->rules ?? [];
                    @endphp
                    <div class="reveal group bg-white text-neutral-900 rounded-2xl shadow-warm overflow-hidden ring-1 ring-white/10 hover:ring-brand/40 hover:-translate-y-2 hover:shadow-warm-lg transition-all duration-400 flex flex-col"
                         style="--reveal-delay: {{ $i * 120 }}ms;">
                        {{-- Card header with chess pattern + chartreuse glow on hover --}}
                        <div class="h-28 relative bg-neutral-100 overflow-hidden">
                            <div class="absolute inset-0 bg-chess-pattern opacity-30 transition-opacity group-hover:opacity-50"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                            <div class="absolute bottom-3 left-5">
                                <span class="badge {{ $levelData[2] }} gap-1 font-semibold">
                                    {{ $levelData[0] }} {{ $levelData[1] }}
                                </span>
                            </div>
                        </div>

                        <div class="p-6 flex flex-col flex-1">
                            <h3 class="font-display text-xl font-bold text-neutral-900 mb-2 leading-snug">
                                {{ $challenge->name }}
                            </h3>
                            <p class="text-neutral-500 text-sm leading-relaxed mb-4 line-clamp-2 flex-1">
                                {{ $challenge->description }}
                            </p>

                            <div class="grid grid-cols-2 gap-3 mb-5 text-sm">
                                <div class="flex items-center gap-2 text-neutral-600">
                                    <span class="text-base">♟</span>
                                    <span><strong>{{ $challenge->puzzles_count ?? $challenge->puzzle_count }}</strong> puzzles</span>
                                </div>
                                <div class="flex items-center gap-2 text-neutral-600">
                                    <span>🏅</span>
                                    <span>Physical medal</span>
                                </div>
                                <div class="flex items-center gap-2 text-neutral-600">
                                    <span>✦</span>
                                    <span>Digital sticker</span>
                                </div>
                                <div class="flex items-center gap-2 text-neutral-600">
                                    <span>📅</span>
                                    <span>{{ ($rules['order'] ?? null) === 'sequential' ? 'Sequential' : 'Any order' }}</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between mb-5 pt-4 border-t border-neutral-100">
                                <div>
                                    <p class="text-2xl font-black text-neutral-900">MYR {{ number_format($challenge->price_myr, 2) }}</p>
                                    <p class="text-xs text-neutral-400">or USD {{ number_format($challenge->price_usd, 2) }}</p>
                                </div>
                                <span class="badge badge-outline border-neutral-300 text-neutral-500 text-[10px]">ONE-TIME</span>
                            </div>

                            <a href="{{ url('/challenges/'.$challenge->slug) }}" id="btn-challenge-{{ $challenge->id }}" class="btn btn-primary w-full gap-2 group">
                                Start Challenge
                                <span class="transition-transform group-hover:translate-x-1">→</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-16 text-white/50">
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
    <section id="bundles" class="py-20 lg:py-28 bg-white relative overflow-hidden">
        <div class="absolute inset-0 bg-dot-pattern-dark pointer-events-none" aria-hidden="true"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center mb-12 reveal">
                <span class="inline-block text-neutral-500 font-bold text-xs uppercase tracking-[0.2em] mb-3">Best Value</span>
                <h2 class="font-display text-4xl lg:text-5xl font-black text-neutral-900">Bundle & Save</h2>
                <p class="text-neutral-500 mt-3 text-lg max-w-xl mx-auto">
                    Get multiple challenges in one purchase. Multiple medals. One great price.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 max-w-4xl mx-auto">
                @forelse($bundles as $i => $bundle)
                    <div class="reveal-scale group relative bg-white rounded-2xl ring-1 ring-neutral-900/10 shadow-warm overflow-hidden hover:shadow-warm-lg hover:-translate-y-2 hover:ring-brand/40 transition-all duration-400"
                         style="--reveal-delay: {{ $i * 150 }}ms;">

                        {{-- "Save More" ribbon (black with chartreuse text) --}}
                        <div class="absolute top-5 right-0 bg-neutral-900 text-brand text-xs font-black px-4 py-1.5 rounded-l-full uppercase tracking-wider transition-transform group-hover:scale-105">
                            🎉 Save More
                        </div>

                        <div class="p-8">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 rounded-xl bg-neutral-100 flex items-center justify-center text-2xl transition-transform group-hover:scale-110 group-hover:rotate-6">🎁</div>
                                <h3 class="font-display text-2xl font-bold text-neutral-900">{{ $bundle->name }}</h3>
                            </div>

                            <p class="text-neutral-500 text-sm leading-relaxed mb-6">{{ $bundle->description }}</p>

                            @if($bundle->challenges->isNotEmpty())
                                <div class="space-y-2 mb-6">
                                    <p class="text-xs font-bold text-neutral-400 uppercase tracking-wider mb-2">Includes</p>
                                    @foreach($bundle->challenges as $c)
                                        <div class="flex items-center gap-2 text-sm text-neutral-700 transition-transform hover:translate-x-1">
                                            <svg class="w-4 h-4 text-neutral-900 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            {{ $c->name }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex items-center justify-between pt-4 border-t border-neutral-100">
                                <div>
                                    <p class="text-3xl font-black text-neutral-900">MYR {{ number_format($bundle->price_myr, 2) }}</p>
                                    <p class="text-xs text-neutral-400">or USD {{ number_format($bundle->price_usd, 2) }}</p>
                                </div>
                                <a href="{{ route('bundles.enroll', $bundle) }}" id="btn-bundle-{{ $bundle->id }}" class="btn btn-primary gap-2 group">
                                    Get Bundle
                                    <span class="transition-transform group-hover:translate-x-1">→</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 text-center py-12 text-neutral-400">
                        <p>No bundles available yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- HALL OF FAME TEASER (black bg, white card)                      --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <section class="py-20 lg:py-28 bg-neutral-900 relative overflow-hidden">
        {{-- Grid pattern + chartreuse glow --}}
        <div class="absolute inset-0 bg-grid-pattern pointer-events-none" aria-hidden="true"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-brand/5 blur-[100px] pointer-events-none" aria-hidden="true"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal-scale bg-white rounded-3xl shadow-warm-lg overflow-hidden ring-1 ring-white/10 hover:ring-brand/30 transition-all duration-500">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">

                    {{-- Left: Copy --}}
                    <div class="p-10 lg:p-14 flex flex-col justify-center">
                        <span class="inline-block text-neutral-500 font-bold text-xs uppercase tracking-[0.2em] mb-3">Your Collection</span>
                        <h2 class="font-display text-3xl lg:text-4xl font-black text-neutral-900 mb-4">
                            Build Your<br>Hall of Fame
                        </h2>
                        <p class="text-neutral-500 leading-relaxed mb-8">
                            Every completed challenge earns you a unique digital sticker — displayed in your personal Hall of Fame. Collect them all, show them off, and track your chess journey.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3">
                            @auth
                                <a href="{{ url('/hall-of-fame') }}" class="btn btn-primary gap-2 group">
                                    View My Hall of Fame
                                    <span class="transition-transform group-hover:translate-x-1">→</span>
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="btn btn-primary gap-2 group">
                                    Start Collecting
                                    <span class="transition-transform group-hover:translate-x-1">→</span>
                                </a>
                                <a href="{{ route('login') }}" class="btn btn-ghost">Already have an account</a>
                            @endauth
                        </div>
                    </div>

                    {{-- Right: Sticker grid preview (dark surface inside the card) --}}
                    <div class="bg-neutral-100 p-10 flex items-center justify-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-dot-pattern-dark pointer-events-none" aria-hidden="true"></div>
                        <div class="relative grid grid-cols-3 gap-4">
                            @foreach(['♔','♕','♖','♗','♘','♟'] as $i => $piece)
                                <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-4xl shadow-sm ring-1 ring-neutral-900/5 transition-all duration-300 hover:scale-125 hover:shadow-warm-lg cursor-default
                                    {{ $i < 3 ? 'bg-white' : 'bg-white/60 text-neutral-300' }}"
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
    {{-- FINAL CTA BANNER (chartreuse — the brand moment)                --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <section class="py-20 lg:py-28 bg-brand text-neutral-900 relative overflow-hidden">
        {{-- Grid pattern overlay --}}
        <div class="absolute inset-0 bg-brand-grid pointer-events-none animate-pulse-soft" aria-hidden="true"></div>

        {{-- Glow rings emanating from center --}}
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[30rem] h-[30rem] bg-neutral-900/5 rounded-full pointer-events-none" aria-hidden="true"></div>

        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 text-center">
            <div class="reveal-scale inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-neutral-900 text-brand text-4xl mb-8 shadow-warm-lg rotate-3 hover:rotate-0 transition-transform duration-700 hover:scale-110 cursor-default">
                ♟
            </div>
            <h2 class="reveal font-display text-4xl lg:text-6xl font-black mb-6 leading-[1.05] tracking-tight" style="--reveal-delay: 100ms;">
                Ready to Earn Your<br>First Medal?
            </h2>
            <p class="reveal text-neutral-900/70 text-lg lg:text-xl leading-relaxed mb-10 max-w-2xl mx-auto" style="--reveal-delay: 200ms;">
                Join over 1,200 chess enthusiasts who are solving puzzles and building their trophy case — one medal at a time.
            </p>
            <div class="reveal flex flex-col sm:flex-row gap-3 justify-center" style="--reveal-delay: 300ms;">
                @auth
                    <a href="{{ url('/challenges') }}" id="btn-cta-challenges" class="btn btn-lg gap-2 bg-neutral-900 text-white border-0 hover:bg-neutral-800 hover:scale-105 shadow-warm-lg transition-all duration-300">
                        Browse Challenges →
                    </a>
                @else
                    <a href="{{ route('register') }}" id="btn-cta-register" class="btn btn-lg gap-2 bg-neutral-900 text-white border-0 hover:bg-neutral-800 hover:scale-105 shadow-warm-lg transition-all duration-300">
                        Create Free Account →
                    </a>
                    <a href="{{ url('/challenges') }}" id="btn-cta-browse" class="btn btn-lg btn-outline border-neutral-900 text-neutral-900 hover:bg-neutral-900 hover:text-white transition-all duration-300">
                        Browse First
                    </a>
                @endauth
            </div>
        </div>
    </section>

    {{-- Scroll-reveal Intersection Observer script --}}
    @push('scripts')
        <script>
            (function() {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('reveal-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.12,
                    rootMargin: '0px 0px -60px 0px'
                });

                const init = () => {
                    document.querySelectorAll('.reveal, .reveal-scale, .reveal-left, .reveal-right').forEach((el) => {
                        observer.observe(el);
                    });
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', init);
                } else {
                    init();
                }

                // Re-init after Livewire navigation
                document.addEventListener('livewire:navigated', init);
            })();
        </script>
    @endpush

</x-marketing-layout>
