{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- SECTION 06: HOW IT WORKS — sticky stacking cards                  --}}
{{-- Section sticks in viewport; cards arrive one by one and stack      --}}
{{-- on top of each other (increasing z-index). After the last card     --}}
{{-- stacks, the section releases and scrolls away normally.            --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<section id="how-it-works" class="bg-white py-20 lg:py-28 relative">

    {{-- Background patterns (won't clip since no overflow-hidden; they're inset-0 so exactly match section size) --}}
    <div class="absolute inset-0 bg-chess-pattern-brand-light-sm pointer-events-none" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-radial-brand-tl pointer-events-none" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-fade-edges-light pointer-events-none" aria-hidden="true"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Intro (keeps reveal animation; not sticky so transform is safe) --}}
        <div class="reveal text-center mb-16 lg:mb-24 max-w-2xl mx-auto">
            <span class="inline-block text-neutral-500 font-bold text-xs uppercase tracking-[0.2em] mb-3">How A Series Works</span>
            <h2 class="font-display text-4xl lg:text-5xl font-black text-neutral-900 mb-4">Four steps. One medal.</h2>
            <p class="text-neutral-500 text-lg">From choosing your series to holding your medal — here's the journey.</p>
        </div>

        @php
            $steps = [
                [
                    'step' => '01',
                    'icon' => '🎯',
                    'title' => 'Choose Your Series',
                    'desc' => "Pick by difficulty (Beginner → Advanced) and tactical theme. Each series is 100 hand-curated puzzles from the Lichess database — filtered by rating, theme, and length.",
                    'features' => [
                        ['♟', 'Filter by rating band — 800 to 2000+'],
                        ['🎯', 'Tactical themes: forks, pins, endgames'],
                        ['📦', 'Mix-and-match multiple series in bundles'],
                    ],
                    'mockup' => 'series-picker',
                ],
                [
                    'step' => '02',
                    'icon' => '♟',
                    'title' => 'Solve 100 Puzzles',
                    'desc' => "Play directly in your browser. Progress autosaves to localStorage — close the tab, pick up exactly where you left off. Undo and reset freely, no penalties.",
                    'features' => [
                        ['↩', 'Undo and reset without penalty'],
                        ['💡', 'Visual hint highlights the piece to move'],
                        ['💾', 'Progress saved in your browser'],
                    ],
                    'mockup' => 'puzzle-player',
                ],
                [
                    'step' => '03',
                    'icon' => '✦',
                    'title' => 'Unlock Your Digital Sticker',
                    'desc' => "The moment you finish puzzle #100, a unique sticker drops into your Hall of Fame — instantly. A permanent record of the tactical theme you just mastered.",
                    'features' => [
                        ['⚡', 'Instant sticker unlock on completion'],
                        ['🖼', 'Hall of Fame collection grows with each series'],
                        ['📢', 'Share your achievement with friends'],
                    ],
                    'mockup' => 'hall-of-fame',
                ],
                [
                    'step' => '04',
                    'icon' => '🏅',
                    'title' => 'Earn Your Physical Medal',
                    'desc' => "Claim your medal in-app. We custom-make and ship it worldwide. Each medal is a unique design tied to the series you completed — a real trophy for your real wall.",
                    'features' => [
                        ['🌍', 'Worldwide shipping — 80+ countries'],
                        ['🏛', 'Custom-designed medals per series'],
                        ['📦', 'Tracking number emailed when shipped'],
                    ],
                    'mockup' => 'medal',
                ],
            ];
        @endphp

        {{-- ═══ STICKY STACKING CARDS ═════════════════════════════════════ --}}
        {{-- structure: each card is position:sticky at top-[15vh]          --}}
       {{-- gap between cards (lg:space-y-[55vh]) creates scroll distance    --}}
        {{-- for the next card to travel up and stack on top (higher z-index) --}}
       {{-- pb-[30vh] gives trailing scroll so last card rests before release --}}
        {{-- NO reveal class (transform breaks sticky) / NO overflow-hidden   --}}
        {{-- on section (creates scroll container that traps sticky)          --}}
        <div class="space-y-6 lg:space-y-[55vh] lg:pb-[30vh]">
            @foreach($steps as $i => $step)
                <div class="bg-brand rounded-3xl p-8 lg:p-10 shadow-warm-lg ring-1 ring-neutral-900/5
                            lg:sticky lg:top-[15vh]"
                     style="z-index: {{ $i + 10 }};">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 {{ $i % 2 === 1 ? 'lg:[direction:rtl]' : '' }}">

                        {{-- Mockup column --}}
                        <div class="[direction:ltr]">
                            <div class="relative aspect-[4/3] rounded-2xl overflow-hidden ring-1 ring-neutral-900/10 shadow-warm-lg bg-neutral-50">
                                <div class="absolute inset-0 bg-radial-brand-center pointer-events-none" aria-hidden="true"></div>
                                @if($step['mockup'] === 'series-picker')
                                    <div class="absolute inset-0 p-6 flex flex-col gap-3">
                                        <div class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Browse Series</div>
                                        <div class="grid grid-cols-2 gap-3 flex-1">
                                            @foreach(['Beginner Forks', 'Pin Mastery', 'Endgame Wins', 'Back-Rank Brutality'] as $card)
                                                <div class="bg-white rounded-xl ring-1 ring-neutral-900/5 p-3 flex flex-col justify-between">
                                                    <span class="text-xs font-bold text-neutral-700">{{ $card }}</span>
                                                    <div class="flex items-center gap-1 mt-2">
                                                        <span class="text-[10px] bg-brand text-neutral-900 px-1.5 py-0.5 rounded font-bold">100</span>
                                                        <span class="text-[10px] text-neutral-400">puzzles</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @elseif($step['mockup'] === 'puzzle-player')
                                    <div class="absolute inset-0 p-6 flex gap-3">
                                        <div class="grid grid-cols-4 w-1/2 aspect-square bg-neutral-900 rounded-xl overflow-hidden">
                                            @for ($r = 0; $r < 4; $r++)
                                                @for ($c = 0; $c < 4; $c++)
                                                    <div class="aspect-square {{ ($r + $c) % 2 === 0 ? 'bg-brand' : 'bg-neutral-900' }}"></div>
                                                @endfor
                                            @endfor
                                        </div>
                                        <div class="flex-1 flex flex-col justify-center gap-2">
                                            <div class="text-xs font-bold text-neutral-400 uppercase">Puzzle 47 / 100</div>
                                            <div class="w-full h-2 bg-neutral-200 rounded-full overflow-hidden">
                                                <div class="h-full bg-brand" style="width: 47%"></div>
                                            </div>
                                            <div class="mt-3 text-xs text-neutral-500">Black to play</div>
                                            <div class="flex gap-1 mt-2">
                                                <span class="px-2 py-0.5 text-[10px] bg-neutral-900 text-white rounded">↩ Undo</span>
                                                <span class="px-2 py-0.5 text-[10px] bg-white ring-1 ring-neutral-900/10 rounded">💡 Hint</span>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($step['mockup'] === 'hall-of-fame')
                                    <div class="absolute inset-0 p-6 flex flex-col">
                                        <div class="text-xs font-bold text-neutral-400 uppercase tracking-wider mb-3">Hall of Fame</div>
                                        <div class="grid grid-cols-3 gap-3 flex-1">
                                            @foreach(['♕','♖','♗','♘','♟','?'] as $k => $piece)
                                                <div class="aspect-square rounded-xl flex items-center justify-center text-3xl ring-1
                                                    {{ $k < 5 ? 'bg-brand/10 text-neutral-900 ring-brand/30' : 'bg-neutral-100 text-neutral-300 ring-neutral-200' }}">
                                                    {{ $piece }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @elseif($step['mockup'] === 'medal')
                                    <div class="absolute inset-0 bg-neutral-900 flex items-center justify-center p-6">
                                        <div class="text-center">
                                            <div class="w-28 h-28 mx-auto rounded-full bg-brand flex items-center justify-center text-5xl shadow-warm-lg ring-4 ring-white/10">
                                                🏅
                                            </div>
                                            <div class="text-white font-display font-bold text-lg mt-4">Ultimate Winter 2026</div>
                                            <div class="text-white/60 text-xs mt-1">Custom physical medal</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Copy column --}}
                        <div class="[direction:ltr] flex flex-col justify-center">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="bg-neutral-900 text-brand rounded-full w-11 h-11 flex items-center justify-center text-sm font-black ring-4 ring-brand/30">
                                    {{ $step['step'] }}
                                </div>
                                <span class="text-2xl">{{ $step['icon'] }}</span>
                            </div>
                            <h3 class="font-display text-2xl lg:text-3xl font-black text-neutral-900 mb-4 leading-tight">{{ $step['title'] }}</h3>
                            <p class="text-neutral-700 text-base lg:text-lg leading-relaxed mb-5">{{ $step['desc'] }}</p>

                            <ul class="space-y-2">
                                @foreach($step['features'] as $feature)
                                    <li class="flex items-center gap-3 text-sm text-neutral-800">
                                        <span class="text-neutral-900 font-bold">{{ $feature[0] }}</span>
                                        <span>{{ $feature[1] }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            @if($i === 3)
                                <a href="{{ url('/challenges') }}" class="inline-flex items-center gap-2 mt-6 btn btn-primary group self-start">
                                    Browse Challenges
                                    <span class="transition-transform group-hover:translate-x-1">→</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
