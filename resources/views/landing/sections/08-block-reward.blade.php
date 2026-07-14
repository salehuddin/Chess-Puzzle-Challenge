{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- SECTION 08: BLOCK — THE REWARD (inverted dark for visual rhythm)  --}}
{{-- Same structure as Block 1, but bg-neutral-900 / text-white          --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<section class="bg-neutral-900 text-white py-20 lg:py-28 relative overflow-hidden">
    <div class="absolute inset-0 bg-chess-pattern-brand-dark-lg pointer-events-none" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-fade-edges-dark pointer-events-none" aria-hidden="true"></div>
    <div class="absolute top-0 right-0 w-96 h-96 bg-brand/5 blur-[100px] pointer-events-none" aria-hidden="true"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-brand/5 blur-[100px] pointer-events-none" aria-hidden="true"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">

            {{-- Illustration: medal + Hall of Fame preview --}}
            <div class="reveal-left flex justify-center order-2 lg:order-1">
                <div class="relative">
                    <div class="absolute inset-0 bg-brand/15 blur-3xl scale-110 pointer-events-none animate-pulse-soft" aria-hidden="true"></div>

                    <div class="relative grid grid-cols-2 gap-6">
                        {{-- Medal mockup --}}
                        <div class="bg-white/5 backdrop-blur-sm rounded-3xl ring-1 ring-white/10 p-8 flex flex-col items-center justify-center aspect-square">
                            <div class="w-32 h-32 rounded-full bg-brand flex items-center justify-center text-6xl shadow-warm-lg ring-4 ring-white/10 hover:scale-110 transition-transform duration-500">
                                🏅
                            </div>
                            <div class="text-center mt-4">
                                <div class="font-display font-bold text-sm">Winter 2026 Medal</div>
                                <div class="text-white/50 text-xs mt-0.5">Custom-designed</div>
                            </div>
                        </div>

                        {{-- Sticker mockup --}}
                        <div class="bg-white/5 backdrop-blur-sm rounded-3xl ring-1 ring-white/10 p-8 flex flex-col items-center justify-center aspect-square">
                            <div class="w-32 h-32 rounded-2xl bg-white/10 flex items-center justify-center text-6xl text-brand ring-1 ring-brand/30 hover:scale-110 transition-transform duration-500">
                                ♛
                            </div>
                            <div class="text-center mt-4">
                                <div class="font-display font-bold text-sm">Digital Sticker</div>
                                <div class="text-white/50 text-xs mt-0.5">Permanent unlock</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Copy --}}
            <div class="reveal-right order-1 lg:order-2">
                <span class="inline-block text-brand font-bold text-xs uppercase tracking-[0.2em] mb-3">The Reward</span>
                <h2 class="font-display text-4xl lg:text-5xl font-black mb-5 leading-tight">
                    A real medal.<br>A permanent sticker.
                </h2>
                <p class="text-white/70 text-lg leading-relaxed mb-8 max-w-lg">
                    Finish all 100 puzzles and your digital sticker unlocks instantly — collected in your Hall of Fame forever. Then we custom-make and ship your physical medal worldwide. Real proof of real mastery.
                </p>

                <div class="space-y-4 mb-8">
                    @foreach([
                        ['⚡', 'Instant digital unlock', 'Sticker appears in your Hall of Fame the moment you finish puzzle #100'],
                        ['🌍', 'Worldwide medal shipping', 'We ship to 80+ countries with courier tracking'],
                        ['🏆', 'Build your collection', 'Every completed series adds a unique medal + sticker to your trophy case'],
                    ] as $feature)
                        <div class="flex items-start gap-4 group">
                            <div class="w-10 h-10 rounded-xl bg-white/5 group-hover:bg-brand group-hover:text-neutral-900 flex items-center justify-center text-lg shrink-0 transition-all duration-300">{{ $feature[0] }}</div>
                            <div>
                                <h4 class="font-display font-bold text-base mb-0.5">{{ $feature[1] }}</h4>
                                <p class="text-sm text-white/60 leading-relaxed">{{ $feature[2] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ url('/challenges') }}" class="btn btn-primary gap-2 group bg-brand text-neutral-900 border-0 hover:bg-brand/90">
                        Browse Challenges
                        <span class="transition-transform group-hover:translate-x-1">→</span>
                    </a>
                    <a href="{{ url('/hall-of-fame') }}" class="btn btn-ghost gap-2 text-white border border-white/20 hover:bg-white/10">
                        See Hall of Fame
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>
