<div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-neutral-900 font-serif">Order Details</h1>
            <p class="text-neutral-500 mt-1">Order #{{ $tracking['order_id'] }} - {{ $tracking['challenge_name'] }}</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline">&larr; Back</a>
    </div>

    <div class="bg-white rounded-3xl shadow-xl border border-neutral-100 overflow-hidden mb-8">
        
        <div class="bg-neutral-50 p-6 border-b border-neutral-100">
            <h2 class="text-lg font-bold text-neutral-800">Tracking Timeline</h2>
        </div>
        
        <div class="p-8">
            <ul class="steps steps-vertical lg:steps-horizontal w-full">
                <!-- Step 1: Enrollment / Paid -->
                <li class="step step-primary" data-content="{{ $tracking['status'] === 'pending' ? '✉' : '✓' }}">
                    <div class="text-left lg:text-center mt-2">
                        <p class="font-bold">{{ $tracking['status'] === 'pending' ? 'Enrollment' : 'Purchased' }}</p>
                        <p class="text-xs text-neutral-500">{{ $tracking['created_at']?->format('M j, Y') }}</p>
                    </div>
                </li>
                
                <!-- Step 2: Playing -->
                <li class="step {{ in_array($tracking['status'], ['in_progress', 'completed', 'shipped']) ? 'step-primary' : '' }}" data-content="♟">
                    <div class="text-left lg:text-center mt-2">
                        <p class="font-bold">In Progress</p>
                    </div>
                </li>
                
                <!-- Step 3: Completed (Ready to ship) -->
                <li class="step {{ in_array($tracking['status'], ['completed', 'shipped', 'medal_pending']) ? 'step-primary' : '' }}" data-content="📦">
                    <div class="text-left lg:text-center mt-2">
                        <p class="font-bold">{{ $tracking['status'] === 'medal_pending' ? 'Medal Pending' : 'Completed & Preparing' }}</p>
                        @if($tracking['completed_at'])
                            <p class="text-xs text-neutral-500">{{ $tracking['completed_at']->format('M j, Y') }}</p>
                        @endif
                    </div>
                </li>
                
                <!-- Step 4: Shipped -->
                <li class="step {{ $tracking['status'] === 'shipped' ? 'step-primary' : '' }}" data-content="🚚">
                    <div class="text-left lg:text-center mt-2">
                        <p class="font-bold">Shipped</p>
                        @if($tracking['shipped_at'])
                            <p class="text-xs text-neutral-500">{{ $tracking['shipped_at']->format('M j, Y') }}</p>
                        @endif
                    </div>
                </li>
            </ul>
        </div>
        
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Shipping Logistics -->
        <div class="card bg-white shadow-md border border-neutral-100">
            <div class="card-body">
                <h3 class="card-title text-neutral-800 border-b pb-2">Logistics Info</h3>
                
                    @if($tracking['status'] === 'pending')
                        <div class="mt-4 text-center py-6">
                            <div class="text-4xl mb-4 text-orange-500">⏳</div>
                            <h4 class="font-bold text-neutral-800">Awaiting payment</h4>
                            <p class="text-neutral-500 text-sm mt-2">Your enrollment has been created. Complete payment to unlock the puzzles and start playing.</p>
                        </div>
                    @elseif($tracking['status'] === 'medal_pending')
                        <div class="mt-4 text-center py-6">
                            <div class="text-4xl mb-4 text-orange-500">🏅</div>
                            <h4 class="font-bold text-neutral-800">Claim your medal</h4>
                            <p class="text-neutral-500 text-sm mt-2">You've completed all puzzles! Confirm your shipping address and request your physical medal to be mailed to you.</p>
                            <a href="{{ route('medal-request', $tracking['enrollment_id']) }}" class="btn btn-primary btn-sm mt-4 gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                Request My Medal
                            </a>
                        </div>
                    @elseif($tracking['status'] === 'shipped')
                    <div class="mt-4 space-y-4">
                        <div>
                            <p class="text-sm text-neutral-500">Courier</p>
                            <p class="font-bold text-lg text-neutral-800">{{ $tracking['courier'] ?: 'Standard Mail' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-neutral-500">Tracking Number</p>
                            <p class="font-mono text-lg bg-neutral-100 px-3 py-1 rounded inline-block">{{ $tracking['tracking_number'] ?: 'Untracked' }}</p>
                        </div>
                        
                        @if($tracking['tracking_url'])
                            <div class="mt-6 pt-4 border-t">
                                <a href="{{ $tracking['tracking_url'] }}" target="_blank" class="btn btn-primary w-full shadow-lg hover:shadow-xl transition-all">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    Track on Courier Website
                                </a>
                            </div>
                        @endif
                    </div>
                    @elseif($tracking['status'] === 'completed')
                    <div class="mt-4 text-center py-6">
                        <div class="text-4xl mb-4 text-orange-500">📦</div>
                        <h4 class="font-bold text-neutral-800">Preparing for Shipment</h4>
                        <p class="text-neutral-500 text-sm mt-2">You've completed the challenge! We are preparing your physical sticker/medal for shipment. Check back soon for tracking information.</p>
                    </div>
                @else
                    <div class="mt-4 text-center py-6 opacity-60">
                        <div class="text-4xl mb-4 text-neutral-400">♟</div>
                        <h4 class="font-bold text-neutral-600">Finish playing first!</h4>
                        <p class="text-neutral-500 text-sm mt-2">Complete all puzzles in this challenge to trigger the shipment of your physical rewards.</p>
                        <a href="{{ route('play', $tracking['enrollment_id']) }}" class="btn btn-outline btn-sm mt-4">Resume Playing</a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Shipping Address Snapshot -->
        <div class="card bg-white shadow-md border border-neutral-100">
            <div class="card-body">
                <h3 class="card-title text-neutral-800 border-b pb-2">Shipping Address</h3>
                
                @if($tracking['address_snapshot'])
                    <div class="mt-4 space-y-1 text-neutral-700">
                        <p class="font-bold">{{ auth()->user()->name }}</p>
                        <p>{{ $tracking['address_snapshot']['address_line1'] ?? '' }}</p>
                        @if(!empty($tracking['address_snapshot']['address_line2']))
                            <p>{{ $tracking['address_snapshot']['address_line2'] }}</p>
                        @endif
                        <p>{{ $tracking['address_snapshot']['city'] ?? '' }}, {{ $tracking['address_snapshot']['state'] ?? '' }} {{ $tracking['address_snapshot']['postcode'] ?? '' }}</p>
                        <p class="font-bold mt-2">{{ $tracking['address_snapshot']['country'] ?? '' }}</p>
                    </div>
                    
                    <div class="mt-4 text-xs text-orange-700 bg-orange-50 p-3 rounded border border-orange-200">
                        📍 This address was recorded at the time of medal request and cannot be changed here. Contact support if you need to update it.
                    </div>
                @elseif($tracking['status'] === 'medal_pending')
                    <div class="mt-4 text-center py-6">
                        <div class="text-3xl mb-3">📮</div>
                        <p class="text-neutral-500 text-sm">No shipping address confirmed yet. Request your medal to lock in your delivery address.</p>
                        <a href="{{ route('medal-request', $tracking['enrollment_id']) }}" class="btn btn-primary btn-sm mt-4 gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            Request Medal
                        </a>
                    </div>
                @else
                    <div class="mt-4 text-center py-6">
                        <p class="text-neutral-500 text-sm">We will use your account's default shipping address when you complete this challenge.</p>
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline btn-sm mt-4">Update Default Address</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
