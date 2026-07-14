{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- SECTION 11: TESTIMONIALS — medal-story poster cards (Clay-style) --}}
{{-- ⚠️ PLACEHOLDER COPY — swap for real solver testimonials before launch --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<section id="testimonials" class="bg-white py-20 lg:py-28 relative overflow-hidden">
    <div class="absolute inset-0 bg-chess-pattern-brand-light pointer-events-none" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-radial-brand-tl pointer-events-none" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-radial-brand-br pointer-events-none" aria-hidden="true"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="reveal text-center mb-12 max-w-2xl mx-auto">
            <span class="inline-block text-neutral-500 font-bold text-xs uppercase tracking-[0.2em] mb-3">From Our Solvers</span>
            <h2 class="font-display text-4xl lg:text-5xl font-black text-neutral-900">Real players. Real medals.</h2>
            <p class="text-neutral-500 mt-2 text-lg">Stories from chess players who finished a series and earned their trophy.</p>
        </div>

        {{-- Rating anchor (no fabricated press logos — single line of social proof) --}}
        <div class="reveal flex items-center justify-center gap-4 mb-12">
            <div class="flex items-center gap-1">
                @for($i = 0; $i < 5; $i++)<span class="text-brand text-lg">★</span>@endfor
            </div>
            <p class="text-sm text-neutral-600">
                <span class="font-bold text-neutral-900">Rated 4.X</span> by our solving community
                {{--<a href="#" class="ml-2 underline underline-offset-2 hover:text-brand">Read all reviews →</a>--}}
            </p>
        </div>

        {{-- 3 medal-poster testimonial cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">

            {{-- ⚠️ PLACEHOLDER COPY — replace with real testimonials --}}
            @php
                $testimonials = [
                    [
                        'medal' => '🏅',
                        'quote' => 'I finally stopped hanging pieces. The Forks series drilled the pattern into my brain — and the medal on my desk reminds me why I keep solving.',
                        'name'  => 'Aisha R.',
                        'country' => 'Malaysia',
                        'series' => 'Beginner Forks Series',
                    ],
                    [
                        'medal' => '♕',
                        'quote' => "Solved 100 endgames in two evenings. My blitz rating jumped 130 points and I've got the photo to prove it.",
                        'name'  => 'Tomás V.',
                        'country' => 'Spain',
                        'series' => 'Endgame Mastery Series',
                    ],
                    [
                        'medal' => '♔',
                        'quote' => "Best $25 I've spent on chess. The Hall of Fame is addictive — three stickers in, working on the fourth.",
                        'name'  => 'Liam K.',
                        'country' => 'United Kingdom',
                        'series' => 'Back-Rank Brutality',
                    ],
                ];
            @endphp

            @foreach($testimonials as $i => $testimonial)
                <div class="reveal group bg-neutral-900 text-white rounded-3xl overflow-hidden ring-1 ring-white/10 hover:ring-brand/40 hover:-translate-y-2 transition-all duration-400 flex flex-col"
                     style="--reveal-delay: {{ $i * 120 }}ms;">

                    {{-- Medal poster art --}}
                    <div class="h-40 relative overflow-hidden bg-gradient-to-br from-brand/20 via-neutral-900 to-neutral-900">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-7xl group-hover:scale-110 transition-transform duration-500 drop-shadow-2xl">{{ $testimonial['medal'] }}</span>
                        </div>
                        <div class="absolute top-3 right-4 text-xs font-bold text-brand bg-neutral-900/80 backdrop-blur-sm rounded-full px-2.5 py-1">
                            {{ $testimonial['series'] }}
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="p-6 flex flex-col flex-1">
                        <div class="flex items-center gap-1 mb-3">
                            @for($k = 0; $k < 5; $k++)<span class="text-brand">★</span>@endfor
                        </div>
                        <blockquote class="text-white/90 text-sm leading-relaxed mb-5 flex-1 italic">
                            "{{ $testimonial['quote'] }}"
                        </blockquote>
                        <div class="flex items-center justify-between pt-4 border-t border-white/10">
                            <div>
                                <p class="font-bold text-sm">— {{ $testimonial['name'] }}</p>
                                <p class="text-xs text-white/50">{{ $testimonial['country'] }}</p>
                            </div>
                            {{-- Status green used sparingly per color budget --}}
                            <span class="text-[10px] bg-success/20 text-success px-2 py-0.5 rounded-full font-semibold uppercase tracking-wider">
                                ✓ Verified Solver
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
