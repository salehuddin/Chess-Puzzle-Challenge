{{--
    Status-aware pricing / enrollment card.

    Props (all except userEnrollment are read at render time):
        enrollUrl     (string) — URL to start enrollment.
        registerUrl   (string) — URL for the Register button (guest state).
        loginUrl      (string) — URL for the Sign In button (guest state).
        checkoutUrl   (string|null) — URL for Complete Payment (pending state).
        playUrl       (string|null) — URL for Continue Playing (active state).
        trackUrl      (string|null) — URL for View Enrollment / View Details.
        challengesUrl (string) — URL for "Browse Challenges" / "Next Challenge".
        stickerArtworkUrl (string|null) — optional sticker thumbnail.
        userEnrollment (?array{status: 'pending'|'active'|'completed'}) — null = no enrollment.
        isGuest       (bool)   — true if no authenticated user.
        isAdmin       (bool)   — true if the current user is an admin (admin-bypass label).

    Renders a different heading/sub-text and button set per status.
    No Livewire — the parent passes the resolved URLs in.
--}}
@props([
    'enrollUrl' => '#',
    'registerUrl' => '#',
    'loginUrl' => '#',
    'checkoutUrl' => null,
    'playUrl' => null,
    'trackUrl' => null,
    'challengesUrl' => '#',
    'stickerArtworkUrl' => null,
    'userEnrollment' => null,
    'isGuest' => true,
    'isAdmin' => false,
])

@php
    $status = $userEnrollment['status'] ?? null;

    $heading = match (true) {
        $status === 'pending'   => 'Complete your payment',
        $status === 'active'    => "You're enrolled!",
        $status === 'completed' => 'Challenge completed!',
        default                 => 'Join this challenge',
    };

    $body = match (true) {
        $status === 'active'    => "Continue solving puzzles where you left off.",
        $status === 'completed' => "You've conquered all puzzles and earned the sticker. Track your medal shipment or browse more challenges.",
        $status === 'pending'   => 'Your enrollment was created. Complete payment to unlock the puzzles.',
        default                 => 'Create an account or sign in to enroll. Admin users can enroll instantly without payment.',
    };

    $eyebrow = $status ? 'Enrollment' : 'Get Started';
@endphp

<div class="overflow-hidden rounded-3xl border border-amber-200 bg-gradient-to-br from-amber-50 via-white to-amber-50 shadow-warm-lg">
    <div class="grid grid-cols-1 lg:grid-cols-5 lg:items-center">
        <div class="flex items-start gap-5 p-6 sm:p-8 lg:col-span-3 lg:p-10">
            @if($stickerArtworkUrl)
                <img
                    src="{{ $stickerArtworkUrl }}"
                    alt="Sticker reward"
                    class="hidden h-24 w-24 shrink-0 rounded-2xl object-contain ring-1 ring-amber-200 sm:block"
                >
            @endif
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">{{ $eyebrow }}</p>
                <h2 class="mt-1 font-display text-2xl font-black text-stone-900 sm:text-3xl">{{ $heading }}</h2>
                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-stone-600 sm:text-base">
                    {{ $body }}
                </p>
            </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-amber-200/60 bg-white/60 p-6 sm:flex-row sm:p-8 lg:col-span-2 lg:items-center lg:justify-end lg:border-l lg:border-t-0 lg:p-10">
            @if($isGuest)
                <a href="{{ $registerUrl }}" class="btn btn-primary w-full sm:w-auto">
                    Register to Enroll
                </a>
                <a href="{{ $loginUrl }}" class="btn btn-outline btn-primary w-full sm:w-auto">
                    Sign In
                </a>
            @elseif($status === 'pending' && $checkoutUrl)
                <a href="{{ $checkoutUrl }}" class="btn btn-primary w-full sm:w-auto">Complete Payment</a>
                @if($trackUrl)
                    <a href="{{ $trackUrl }}" class="btn btn-outline btn-primary w-full sm:w-auto">View Enrollment</a>
                @endif
            @elseif($status === 'active' && $playUrl)
                <a href="{{ $playUrl }}" class="btn btn-primary w-full gap-2 sm:w-auto">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.5v5a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Continue Playing
                </a>
                <a href="{{ $challengesUrl }}" class="btn btn-outline btn-primary w-full sm:w-auto">Browse Challenges</a>
            @elseif($status === 'completed' && $trackUrl)
                <a href="{{ $trackUrl }}" class="btn btn-primary w-full sm:w-auto">View Details</a>
                <a href="{{ $challengesUrl }}" class="btn btn-outline btn-primary w-full sm:w-auto">Next Challenge</a>
            @else
                <a href="{{ $enrollUrl }}" class="btn btn-primary w-full sm:w-auto">
                    {{ $isAdmin ? 'Enroll as Admin' : 'Enroll Now' }}
                </a>
            @endif
        </div>
    </div>
</div>
