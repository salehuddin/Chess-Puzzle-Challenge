{{--
    Stat trio — the three stat cards under the hero.

    Props (all required, even if zero):
        puzzleTotal (int)        — number of puzzles in the challenge.
        orderLabel  (string)     — "Sequential order" / "Free order".
        timeLimit   (int)        — time limit in minutes; 0 means "no overall time cap".
        priceMyr    (float)      — price in MYR.
        priceUsd    (float)      — price in USD.
--}}
@props([
    'puzzleTotal' => 0,
    'orderLabel' => '',
    'timeLimit' => 0,
    'priceMyr' => 0,
    'priceUsd' => 0,
])

<div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
    <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-warm">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-neutral-400">Puzzles</p>
        <p class="mt-3 text-5xl font-black text-neutral-900">{{ $puzzleTotal }}</p>
        <p class="mt-2 text-sm text-neutral-500">Curated positions to solve.</p>
    </div>

    <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-warm">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-neutral-400">Rules</p>
        <p class="mt-3 text-2xl font-black text-neutral-900">{{ $orderLabel }}</p>
        <p class="mt-2 text-sm text-neutral-500">
            @if((int) $timeLimit > 0)
                Time limit: {{ (int) $timeLimit }} minutes
            @else
                No overall time cap.
            @endif
        </p>
    </div>

    <div class="rounded-2xl border border-orange-200 bg-gradient-to-br from-orange-50 to-white p-6 shadow-warm">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-orange-700">Price</p>
        <p class="mt-3 text-3xl font-black text-neutral-900">
            RM {{ number_format((float) $priceMyr, 2) }}
        </p>
        <p class="mt-1 text-sm text-neutral-500">
            or USD {{ number_format((float) $priceUsd, 2) }}
        </p>
    </div>
</div>
