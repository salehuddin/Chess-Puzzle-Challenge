{{--
    Sticky CTA bar that appears once the user scrolls past the hero.

    Props:
        ctaLabel  (string) — primary button label (e.g. "Continue Playing", "Enroll Now").
        ctaHref   (string) — primary button URL.
        secondaryLabel (string|null) — optional secondary button label.
        secondaryHref  (string|null) — optional secondary button URL.
        icon      (string) — emoji or character shown before the heading (default "♟").
        title     (string) — main heading shown on the bar.
        subtitle  (string) — small sub-line beneath the heading.
        showAfter (int)   — scroll offset (px) after which the bar appears (default 600).

    Slot: optional extra content shown on the right side (e.g. progress chip).
--}}
@props([
    'ctaLabel' => 'Enroll Now',
    'ctaHref' => '#',
    'secondaryLabel' => null,
    'secondaryHref' => null,
    'icon' => '♟',
    'title' => 'Ready to play?',
    'subtitle' => '',
    'showAfter' => 600,
])

<div
    x-data="{ visible: false }"
    x-init="window.addEventListener('scroll', () => { visible = window.scrollY > {{ (int) $showAfter }} }, { passive: true })"
    x-show="visible"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-y-full opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-y-0 opacity-100"
    x-transition:leave-end="translate-y-full opacity-0"
    class="fixed inset-x-0 bottom-0 z-40 border-t border-amber-200 bg-white/95 shadow-warm-lg backdrop-blur-md"
    role="region"
    aria-label="Challenge enrollment"
>
    <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-3 sm:px-6 sm:flex-row sm:items-center sm:justify-between lg:px-8">
        <div class="flex items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-full bg-amber-100 text-lg text-amber-800">
                {{ $icon }}
            </span>
            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-stone-900 sm:text-base">{{ $title }}</p>
                @if($subtitle !== '')
                    <p class="truncate text-xs text-stone-500 sm:text-sm">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            {{ $slot }}

            @if($secondaryLabel && $secondaryHref)
                <a href="{{ $secondaryHref }}" class="btn btn-ghost btn-sm sm:btn-md">
                    {{ $secondaryLabel }}
                </a>
            @endif

            <a href="{{ $ctaHref }}" class="btn btn-primary btn-sm sm:btn-md gap-2">
                {{ $ctaLabel }}
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </div>
</div>
