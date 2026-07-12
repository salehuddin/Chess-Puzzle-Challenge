<x-marketing-layout :title="$user->name . ' — Chess Puzzle Challenge'">
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">

        {{-- Profile Header --}}
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-8 mb-12">
            {{-- Avatar --}}
            <div class="shrink-0">
                @if($user->avatar)
                    <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="w-28 h-28 rounded-full object-cover border-4 border-white shadow-warm-lg" />
                @else
                    <div class="w-28 h-28 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-4xl border-4 border-white shadow-warm-lg">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
            </div>

            {{-- Info --}}
            <div class="text-center sm:text-left flex-1">
                <h1 class="font-display text-3xl font-bold text-stone-900">{{ $user->name }}</h1>
                <p class="text-stone-500 text-sm mt-1">&#64;{{ $user->username }}</p>
                <p class="text-stone-400 text-xs mt-2">Member since {{ $user->created_at->format('F Y') }}</p>

                @if($user->bio)
                    <p class="mt-4 text-stone-700 leading-relaxed max-w-lg">{{ $user->bio }}</p>
                @endif
            </div>
        </div>

        {{-- Stat Counters --}}
        <div class="grid grid-cols-3 gap-4 mb-12">
            <div class="bg-white rounded-2xl shadow-warm border border-stone-100 p-6 text-center">
                <p class="text-3xl font-bold text-primary font-display">{{ $completedChallengesCount }}</p>
                <p class="text-sm text-stone-500 mt-1">Challenges Completed</p>
            </div>
            <div class="bg-white rounded-2xl shadow-warm border border-stone-100 p-6 text-center">
                <p class="text-3xl font-bold text-primary font-display">{{ $solvedPuzzlesCount }}</p>
                <p class="text-sm text-stone-500 mt-1">Puzzles Solved</p>
            </div>
            <div class="bg-white rounded-2xl shadow-warm border border-stone-100 p-6 text-center">
                <p class="text-3xl font-bold text-primary font-display">{{ $stickersCount }}</p>
                <p class="text-sm text-stone-500 mt-1">Stickers Earned</p>
            </div>
        </div>

        {{-- Earned Stickers --}}
        @if($user->stickers->isNotEmpty())
            <div class="mb-12">
                <h2 class="font-display text-2xl font-bold text-stone-900 mb-6 text-center">Earned Stickers</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($user->stickers as $sticker)
                        <div class="flex flex-col items-center p-4 bg-white rounded-2xl shadow-warm border border-stone-100 hover:-translate-y-1 transition-all duration-300">
                            @if($sticker->challenge->sticker_artwork)
                                <img src="{{ Storage::url($sticker->challenge->sticker_artwork) }}" alt="{{ $sticker->challenge->name }}" class="w-24 h-24 object-contain mb-3 drop-shadow-lg" />
                            @else
                                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-amber-300 to-amber-600 flex items-center justify-center text-white text-sm font-bold shadow-inner mb-3">
                                    {{ $sticker->challenge->name }}
                                </div>
                            @endif
                            <p class="text-sm font-semibold text-stone-800 text-center">{{ $sticker->challenge->name }}</p>
                            @if($sticker->unlocked_at)
                                <p class="text-xs text-stone-400 mt-1">{{ $sticker->unlocked_at->format('M Y') }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Completed Challenges --}}
        @if($user->enrollments->isNotEmpty())
            <div class="mb-12">
                <h2 class="font-display text-2xl font-bold text-stone-900 mb-6 text-center">Completed Challenges</h2>
                <div class="space-y-3">
                    @foreach($user->enrollments as $enrollment)
                        <a href="{{ route('challenges.show', $enrollment->challenge->slug) }}" class="flex items-center justify-between p-4 bg-white rounded-xl shadow-warm border border-stone-100 hover:border-primary/30 transition-colors group">
                            <div class="flex items-center gap-3">
                                @if($enrollment->challenge->sticker_artwork)
                                    <img src="{{ Storage::url($enrollment->challenge->sticker_artwork) }}" alt="" class="w-10 h-10 object-contain" />
                                @endif
                                <div>
                                    <p class="font-semibold text-stone-800 group-hover:text-primary transition-colors">{{ $enrollment->challenge->name }}</p>
                                    <p class="text-xs text-stone-400">Completed {{ $enrollment->completed_at?->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-stone-300 group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Empty state --}}
        @if($user->stickers->isEmpty() && $user->enrollments->isEmpty())
            <div class="text-center py-16">
                <p class="text-5xl mb-4">♟</p>
                <p class="text-stone-500">This player hasn't completed any challenges yet.</p>
                <a href="{{ url('/challenges') }}" class="btn btn-primary btn-sm mt-4">Browse Challenges →</a>
            </div>
        @endif
    </div>
</x-marketing-layout>
