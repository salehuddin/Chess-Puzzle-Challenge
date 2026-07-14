{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- SECTION 04: STATS — Clay-style asymmetric metric strip on black   --}}
{{-- One big emphasized metric (chartreuse) + 3 small supporting        --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<section class="bg-neutral-900 text-white py-16 lg:py-20 relative overflow-hidden">
    {{-- Grid pattern overlay --}}
    <div class="absolute inset-0 bg-grid-pattern pointer-events-none" aria-hidden="true"></div>
    {{-- Chartreuse glow accent --}}
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-96 h-96 bg-brand/5 blur-[100px] pointer-events-none" aria-hidden="true"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-10 lg:gap-8 items-center">

            {{-- Big featured metric — uses chartreuse for the one emphasis --}}
            <div class="reveal lg:col-span-1 text-center lg:text-left">
                <p class="font-display text-6xl lg:text-7xl font-black text-brand leading-none mb-2">87.9%</p>
                <p class="text-sm text-white/60 font-medium uppercase tracking-wider">Completion Rate</p>
                <p class="text-xs text-white/40 mt-1.5 leading-relaxed">of solvers who start a series finish all 100 puzzles</p>
            </div>

            {{-- 3 supporting metrics --}}
            <div class="lg:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-6 lg:gap-8">
                @foreach([
                    ['♟', '100', 'Puzzles Per Series'],
                    ['🌍', '80+', 'Shipping Countries'],
                    ['🏆', '1,200+', 'Active Solvers'],
                ] as $i => [$icon, $value, $label])
                    <div class="reveal border-l border-white/10 pl-6" style="--reveal-delay: {{ $i * 100 }}ms;">
                        <div class="text-brand text-2xl mb-2">{{ $icon }}</div>
                        <p class="font-display text-4xl font-black mb-1">{{ $value }}</p>
                        <p class="text-sm text-white/60 font-medium">{{ $label }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
