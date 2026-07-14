{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- SECTION 07: BLOCK — THE PUZZLES                                    --}}
{{-- Clay-style feature block: eyebrow / H2 / 3 customer logos here     --}}
{{-- simplified to eyebrow / H2 / 3 micro-features / 2 CTAs / still-art  --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<section class="bg-white py-20 lg:py-28 relative overflow-hidden">
    <div class="absolute inset-0 bg-radial-brand-tl pointer-events-none" aria-hidden="true"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">

            {{-- Copy --}}
            <div class="reveal-left">
                <span class="inline-block text-brand font-bold text-xs uppercase tracking-[0.2em] mb-3">The Puzzles</span>
                <h2 class="font-display text-4xl lg:text-5xl font-black text-neutral-900 mb-5 leading-tight">
                    100 curated puzzles.<br>Your pace, your level.
                </h2>
                <p class="text-neutral-600 text-lg leading-relaxed mb-8 max-w-lg">
                    Each series is built from the Lichess puzzle database — filtered by rating, theme and length. No filler, no random moves — just deliberate practice on the patterns that actually win games.
                </p>

                <div class="space-y-4 mb-8">
                    @foreach([
                        ['🎯', 'Filter by rating', 'Series from 800 to 2000+ — meet your skill where it actually is'],
                        ['♟', 'Theme-focused curation', 'Each series targets one tactic — forks, pins, endgames — until you see it everywhere'],
                        ['↩', 'Practice-friendly', 'Undo and reset without penalty; visual hint shows the piece to move'],
                    ] as $feature)
                        <div class="flex items-start gap-4 group">
                            <div class="w-10 h-10 rounded-xl bg-neutral-100 group-hover:bg-brand group-hover:text-neutral-900 flex items-center justify-center text-lg shrink-0 transition-all duration-300">{{ $feature[0] }}</div>
                            <div>
                                <h4 class="font-display font-bold text-neutral-900 text-base mb-0.5">{{ $feature[1] }}</h4>
                                <p class="text-sm text-neutral-500 leading-relaxed">{{ $feature[2] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ url('/challenges') }}" class="btn btn-primary gap-2 group">
                        Browse Challenges
                        <span class="transition-transform group-hover:translate-x-1">→</span>
                    </a>
                    <a href="{{ url('/hall-of-fame') }}" class="btn btn-ghost gap-2">
                        See Hall of Fame
                    </a>
                </div>
            </div>

            {{-- Illustration: stylized puzzle board mid-puzzle --}}
            <div class="reveal-right flex justify-center" style="--reveal-delay: 150ms;">
                <div class="relative">
                    <div class="absolute inset-0 bg-brand/15 blur-3xl scale-90 pointer-events-none animate-pulse-soft" aria-hidden="true"></div>

                    <div class="relative bg-white rounded-3xl shadow-warm-lg ring-1 ring-neutral-900/5 p-6 w-full max-w-md">
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Puzzle 47 of 100</div>
                            <div class="text-xs font-bold text-brand flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-brand animate-pulse-soft"></span> In Progress
                            </div>
                        </div>

                        {{-- Mini board --}}
                        <div class="grid grid-cols-6 aspect-square rounded-xl overflow-hidden ring-1 ring-neutral-900/10 mb-4">
                            @php
                                $miniPieces = [
                                    [0,2,'♛'],[1,2,'♟'],[1,3,'♟'],[2,1,'♚'],[2,4,'♜'],
                                    [3,1,'♗'],[3,3,'♟'],[4,0,'♖'],[4,2,'♟'],[5,5,'♙'],
                                ];
                                $miniMap = [];
                                foreach ($miniPieces as $p) { $miniMap[$p[0].','.$p[1]] = $p[2]; }
                            @endphp
                            @for($r=0;$r<6;$r++)
                                @for($c=0;$c<6;$c++)
                                    @php $light = ($r+$c)%2===0; $p = $miniMap[$r.','.$c] ?? ''; @endphp
                                    <div class="aspect-square flex items-center justify-center text-lg
                                        {{ $light ? 'bg-brand' : 'bg-neutral-900' }}">
                                        @if($p)
                                            <span class="{{ $r < 3 ? 'text-neutral-900' : 'text-white drop-shadow' }}">{{ $p }}</span>
                                        @endif
                                    </div>
                                @endfor
                            @endfor
                        </div>

                        {{-- Progress bar --}}
                        <div class="w-full h-2 bg-neutral-100 rounded-full overflow-hidden mb-3">
                            <div class="h-full bg-brand" style="width: 47%"></div>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <span class="text-neutral-500">Black to play — find the fork</span>
                            <div class="flex gap-1.5">
                                <span class="px-2 py-1 bg-neutral-900 text-white rounded font-semibold">↩ Undo</span>
                                <span class="px-2 py-1 bg-neutral-100 ring-1 ring-neutral-900/10 rounded font-semibold">💡 Hint (2)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
