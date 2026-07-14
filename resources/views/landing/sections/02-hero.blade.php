{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- SECTION 02: HERO — bold short H1, two CTAs, chartreuse+black board --}}
{{-- Background image with bright white overlay for legibility            --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
@php
    use Illuminate\Support\Facades\Vite;
    try {
        $heroBgUrl = Vite::asset('resources/images/home-hero-bg-01.webp');
    } catch (\Throwable $e) {
        $heroBgUrl = null;
    }
@endphp

<section class="relative overflow-hidden bg-white pt-16 pb-20 lg:pt-24 lg:pb-28">

    {{-- Background image layer (only rendered if asset exists) --}}
    @if($heroBgUrl)
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true"
         style="background-image: url('{{ $heroBgUrl }}'); background-size: cover; background-position: center;"></div>

    {{-- Bright white overlay — fades from solid (top+bottom) to semi-transparent (center) for text legibility --}}
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true"
         style="background: linear-gradient(to bottom, rgba(254,254,254,0.95) 0%, rgba(254,254,254,0.75) 40%, rgba(254,254,254,0.85) 70%, rgba(254,254,254,0.98) 100%);"></div>
    @endif

    {{-- Subtle dot pattern background (atop overlay for texture) --}}
    <div class="absolute inset-0 bg-dot-pattern-dark pointer-events-none" aria-hidden="true"></div>

    {{-- Chartreuse glow blob top-right --}}
    <div class="absolute -top-32 -right-32 w-[28rem] h-[28rem] rounded-full bg-brand/10 blur-[100px] pointer-events-none animate-pulse-soft" aria-hidden="true"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">

            {{-- Left: Copy (short Clay-style H1) --}}
            <div class="reveal-left">
                <div class="inline-flex items-center gap-2 bg-brand text-neutral-900 text-xs font-black px-3 py-1.5 rounded-full mb-6 uppercase tracking-widest">
                    <span class="w-1.5 h-1.5 rounded-full bg-neutral-900 animate-pulse-soft"></span>
                    Winter 2026 Series
                </div>

                <h1 class="font-display text-5xl lg:text-7xl font-black text-neutral-900 leading-[1.05] tracking-tight mb-6">
                    Solve 100 Puzzles.<br>
                    Earn a<br>
                    <span class="relative inline-block">
                        <span class="relative z-10">Real Medal.</span>
                        <span class="absolute inset-x-0 bottom-2 h-3 bg-brand -z-0 animate-pulse-soft"></span>
                    </span>
                </h1>

                <p class="text-lg lg:text-xl text-neutral-600 leading-relaxed mb-8 max-w-lg">
                    Work through 100 curated puzzles from the Lichess database. Complete the series and we'll ship you a <strong class="text-neutral-900">custom-designed physical medal</strong> — plus a digital sticker for your Hall of Fame.
                </p>

                {{-- Top effects line (Conqueror "Top 3 Effects" pattern) --}}
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mb-10 text-sm font-semibold text-neutral-700">
                    <span class="flex items-center gap-1.5"><span class="text-brand">♞</span> Sharpen Your Tactics</span>
                    <span class="opacity-30">·</span>
                    <span class="flex items-center gap-1.5"><span class="text-brand">♟</span> Spot Patterns Faster</span>
                    <span class="opacity-30">·</span>
                    <span class="flex items-center gap-1.5"><span class="text-brand">♔</span> Climb Your Rating</span>
                </div>

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

            {{-- Right: Chartreuse + Black Chess Board --}}
            <div class="flex justify-center lg:justify-end reveal-right" style="--reveal-delay: 200ms;">
                <div class="relative">

                    {{-- Glow behind board --}}
                    <div class="absolute inset-0 bg-brand/20 blur-3xl scale-90 pointer-events-none animate-pulse-soft" aria-hidden="true"></div>

                    {{-- Main chess board — chartreuse + black squares --}}
                    <div class="relative rounded-3xl overflow-hidden shadow-warm-lg ring-1 ring-neutral-900/10 rotate-3 transform hover:rotate-0 transition-transform duration-700">
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
                                        {{ $isLight ? 'bg-brand' : 'bg-neutral-900' }}">
                                        @if($piece)
                                            <span class="{{ $row < 2 ? 'text-neutral-900' : 'text-white drop-shadow' }} transition-transform hover:scale-150 cursor-default">{{ $piece }}</span>
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

                    {{-- Floating puzzle count badge (neutral-900 bg with chartreuse text) --}}
                    <div class="absolute -top-4 -right-6 bg-neutral-900 text-brand rounded-2xl shadow-warm-lg px-4 py-2.5 text-center ring-1 ring-white/10 hover:scale-110 transition-transform duration-300 cursor-default">
                        <p class="text-2xl font-black leading-none">100</p>
                        <p class="text-xs font-bold mt-0.5">Puzzles</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
