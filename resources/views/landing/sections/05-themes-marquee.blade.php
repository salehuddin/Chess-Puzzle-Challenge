{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- SECTION 05: THEMES MARQUEE — replaces Clay's customer-logo strip  --}}
{{-- 8 tactical-theme tiles scrolling in a marquee; chartreuse dot each  --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<section class="bg-white py-16 lg:py-20 relative overflow-hidden">

    {{-- Chess pattern + radial gradients for depth --}}
    <div class="absolute inset-0 bg-chess-pattern-brand-light pointer-events-none" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-radial-brand-tl pointer-events-none" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-radial-brand-br pointer-events-none" aria-hidden="true"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-10">
        <div class="reveal text-center">
            <span class="inline-block text-neutral-500 font-bold text-xs uppercase tracking-[0.2em] mb-3">Tactical Themes</span>
            <h2 class="font-display text-3xl lg:text-4xl font-black text-neutral-900">Drill the patterns. Master the tactics.</h2>
            <p class="text-neutral-500 mt-2 text-lg max-w-2xl mx-auto">The chess-native "franchises" — every series is built around a recurring tactical theme.</p>
        </div>
    </div>

    @php
        $themes = [
            ['♞', 'Forks', 'Win material with two attacks at once'],
            ['⚓', 'Pins & Skewers', 'Freeze the king, win the piece behind'],
            ['⚔', 'Mating Attacks', 'Mate-in-N combinations that finish the king'],
            ['♚', 'Endgame Mastery', 'Convert the advantage, close the game'],
            ['⚡', 'Opening Traps', 'Punish greedy openings early'],
            ['▦', 'Back-Rank Brutality', 'The classic checkmate pattern'],
            ['♘', 'Knight Tactics', 'Royal forks and smothered mates'],
            ['◌', 'Zugzwang & Quiet Moves', 'When doing nothing is the only winning move'],
        ];
    @endphp

    {{-- Marquee: 2-row scrolling grid; each row duplicated for seamless loop --}}
    <div class="relative space-y-3">
        {{-- Row 1: themes 0-3 --}}
        <div class="flex gap-3 animate-[marquee_40s_linear_infinite] hover:[animation-play-state:paused]">
            @foreach(array_merge($themes, $themes) as $theme)
                <div class="shrink-0 w-72 bg-white ring-1 ring-neutral-900/10 rounded-2xl p-5 hover:ring-brand/40 hover:shadow-warm-lg transition-all duration-300 group">
                    <div class="flex items-start gap-3">
                        <span class="w-10 h-10 rounded-xl bg-neutral-100 group-hover:bg-brand group-hover:text-neutral-900 flex items-center justify-center text-xl transition-all duration-300">{{ $theme[0] }}</span>
                        <div class="flex-1">
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-brand"></span>
                                <h3 class="font-display text-base font-bold text-neutral-900">{{ $theme[1] }}</h3>
                            </div>
                            <p class="text-xs text-neutral-500 leading-relaxed">{{ $theme[2] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Edge fades --}}
    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-24 h-32 bg-gradient-to-r from-white to-transparent pointer-events-none"></div>
    <div class="absolute right-0 top-1/2 -translate-y-1/2 w-24 h-32 bg-gradient-to-l from-white to-transparent pointer-events-none"></div>

    <style>
        @keyframes marquee {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }
    </style>
</section>
