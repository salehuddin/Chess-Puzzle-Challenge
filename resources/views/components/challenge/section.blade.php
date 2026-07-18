{{--
    Section wrapper used by all public Challenge page blocks.

    Slot:      default — section body content.
    Props:
        eyebrow (string|null) — small uppercase label above the heading.
        heading (string|null) — main section heading (rendered as h2).
        sub     (string|null) — optional lead paragraph below the heading.
        bg      (string) — background tone:
                  'base'   = warm-ivory (default — used sparingly, avoid)
                  'white'  = pure white (#FEFEFE)
                  'mist'   = pale grey (#F5F5F5) — use to break up white runs
                  'dark'   = onyx black (#111111) with white text
                  'brand'  = chartreuse (#B7FF00) with dark text — use once per page
                  'none'   = no background, transparent
        id      (string|null) — DOM id (used for anchor links / aria-labelledby).
        contained (bool) — when true (default), wraps body in the standard max-width container.
                            Pass false to render the slot flush (e.g. full-bleed media).

    Usage:
        <x-challenge.section eyebrow="The journey" heading="Challenge Details">
            ... body ...
        </x-challenge.section>
--}}
@props([
    'eyebrow' => null,
    'heading' => null,
    'sub' => null,
    'bg' => 'base',
    'id' => null,
    'contained' => true,
])

@php
    $bgClass = match ($bg) {
        'white'  => 'bg-white text-neutral-900',
        'mist'   => 'bg-base-200 text-neutral-900',
        'dark'   => 'bg-neutral-900 text-white',
        'brand'  => 'bg-brand text-neutral-900',
        'none'   => '',
        default  => 'bg-base-100 text-neutral-900',
    };

    $eyebrowColor = match ($bg) {
        'dark'   => 'text-brand',
        'brand'  => 'text-neutral-700',
        default  => 'text-neutral-500',
    };

    $headingColor = match ($bg) {
        'dark'   => 'text-white',
        'brand'  => 'text-neutral-900',
        default  => 'text-neutral-900',
    };

    $subColor = match ($bg) {
        'dark'   => 'text-neutral-300',
        'brand'  => 'text-neutral-700',
        default  => 'text-neutral-500',
    };
@endphp

<section @if($id) id="{{ $id }}" @endif class="{{ $bgClass }} py-12 sm:py-14 lg:py-16">
    @if($contained)
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if($eyebrow || $heading || $sub)
                <div class="mb-10 max-w-3xl">
                    @if($eyebrow)
                        <p class="text-xs font-bold uppercase tracking-[0.2em] {{ $eyebrowColor }}">{{ $eyebrow }}</p>
                    @endif
                    @if($heading)
                        <h2 class="mt-2 font-display text-3xl font-black sm:text-4xl lg:text-5xl {{ $headingColor }}">{{ $heading }}</h2>
                    @endif
                    @if($sub)
                        <p class="mt-3 text-base leading-relaxed sm:text-lg {{ $subColor }}">{{ $sub }}</p>
                    @endif
                </div>
            @endif

            {{ $slot }}
        </div>
    @else
        {{-- Full-bleed: still emit heading/eyebrow above the slot, but let body escape the container. --}}
        @if($eyebrow || $heading || $sub)
            <div class="mx-auto mb-10 max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    @if($eyebrow)
                        <p class="text-xs font-bold uppercase tracking-[0.2em] {{ $eyebrowColor }}">{{ $eyebrow }}</p>
                    @endif
                    @if($heading)
                        <h2 class="mt-2 font-display text-3xl font-black sm:text-4xl lg:text-5xl {{ $headingColor }}">{{ $heading }}</h2>
                    @endif
                    @if($sub)
                        <p class="mt-3 text-base leading-relaxed sm:text-lg {{ $subColor }}">{{ $sub }}</p>
                    @endif
                </div>
            </div>
        @endif

        {{ $slot }}
    @endif
</section>
