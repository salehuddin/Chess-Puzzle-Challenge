<div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold font-serif text-green-900">My Dashboard</h1>
            <p class="text-gray-600 mt-2 text-lg">Welcome back, {{ auth()->user()->name }}! Here's your chess journey.</p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-4">
            <a href="{{ route('hall-of-fame') }}" class="btn bg-amber-500 hover:bg-amber-600 text-white border-transparent">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                Hall of Fame
            </a>
            <a href="{{ route('challenges.index') }}" class="btn btn-primary">Browse Challenges</a>
        </div>
    </div>

    @if($activeCards->isEmpty() && $completedCards->isEmpty())
        <div class="text-center py-20 bg-white rounded-3xl shadow-sm border border-gray-100">
            <div class="text-6xl mb-6">♟️</div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2 font-serif">You haven't started any challenges yet.</h2>
            <p class="text-gray-500 mb-8 max-w-md mx-auto">Pick a challenge bundle, defeat the puzzles, and earn your physical stickers and medals!</p>
            <a href="{{ route('challenges.index') }}" class="btn btn-primary btn-lg">View Available Challenges</a>
        </div>
    @endif

    @if($pendingCards->isNotEmpty())
        <h2 class="text-2xl font-bold font-serif text-gray-800 mb-6 flex items-center gap-2 mt-12">
            <span class="w-3 h-3 rounded-full bg-amber-500 animate-pulse"></span>
            Awaiting Payment
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            @foreach($pendingCards as $card)
                <div class="card bg-amber-50 shadow-md border border-amber-100">
                    <div class="card-body">
                        <h3 class="card-title text-xl font-serif text-amber-900">{{ $card->challenge->name }}</h3>
                        <p class="text-sm text-gray-600 mb-4">Enrollment created on {{ $card->created_at->format('M j, Y') }}</p>
                        <div class="badge badge-warning badge-outline mb-4">Pending payment</div>
                        <div class="card-actions justify-end mt-auto">
                            <a href="{{ route('checkout.show', $card->order_id) }}" class="btn btn-primary btn-sm w-full">Complete Payment</a>
                            <a href="{{ route('orders.track', $card->enrollment_id) }}" class="btn btn-outline btn-sm w-full">View Enrollment</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($activeCards->isNotEmpty())
        <h2 class="text-2xl font-bold font-serif text-gray-800 mb-6 flex items-center gap-2">
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
                <div class="card bg-white shadow-xl border border-gray-100 hover:shadow-2xl transition-all duration-300">
                    <div class="card-body">
                        <h3 class="card-title text-xl font-serif text-green-900">{{ $card->challenge->name }}</h3>
                        <p class="text-sm text-gray-500 mb-4">Purchased on {{ $card->created_at->format('M j, Y') }}</p>
                        
                        <div class="w-full bg-gray-100 rounded-full h-2 mb-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600 font-medium mb-6">
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
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($completedCards->isNotEmpty())
        <h2 class="text-2xl font-bold font-serif text-gray-800 mb-6 border-b pb-2">Completed & Shipped</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($completedCards as $card)
                <div class="card bg-amber-50 shadow-md border border-amber-100 relative overflow-hidden">
                    <div class="absolute -right-10 -top-10 opacity-10">
                        <svg width="150" height="150" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </div>
                    
                    <div class="card-body z-10">
                        <h3 class="card-title text-xl font-serif text-amber-900">{{ $card->challenge->name }}</h3>
                        
                        <div class="mt-2 text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-medium {{ $card->status === 'shipped' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ ucfirst($card->status) }}
                            </span>
                        </div>
                        
                        <p class="text-sm text-gray-600 mt-4">Completed on {{ $card->completed_at ? $card->completed_at->format('M j, Y') : 'Unknown' }}</p>
                        
                        <div class="card-actions justify-end mt-6">
                            @if($card->status === 'shipped' && $card->tracking_url)
                                <a href="{{ $card->tracking_url }}" target="_blank" class="btn btn-outline btn-primary btn-sm w-full">Track Package ({{ $card->courier }})</a>
                            @else
                                <a href="{{ route('orders.track', $card->enrollment_id) }}" class="btn btn-outline btn-sm w-full">View Order Details</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
