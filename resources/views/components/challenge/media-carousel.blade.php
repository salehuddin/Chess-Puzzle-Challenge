{{--
    Rotating image carousel (Alpine).

    Props:
        images (array<int,string>) — list of image URLs to cycle through.
        alt    (string)             — alt text used for every image.
        autoplay (bool)             — when true, advances every `interval` ms (default true).
        interval (int)              — autoplay delay in ms (default 5000).
        aspect  (string)            — Tailwind aspect-ratio class (default 'aspect-square').

    Slot: optional caption block rendered beneath the carousel.

    Behaviour:
        Renders nothing when `$images` is empty.
        Uses Alpine x-data to track `active` index; arrow / dot clicks + autoplay advance it.
        Pauses autoplay on hover (x-on:mouseenter / mouseleave).
--}}
@props([
    'images' => [],
    'alt' => '',
    'autoplay' => true,
    'interval' => 5000,
    'aspect' => 'aspect-square',
])

@php
    $images = array_values(array_filter($images, fn ($i) => is_string($i) && trim($i) !== ''));
@endphp

@if($images !== [])
    <div
        x-data="{
            active: 0,
            count: {{ count($images) }},
            interval: {{ (int) $interval }},
            timer: null,
            autoplay: {{ $autoplay ? 'true' : 'false' }},
            start() { if (this.autoplay) { this.timer = setInterval(() => this.next(), this.interval) } },
            stop()  { if (this.timer) { clearInterval(this.timer); this.timer = null } },
            next()  { this.active = (this.active + 1) % this.count },
            prev()  { this.active = (this.active - 1 + this.count) % this.count },
            go(i)   { this.active = i; this.stop(); this.start() },
        }"
        x-init="start()"
        x-on:mouseenter="stop()"
        x-on:mouseleave="start()"
        class="relative w-full overflow-hidden rounded-2xl bg-neutral-900 shadow-warm-lg"
    >
        <div class="{{ $aspect }} w-full">
            @foreach($images as $i => $src)
                <img
                    src="{{ $src }}"
                    alt="{{ $alt }}"
                    loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                    x-show="active === {{ $i }}"
                    x-transition:enter="transition-opacity duration-500"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity duration-500"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute inset-0 h-full w-full {{ $aspect === 'aspect-square' ? 'object-contain' : 'object-cover' }}"
                >
            @endforeach
        </div>

        @if(count($images) > 1)
            <button
                type="button"
                x-on:click="prev()"
                aria-label="Previous image"
                class="absolute left-3 top-1/2 -translate-y-1/2 grid h-10 w-10 place-items-center rounded-full bg-neutral-900/60 text-white backdrop-blur-sm transition hover:bg-neutral-900/80"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <button
                type="button"
                x-on:click="next()"
                aria-label="Next image"
                class="absolute right-3 top-1/2 -translate-y-1/2 grid h-10 w-10 place-items-center rounded-full bg-neutral-900/60 text-white backdrop-blur-sm transition hover:bg-neutral-900/80"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            <div class="absolute bottom-3 left-1/2 flex -translate-x-1/2 gap-2">
                @foreach($images as $i => $src)
                    <button
                        type="button"
                        x-on:click="go({{ $i }})"
                        :class="active === {{ $i }} ? 'bg-orange-400 w-6' : 'bg-white/60 hover:bg-white/90 w-2'"
                        class="h-2 rounded-full transition-all"
                        aria-label="Go to image {{ $i + 1 }}"
                    ></button>
                @endforeach
            </div>
        @endif

        {{ $slot }}
    </div>
@endif
