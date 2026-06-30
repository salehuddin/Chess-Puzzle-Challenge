@push('vite')
    @vite('resources/js/puzzle-player-page.js')
@endpush

<div class="max-w-5xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    
    {{-- Header Options --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold font-serif text-green-900">{{ $challenge->name }}</h1>
            <p class="text-gray-600 mt-1" x-data>
                Puzzle 
                <span class="font-bold text-gray-900">{{ $completedPuzzles + ($isComplete ? 0 : 1) }}</span> 
                of {{ $totalPuzzles }}
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline mt-4 md:mt-0">Back to Dashboard</a>
    </div>

    {{-- Progress Bar --}}
    <div class="w-full bg-gray-200 rounded-full h-3 mb-8 overflow-hidden shadow-inner">
        <div class="bg-green-600 h-full rounded-full transition-all duration-500 ease-out" style="width: {{ $totalPuzzles > 0 ? ($completedPuzzles / $totalPuzzles) * 100 : 0 }}%"></div>
    </div>

    {{-- Puzzle Progress Grid --}}
    @if(!empty($orderedPuzzleIds))
        <div class="mb-8 bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-600">Puzzle Progress Map</h3>
                <p class="text-xs text-gray-500">{{ $completedPuzzles }} / {{ $totalPuzzles }} solved</p>
            </div>

            <div class="grid grid-cols-10 sm:grid-cols-12 md:grid-cols-15 lg:grid-cols-20 gap-1.5">
                @foreach($orderedPuzzleIds as $index => $puzzleId)
                    @php
                        $solved = in_array($puzzleId, $solvedPuzzleIds);
                        $current = $puzzleId === ($currentPuzzleId ?? null);
                    @endphp
                    <div
                        class="h-4 rounded-sm border {{ $solved ? 'bg-green-500 border-green-600' : ($current ? 'bg-amber-400 border-amber-500' : 'bg-gray-100 border-gray-200') }}"
                        title="Puzzle {{ $index + 1 }}{{ $current ? ' (current)' : '' }}{{ $solved ? ' (solved)' : '' }}"
                    ></div>
                @endforeach
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-gray-600">
                <div class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-sm bg-green-500 border border-green-600"></span><span>Solved</span></div>
                <div class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-sm bg-amber-400 border border-amber-500"></span><span>Current</span></div>
                <div class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-sm bg-gray-100 border border-gray-200"></span><span>Remaining</span></div>
            </div>
        </div>
    @endif

    @if($isComplete)
        <div class="text-center py-20 bg-amber-50 rounded-2xl shadow-xl border border-amber-200">
            <div class="w-32 h-32 mx-auto mb-6">
                <!-- Simple Trophy SVG -->
                <svg class="w-full h-full text-amber-500 drop-shadow-md" fill="currentColor" viewBox="0 0 24 24"><path d="M21 4h-3V3a1 1 0 00-1-1H7a1 1 0 00-1 1v1H3a1 1 0 00-1 1v3c0 4.31 3.14 7.92 7.28 8.82l-.4 3.18H6a1 1 0 00-1 1v2a1 1 0 001 1h12a1 1 0 001-1v-2a1 1 0 00-1-1h-2.88l-.4-3.18C18.86 15.92 22 12.31 22 8V5a1 1 0 00-1-1zM4 8V6h2v6.83C4.85 11.53 4 9.87 4 8zm10.5 8c-1.38 0-2.5-.9-2.5-2s1.12-2 2.5-2 2.5.9 2.5 2-1.12 2-2.5 2zm5.5-8c0 1.87-.85 3.53-2 4.83V6h2v2z"/></svg>
            </div>
            <h2 class="text-4xl font-bold text-amber-700 mb-4 font-serif">Challenge Complete! 🎉</h2>
            <p class="text-lg text-gray-700 mb-8 max-w-lg mx-auto">You have successfully solved all the puzzles. Your new sticker has been added to your dashboard.</p>

            @if($medalRequestPending)
                <div class="max-w-md mx-auto mb-6 p-4 bg-white rounded-xl border border-amber-200 shadow-sm">
                    <div class="flex items-start gap-3 text-left">
                        <div class="text-3xl">🏅</div>
                        <div>
                            <p class="font-bold text-stone-800">Claim your physical medal</p>
                            <p class="text-sm text-gray-600 mt-1">Confirm your shipping address and request your medal, or do it later from your dashboard.</p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="{{ route('medal-request', $enrollment) }}" class="btn btn-primary btn-lg gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        Request My Medal
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline">View Dashboard</a>
                </div>
            @else
                <div class="flex justify-center gap-4">
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">View Dashboard</a>
                    <a href="{{ route('challenges.index') }}" class="btn btn-secondary">Next Challenge</a>
                </div>
            @endif
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
                <div class="aspect-square relative rounded-lg shadow-2xl border-4 border-green-900/10 overflow-hidden bg-white transition-opacity duration-200">
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
                         
                        <div class="bg-white p-8 rounded-2xl shadow-xl border border-green-100 text-center transform max-w-sm w-full mx-4">
                            <div class="w-20 h-20 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-4 text-green-600 animate-bounce">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 mb-2 font-serif">Excellent!</h3>
                            <p class="text-gray-600 mb-6 font-medium">You solved this puzzle correctly.</p>
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
                        <div class="bg-white p-8 rounded-2xl shadow-xl border border-red-200 text-center max-w-sm w-full mx-4">
                            <div class="w-20 h-20 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4 text-red-600">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2 font-serif">Puzzle Error</h3>
                            <p class="text-gray-600 mb-4 font-medium text-sm" x-text="puzzleErrorMessage"></p>
                            <p class="text-xs text-gray-400">Please contact support with the puzzle ID.</p>
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
                         
                        <div class="bg-white p-8 rounded-2xl shadow-xl border border-red-100 text-center transform max-w-sm w-full mx-4">
                            <div class="w-20 h-20 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4 text-red-600">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 mb-2 font-serif">Incorrect Move</h3>
                            <p x-show="!lastMoveError" class="text-gray-600 mb-6 font-medium">That's not the correct follow-up in this position.</p>
                            <p x-show="lastMoveError" class="text-gray-600 mb-6 font-medium text-sm" x-text="lastMoveError"></p>
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
                <div class="card bg-base-100 shadow-xl border border-gray-100 h-full">
                    <div class="card-body">
                        <h3 class="card-title text-2xl font-serif text-gray-800 tracking-wide">Your Turn</h3>
                        
                        <div x-show="puzzleError" class="py-10 text-center">
                            <p class="text-red-600 font-semibold">Puzzle data error</p>
                            <p class="text-gray-500 text-sm mt-2" x-text="puzzleErrorMessage"></p>
                        </div>
                        
                        <div x-show="!ready && !puzzleError" class="flex flex-col items-center justify-center py-10 text-gray-400">
                            <svg class="w-8 h-8 animate-spin mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span>Loading puzzle...</span>
                        </div>
                        
                        <div x-show="ready && !puzzleError" x-cloak class="mt-4">
                            @if(!empty($currentPuzzleThemes))
                                <div class="flex flex-wrap gap-1.5 mb-4">
                                    @foreach($currentPuzzleThemes as $theme)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ ucfirst(str_replace(['-', '_'], ' ', $theme)) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <p class="text-gray-600 text-lg">
                                Find the best move for 
                                <span class="font-bold inline-block px-3 py-1 rounded shadow-sm"
                                      :class="playerColor === 'white' ? 'bg-amber-100 text-amber-900' : 'bg-gray-800 text-white'"
                                      x-text="playerColor === 'white' ? 'White' : 'Black'">
                                </span>.
                            </p>
                            
                            <template x-if="lastOpponentMove">
                                <div class="mt-8 p-4 bg-gray-50 rounded-lg border border-gray-100">
                                    <div class="flex items-center gap-3 text-gray-600">
                                        <span class="w-3 h-3 rounded-full bg-red-400 inline-block animate-pulse shadow-[0_0_8px_rgba(248,113,113,0.8)]"></span> 
                                        <span class="text-sm uppercase tracking-wider font-semibold">Opponent Played</span>
                                    </div>
                                    <div class="mt-2 font-mono text-2xl font-bold text-gray-800 pl-6" x-text="lastOpponentMove"></div>
                                </div>
                            </template>

                            <div class="mt-8 grid grid-cols-2 gap-3">
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
                                    class="btn btn-ghost border border-gray-200"
                                >
                                    Reset Puzzle
                                </button>
                            </div>

                            <div class="mt-4">
                                <button
                                    type="button"
                                    @click="showEngineHint()"
                                    class="w-full btn btn-info"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                                    Need a hint?
                                </button>

                                <p x-show="hintClicks > 0" x-cloak class="mt-2 text-xs text-center text-gray-500">
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
