{{--
    Hero section for the public Challenge page.

    Props:
        name        (string)        — challenge name (h1).
        description (string)        — already-rendered safe HTML for the lead paragraph.
        hasDescription (bool)       — whether to render the description at all.
        backHref    (string)        — URL for the back link (default: route('challenges.index')).
        backLabel   (string)        — text for the back link.
        posterImageUrl (string|null)— hero background poster (covers the section with low opacity).
        badgeLevel  (array)         — [icon, label, badgeClass] for the level badge.
        badgePuzzles (int)          — count of puzzles (rendered in a secondary badge).
        orderLabel  (string)        — "Sequential order" / "Free order" / etc.
        mediaImages (array)         — list of media carousel image URLs (poster + medal + gallery).

    Slot: extra badges (e.g. "Early access").
--}}
@props([
    'name' => '',
    'description' => '',
    'hasDescription' => false,
    'backHref' => null,
    'backLabel' => 'Back to Challenges',
    'posterImageUrl' => null,
    'badgeLevel' => ['♟', 'Challenge', 'badge-primary'],
    'badgePuzzles' => 0,
    'orderLabel' => '',
    'mediaImages' => [],
])

@php
    $backHref ??= \Illuminate\Support\Facades\Route::has('challenges.index')
        ? route('challenges.index')
        : url('/challenges');

    [$levelIcon, $levelLabel, $levelClass] = $badgeLevel;
@endphp

<section class="relative overflow-hidden bg-neutral-900 text-white">
    @if($posterImageUrl)
        <img src="{{ $posterImageUrl }}" alt="" aria-hidden="true" class="absolute inset-0 h-full w-full object-cover opacity-60">
    @endif

    <div class="absolute inset-0 bg-gradient-to-br from-neutral-950/80 via-neutral-900/60 to-neutral-900/40"></div>
    <div class="absolute inset-0 bg-chess-pattern opacity-[0.04]"></div>

    <div class="relative mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <a href="{{ $backHref }}" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-orange-200/90 transition hover:text-orange-100">
            <span aria-hidden="true">←</span>
            <span>{{ $backLabel }}</span>
        </a>

        <div class="grid grid-cols-1 gap-10 lg:grid-cols-5 lg:items-center">
            <div class="lg:col-span-3">
                <div class="mb-5 flex flex-wrap items-center gap-2.5">
                    <span class="badge {{ $levelClass }} gap-1 px-3 py-3 font-semibold">
                        <span aria-hidden="true">{{ $levelIcon }}</span>
                        <span>{{ $levelLabel }}</span>
                    </span>

                    <span class="badge badge-outline border-white/30 px-3 py-3 text-white/90">
                        {{ $badgePuzzles }} {{ \Illuminate\Support\Str::plural('puzzle', $badgePuzzles) }}
                    </span>

                    @if($orderLabel !== '')
                        <span class="badge badge-outline border-white/30 px-3 py-3 text-white/90">
                            {{ $orderLabel }}
                        </span>
                    @endif

                    {{ $slot }}
                </div>

                <h1 class="font-display text-4xl font-black leading-[1.05] text-white sm:text-5xl lg:text-6xl">
                    {{ $name }}
                </h1>

                @if($hasDescription)
                    <div class="prose prose-invert mt-5 max-w-2xl text-base leading-relaxed text-neutral-200 lg:text-lg">
                        {!! $description !!}
                    </div>
                @endif

                <div class="mt-8 flex flex-wrap items-center gap-3 text-sm text-neutral-300">
                    <span class="inline-flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-orange-400"></span>
                        Physical medal shipped
                    </span>
                    <span class="text-neutral-600">·</span>
                    <span class="inline-flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-orange-400"></span>
                        Sticker on completion
                    </span>
                    <span class="text-neutral-600">·</span>
                    <span class="inline-flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-orange-400"></span>
                        Global shipping
                    </span>
                </div>
            </div>

            <div class="lg:col-span-2">
                <x-challenge.media-carousel
                    :images="$mediaImages"
                    :alt="$name.' gallery'"
                    aspect="aspect-square"
                />
            </div>
        </div>
    </div>
</section>
