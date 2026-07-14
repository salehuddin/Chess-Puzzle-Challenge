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
        variant       (string) — 'default' (warm-amber tint, on white) or 'brand' (chartreuse, on dark section).

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
    'variant' => 'default',
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

    $isBrand = $variant === 'brand';
@endphp

<div @class([
    'overflow-hidden rounded-3xl shadow-warm-lg ring-1',
    'bg-white ring-neutral-900/10' => ! $isBrand,
    'bg-neutral-900 ring-white/10 text-white' => $isBrand,
])>
    <div class="grid grid-cols-1 lg:grid-cols-5 lg:items-center">
        <div class="flex items-start gap-5 p-6 sm:p-8 lg:col-span-3 lg:p-10">
            @if($stickerArtworkUrl)
                <img
                    src="{{ $stickerArtworkUrl }}"
                    alt="Sticker reward"
                    class="hidden h-24 w-24 shrink-0 rounded-2xl object-contain ring-1 ring-neutral-900/10 sm:block"
                >
            @endif
            <div>
                <p @class([
                    'text-xs font-bold uppercase tracking-[0.2em]',
                    'text-neutral-500' => ! $isBrand,
                    'text-brand' => $isBrand,
                ])>{{ $eyebrow }}</p>
                <h2 @class([
                    'mt-1 font-display text-2xl font-black sm:text-3xl',
                    'text-neutral-900' => ! $isBrand,
                    'text-white' => $isBrand,
                ])>{{ $heading }}</h2>
                <p @class([
                    'mt-3 max-w-2xl text-sm leading-relaxed sm:text-base',
                    'text-neutral-600' => ! $isBrand,
                    'text-neutral-300' => $isBrand,
                ])>
                    {{ $body }}
                </p>
            </div>
        </div>

        <div @class([
            'flex flex-col gap-3 p-6 sm:flex-row sm:p-8 lg:col-span-2 lg:items-center lg:justify-end lg:p-10',
            'border-t border-neutral-100' => ! $isBrand,
            'border-t border-white/10' => $isBrand,
        ])>
            @if($isGuest)
                <a href="{{ $registerUrl }}" class="btn btn-primary w-full sm:w-auto">Register to Enroll</a>
                <a href="{{ $loginUrl }}" @class([
                    'btn btn-outline w-full sm:w-auto',
                    'btn-primary' => ! $isBrand,
                    'border-white/30 text-white hover:bg-white/10' => $isBrand,
                ])>Sign In</a>
            @elseif($status === 'pending' && $checkoutUrl)
                <a href="{{ $checkoutUrl }}" class="btn btn-primary w-full sm:w-auto">Complete Payment</a>
                @if($trackUrl)
                    <a href="{{ $trackUrl }}" @class([
                        'btn btn-outline w-full sm:w-auto',
                        'btn-primary' => ! $isBrand,
                        'border-white/30 text-white hover:bg-white/10' => $isBrand,
                    ])>View Enrollment</a>
                @endif
            @elseif($status === 'active' && $playUrl)
                <a href="{{ $playUrl }}" @class([
                    'btn btn-primary w-full gap-2 sm:w-auto',
                    'bg-brand text-neutral-900 hover:bg-brand/90 border-0' => $isBrand,
                ])>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1a1 0 0010 9.5v5a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Continue Playing
                </a>
                <a href="{{ $challengesUrl }}" @class([
                    'btn btn-outline w-full sm:w-auto',
                    'btn-primary' => ! $isBrand,
                    'border-white/30 text-white hover:bg-white/10' => $isBrand,
                ])>Browse Challenges</a>
            @elseif($status === 'completed' && $trackUrl)
                <a href="{{ $trackUrl }}" @class([
                    'btn btn-primary w-full sm:w-auto',
                    'bg-brand text-neutral-900 hover:bg-brand/90 border-0' => $isBrand,
                ])>View Details</a>
                <a href="{{ $challengesUrl }}" @class([
                    'btn btn-outline w-full sm:w-auto',
                    'btn-primary' => ! $isBrand,
                    'border-white/30 text-white hover:bg-white/10' => $isBrand,
                ])>Next Challenge</a>
            @else
                <a href="{{ $enrollUrl }}" @class([
                    'btn btn-primary w-full sm:w-auto',
                    'bg-brand text-neutral-900 hover:bg-brand/90 border-0' => $isBrand,
                ])>
                    {{ $isAdmin ? 'Enroll as Admin' : 'Enroll Now' }}
                </a>
            @endif
        </div>
    </div>
</div>
