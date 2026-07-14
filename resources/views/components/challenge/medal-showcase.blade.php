{{--
    Medal showcase — front/back/large preview treatment.

    Props:
        name        (string)        — challenge name (used for alt text).
        medalArtworkUrl (string|null) — primary medal artwork URL.
        medalImages (array<int,string>) — additional gallery images.

    Layout:
        Two-column on lg: left = sticky preview of the primary medal artwork, right = gallery grid.
        On smaller breakpoints, the preview appears first and the gallery below.
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
            <div class="relative overflow-hidden rounded-3xl border border-orange-200 bg-gradient-to-br from-orange-50 via-white to-orange-100 p-8 shadow-warm-lg">
                <div class="absolute inset-0 bg-chess-pattern opacity-[0.07]"></div>
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
                    <p class="text-xs font-semibold uppercase tracking-widest text-neutral-400">Finisher</p>
                    <p class="mt-1 font-display text-lg font-black text-neutral-900">Medal</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-neutral-400">Custom</p>
                    <p class="mt-1 font-display text-lg font-black text-neutral-900">Design</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-neutral-400">Free</p>
                    <p class="mt-1 font-display text-lg font-black text-neutral-900">Shipping</p>
                </div>
            </div>
        </div>

        <div>
            @if($medalArtworkUrl)
                <figure class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-warm">
                    <img src="{{ $medalArtworkUrl }}" alt="{{ $name }} medal artwork" loading="lazy" class="h-72 w-full object-contain p-6 sm:h-96">
                </figure>
            @endif

            @if($medalImages !== [])
                <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3">
                    @foreach($medalImages as $i => $src)
                        <figure class="group overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-warm" wire:key="medal-{{ md5($src) }}">
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

            <div class="mt-8 rounded-2xl border border-orange-200 bg-orange-50 p-5">
                <p class="text-sm font-semibold text-orange-900">Hand-crafted and shipped with care.</p>
                <p class="mt-1 text-sm leading-relaxed text-orange-800">
                    Each medal is produced in small batches. Once you've finished the challenge, your medal is dispatched from our fulfillment center with tracked shipping to your door.
                </p>
            </div>
        </div>
    </div>
@endif
