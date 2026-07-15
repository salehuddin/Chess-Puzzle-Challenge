{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- SECTION 06: HOW IT WORKS — section sticky, cards stack             --}}
{{--                                                                    --}}
{{-- Desktop: The entire section pins to the viewport via a tall outer   --}}
{{-- container (400vh) + sticky inner (h-screen). Inside the pinned      --}}
{{-- section, cards arrive one by one and stack on top of each other     --}}
{{-- (Alpine tracks scroll progress → sets active card index). After     --}}
{{-- the 4th card, scrolling pushes the section away normally.           --}}
{{--                                                                    --}}
{{-- Mobile: Normal stacked cards in document flow, no sticky.           --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<section id="how-it-works" class="bg-white relative py-16 lg:py-0">

    {{-- Background patterns (cover both mobile and desktop) --}}
    <div class="absolute inset-0 bg-chess-pattern-brand-light-sm pointer-events-none" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-radial-brand-tl pointer-events-none" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-fade-edges-light pointer-events-none" aria-hidden="true"></div>

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

    {{-- Mobile heading (normal flow) --}}
    <div class="lg:hidden relative z-10 text-center mb-12 max-w-2xl mx-auto px-4 sm:px-6">
        <span class="inline-block text-neutral-500 font-bold text-xs uppercase tracking-[0.2em] mb-3">How A Series Works</span>
        <h2 class="font-display text-4xl font-black text-neutral-900 mb-4">Four steps. One medal.</h2>
        <p class="text-neutral-500 text-lg">From choosing your series to holding your medal — here's the journey.</p>
    </div>

    {{-- Mobile: normal stacked cards --}}
    <div class="lg:hidden relative z-10 space-y-6 px-4 sm:px-6 pb-10">
        @foreach($steps as $i => $step)
            <div class="bg-brand rounded-3xl p-8 shadow-warm-lg ring-1 ring-neutral-900/5">
                <div class="grid grid-cols-1 gap-6">
                    @include('landing.sections.partials.step-card-content', ['step' => $step, 'i' => $i])
                </div>
            </div>
        @endforeach
    </div>

    {{-- ═══ DESKTOP: sticky section + scroll-driven card stacking ════════ --}}
    {{-- Outer: 400vh tall → creates scroll distance for 4 cards             --}}
    {{-- Inner: sticky top-0 h-screen → pins the whole section to viewport    --}}
    {{-- Alpine: tracks scroll progress through the outer container,          --}}
    {{--        sets `active` to 0-3 based on which quarter of scroll we're in --}}
    {{-- Cards: absolute inset-0, transition opacity+transform, z-index stacks  --}}
    <div class="hidden lg:block relative h-[400vh]"
         x-data="{
             active: 0,
             _el: null,
             update() {
                 const rect = this._el.getBoundingClientRect();
                 const total = rect.height - window.innerHeight;
                 if (total <= 0) { this.active = 3; return; }
                 const progress = Math.max(0, Math.min(1, -rect.top / total));
                 this.active = Math.min(3, Math.floor(progress * 4));
             }
         }"
         x-init="_el = $el; update()"
         @scroll.window.throttle.16ms="update()"
         @resize.window.debounce.100ms="update()">

        {{-- Sticky inner: pins to top of viewport, centers content --}}
        <div class="sticky top-0 h-screen flex flex-col items-center justify-center overflow-hidden px-8">

            {{-- Heading: always visible while section is pinned --}}
            <div class="text-center mb-10 max-w-2xl shrink-0">
                <span class="inline-block text-neutral-500 font-bold text-xs uppercase tracking-[0.2em] mb-3">How A Series Works</span>
                <h2 class="font-display text-4xl lg:text-5xl font-black text-neutral-900 mb-4">Four steps. One medal.</h2>
                <p class="text-neutral-500 text-lg">From choosing your series to holding your medal — here's the journey.</p>
            </div>

            {{-- Card stack area: cards slide in from below, stack on top (z-index) --}}
            <div class="relative w-full max-w-5xl min-h-[440px]">
                @foreach($steps as $i => $step)
                    <div class="absolute inset-0 transition-transform duration-[750ms] ease-out"
                         :class="{{ $i }} <= active ? 'translate-y-0' : 'translate-y-[120vh]'"
                         style="z-index: {{ $i + 10 }};">
                        <div class="bg-brand rounded-3xl p-10 shadow-warm-lg ring-1 ring-neutral-900/5">
                            <div class="grid grid-cols-2 gap-12 {{ $i % 2 === 1 ? '[direction:rtl]' : '' }}">
                                @include('landing.sections.partials.step-card-content', ['step' => $step, 'i' => $i])
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
