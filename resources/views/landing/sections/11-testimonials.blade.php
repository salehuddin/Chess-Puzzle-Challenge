{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- SECTION 11: TESTIMONIALS — medal-story poster cards (Clay-style) --}}
{{-- ⚠️ PLACEHOLDER COPY — swap for real solver testimonials before launch --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<section id="testimonials" class="bg-white py-20 lg:py-28 relative overflow-hidden">
    <div class="absolute inset-0 bg-radial-brand-tl pointer-events-none" aria-hidden="true"></div>

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
    </div>{{-- /max-w-7xl container --}}

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
            [
                'medal' => '♞',
                'quote' => 'The knight tactics series finally made me see royal forks coming. Won three tournament games last month with the exact pattern.',
                'name'  => 'Priya S.',
                'country' => 'India',
                'series' => 'Knight Tactics Series',
            ],
            [
                'medal' => '♖',
                'quote' => 'I always forgot about back-rank mates. After 100 puzzles I see them instantly — my club mates think I improved overnight.',
                'name'  => 'Marco B.',
                'country' => 'Italy',
                'series' => 'Back-Rank Brutality',
            ],
            [
                'medal' => '♗',
                'quote' => 'The pins and skewers series fixed my biggest blind spot. The medal arrived in perfect condition — beautiful craftsmanship.',
                'name'  => 'Yuki T.',
                'country' => 'Japan',
                'series' => 'Pins & Skewers Series',
            ],
        ];
    @endphp

    {{-- Marquee: two identical sets side-by-side for seamless loop --}}
    <div class="relative overflow-hidden">
        <div class="flex animate-[marquee_25s_linear_infinite] lg:animate-[marquee_50s_linear_infinite] hover:[animation-play-state:paused]">

                {{-- Set 1 --}}
                <div class="flex shrink-0 gap-6 pr-6">
                    @foreach($testimonials as $testimonial)
                        <div class="shrink-0 w-80 bg-neutral-900 text-white rounded-3xl overflow-hidden ring-1 ring-white/10 hover:ring-brand/40 transition-all duration-300 group flex flex-col">
                            <div class="h-32 relative overflow-hidden bg-gradient-to-br from-brand/20 via-neutral-900 to-neutral-900">
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-6xl group-hover:scale-110 transition-transform duration-500 drop-shadow-2xl">{{ $testimonial['medal'] }}</span>
                                </div>
                                <div class="absolute top-3 right-3 text-[10px] font-bold text-brand bg-neutral-900/80 backdrop-blur-sm rounded-full px-2 py-0.5">
                                    {{ $testimonial['series'] }}
                                </div>
                            </div>
                            <div class="p-5 flex flex-col flex-1">
                                <div class="flex items-center gap-1 mb-3">
                                    @for($k = 0; $k < 5; $k++)<span class="text-brand text-sm">★</span>@endfor
                                </div>
                                <blockquote class="text-white/90 text-sm leading-relaxed mb-4 flex-1 italic">
                                    "{{ $testimonial['quote'] }}"
                                </blockquote>
                                <div class="flex items-center justify-between pt-4 border-t border-white/10">
                                    <div>
                                        <p class="font-bold text-sm">— {{ $testimonial['name'] }}</p>
                                        <p class="text-xs text-white/50">{{ $testimonial['country'] }}</p>
                                    </div>
                                    <span class="text-[10px] bg-success/20 text-success px-2 py-0.5 rounded-full font-semibold uppercase tracking-wider">
                                        ✓ Verified
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Set 2 (duplicate, hidden from screen readers) --}}
                <div class="flex shrink-0 gap-6 pr-6" aria-hidden="true">
                    @foreach($testimonials as $testimonial)
                        <div class="shrink-0 w-80 bg-neutral-900 text-white rounded-3xl overflow-hidden ring-1 ring-white/10 hover:ring-brand/40 transition-all duration-300 group flex flex-col">
                            <div class="h-32 relative overflow-hidden bg-gradient-to-br from-brand/20 via-neutral-900 to-neutral-900">
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-6xl group-hover:scale-110 transition-transform duration-500 drop-shadow-2xl">{{ $testimonial['medal'] }}</span>
                                </div>
                                <div class="absolute top-3 right-3 text-[10px] font-bold text-brand bg-neutral-900/80 backdrop-blur-sm rounded-full px-2 py-0.5">
                                    {{ $testimonial['series'] }}
                                </div>
                            </div>
                            <div class="p-5 flex flex-col flex-1">
                                <div class="flex items-center gap-1 mb-3">
                                    @for($k = 0; $k < 5; $k++)<span class="text-brand text-sm">★</span>@endfor
                                </div>
                                <blockquote class="text-white/90 text-sm leading-relaxed mb-4 flex-1 italic">
                                    "{{ $testimonial['quote'] }}"
                                </blockquote>
                                <div class="flex items-center justify-between pt-4 border-t border-white/10">
                                    <div>
                                        <p class="font-bold text-sm">— {{ $testimonial['name'] }}</p>
                                        <p class="text-xs text-white/50">{{ $testimonial['country'] }}</p>
                                    </div>
                                    <span class="text-[10px] bg-success/20 text-success px-2 py-0.5 rounded-full font-semibold uppercase tracking-wider">
                                        ✓ Verified
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Edge fades inside marquee container so they can't overflow into next section --}}
            <div class="absolute left-0 top-0 bottom-0 w-24 bg-gradient-to-r from-white to-transparent pointer-events-none z-10"></div>
            <div class="absolute right-0 top-0 bottom-0 w-24 bg-gradient-to-l from-white to-transparent pointer-events-none z-10"></div>
        </div>
</section>
