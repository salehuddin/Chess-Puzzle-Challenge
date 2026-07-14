<x-slot name="title">Browse Challenges — Chess Puzzle Challenge</x-slot>

<div>
    {{-- Hero Sector --}}
    <div class="bg-base-200 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="font-display text-4xl lg:text-5xl font-black text-neutral-900 mb-4">Browse Challenges</h1>
            <p class="text-lg text-neutral-500 max-w-2xl mx-auto">
                Filter by difficulty or grab a bundle deal for multiple series. 
                Complete a challenge to earn its physical medal.
            </p>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        {{-- Filters (Livewire Reactivity) --}}
        <div class="flex flex-wrap gap-2 justify-center mb-12">
            <button wire:click="$set('filter', 'all')" class="btn {{ $filter === 'all' ? 'btn-primary' : 'btn-outline btn-primary' }} rounded-full px-6">
                All challenges
            </button>
            <button wire:click="$set('filter', 'beginner')" class="btn {{ $filter === 'beginner' ? 'btn-success text-white' : 'btn-outline btn-success' }} rounded-full px-6 gap-2">
                🌱 Beginner
            </button>
            <button wire:click="$set('filter', 'intermediate')" class="btn {{ $filter === 'intermediate' ? 'btn-warning text-neutral-900' : 'btn-outline btn-warning' }} rounded-full px-6 gap-2">
                ⚡ Intermediate
            </button>
            <button wire:click="$set('filter', 'advanced')" class="btn {{ $filter === 'advanced' ? 'btn-error text-white' : 'btn-outline btn-error' }} rounded-full px-6 gap-2">
                🔥 Advanced
            </button>
        </div>

        {{-- Challenges Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-24">
            @forelse($challenges as $challenge)
                @php
                    $levelData = match(true) {
                        str_contains(strtolower($challenge->name), 'beginner') => ['🌱', 'Beginner', 'badge-success'],
                        str_contains(strtolower($challenge->name), 'intermediate') => ['⚡', 'Intermediate', 'badge-warning'],
                        str_contains(strtolower($challenge->name), 'advanced') => ['🔥', 'Advanced', 'badge-error'],
                        default => ['♟', 'Challenge', 'badge-primary'],
                    };
                    $rules = $challenge->rules ?? [];
                    $displayPuzzleCount = (int) ($challenge->puzzles_count ?? $challenge->puzzle_count ?? 0);
                    $enrollmentStatus = $enrollmentStatuses[$challenge->id] ?? null;
                    $enrollmentBadge = match($enrollmentStatus) {
                        'active' => ['In Progress', 'bg-green-100 text-green-800 border-green-200'],
                        'completed' => ['Completed', 'bg-orange-100 text-orange-800 border-orange-200'],
                        'pending' => ['Payment Pending', 'bg-orange-100 text-orange-800 border-orange-200'],
                        default => null,
                    };
                @endphp
                <div wire:key="challenge-{{ $challenge->id }}" class="bg-white rounded-2xl shadow-warm overflow-hidden border border-neutral-100 hover:shadow-warm-lg hover:-translate-y-1 transition-all duration-300 flex flex-col">
                    @if($challenge->poster_image)
                        <div class="h-40 relative overflow-hidden">
                            <img src="{{ asset('storage/'.$challenge->poster_image) }}" alt="{{ $challenge->name }}" class="h-full w-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                            <div class="absolute bottom-3 left-5">
                                <span class="badge {{ $levelData[2] }} badge-sm gap-1 font-semibold">
                                    {{ $levelData[0] }} {{ $levelData[1] }}
                                </span>
                            </div>
                            @if($enrollmentBadge)
                                <div class="absolute top-3 right-3">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $enrollmentBadge[1] }}">
                                        @if($enrollmentStatus === 'completed')
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        @elseif($enrollmentStatus === 'active')
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                        @endif
                                        {{ $enrollmentBadge[0] }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="bg-chess-pattern h-24 relative">
                            <div class="absolute inset-0 bg-gradient-to-b from-transparent to-white/80"></div>
                            <div class="absolute bottom-3 left-5">
                                <span class="badge {{ $levelData[2] }} badge-sm gap-1 font-semibold">
                                    {{ $levelData[0] }} {{ $levelData[1] }}
                                </span>
                            </div>
                            @if($enrollmentBadge)
                                <div class="absolute top-3 right-3">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $enrollmentBadge[1] }}">
                                        @if($enrollmentStatus === 'completed')
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        @elseif($enrollmentStatus === 'active')
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                        @endif
                                        {{ $enrollmentBadge[0] }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="p-6 flex flex-col flex-1">
                        <h3 class="font-display text-xl font-bold text-neutral-900 mb-2">{{ $challenge->name }}</h3>
                        <p class="text-neutral-500 text-sm mb-4 line-clamp-3 flex-1">{{ $challenge->description }}</p>

                        <div class="grid grid-cols-2 gap-y-2 mb-5 text-sm text-neutral-600">
                            <div class="flex items-center gap-2"><svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg> <strong>{{ $displayPuzzleCount }}</strong> puzzles</div>
                            <div class="flex items-center gap-2"><span>🏅</span> Physical medal</div>
                        </div>

                        <div class="flex items-center justify-between mb-5 pt-4 border-t border-neutral-100">
                            <div>
                                <p class="text-2xl font-black text-neutral-900">MYR {{ number_format($challenge->price_myr, 2) }}</p>
                                <p class="text-xs text-neutral-400">or USD {{ number_format($challenge->price_usd, 2) }}</p>
                            </div>
                        </div>

                        <a href="{{ url('/challenges/'.$challenge->slug) }}" class="btn btn-primary w-full gap-2">
                            @if($enrollmentStatus === 'active')
                                Continue Playing →
                            @elseif($enrollmentStatus === 'completed')
                                View Details →
                            @elseif($enrollmentStatus === 'pending')
                                Complete Payment →
                            @else
                                View Details →
                            @endif
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-12">
                    <p class="text-3xl mb-4">🤷‍♂️</p>
                    <p class="text-neutral-500 font-medium">No challenges found for this filter.</p>
                </div>
            @endforelse
        </div>

        {{-- Bundles --}}
        <div id="bundles" class="mb-12">
            <div class="text-center mb-10">
                <span class="inline-block text-accent font-semibold text-sm uppercase tracking-widest mb-2">Best Value</span>
                <h2 class="font-display text-4xl font-black text-neutral-900">Challenge Bundles</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:px-16">
                @foreach($bundles as $bundle)
                    @php
                        $bundleChallengeIds = $bundle->challenges->pluck('id')->all();
                        $ownedCount = collect($bundleChallengeIds)->filter(fn ($id) => isset($enrollmentStatuses[$id]))->count();
                    @endphp
                    <div wire:key="bundle-{{ $bundle->id }}" class="bg-white rounded-2xl shadow-warm border border-neutral-100 hover:shadow-warm-lg hover:-translate-y-1 transition-all duration-300 p-8 flex flex-col">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-accent/10 flex items-center justify-center text-xl">🎁</div>
                            <h3 class="font-display text-2xl font-bold text-neutral-900">{{ $bundle->name }}</h3>
                        </div>
                        <p class="text-neutral-500 text-sm mb-6 flex-1">{{ $bundle->description }}</p>
                        
                        <div class="mb-6 pb-6 border-b border-neutral-100">
                            <p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-3">Includes</p>
                            <div class="space-y-2">
                                @foreach($bundle->challenges as $c)
                                    @php
                                        $cStatus = $enrollmentStatuses[$c->id] ?? null;
                                    @endphp
                                    <div class="flex items-center gap-2 text-sm font-medium text-neutral-700">
                                        @if($cStatus)
                                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>{{ $c->name }}</span>
                                            <span class="text-xs text-green-600">({{ $cStatus === 'completed' ? 'completed' : 'enrolled' }})</span>
                                        @else
                                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>{{ $c->name }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            @if($ownedCount > 0)
                                <p class="mt-3 text-xs text-orange-700 bg-orange-50 px-3 py-2 rounded border border-orange-100">
                                    You already own {{ $ownedCount }} of {{ count($bundleChallengeIds) }} challenges in this bundle.
                                </p>
                            @endif
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-3xl font-black text-neutral-900">MYR {{ number_format($bundle->price_myr, 2) }}</p>
                                <p class="text-xs text-neutral-400">or USD {{ number_format($bundle->price_usd, 2) }}</p>
                            </div>
                            <a href="{{ route('bundles.enroll', $bundle) }}" class="btn btn-accent px-6">Buy Bundle</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
