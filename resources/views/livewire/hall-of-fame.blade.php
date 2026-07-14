<div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
        <h1 class="text-5xl font-extrabold font-serif text-orange-600 drop-shadow-sm mb-4">🏆 Hall of Fame</h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">Your collection of earned stickers and physical medals. Complete challenges to unlock the missing silhouettes!</p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-8">
        @foreach($challenges as $challenge)
            @php
                $isEarned = in_array($challenge->id, $earnedStickerChallengeIds);
            @endphp
            
            <div class="flex flex-col items-center justify-center p-6 bg-white rounded-3xl shadow-lg border border-gray-100 transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl relative overflow-hidden group">
                
                @if($isEarned)
                    <!-- Confetti background subtle -->
                    <div class="absolute inset-0 bg-orange-50 opacity-20 pointer-events-none"></div>
                    <div class="absolute -top-4 -right-4 w-16 h-16 bg-orange-400 rounded-full opacity-10 filter blur-xl"></div>
                    
                    <div class="relative w-32 h-32 mb-4 drop-shadow-[0_10px_15px_rgba(217,119,6,0.3)] scale-110 transition-transform duration-500 group-hover:scale-125 group-hover:rotate-6">
                        @if($challenge->sticker_artwork)
                            <img src="{{ Storage::url($challenge->sticker_artwork) }}" alt="{{ $challenge->name }} Sticker" class="w-full h-full object-contain" />
                        @else
                            <!-- Placeholder if no image -->
                            <div class="w-full h-full rounded-full bg-gradient-to-br from-orange-300 to-orange-600 flex items-center justify-center text-white p-4 text-center text-sm font-bold shadow-inner">
                                {{ $challenge->name }}
                            </div>
                        @endif
                        
                        <!-- Sparkle marker -->
                        <div class="absolute -top-1 -right-1 text-2xl animate-pulse delay-150">✨</div>
                    </div>
                    
                    <h3 class="text-center font-bold text-gray-800 text-lg font-serif z-10">{{ $challenge->name }}</h3>
                    <p class="text-orange-600 text-xs font-bold uppercase tracking-wider mt-1 z-10">Unlocked</p>
                @else
                    <div class="relative w-32 h-32 mb-4 opacity-30 grayscale saturate-0 contrast-200 transition-all duration-300 group-hover:opacity-60">
                        @if($challenge->sticker_artwork)
                            <img src="{{ Storage::url($challenge->sticker_artwork) }}" alt="Locked" class="w-full h-full object-contain mix-blend-multiply brightness-0" />
                        @else
                            <div class="w-full h-full rounded-full bg-gray-800 flex items-center justify-center text-gray-500 text-4xl shadow-inner border-4 border-gray-300">
                                ?
                            </div>
                        @endif
                    </div>
                    
                    <h3 class="text-center font-bold text-gray-400 text-lg font-serif">{{ $challenge->name }}</h3>
                    <p class="text-gray-400 text-xs font-medium uppercase tracking-wider mt-1">Locked</p>
                    
                    <a href="{{ route('challenges.show', $challenge->slug) }}" class="absolute inset-0 z-20 flex items-center justify-center bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 backdrop-blur-sm rounded-3xl">
                        <span class="btn btn-primary btn-sm">View Challenge</span>
                    </a>
                @endif
                
            </div>
        @endforeach
    </div>
</div>
