{{--
    Section wrapper used by all public Challenge page blocks.

    Slot:      default — section body content.
    Props:
        eyebrow (string|null) — small uppercase label above the heading.
        heading (string|null) — main section heading (rendered as h2).
        sub     (string|null) — optional lead paragraph below the heading.
        bg      (string) — background tone: 'base' (default), 'base-2', 'white', 'dark', 'none'.
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
        'base-2' => 'bg-base-200',
        'white'  => 'bg-white',
        'dark'   => 'bg-stone-900 text-stone-100',
        'none'   => '',
        default  => 'bg-base-100',
    };

    $eyebrowColor = $bg === 'dark' ? 'text-amber-300' : 'text-amber-700';
    $headingColor = $bg === 'dark' ? 'text-white' : 'text-stone-900';
    $subColor     = $bg === 'dark' ? 'text-stone-300' : 'text-stone-600';
@endphp

<section @if($id) id="{{ $id }}" @endif class="{{ $bgClass }} py-16 sm:py-20 lg:py-24">
    @if($contained)
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if($eyebrow || $heading || $sub)
                <div class="mb-10 max-w-3xl {{ $bg === 'dark' ? 'text-stone-100' : '' }}">
                    @if($eyebrow)
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] {{ $eyebrowColor }}">{{ $eyebrow }}</p>
                    @endif
                    @if($heading)
                        <h2 class="mt-2 font-display text-3xl font-black sm:text-4xl {{ $headingColor }}">{{ $heading }}</h2>
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
                <div class="max-w-3xl {{ $bg === 'dark' ? 'text-stone-100' : '' }}">
                    @if($eyebrow)
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] {{ $eyebrowColor }}">{{ $eyebrow }}</p>
                    @endif
                    @if($heading)
                        <h2 class="mt-2 font-display text-3xl font-black sm:text-4xl {{ $headingColor }}">{{ $heading }}</h2>
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
