{{--
    Medal showcase — front/back/large preview treatment.

    Props:
        name        (string)        — challenge name (used for alt text).
        medalArtworkUrl (string|null) — primary medal artwork URL.
        medalImages (array<int,string>) — additional gallery images.

    Layout:
        Two-column on lg: left = sticky preview of the primary medal artwork, right = gallery grid.
        On smaller breakpoints, the preview appears first and the gallery below.

    Designed to work on both white and dark (neutral-900) section backgrounds.
--}}
@props([
    'name' => '',
    'medalArtworkUrl' => null,
    'medalImages' => [],
])

@php
    $medalImages = array_values(array_filter($medalImages, fn ($i) => is_string($i) && trim($i) !== ''));
@endphp

@if($medalArtworkUrl || $medalImages !== [])
    <div class="grid grid-cols-1 gap-10 lg:grid-cols-2 lg:items-start">
        <div class="lg:sticky lg:top-28">
            <div class="relative overflow-hidden rounded-3xl bg-white p-8 shadow-warm-lg ring-1 ring-neutral-900/10">
                <div class="absolute inset-0 bg-chess-pattern opacity-[0.04]"></div>
                @if($medalArtworkUrl)
                    <img
                        src="{{ $medalArtworkUrl }}"
                        alt="{{ $name }} medal"
                        class="relative mx-auto h-64 w-64 object-contain drop-shadow-2xl sm:h-80 sm:w-80"
                    >
                @endif
            </div>

            <div class="mt-6 grid grid-cols-3 gap-3 text-center">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-neutral-500">Finisher</p>
                    <p class="mt-1 font-display text-lg font-black text-neutral-900">Medal</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-neutral-500">Custom</p>
                    <p class="mt-1 font-display text-lg font-black text-neutral-900">Design</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-neutral-500">Free</p>
                    <p class="mt-1 font-display text-lg font-black text-neutral-900">Shipping</p>
                </div>
            </div>
        </div>

        <div>
            @if($medalArtworkUrl)
                <figure class="overflow-hidden rounded-2xl bg-white shadow-warm ring-1 ring-neutral-900/10">
                    <img src="{{ $medalArtworkUrl }}" alt="{{ $name }} medal artwork" loading="lazy" class="h-72 w-full object-contain p-6 sm:h-96">
                </figure>
            @endif

            @if($medalImages !== [])
                <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3">
                    @foreach($medalImages as $i => $src)
                        <figure class="group overflow-hidden rounded-2xl bg-white shadow-warm ring-1 ring-neutral-900/10" wire:key="medal-{{ md5($src) }}">
                            <img
                                src="{{ $src }}"
                                alt="{{ $name }} medal view {{ $i + 1 }}"
                                loading="lazy"
                                class="h-40 w-full object-contain p-3 transition duration-300 group-hover:scale-105 sm:h-48"
                            >
                        </figure>
                    @endforeach
                </div>
            @endif

            <div class="mt-8 rounded-2xl bg-white p-5 shadow-warm ring-1 ring-neutral-900/10">
                <p class="text-sm font-bold text-neutral-900">Hand-crafted and shipped with care.</p>
                <p class="mt-1 text-sm leading-relaxed text-neutral-600">
                    Each medal is produced in small batches. Once you've finished the challenge, your medal is dispatched from our fulfillment center with tracked shipping to your door.
                </p>
            </div>
        </div>
    </div>
@endif
