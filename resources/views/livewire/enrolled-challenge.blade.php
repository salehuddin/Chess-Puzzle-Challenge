<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">

    @php
        // Level badge data — same logic as puzzle-player.blade.php:151-156
        $levelData = match (true) {
            str_contains(strtolower($challenge->name), 'beginner')     => ['🌱', 'Beginner', 'badge-success'],
            str_contains(strtolower($challenge->name), 'intermediate') => ['⚡', 'Intermediate', 'badge-warning'],
            str_contains(strtolower($challenge->name), 'advanced')     => ['🔥', 'Advanced', 'badge-error'],
            default                                                    => ['♟', 'Challenge', 'badge-primary'],
        };

        // Status pill data — chartreuse = success/positive, grey = neutral/transit, black = terminal
        $statusPill = match ($derivedStatus) {
            'pending'       => ['Awaiting Payment', 'bg-base-200 text-neutral-700 border-neutral-300'],
            'active'        => ['In Progress',      'bg-base-100 text-neutral-800 border-neutral-200'],
            'completed'     => ['Completed',        'bg-brand text-neutral-900 border-transparent'],
            'medal_pending' => ['Medal Pending',     'bg-brand/30 text-neutral-900 border-brand/40'],
            'preparing'     => ['Preparing',        'bg-base-200 text-neutral-700 border-neutral-300'],
            'shipped'       => ['Shipped',          'bg-neutral-900 text-white border-transparent'],
            default         => [ucfirst($derivedStatus), 'bg-base-200 text-neutral-800 border-neutral-200'],
        };

        // CTA button (label + href + style)
        $primaryCta = match ($derivedStatus) {
            'active'        => ['Resume Playing',  route('play', $enrollment), 'btn btn-primary'],
            'pending'       => ['Complete Payment', route('checkout.show', $enrollment->orderItem?->order_id ?? 0), 'btn btn-primary'],
            'medal_pending' => ['Claim Your Medal', route('medal-request', $enrollment), 'btn btn-primary'],
            'preparing'     => ['Preparing for Shipment', null, 'btn btn-disabled'],
            'shipped'       => ['Track Package', $fulfillment?->tracking_url ?: '#', 'btn btn-outline'],
            default         => [null, null, 'btn'],
        };

        $stickerSrc = $challenge->sticker_artwork
            ? \Illuminate\Support\Facades\Storage::url($challenge->sticker_artwork)
            : ($challenge->poster_image
                ? \Illuminate\Support\Facades\Storage::url($challenge->poster_image)
                : null);

        $percent = $totalPuzzles > 0 ? (int) round(($completedPuzzles / $totalPuzzles) * 100) : 0;

        $showProgress      = in_array($derivedStatus, ['active', 'completed', 'medal_pending', 'preparing', 'shipped'], true);
        $showSticker       = true;  // locked silhouette if not yet earned
        $showMedalPipeline = true;  // stepper shows current step
        $showReview        = in_array($derivedStatus, ['completed', 'medal_pending', 'preparing', 'shipped'], true);
        $showHistory       = in_array($derivedStatus, ['active', 'completed', 'medal_pending', 'preparing', 'shipped'], true);
    @endphp

    {{-- =============================================================== --}}
    {{-- breadcrumb --}}
    {{-- =============================================================== --}}
    <nav class="mb-4 text-xs text-neutral-500">
        <a href="{{ route('dashboard') }}" class="hover:text-neutral-900 transition-colors">Dashboard</a>
        <span class="mx-1.5 text-neutral-400">/</span>
        <span class="text-neutral-700">{{ $challenge->name }}</span>
    </nav>

    {{-- =============================================================== --}}
    {{-- 1. Hero header: sticker + name + level badge + status pill --}}
    {{-- =============================================================== --}}
    <section class="mb-6 bg-white rounded-2xl border border-neutral-200 shadow-warm overflow-hidden">
        <div class="flex items-start sm:items-center gap-4 p-5 sm:p-6">
            <div class="flex-shrink-0 w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden border border-neutral-900/10 bg-brand flex items-center justify-center">
                @if($stickerSrc)
                    <img src="{{ $stickerSrc }}" alt="{{ $challenge->name }} sticker" class="w-full h-full object-cover" />
                @else
                    <span class="text-3xl text-neutral-900">♟</span>
                @endif
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                    <span class="badge {{ $levelData[2] }} badge-sm gap-1">
                        <span>{{ $levelData[0] }}</span> {{ $levelData[1] }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $statusPill[1] }}">
                        {{ $statusPill[0] }}
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-display font-black text-neutral-900 leading-tight truncate">
                    {{ $challenge->name }}
                </h1>
                <a href="{{ route('challenges.show', $challenge) }}"
                   class="inline-flex items-center gap-1 mt-1 text-xs sm:text-sm text-neutral-500 hover:text-brand transition-colors"
                   wire:navigate>
                    View challenge page
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- 2. Primary CTA banner (state-driven) --}}
        @if($primaryCta[0])
            <div class="border-t border-neutral-200 bg-base-200/50 px-5 sm:px-6 py-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div>
                        @if($derivedStatus === 'active')
                            <p class="text-sm font-semibold text-neutral-900">Pick up where you left off</p>
                            <p class="text-xs text-neutral-500 mt-0.5">{{ $completedPuzzles }} of {{ $totalPuzzles }} puzzles solved</p>
                        @elseif($derivedStatus === 'pending')
                            <p class="text-sm font-semibold text-neutral-900">Your spot is reserved</p>
                            <p class="text-xs text-neutral-500 mt-0.5">Complete payment to start playing</p>
                        @elseif($derivedStatus === 'medal_pending')
                            <p class="text-sm font-semibold text-neutral-900">You defeated the challenge 🏆</p>
                            <p class="text-xs text-neutral-500 mt-0.5">Claim your physical medal</p>
                        @elseif($derivedStatus === 'preparing')
                            <p class="text-sm font-semibold text-neutral-900">Medal in preparation</p>
                            <p class="text-xs text-neutral-500 mt-0.5">Our team is getting it ready to ship</p>
                        @elseif($derivedStatus === 'shipped')
                            <p class="text-sm font-semibold text-neutral-900">On its way!</p>
                            <p class="text-xs text-neutral-500 mt-0.5">Track via courier: {{ $fulfillment?->courier ?? '—' }}</p>
                        @endif
                    </div>
                    @if($primaryCta[1])
                        <a href="{{ $primaryCta[1] }}"
                           class="{{ $primaryCta[2] }} gap-2 flex-shrink-0"
                           @if($derivedStatus === 'shipped') target="_blank" rel="noopener noreferrer" @endif
                           wire:navigate>
                            {{-- icon --}}
                            @if($derivedStatus === 'active')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @elseif($derivedStatus === 'pending')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            @elseif($derivedStatus === 'medal_pending')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            @elseif($derivedStatus === 'shipped')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 4H4v11h12V8M14 13h4l3 3v3h-2"/></svg>
                            @endif
                            {{ $primaryCta[0] }}
                        </a>
                    @else
                        <span class="{{ $primaryCta[2] }} gap-2 flex-shrink-0 cursor-default">{{ $primaryCta[0] }}</span>
                    @endif
                </div>
            </div>
        @endif
    </section>

    {{-- =============================================================== --}}
    {{-- 3. Progress card (verbatim reuse of play-page patterns) --}}
    {{-- =============================================================== --}}
    @if($showProgress && !empty($orderedPuzzleIds))
        <section class="mb-6 bg-white rounded-2xl border border-neutral-200 shadow-warm p-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold uppercase tracking-[0.2em] text-neutral-500">Progress</h2>
                <p class="text-xs text-neutral-500">{{ $completedPuzzles }} / {{ $totalPuzzles }} solved · {{ $percent }}%</p>
            </div>

            {{-- Linear progress bar (dashboard.blade.php:119-125 pattern, recoloured) --}}
            <div class="w-full bg-base-200 rounded-full h-2 mb-4 overflow-hidden">
                <div class="bg-brand h-full rounded-full transition-all duration-500 ease-out" style="width: {{ $percent }}%"></div>
            </div>

            {{-- GitHub-style grid (verbatim from play page, recoloured to brand palette) --}}
            <div class="grid grid-cols-[repeat(15,minmax(0,1fr))] sm:grid-cols-[repeat(20,minmax(0,1fr))] lg:grid-cols-[repeat(25,minmax(0,1fr))] gap-1 mb-3">
                @foreach($orderedPuzzleIds as $index => $puzzleId)
                    @php
                        $solved = in_array($puzzleId, $solvedPuzzleIds);
                        $isComplete = $derivedStatus !== 'active';
                    @endphp
                    <div
                        class="h-3.5 rounded-sm border {{ $solved ? 'bg-brand border-transparent' : 'bg-base-200 border-neutral-200' }}"
                        title="Puzzle {{ $index + 1 }}{{ $solved ? ' (solved)' : '' }}"
                    ></div>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center gap-3 text-[11px] text-neutral-600">
                <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-sm bg-brand"></span><span>Solved</span></span>
                <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-sm bg-base-200 border border-neutral-200"></span><span>Remaining</span></span>
            </div>
        </section>
    @endif

    {{-- =============================================================== --}}
    {{-- 4. Sticker card — earned when completed, locked silhouette otherwise --}}
    {{-- =============================================================== --}}
    @if($showSticker)
        @php
            $isStickerEarned = $sticker && $sticker->unlocked_at !== null;
            $stickerHeading = $isStickerEarned ? 'Sticker Earned' : 'Sticker to Earn';
        @endphp
        <section class="mb-6 bg-white rounded-2xl border border-neutral-200 shadow-warm p-5 sm:p-6">
            <h2 class="text-sm font-bold uppercase tracking-[0.2em] text-neutral-500 mb-4">{{ $stickerHeading }}</h2>
            <div class="flex items-center gap-5">
                <div class="relative w-28 h-28 sm:w-32 sm:h-32 flex-shrink-0 @if($isStickerEarned) drop-shadow-[0_10px_15px_rgba(183,255,0,0.25)] @else opacity-40 grayscale saturate-0 @endif">
                    @if($stickerSrc)
                        <img src="{{ $stickerSrc }}" alt="{{ $challenge->name }} sticker" class="w-full h-full object-contain" />
                    @else
                        <div class="w-full h-full rounded-full bg-brand flex items-center justify-center text-neutral-900 p-4 text-center text-sm font-bold shadow-inner">
                            {{ $challenge->name }}
                        </div>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="font-display font-black text-neutral-900 text-lg">{{ $challenge->name }}</p>
                    @if($isStickerEarned)
                        <p class="text-xs text-neutral-500 mt-1">
                            Unlocked {{ $sticker->unlocked_at?->format('M j, Y') }}
                        </p>
                        <a href="{{ route('hall-of-fame') }}"
                           class="inline-flex items-center gap-1 mt-3 text-xs sm:text-sm text-neutral-500 hover:text-brand transition-colors"
                           wire:navigate>
                            View in Hall of Fame
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    @else
                        <p class="text-xs text-neutral-500 mt-1">
                            Solve all {{ $totalPuzzles }} puzzles to unlock this sticker
                        </p>
                        @if($totalPuzzles > 0)
                            <p class="text-xs text-neutral-400 mt-0.5">
                                {{ $completedPuzzles }} / {{ $totalPuzzles }} solved
                            </p>
                        @endif
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- =============================================================== --}}
    {{-- 5. Medal pipeline card (stepper + address + tracking) --}}
    {{-- =============================================================== --}}
    @if($showMedalPipeline)
        <section class="mb-6 bg-white rounded-2xl border border-neutral-200 shadow-warm p-5 sm:p-6">
            <h2 class="text-sm font-bold uppercase tracking-[0.2em] text-neutral-500 mb-5">Medal Pipeline</h2>

            @php
                $fulfillmentStatus = $fulfillment?->status ?? 'pending';
                // Pending payment — step 1 not even complete yet
                $isPurchased = $derivedStatus !== 'pending';
                $isCompleted = in_array($derivedStatus, ['completed', 'medal_pending', 'preparing', 'shipped'], true);
                $isPreparingOrFurther = in_array($derivedStatus, ['preparing', 'shipped'], true);
                $isShipped = $derivedStatus === 'shipped';
            @endphp

            {{-- DaisyUI stepper — 4 stages --}}
            <ul class="steps steps-vertical sm:steps-horizontal w-full mb-6">
                <li class="step {{ $isPurchased ? 'step-primary' : '' }}" data-content="{{ $isPurchased ? '✓' : '⏳' }}">
                    Purchased
                </li>
                <li class="step {{ $isCompleted ? 'step-primary' : '' }}" data-content="♟">
                    {{ $derivedStatus === 'active' ? 'In Progress' : 'Challenge Completed' }}
                </li>
                <li class="step {{ $isPreparingOrFurther ? 'step-primary' : '' }}" data-content="{{ $isPreparingOrFurther ? '✓' : '📦' }}">
                    Preparing
                </li>
                <li class="step {{ $isShipped ? 'step-primary' : '' }}" data-content="{{ $isShipped ? '✓' : '🚚' }}">
                    Shipped{{ $fulfillment?->delivered_at ? ' & Delivered' : '' }}
                </li>
            </ul>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Logistics column --}}
                <div class="border border-neutral-200 rounded-xl p-4">
                    <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-neutral-500 mb-2 border-b pb-2">Status</h3>

                    @if($derivedStatus === 'pending')
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">⏳</span>
                            <div>
                                <p class="font-semibold text-neutral-900">Awaiting Payment</p>
                                <p class="text-xs text-neutral-500 mt-1">Complete payment to unlock the puzzles and start your challenge.</p>
                                @if($enrollment->orderItem?->order_id)
                                    <a href="{{ route('checkout.show', $enrollment->orderItem->order_id) }}" class="btn btn-primary btn-sm mt-3 gap-2" wire:navigate>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        Complete Payment
                                    </a>
                                @endif
                            </div>
                        </div>
                    @elseif($derivedStatus === 'active')
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">♟</span>
                            <div>
                                <p class="font-semibold text-neutral-900">In Progress</p>
                                <p class="text-xs text-neutral-500 mt-1">
                                    Solve {{ $totalPuzzles - $completedPuzzles }} more puzzle{{ ($totalPuzzles - $completedPuzzles) === 1 ? '' : 's' }} to complete the challenge and unlock your medal claim.
                                </p>
                                <a href="{{ route('play', $enrollment) }}" class="btn btn-primary btn-sm mt-3 gap-2" wire:navigate>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Resume Playing
                                </a>
                            </div>
                        </div>
                    @elseif($derivedStatus === 'completed')
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">🏆</span>
                            <div>
                                <p class="font-semibold text-neutral-900">Challenge Complete</p>
                                <p class="text-xs text-neutral-500 mt-1">Your medal claim is ready.</p>
                                <a href="{{ route('medal-request', $enrollment) }}" class="btn btn-primary btn-sm mt-3 gap-2" wire:navigate>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                    Claim Medal
                                </a>
                            </div>
                        </div>
                    @elseif($derivedStatus === 'medal_pending')
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">🏅</span>
                            <div>
                                <p class="font-semibold text-neutral-900">Awaiting Medal Claim</p>
                                <p class="text-xs text-neutral-500 mt-1">Confirm your shipping address to start fulfillment.</p>
                                <a href="{{ route('medal-request', $enrollment) }}" class="btn btn-primary btn-sm mt-3 gap-2" wire:navigate>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                    Claim Medal
                                </a>
                            </div>
                        </div>
                    @elseif($derivedStatus === 'preparing')
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">📦</span>
                            <div>
                                <p class="font-semibold text-neutral-900">Preparing for Shipment</p>
                                <p class="text-xs text-neutral-500 mt-1">Our team is packaging your medal. You'll get tracking info soon.</p>
                            </div>
                        </div>
                    @elseif($derivedStatus === 'shipped')
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">🚚</span>
                            <div class="min-w-0">
                                <p class="font-semibold text-neutral-900">
                                    {{ $fulfillment?->delivered_at ? 'Delivered' : 'Shipped' }}
                                </p>
                                @if($fulfillment?->courier)
                                    <p class="text-xs text-neutral-500 mt-1">Courier: {{ $fulfillment->courier }}</p>
                                @endif
                                @if($fulfillment?->tracking_number)
                                    <p class="text-xs text-neutral-600 mt-1 font-mono">Tracking #: {{ $fulfillment->tracking_number }}</p>
                                @endif
                                @if($fulfillment?->tracking_url)
                                    <a href="{{ $fulfillment->tracking_url }}"
                                       target="_blank" rel="noopener noreferrer"
                                       class="btn btn-outline btn-sm mt-3 gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        Track on Courier Website
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Address column --}}
                <div class="border border-neutral-200 rounded-xl p-4">
                    <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-neutral-500 mb-2 border-b pb-2">Shipping Address</h3>

                    @php($address = $fulfillment?->address_snapshot)
                    @if(!empty($address))
                        <address class="not-italic text-sm text-neutral-700 leading-relaxed">
                            @if(!empty($address['name']))<p class="font-semibold text-neutral-900">{{ $address['name'] }}</p>@endif
                            @if(!empty($address['line1']))<p>{{ $address['line1'] }}</p>@endif
                            @if(!empty($address['line2']))<p>{{ $address['line2'] }}</p>@endif
                            <p>
                                @if(!empty($address['city'])){{ $address['city'] }}@endif
                                @if(!empty($address['state'])){{ ', ' . $address['state'] }}@endif
                                @if(!empty($address['postcode'])){{ ' ' . $address['postcode'] }}@endif
                            </p>
                            @if(!empty($address['country']))<p>{{ $address['country'] }}</p>@endif
                        </address>
                    @else
                        <p class="text-xs text-neutral-500">
                            @if(in_array($derivedStatus, ['completed', 'medal_pending'], true))
                                Address will be captured when you claim your medal.
                            @elseif($derivedStatus === 'pending')
                                Address captured after payment.
                            @else
                                Address captured when you claim your medal after completion.
                            @endif
                        </p>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- =============================================================== --}}
    {{-- 6. Review card --}}
    {{-- =============================================================== --}}
    @if($showReview)
        <section class="mb-6 bg-white rounded-2xl border border-neutral-200 shadow-warm p-5 sm:p-6">
            <h2 class="text-sm font-bold uppercase tracking-[0.2em] text-neutral-500 mb-4">Your Review</h2>

            @if($review && $review->status === 'submitted')
                {{-- Submitted review — read-only display --}}
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-brand">
                            <svg class="w-3.5 h-3.5 text-neutral-900" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <span class="text-sm font-bold text-neutral-900">Review submitted</span>
                        @if($review->submitted_at)
                            <span class="text-xs text-neutral-400">· {{ $review->submitted_at->format('M j, Y') }}</span>
                        @endif
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6 mb-4 pb-4 border-b border-neutral-100">
                        <div>
                            <p class="text-xs text-neutral-500 uppercase tracking-wider mb-1">Puzzle Rating</p>
                            <x-challenge.piece-rating
                                label="Puzzle rating"
                                :selected="(string) ($review->puzzle_rating ?? 0)"
                                size="sm"
                            />
                        </div>
                        <div>
                            <p class="text-xs text-neutral-500 uppercase tracking-wider mb-1">Platform Rating</p>
                            <x-challenge.piece-rating
                                label="Platform rating"
                                :selected="(string) ($review->platform_rating ?? 0)"
                                size="sm"
                            />
                        </div>
                        @if($review->is_public)
                            <span class="badge badge-sm bg-base-200 text-neutral-700 border border-neutral-200 sm:ml-auto">Public</span>
                        @endif
                    </div>

                    @if($review->title)
                        <h3 class="font-display font-black text-neutral-900 text-lg mb-2">{{ $review->title }}</h3>
                    @endif
                    @if($review->body)
                        <p class="text-sm text-neutral-700 leading-relaxed whitespace-pre-line">{{ $review->body }}</p>
                    @else
                        <p class="text-sm text-neutral-500 italic">No written feedback provided.</p>
                    @endif
                </div>
            @elseif($review && $review->status === 'pending')
                {{-- Pending review — inline form (mirrors play page form) --}}
                <div
                    x-data="{
                        puzzleRating: {{ (int) ($puzzleRating ?? 0) }},
                        platformRating: {{ (int) ($platformRating ?? 0) }},
                        selectPuzzle(v) { this.puzzleRating = v; $wire.set('puzzleRating', v) },
                        selectPlatform(v) { this.platformRating = v; $wire.set('platformRating', v) },
                    }"
                    x-init="$wire.on('review-submitted', () => $wire.call('refreshReview'))"
                >
                    <p class="text-sm text-neutral-600 mb-5">Tell us what you thought of <span class="font-bold">{{ $challenge->name }}</span>. Your feedback shapes future puzzle series and the CPC platform.</p>

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-neutral-700 mb-2">How was this challenge?</label>
                        <div class="flex items-center justify-between gap-3">
                            <x-challenge.piece-rating
                                label="Puzzle rating"
                                selected="puzzleRating"
                                on-pick="selectPuzzle"
                            />
                            <span class="text-xs text-neutral-500" x-text="puzzleRating === 5 ? 'Masterpiece' : puzzleRating === 4 ? 'Great' : puzzleRating === 3 ? 'Solid' : puzzleRating === 2 ? 'Meh' : puzzleRating === 1 ? 'Tough' : ''"></span>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-neutral-700 mb-2">And how is the overall CPC platform?</label>
                        <div class="flex items-center justify-between gap-3">
                            <x-challenge.piece-rating
                                label="Platform rating"
                                selected="platformRating"
                                on-pick="selectPlatform"
                            />
                            <span class="text-xs text-neutral-500" x-text="platformRating === 5 ? 'Love it' : platformRating === 4 ? 'Great' : platformRating === 3 ? 'Good' : platformRating === 2 ? 'Okay' : platformRating === 1 ? 'Needs work' : ''"></span>
                        </div>
                    </div>

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

                    <div class="mb-5">
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
                        wire:click="submitReview"
                        wire:loading.attr="disabled"
                        class="btn btn-primary gap-2"
                    >
                        <svg wire:loading.remove wire:target="submitReview" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <svg wire:loading wire:target="submitReview" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Submit Review
                    </button>
                </div>
            @else
                <p class="text-sm text-neutral-500">Complete the challenge to leave a review.</p>
            @endif
        </section>
    @endif

    {{-- =============================================================== --}}
    {{-- 7. History card --}}
    {{-- =============================================================== --}}
    @if($showHistory)
        <section class="mb-6 bg-white rounded-2xl border border-neutral-200 shadow-warm p-5 sm:p-6">
            <h2 class="text-sm font-bold uppercase tracking-[0.2em] text-neutral-500 mb-4">Solve History</h2>

            @if(empty($challengeProgress))
                <p class="text-sm text-neutral-500">No puzzles solved yet — make your first move to start your history.</p>
            @else
                <ul class="divide-y divide-neutral-100">
                    @foreach($challengeProgress as $entry)
                        <li class="flex items-center justify-between gap-3 py-2.5">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-8 h-8 flex-shrink-0 rounded-md bg-brand/20 text-neutral-900 font-display font-bold text-sm flex items-center justify-center">
                                    {{ $entry['sequence'] > 0 ? '#'.$entry['sequence'] : '#' }}
                                </span>
                                <span class="font-medium text-neutral-800 text-sm truncate">Puzzle {{ $entry['sequence'] > 0 ? $entry['sequence'] : '' }}</span>
                            </div>
                            <time
                                datetime="{{ $entry['solved_at']?->toIso8601String() }}"
                                title="{{ $entry['solved_at']?->format('M j, Y · g:ia') }}"
                                class="text-xs text-neutral-500 flex-shrink-0"
                            >
                                {{ $entry['solved_at']?->diffForHumans() }}
                            </time>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif

</div>
