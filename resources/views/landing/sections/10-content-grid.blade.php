{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- SECTION 10: CONTENT GRID — 4 cards (Bundles/Difficulty/HoF/FAQ)   --}}
{{-- If $bundles contains content, show first bundle inline as Bundle card --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<section class="bg-white py-20 lg:py-28 relative overflow-hidden">
    <div class="absolute inset-0 bg-chess-pattern-brand-light pointer-events-none" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-radial-brand-tl pointer-events-none" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-radial-brand-br pointer-events-none" aria-hidden="true"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="reveal text-center mb-12 max-w-2xl mx-auto">
            <span class="inline-block text-neutral-500 font-bold text-xs uppercase tracking-[0.2em] mb-3">More To Explore</span>
            <h2 class="font-display text-4xl lg:text-5xl font-black text-neutral-900">Find your next challenge</h2>
            <p class="text-neutral-500 mt-2 text-lg">Bundles, difficulty paths, your Hall of Fame, and answers to common questions.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            {{-- Card 1: Bundles (or generic CTA if no live bundles) --}}
            @if($bundles->isNotEmpty())
                <a href="{{ url('/challenges#bundles') }}" class="reveal group bg-neutral-900 text-white rounded-3xl p-8 hover:-translate-y-2 transition-all duration-400 ring-1 ring-neutral-900 hover:ring-brand/40 block relative overflow-hidden"
                   style="--reveal-delay: 0ms;">
                    <div class="absolute bottom-0 right-0 w-32 h-32 bg-brand/10 rounded-full blur-3xl group-hover:bg-brand/20 transition-colors"></div>
                    <div class="relative">
                        <div class="w-12 h-12 rounded-2xl bg-brand text-neutral-900 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 group-hover:rotate-6 transition-transform">🎁</div>
                        <h3 class="font-display text-2xl font-black mb-1">Bundles</h3>
                        <p class="text-white/60 text-sm mb-4 leading-relaxed">Multiple series, one purchase — save up to 25%.</p>
                        <div class="text-brand font-bold text-sm flex items-center gap-1 group-hover:gap-2 transition-all">Explore bundles →</div>
                    </div>
                </a>
            @else
                <a href="{{ url('/challenges#bundles') }}" class="reveal group bg-neutral-900 text-white rounded-3xl p-8 hover:-translate-y-2 transition-all duration-400 ring-1 ring-neutral-900 hover:ring-brand/40 block"
                   style="--reveal-delay: 0ms;">
                    <div class="w-12 h-12 rounded-2xl bg-brand text-neutral-900 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 group-hover:rotate-6 transition-transform">🎁</div>
                    <h3 class="font-display text-2xl font-black mb-1">Bundles</h3>
                    <p class="text-white/60 text-sm mb-4 leading-relaxed">Combine multiple series and save — coming soon.</p>
                    <div class="text-brand font-bold text-sm flex items-center gap-1 group-hover:gap-2 transition-all">Learn more →</div>
                </a>
            @endif

            {{-- Card 2: Browse by Difficulty --}}
            <a href="{{ url('/challenges') }}" class="reveal group bg-white rounded-3xl p-8 hover:-translate-y-2 transition-all duration-400 ring-1 ring-neutral-900/10 hover:ring-brand/40 hover:shadow-warm-lg block"
               style="--reveal-delay: 100ms;">
                <div class="w-12 h-12 rounded-2xl bg-neutral-100 group-hover:bg-brand group-hover:text-neutral-900 flex items-center justify-center text-2xl mb-4 transition-all duration-300">🎚</div>
                <h3 class="font-display text-2xl font-black text-neutral-900 mb-1">By Difficulty</h3>
                <p class="text-neutral-500 text-sm mb-4 leading-relaxed">Beginner (800-1200), Intermediate (1200-1600), Advanced (1600+).</p>
                <div class="text-neutral-900 font-bold text-sm flex items-center gap-1 group-hover:gap-2 group-hover:text-brand transition-all">Find your level →</div>
            </a>

            {{-- Card 3: Hall of Fame --}}
            <a href="{{ url('/hall-of-fame') }}" class="reveal group bg-white rounded-3xl p-8 hover:-translate-y-2 transition-all duration-400 ring-1 ring-neutral-900/10 hover:ring-brand/40 hover:shadow-warm-lg block"
               style="--reveal-delay: 200ms;">
                <div class="w-12 h-12 rounded-2xl bg-neutral-100 group-hover:bg-brand group-hover:text-neutral-900 flex items-center justify-center text-2xl mb-4 transition-all duration-300">🏆</div>
                <h3 class="font-display text-2xl font-black text-neutral-900 mb-1">Hall of Fame</h3>
                <p class="text-neutral-500 text-sm mb-4 leading-relaxed">Collect a unique digital sticker with every completed series.</p>
                <div class="text-neutral-900 font-bold text-sm flex items-center gap-1 group-hover:gap-2 group-hover:text-brand transition-all">See the collection →</div>
            </a>

            {{-- Card 4: FAQ --}}
            <a href="#faq" class="reveal group bg-white rounded-3xl p-8 hover:-translate-y-2 transition-all duration-400 ring-1 ring-neutral-900/10 hover:ring-brand/40 hover:shadow-warm-lg block"
               style="--reveal-delay: 300ms;">
                <div class="w-12 h-12 rounded-2xl bg-neutral-100 group-hover:bg-brand group-hover:text-neutral-900 flex items-center justify-center text-2xl mb-4 transition-all duration-300">?</div>
                <h3 class="font-display text-2xl font-black text-neutral-900 mb-1">FAQ</h3>
                <p class="text-neutral-500 text-sm mb-4 leading-relaxed">How solving works, shipping, pricing — everything you need.</p>
                <div class="text-neutral-900 font-bold text-sm flex items-center gap-1 group-hover:gap-2 group-hover:text-brand transition-all">Get answers →</div>
            </a>
        </div>

        {{-- Bundle card summarised inline below if exists --}}
        @if($bundles->isNotEmpty())
            <div id="bundles" class="reveal mt-16 max-w-3xl mx-auto">
                <div class="bg-white rounded-3xl ring-1 ring-neutral-900/10 shadow-warm p-8 hover:shadow-warm-lg transition-all duration-400">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-brand text-neutral-900 text-xs font-black px-2 py-0.5 rounded uppercase tracking-wider">Save More</span>
                                <span class="text-xs text-neutral-400 font-semibold uppercase tracking-wider">Featured Bundle</span>
                            </div>
                            <h3 class="font-display text-2xl font-black text-neutral-900 mb-1">{{ $bundles->first()->name }}</h3>
                            <p class="text-neutral-500 text-sm leading-relaxed mb-3">{{ $bundles->first()->description }}</p>
                            <p class="text-xs text-neutral-400">Each medal ships individually as you complete each series.</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-3xl font-black text-neutral-900">MYR {{ number_format($bundles->first()->price_myr, 2) }}</p>
                            <p class="text-xs text-neutral-400 mb-3">or USD {{ number_format($bundles->first()->price_usd, 2) }}</p>
                            <a href="{{ route('bundles.enroll', $bundles->first()) }}" class="btn btn-primary btn-sm gap-1 group">
                                Get Bundle <span class="transition-transform group-hover:translate-x-1">→</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
