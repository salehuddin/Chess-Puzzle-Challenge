<div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold font-serif text-green-900">My Dashboard</h1>
            <p class="text-neutral-600 mt-2 text-lg">Welcome back, {{ auth()->user()->name }}! Here's your chess journey.</p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-4">
            <a href="{{ route('challenges.index') }}" class="btn btn-primary">Browse Challenges</a>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div role="tablist" class="tabs tabs-bordered mb-8">
        <button role="tab" class="tab {{ $activeTab === 'challenges' ? 'tab-active font-semibold' : '' }}" wire:click="setTab('challenges')">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                My Challenges
                @if($activeCards->count() > 0)
                    <span class="badge badge-primary badge-sm">{{ $activeCards->count() }}</span>
                @endif
            </span>
        </button>
        <button role="tab" class="tab {{ $activeTab === 'collection' ? 'tab-active font-semibold' : '' }}" wire:click="setTab('collection')">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                Collection
                @if(count($earnedStickerChallengeIds) > 0)
                    <span class="badge badge-warning badge-sm">{{ count($earnedStickerChallengeIds) }}</span>
                @endif
            </span>
        </button>
        <button role="tab" class="tab {{ $activeTab === 'orders' ? 'tab-active font-semibold' : '' }}" wire:click="setTab('orders')">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                Orders
                @if($orders->count() > 0)
                    <span class="badge badge-sm">{{ $orders->count() }}</span>
                @endif
            </span>
        </button>
    </div>

    {{-- ==================== MY CHALLENGES TAB ==================== --}}
    @if($activeTab === 'challenges')
        <div wire:key="tab-challenges">
            @if($pendingMedalRequests->isNotEmpty())
                <div class="mb-8 rounded-2xl border border-orange-300 bg-gradient-to-r from-orange-50 to-orange-50 p-6 shadow-sm">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <div class="text-4xl">🏅</div>
                        <div class="flex-1">
                            <h2 class="font-display text-xl font-bold text-orange-900">Claim your physical medal{{ $pendingMedalRequests->count() > 1 ? 's' : '' }}!</h2>
                            <p class="text-sm text-orange-700 mt-1">
                                You've completed {{ $pendingMedalRequests->count() }} challenge{{ $pendingMedalRequests->count() > 1 ? 's' : '' }} but haven't requested your medal yet.
                                Confirm your shipping address and we'll mail it to you.
                            </p>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @foreach($pendingMedalRequests as $request)
                            <a href="{{ route('medal-request', $request->enrollment_id) }}" class="btn btn-primary btn-sm gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                Request medal for {{ $request->challenge->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($activeCards->isEmpty() && $completedCards->isEmpty() && $pendingCards->isEmpty())
                <div class="text-center py-20 bg-white rounded-3xl shadow-sm border border-neutral-100">
                    <div class="text-6xl mb-6">♟️</div>
                    <h2 class="text-2xl font-bold text-neutral-800 mb-2 font-serif">You haven't started any challenges yet.</h2>
                    <p class="text-neutral-500 mb-8 max-w-md mx-auto">Pick a challenge bundle, defeat the puzzles, and earn your physical stickers and medals!</p>
                    <a href="{{ route('challenges.index') }}" class="btn btn-primary btn-lg">View Available Challenges</a>
                </div>
            @endif

            @if($pendingCards->isNotEmpty())
                <h2 class="text-2xl font-bold font-serif text-neutral-800 mb-6 flex items-center gap-2 mt-4">
                    <span class="w-3 h-3 rounded-full bg-orange-500 animate-pulse"></span>
                    Awaiting Payment
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                    @foreach($pendingCards as $card)
                        <div class="card bg-orange-50 shadow-md border border-orange-100" wire:key="pending-{{ $card->id }}">
                            <div class="card-body">
                                <h3 class="card-title text-xl font-serif text-orange-900">{{ $card->challenge->name }}</h3>
                                <p class="text-sm text-neutral-600 mb-4">Enrollment created on {{ $card->created_at->format('M j, Y') }}</p>
                                <div class="badge badge-warning badge-outline mb-4">Pending payment</div>
                                <div class="card-actions justify-end mt-auto">
                                    <a href="{{ route('checkout.show', $card->order_id) }}" class="btn btn-primary btn-sm w-full">Complete Payment</a>
                                    <a href="{{ route('enrollments.show', $card->enrollment_id) }}" class="btn btn-outline btn-sm w-full">View Enrollment</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($activeCards->isNotEmpty())
                <h2 class="text-2xl font-bold font-serif text-neutral-800 mb-6 flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></span>
                    In Progress
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                    @foreach($activeCards as $card)
                        @php
                            $total = $card->challenge->puzzles_count ?? 0;
                            $completed = $card->solved_puzzles_count ?? 0;
                            $percent = $total > 0 ? round(($completed / $total) * 100) : 0;
                        @endphp
                        <div class="card bg-white shadow-xl border border-neutral-100 hover:shadow-2xl transition-all duration-300" wire:key="active-{{ $card->id }}">
                            <div class="card-body">
                                <h3 class="card-title text-xl font-serif text-green-900">{{ $card->challenge->name }}</h3>
                                <p class="text-sm text-neutral-500 mb-4">Purchased on {{ $card->created_at->format('M j, Y') }}</p>
                                
                                <div class="w-full bg-neutral-100 rounded-full h-2 mb-2">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                                <div class="flex justify-between text-sm text-neutral-600 font-medium mb-6">
                                    <span>{{ $percent }}% Complete</span>
                                    <span>{{ $completed }} / {{ $total }} Puzzles</span>
                                </div>
                                
                                <div class="card-actions justify-end mt-auto">
                                    <a href="{{ route('play', $card->enrollment_id) }}" class="btn btn-primary w-full text-lg">
                                        @if($completed > 0)
                                            Resume Playing
                                        @else
                                            Start Challenge
                                        @endif
                                    </a>
                                    <a href="{{ route('enrollments.show', $card->enrollment_id) }}" class="text-xs text-neutral-500 hover:text-brand transition-colors w-full text-center mt-1" wire:navigate>
                                        View details →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($completedCards->isNotEmpty())
                <h2 class="text-2xl font-bold font-serif text-neutral-800 mb-6 border-b pb-2">Completed & Shipped</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($completedCards as $card)
                        <div class="card bg-orange-50 shadow-md border border-orange-100 relative overflow-hidden" wire:key="completed-{{ $card->id }}">
                            <div class="absolute -right-10 -top-10 opacity-10">
                                <svg width="150" height="150" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            </div>
                            
                            <div class="card-body z-10">
                                <h3 class="card-title text-xl font-serif text-orange-900">{{ $card->challenge->name }}</h3>
                                
                                <div class="mt-2 text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-medium {{ $card->status === 'shipped' ? 'bg-primary/15 text-primary' : 'bg-accent/15 text-accent' }}">
                                        {{ ucfirst($card->status) }}
                                    </span>
                                </div>
                                
                                <p class="text-sm text-neutral-600 mt-4">Completed on {{ $card->completed_at ? $card->completed_at->format('M j, Y') : 'Unknown' }}</p>
                                
                                <div class="card-actions justify-end mt-6">
                                    @if($card->medal_request_pending)
                                        <a href="{{ route('medal-request', $card->enrollment_id) }}" class="btn btn-primary btn-sm w-full gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                            Request Medal
                                        </a>
                                    @elseif($card->status === 'shipped' && $card->tracking_url)
                                        <a href="{{ $card->tracking_url }}" target="_blank" class="btn btn-outline btn-primary btn-sm w-full">Track Package ({{ $card->courier }})</a>
                                    @else
                                        <a href="{{ route('enrollments.show', $card->enrollment_id) }}" class="btn btn-outline btn-sm w-full">View Order Details</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- ==================== COLLECTION TAB ==================== --}}
    @if($activeTab === 'collection')
        <div wire:key="tab-collection">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-extrabold font-serif text-orange-600 mb-3">🏆 Sticker Collection</h2>
                <p class="text-neutral-600 max-w-2xl mx-auto">Your collection of earned stickers. Complete challenges to unlock the missing silhouettes!</p>
                <p class="text-sm text-neutral-400 mt-2">{{ count($earnedStickerChallengeIds) }} / {{ $collectionChallenges->count() }} unlocked</p>
            </div>

            @if($collectionChallenges->isEmpty())
                <div class="text-center py-16 bg-white rounded-3xl shadow-sm border border-neutral-100">
                    <div class="text-5xl mb-4">🎨</div>
                    <p class="text-neutral-500">No challenges available yet. Check back soon!</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-8">
                    @foreach($collectionChallenges as $challenge)
                        @php
                            $isEarned = in_array($challenge->id, $earnedStickerChallengeIds);
                        @endphp
                        
                        <div class="flex flex-col items-center justify-center p-6 bg-white rounded-3xl shadow-lg border border-neutral-100 transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl relative overflow-hidden group" wire:key="sticker-{{ $challenge->id }}">
                            @if($isEarned)
                                <div class="absolute inset-0 bg-orange-50 opacity-20 pointer-events-none"></div>
                                <div class="absolute -top-4 -right-4 w-16 h-16 bg-orange-400 rounded-full opacity-10 filter blur-xl"></div>
                                
                                <div class="relative w-32 h-32 mb-4 drop-shadow-[0_10px_15px_rgba(217,119,6,0.3)] scale-110 transition-transform duration-500 group-hover:scale-125 group-hover:rotate-6">
                                    @if($challenge->sticker_artwork)
                                        <img src="{{ Storage::url($challenge->sticker_artwork) }}" alt="{{ $challenge->name }} Sticker" class="w-full h-full object-contain" />
                                    @else
                                        <div class="w-full h-full rounded-full bg-gradient-to-br from-orange-300 to-orange-600 flex items-center justify-center text-white p-4 text-center text-sm font-bold shadow-inner">
                                            {{ $challenge->name }}
                                        </div>
                                    @endif
                                    
                                    <div class="absolute -top-1 -right-1 text-2xl animate-pulse delay-150">✨</div>
                                </div>
                                
                                <h3 class="text-center font-bold text-neutral-800 text-lg font-serif z-10">{{ $challenge->name }}</h3>
                                <p class="text-orange-600 text-xs font-bold uppercase tracking-wider mt-1 z-10">Unlocked</p>
                            @else
                                <div class="relative w-32 h-32 mb-4 opacity-30 grayscale saturate-0 contrast-200 transition-all duration-300 group-hover:opacity-60">
                                    @if($challenge->sticker_artwork)
                                        <img src="{{ Storage::url($challenge->sticker_artwork) }}" alt="Locked" class="w-full h-full object-contain mix-blend-multiply brightness-0" />
                                    @else
                                        <div class="w-full h-full rounded-full bg-neutral-800 flex items-center justify-center text-neutral-500 text-4xl shadow-inner border-4 border-neutral-300">
                                            ?
                                        </div>
                                    @endif
                                </div>
                                
                                <h3 class="text-center font-bold text-neutral-400 text-lg font-serif">{{ $challenge->name }}</h3>
                                <p class="text-neutral-400 text-xs font-medium uppercase tracking-wider mt-1">Locked</p>
                                
                                <a href="{{ route('challenges.show', $challenge->slug) }}" class="absolute inset-0 z-20 flex items-center justify-center bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 backdrop-blur-sm rounded-3xl">
                                    <span class="btn btn-primary btn-sm">View Challenge</span>
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- ==================== ORDERS TAB ==================== --}}
    @if($activeTab === 'orders')
        <div wire:key="tab-orders">
            <h2 class="text-2xl font-bold font-serif text-neutral-800 mb-6">Order History</h2>

            @if($orders->isEmpty())
                <div class="text-center py-16 bg-white rounded-3xl shadow-sm border border-neutral-100">
                    <div class="text-5xl mb-4">🛒</div>
                    <p class="text-neutral-500 mb-6">You haven't made any purchases yet.</p>
                    <a href="{{ route('challenges.index') }}" class="btn btn-primary">Browse Challenges</a>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($orders as $order)
                        @php
                            $statusBadge = match($order->status) {
                                'paid' => ['Paid', 'bg-green-100 text-green-800 border-green-200'],
                                'pending' => ['Pending', 'bg-orange-100 text-orange-800 border-orange-200'],
                                'failed' => ['Failed', 'bg-red-100 text-red-800 border-red-200'],
                                'refunded' => ['Refunded', 'bg-neutral-100 text-neutral-800 border-neutral-200'],
                                default => [ucfirst($order->status), 'bg-neutral-100 text-neutral-800 border-neutral-200'],
                            };
                        @endphp
                        <div class="bg-white rounded-2xl shadow-md border border-neutral-100 overflow-hidden" wire:key="order-{{ $order->id }}">
                            <div class="px-6 py-4 border-b border-neutral-100 bg-neutral-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <p class="font-bold text-neutral-800">Order #{{ $order->id }}</p>
                                    <p class="text-sm text-neutral-500">{{ $order->created_at->format('M j, Y \a\t g:i A') }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $statusBadge[1] }}">
                                        {{ $statusBadge[0] }}
                                    </span>
                                    <span class="font-bold text-lg text-neutral-900">{{ strtoupper($order->currency) }} {{ number_format((float) $order->total_amount, 2) }}</span>
                                </div>
                            </div>

                            <div class="p-6">
                                <div class="space-y-3">
                                    @foreach($order->items as $item)
                                        <div class="flex items-center justify-between py-2 border-b border-neutral-50 last:border-0">
                                            <div class="flex-1">
                                                <p class="font-medium text-neutral-800">{{ $item->name_snapshot }}</p>
                                                <p class="text-xs text-neutral-400">{{ $item->sku_snapshot }} &middot; Qty {{ $item->quantity }}</p>
                                                @foreach($item->enrollments as $enrollment)
                                                    <a href="{{ route('enrollments.show', $enrollment->id) }}" class="text-xs text-primary hover:underline mt-1 inline-flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                                        View enrollment ({{ $enrollment->status }})
                                                    </a>
                                                @endforeach
                                            </div>
                                            <div class="text-right">
                                                <p class="font-medium text-neutral-700">{{ strtoupper($order->currency) }} {{ number_format((float) $item->line_total, 2) }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if($order->status === 'pending')
                                    <div class="mt-4 pt-4 border-t border-neutral-100">
                                        <a href="{{ route('checkout.show', $order) }}" class="btn btn-primary btn-sm gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                            Complete Payment
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
