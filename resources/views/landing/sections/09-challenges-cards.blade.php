{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- SECTION 09: ACTIVE CHALLENGES — Clay medal poster cards           --}}
{{-- Pulls live $challenges; each card = medal poster + metadata + CTA  --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<section id="challenges" class="bg-neutral-900 text-white py-20 lg:py-28 relative overflow-hidden">
    <div class="absolute inset-0 bg-chess-pattern-brand-dark-sm pointer-events-none" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-fade-edges-dark pointer-events-none" aria-hidden="true"></div>
    <div class="absolute top-0 right-0 w-96 h-96 bg-brand/5 blur-[100px] pointer-events-none" aria-hidden="true"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="reveal flex flex-col md:flex-row md:items-end justify-between mb-12 gap-4">
            <div>
                <span class="inline-block text-brand font-bold text-xs uppercase tracking-[0.2em] mb-3">Current Series</span>
                <h2 class="font-display text-4xl lg:text-5xl font-black text-white">Earn These Medals</h2>
                <p class="text-white/60 mt-2 text-lg">Each series = 100 puzzles · unique medal · digital sticker · worldwide shipping.</p>
            </div>
            <a href="{{ url('/challenges') }}" class="btn bg-white text-neutral-900 hover:bg-brand hover:text-neutral-900 shrink-0 border-0 group transition-all duration-300">
                View All Challenges
                <span class="transition-transform group-hover:translate-x-1">→</span>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            @forelse($challenges as $i => $challenge)
                @php
                    $rules = $challenge->rules ?? [];
                    $levelData = match(true) {
                        str_contains(strtolower($challenge->name), 'beginner') => ['🌱', 'Beginner', 'bg-success/20 text-success'],
                        str_contains(strtolower($challenge->name), 'intermediate') => ['⚡', 'Intermediate', 'bg-warning/20 text-warning'],
                        str_contains(strtolower($challenge->name), 'advanced') => ['🔥', 'Advanced', 'bg-error/20 text-error'],
                        default => ['♟', 'Challenge', 'bg-white/10 text-white'],
                    };
                @endphp
                <div class="reveal group bg-white text-neutral-900 rounded-3xl shadow-warm overflow-hidden ring-1 ring-white/10 hover:ring-brand/40 hover:-translate-y-2 hover:shadow-warm-lg transition-all duration-400 flex flex-col"
                     style="--reveal-delay: {{ $i * 100 }}ms;">

                    {{-- Medal poster header --}}
                    <div class="h-48 relative bg-neutral-900 overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-brand/20 via-neutral-900 to-neutral-900"></div>
                        @if($challenge->poster_image)
                            <img src="{{ asset('storage/'.$challenge->poster_image) }}" alt="{{ $challenge->name }}" class="absolute inset-0 h-full w-full object-cover">
                        @elseif($challenge->medal_artwork)
                            <img src="{{ asset('storage/'.$challenge->medal_artwork) }}" alt="{{ $challenge->name }}" class="absolute inset-0 h-full w-full object-cover">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-7xl group-hover:scale-110 transition-transform duration-500 drop-shadow-2xl">🏅</div>
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-neutral-900/80 via-transparent to-transparent"></div>
                        <div class="absolute bottom-3 left-4">
                            <span class="badge {{ $levelData[2] }} gap-1 font-semibold">
                                {{ $levelData[0] }} {{ $levelData[1] }}
                            </span>
                        </div>
                        <div class="absolute top-3 right-4 bg-neutral-900/80 backdrop-blur-sm text-brand rounded-full px-2.5 py-0.5 text-xs font-bold">
                            {{ $challenge->puzzles_count ?? 100 }} puzzles
                        </div>
                    </div>

                    {{-- Card body --}}
                    <div class="p-6 flex flex-col flex-1">
                        <h3 class="font-display text-xl font-bold text-neutral-900 mb-2 leading-snug">{{ $challenge->name }}</h3>
                        <p class="text-neutral-500 text-sm leading-relaxed mb-5 line-clamp-2 flex-1">{{ $challenge->description }}</p>

                        <div class="flex items-center justify-between mb-5 pt-4 border-t border-neutral-100">
                            <div>
                                <p class="text-2xl font-black text-neutral-900">MYR {{ number_format($challenge->price_myr, 2) }}</p>
                                <p class="text-xs text-neutral-400">or USD {{ number_format($challenge->price_usd, 2) }}</p>
                            </div>
                            <span class="badge badge-outline border-neutral-300 text-neutral-500 text-[10px]">ONE-TIME</span>
                        </div>

                        <a href="{{ url('/challenges/'.$challenge->slug) }}" id="btn-challenge-{{ $challenge->id }}" class="btn btn-primary w-full gap-2 group">
                            Start Challenge
                            <span class="transition-transform group-hover:translate-x-1">→</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-16 text-white/50">
                    <p class="text-4xl mb-4">♟</p>
                    <p class="text-lg font-medium">No challenges yet — check back soon!</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
