{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- SECTION 13: FINAL CTA — the only full-saturation chartreuse moment  --}}
{{-- Black button on chartreuse bg — premium and unmissable              --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<section class="bg-brand text-neutral-900 py-20 lg:py-28 relative overflow-hidden">
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
