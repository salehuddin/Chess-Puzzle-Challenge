@push('vite')
    @vite(['resources/js/puzzle-player-page.js', 'resources/js/challenge-complete.js'])
@endpush

<div class="max-w-5xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

    {{-- Header Options --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h1 class="text-3xl sm:text-4xl font-display font-black text-neutral-900">{{ $challenge->name }}</h1>
            <p class="text-neutral-600 mt-1">
                Puzzle
                <span class="font-bold text-neutral-900">{{ $completedPuzzles + ($isComplete ? 0 : 1) }}</span>
                of {{ $totalPuzzles }}
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline mt-4 md:mt-0">Back to Dashboard</a>
    </div>

    {{-- Progress Bar --}}
    <div class="w-full bg-base-200 rounded-full h-2.5 mb-8 overflow-hidden">
        <div class="bg-neutral-900 h-full rounded-full transition-all duration-500 ease-out" style="width: {{ $totalPuzzles > 0 ? ($completedPuzzles / $totalPuzzles) * 100 : 0 }}%"></div>
    </div>

    {{-- Puzzle Progress Grid --}}
    @if(!empty($orderedPuzzleIds))
        <div class="mb-8 bg-white rounded-2xl border border-neutral-200 shadow-warm p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-neutral-500">Puzzle Progress Map</h3>
                <p class="text-xs text-neutral-500">{{ $completedPuzzles }} / {{ $totalPuzzles }} solved</p>
            </div>

            <div class="grid grid-cols-10 sm:grid-cols-12 md:grid-cols-15 lg:grid-cols-20 gap-1.5">
                @foreach($orderedPuzzleIds as $index => $puzzleId)
                    @php
                        $solved = in_array($puzzleId, $solvedPuzzleIds);
                        $current = $puzzleId === ($currentPuzzleId ?? null);
                    @endphp
                    <div
                        class="h-4 rounded-sm border {{ $solved ? 'bg-green-500 border-green-600' : ($current ? 'bg-orange-400 border-orange-500' : 'bg-base-200 border-neutral-200') }}"
                        title="Puzzle {{ $index + 1 }}{{ $current ? ' (current)' : '' }}{{ $solved ? ' (solved)' : '' }}"
                    ></div>
                @endforeach
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-neutral-600">
                <div class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-sm bg-green-500 border border-green-600"></span><span>Solved</span></div>
                <div class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-sm bg-orange-400 border border-orange-500"></span><span>Current</span></div>
                <div class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-sm bg-base-200 border border-neutral-200"></span><span>Remaining</span></div>
            </div>
        </div>
    @endif

    @if($isComplete)
        @php
            $levelData = match (true) {
                str_contains(strtolower($challenge->name), 'beginner')     => ['🌱', 'Beginner', 'badge-success'],
                str_contains(strtolower($challenge->name), 'intermediate') => ['⚡', 'Intermediate', 'badge-warning'],
                str_contains(strtolower($challenge->name), 'advanced')     => ['🔥', 'Advanced', 'badge-error'],
                default                                                    => ['♟', 'Challenge', 'badge-primary'],
            };

            $shareUrl = auth()->user()?->isPubliclyViewable()
                ? route('profile.show', auth()->user())
                : route('dashboard');
            $shareText = rawurlencode("I just solved all {$totalPuzzles} puzzles in {$challenge->name} at ChessPuzzleChallenge.com");
            $shareLink = rawurlencode($shareUrl);
        @endphp

        <div
            x-data="challengeComplete()"
            x-init="init()"
            class="relative overflow-hidden rounded-3xl border border-orange-200 bg-gradient-to-br from-orange-50 via-white to-brand/5 shadow-warm-lg"
        >
            {{-- Decorative glow --}}
            <div class="pointer-events-none absolute -top-32 -right-32 w-96 h-96 bg-brand/10 rounded-full blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-32 w-96 h-96 bg-orange-300/20 rounded-full blur-3xl"></div>

            <div class="relative px-6 py-16 sm:px-12 sm:py-20 text-center reveal-scale reveal-visible">
                {{-- Trophy / Stats Hero --}}
                <div class="w-28 h-28 sm:w-32 sm:h-32 mx-auto mb-6 animate-float">
                    <svg class="w-full h-full text-orange-500 drop-shadow-lg" fill="currentColor" viewBox="0 0 24 24"><path d="M21 4h-3V3a1 1 0 00-1-1H7a1 1 0 00-1 1v1H3a1 1 0 00-1 1v3c0 4.31 3.14 7.92 7.28 8.82l-.4 3.18H6a1 1 0 00-1 1v2a1 1 0 001 1h12a1 1 0 001-1v-2a1 1 0 00-1-1h-2.88l-.4-3.18C18.86 15.92 22 12.31 22 8V5a1 1 0 00-1-1zM4 8V6h2v6.83C4.85 11.53 4 9.87 4 8zm16 0c0 1.87-.85 3.53-2 4.83V6h2v2z"/></svg>
                </div>

                <span class="badge {{ $levelData[2] }} badge-lg gap-1 mb-4">
                    <span>{{ $levelData[0] }}</span> {{ $levelData[1] }}
                </span>

                <h2 class="text-4xl sm:text-5xl font-display font-black text-neutral-900 mb-3">Challenge Complete!</h2>
                <p class="text-lg text-neutral-700 mb-10 max-w-lg mx-auto">You have successfully solved all the puzzles. Your new sticker has been added to your dashboard.</p>

                {{-- Stats Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4 max-w-2xl mx-auto mb-10">
                    <div class="bg-white/80 backdrop-blur rounded-2xl border border-neutral-200 p-5 text-left">
                        <p class="text-xs font-bold uppercase tracking-[0.15em] text-neutral-500">Puzzles Solved</p>
                        <p class="text-3xl font-display font-black text-neutral-900 mt-1">{{ $completedPuzzles }}<span class="text-lg text-neutral-400">/{{ $totalPuzzles }}</span></p>
                    </div>
                    <div class="bg-white/80 backdrop-blur rounded-2xl border border-neutral-200 p-5 text-left">
                        <p class="text-xs font-bold uppercase tracking-[0.15em] text-neutral-500">Difficulty</p>
                        <p class="text-2xl font-display font-bold text-neutral-900 mt-1.5">{{ $levelData[0] }} {{ $levelData[1] }}</p>
                    </div>
                    <div class="bg-white/80 backdrop-blur rounded-2xl border border-neutral-200 p-5 text-left col-span-2 sm:col-span-1">
                        <p class="text-xs font-bold uppercase tracking-[0.15em] text-neutral-500">Sticker Earned</p>
                        <p class="text-2xl font-display font-bold text-brand mt-1.5">✨ Unlocked</p>
                    </div>
                </div>

                {{-- Rating CTA (only when review pending and not yet submitted) --}}
                <div x-show="!reviewSubmitted" x-cloak>
                    <h3 class="text-2xl font-display font-bold text-neutral-900 mb-2">What did you think of this puzzle?</h3>
                    <p class="text-sm text-neutral-600 mb-6">Tap a chess piece — pawn to queen — to share your verdict.</p>

                    <div class="flex justify-center mb-6">
                        <x-challenge.piece-rating
                            label="Puzzle rating"
                            selected="selectedPuzzleRating"
                            on-pick="selectPuzzleRating"
                            size="lg"
                        />
                    </div>

                    {{-- Reveal Review Card --}}
                    <div
                        x-ref="reviewCard"
                        x-show="showReviewCard"
                        x-cloak
                        x-transition:enter="transition ease-out duration-500"
                        x-transition:enter-start="opacity-0 translate-y-6"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="max-w-xl mx-auto mt-8 bg-white rounded-2xl border border-neutral-200 shadow-warm p-6 sm:p-8 text-left"
                    >
                        <h4 class="text-xl font-display font-black text-neutral-900 mb-1">Leave a Review</h4>
                        <p class="text-sm text-neutral-600 mb-6">Your feedback shapes future puzzle series and the CPC platform.</p>

                        {{-- Puzzle rating (persisted) --}}
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-neutral-700 mb-2">How was <span class="font-bold">{{ $challenge->name }}</span>?</label>
                            <div class="flex items-center justify-between gap-3">
                                <x-challenge.piece-rating
                                    label="Puzzle rating"
                                    selected="selectedPuzzleRating"
                                    on-pick="selectPuzzleRating"
                                />
                                <span class="text-xs text-neutral-500" x-text="selectedPuzzleRating === 5 ? 'Masterpiece' : selectedPuzzleRating === 4 ? 'Great' : selectedPuzzleRating === 3 ? 'Solid' : selectedPuzzleRating === 2 ? 'Meh' : selectedPuzzleRating === 1 ? 'Tough' : ''"></span>
                            </div>
                        </div>

                        {{-- Platform rating --}}
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-neutral-700 mb-2">And how is the overall CPC platform?</label>
                            <div class="flex items-center justify-between gap-3">
                                <x-challenge.piece-rating
                                    label="Platform rating"
                                    selected="selectedPlatformRating"
                                    on-pick="selectPlatformRating"
                                />
                                <span class="text-xs text-neutral-500" x-text="selectedPlatformRating === 5 ? 'Love it' : selectedPlatformRating === 4 ? 'Great' : selectedPlatformRating === 3 ? 'Good' : selectedPlatformRating === 2 ? 'Okay' : selectedPlatformRating === 1 ? 'Needs work' : ''"></span>
                            </div>
                        </div>

                        {{-- Title (optional) --}}
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-neutral-700 mb-2">Headline <span class="text-neutral-400 font-normal">(optional)</span></label>
                            <input
                                type="text"
                                wire:model="reviewTitle"
                                maxlength="120"
                                placeholder="A few words about your experience"
                                class="input input-bordered w-full"
                            />
                        </div>

                        {{-- Feedback (optional) --}}
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-neutral-700 mb-2">Tell us more <span class="text-neutral-400 font-normal">(optional)</span></label>
                            <textarea
                                wire:model="reviewBody"
                                rows="4"
                                maxlength="2000"
                                placeholder="What did you enjoy? What could be better? Any puzzle that stumped you?"
                                class="textarea textarea-bordered w-full"
                            ></textarea>
                        </div>

                        <button
                            type="button"
                            @click="submitReview()"
                            :disabled="selectedPuzzleRating === 0 || selectedPlatformRating === 0"
                            class="btn btn-primary btn-block gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Submit Review
                        </button>
                    </div>
                </div>

                {{-- Thank-you state after submit --}}
                <div
                    x-show="reviewSubmitted"
                    x-cloak
                    x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="max-w-md mx-auto mt-8 p-6 bg-white rounded-2xl border border-brand/30 shadow-warm"
                >
                    <div class="w-14 h-14 mx-auto bg-brand/15 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-7 h-7 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h4 class="text-xl font-display font-black text-neutral-900 mb-1">Thanks for your review!</h4>
                    <p class="text-sm text-neutral-600">Your feedback helps us curate better puzzles and improve CPC.</p>
                </div>

                {{-- Social share: shown if review not pending OR just submitted --}}
                @if(! $reviewPending)
                    <div x-show="reviewSubmitted || true" class="mt-10 max-w-md mx-auto">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-neutral-500 mb-3">Share your win</p>
                        <div class="flex flex-wrap justify-center gap-2">
                            <button type="button" @click="copyShareLink()" class="btn btn-outline btn-sm gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 015.656 0l1.414 1.414a4 4 0 010 5.656l-3 3a4 4 0 01-5.656 0M10.172 13.828a4 4 0 01-5.656 0L3.1 12.414a4 4 0 010-5.656l3-3a4 4 0 015.656 0"/></svg>
                                Copy link
                            </button>
                            <a href="https://twitter.com/intent/tweet?text={{ $shareText }}&url={{ $shareLink }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-sm gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231 5.45-6.231zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77z"/></svg>
                                X
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareLink }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-sm gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 011.141.195v3.325a8.623 8.623 0 00-.653-.036 26.805 26.805 0 00-.733-.009c-.307 0-.601.015-.903.048-.497.05-.898.17-1.181.395-.281.224-.481.505-.586.851-.116.342-.191.819-.191 1.406v1.285h2.953l-.672 3.667h-2.281v7.98z"/></svg>
                                Facebook
                            </a>
                            <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareLink }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-sm gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                WhatsApp
                            </a>
                        </div>

                        {{-- View profile link --}}
                        <a href="{{ $shareUrl }}" class="block mt-4 text-xs text-neutral-500 hover:text-neutral-900 transition-colors">
                            View your public profile →
                        </a>
                    </div>
                @endif

                {{-- Medal claim CTA: preserves existing behavior, shown below the review/share flow --}}
                @if($medalRequestPending)
                    <div class="mt-10 max-w-md mx-auto p-5 bg-white rounded-2xl border border-neutral-200 shadow-warm">
                        <div class="flex items-start gap-3 text-left">
                            <div class="text-3xl">🏅</div>
                            <div>
                                <p class="font-display font-black text-neutral-900">Claim your physical medal</p>
                                <p class="text-sm text-neutral-600 mt-1">Confirm your shipping address and request your medal, or do it later from your dashboard.</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col sm:flex-row justify-center gap-4">
                        <a href="{{ route('medal-request', $enrollment) }}" class="btn btn-primary btn-lg gap-2" wire:navigate>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            Request My Medal
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline btn-lg" wire:navigate>View Dashboard</a>
                    </div>
                @else
                    <div class="mt-10 flex justify-center gap-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg" wire:navigate>View Dashboard</a>
                        <a href="{{ route('challenges.index') }}" class="btn btn-outline btn-lg" wire:navigate>Next Challenge</a>
                    </div>
                @endif
            </div>
        </div>
    @else
        {{-- Alpine Board Component --}}
        <div
            x-data="puzzlePlayer()"
            x-init="() => {}"
            @puzzle-loaded.window="initPlayer($wire.currentFen, $wire.currentMoves, $wire.currentPuzzleId, $wire.completionToken, $wire.isFinalPuzzle)"
            data-puzzle-player
            class="flex flex-col lg:flex-row gap-8 relative"
        >

            {{-- Board Area --}}
            <div class="w-full lg:w-2/3 max-w-[600px] mx-auto flex-shrink-0 relative">
                <div class="aspect-square relative rounded-2xl border border-neutral-900/10 overflow-hidden bg-white shadow-warm-lg transition-opacity duration-200">
                    <div id="board" wire:ignore class="w-full h-full"></div>

                    {{-- Animated Success Modal --}}
                    <div x-show="showSuccess" x-cloak
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-90 translate-y-4"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-90"
                         class="absolute inset-0 z-50 flex items-center justify-center bg-white/80 backdrop-blur-sm"
                         style="display: none;">

                        <div class="bg-white p-8 rounded-2xl border border-neutral-200 shadow-warm-lg text-center transform max-w-sm w-full mx-4">
                            <div class="w-20 h-20 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-4 text-green-600 animate-bounce">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <h3 class="text-3xl font-display font-black text-neutral-900 mb-2">Excellent!</h3>
                            <p class="text-neutral-600 mb-6 font-medium">You solved this puzzle correctly.</p>
                            <button @click="nextPuzzle($wire)" class="w-full btn btn-primary flex items-center justify-center gap-2 h-12 text-lg">
                                Next Puzzle
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Puzzle Data Error Overlay --}}
                    <div x-show="puzzleError" x-cloak
                         class="absolute inset-0 z-50 flex items-center justify-center bg-red-950/80 backdrop-blur-sm"
                         style="display: none;">
                        <div class="bg-white p-8 rounded-2xl border border-neutral-200 shadow-warm-lg text-center max-w-sm w-full mx-4">
                            <div class="w-20 h-20 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4 text-red-600">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            </div>
                            <h3 class="text-2xl font-display font-black text-neutral-900 mb-2">Puzzle Error</h3>
                            <p class="text-neutral-600 mb-4 font-medium text-sm" x-text="puzzleErrorMessage"></p>
                            <p class="text-xs text-neutral-400">Please contact support with the puzzle ID.</p>
                        </div>
                    </div>

                    {{-- Animated Error Modal --}}
                    <div x-show="showError" x-cloak
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-90 translate-y-4"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-90"
                         class="absolute inset-0 z-50 flex items-center justify-center bg-white/80 backdrop-blur-sm"
                         style="display: none;">

                        <div class="bg-white p-8 rounded-2xl border border-neutral-200 shadow-warm-lg text-center transform max-w-sm w-full mx-4">
                            <div class="w-20 h-20 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4 text-red-600">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </div>
                            <h3 class="text-3xl font-display font-black text-neutral-900 mb-2">Incorrect Move</h3>
                            <p x-show="!lastMoveError" class="text-neutral-600 mb-6 font-medium">That's not the correct follow-up in this position.</p>
                            <p x-show="lastMoveError" class="text-neutral-600 mb-6 font-medium text-sm" x-text="lastMoveError"></p>
                            <button @click="retryMove()" class="w-full btn bg-red-500 text-white hover:bg-red-600 border-none flex items-center justify-center gap-2 h-12 text-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Retry Move
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar / Instructions --}}
            <div class="w-full lg:w-1/3">
                <div class="rounded-2xl border border-neutral-200 bg-white shadow-warm h-full">
                    <div class="p-6">
                        <h3 class="text-2xl font-display font-black text-neutral-900">Your Turn</h3>

                        <div x-show="puzzleError" class="py-10 text-center">
                            <p class="text-red-600 font-semibold">Puzzle data error</p>
                            <p class="text-neutral-500 text-sm mt-2" x-text="puzzleErrorMessage"></p>
                        </div>

                        <div x-show="!ready && !puzzleError" class="flex flex-col items-center justify-center py-10 text-neutral-400">
                            <svg class="w-8 h-8 animate-spin mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span>Loading puzzle...</span>
                        </div>

                        <div x-show="ready && !puzzleError" x-cloak class="mt-4">
                            @if(!empty($currentPuzzleThemes))
                                <div class="flex flex-wrap gap-1.5 mb-4">
                                    @foreach($currentPuzzleThemes as $theme)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-50 text-orange-700 border border-orange-200">
                                            {{ ucfirst(str_replace(['-', '_'], ' ', $theme)) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <p class="text-neutral-700 text-base sm:text-lg">
                                Find the best move for
                                <span class="font-bold inline-block px-3 py-1 rounded-md"
                                      :class="playerColor === 'white' ? 'bg-orange-100 text-orange-900' : 'bg-neutral-800 text-white'"
                                      x-text="playerColor === 'white' ? 'White' : 'Black'">
                                </span>.
                            </p>

                            <template x-if="lastOpponentMove">
                                <div class="mt-6 p-4 bg-base-200 rounded-xl border border-neutral-200">
                                    <div class="flex items-center gap-3 text-neutral-700">
                                        <span class="w-2.5 h-2.5 rounded-full bg-red-400 inline-block animate-pulse shadow-[0_0_8px_rgba(248,113,113,0.8)]"></span>
                                        <span class="text-xs font-bold uppercase tracking-[0.2em] text-neutral-500">Opponent Played</span>
                                    </div>
                                    <div class="mt-2 font-mono text-2xl font-bold text-neutral-900 pl-6" x-text="lastOpponentMove"></div>
                                </div>
                            </template>

                            <div class="mt-6 grid grid-cols-2 gap-3">
                                <button
                                    type="button"
                                    @click="undoMove()"
                                    :disabled="currentMoveIndex <= 1"
                                    class="btn btn-outline"
                                >
                                    Undo
                                </button>
                                <button
                                    type="button"
                                    @click="resetPuzzle()"
                                    class="btn btn-ghost border border-neutral-200"
                                >
                                    Reset Puzzle
                                </button>
                            </div>

                            <div class="mt-4">
                                <button
                                    type="button"
                                    @click="showEngineHint()"
                                    class="w-full btn btn-outline"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                                    Need a hint?
                                </button>

                                <p x-show="hintClicks > 0" x-cloak class="mt-2 text-xs text-center text-neutral-500">
                                    Hints used: <span x-text="hintClicks"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    @endif

</div>
