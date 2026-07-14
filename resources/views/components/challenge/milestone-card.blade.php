{{--
    Single puzzle milestone card.

    Props:
        sequence (int)       — sequence number (1-indexed) within the challenge.
        total    (int)       — total milestones in the set (used for the "N of total" label).
        title    (string)    — puzzle title / theme (falls back to a generated label).
        themes   (array)     — optional list of chess themes (e.g. ['fork', 'pin']).
        rating   (int|null)  — puzzle rating shown as a small chip; null hides the chip.
        isFirst  (bool)      — true for the first milestone (changes accent treatment).
        isLast   (bool)      — true for the last milestone (adds a "Finisher" flag).

    The card is pure presentation — no navigation. Use a wrapper link/button if needed.
--}}
@props([
    'sequence' => 0,
    'total' => 0,
    'title' => '',
    'themes' => [],
    'rating' => null,
    'isFirst' => false,
    'isLast' => false,
])

@php
    $themes = is_array($themes) ? array_slice(array_filter(array_map('strval', $themes)), 0, 3) : [];
    $title = trim($title) !== '' ? $title : 'Puzzle '.((int) $sequence);
@endphp

<div class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-neutral-200 bg-white p-6 shadow-warm transition hover:-translate-y-1 hover:shadow-warm-lg">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-orange-700">
                Milestone {{ (int) $sequence }}<span class="text-neutral-400"> / {{ (int) $total }}</span>
            </p>
            <h3 class="mt-2 font-display text-xl font-black text-neutral-900 line-clamp-2">
                {{ $title }}
            </h3>
        </div>
        <div class="flex flex-col items-end gap-1.5">
            <span class="grid h-12 w-12 place-items-center rounded-full bg-orange-50 font-display text-lg font-black text-orange-700 ring-1 ring-orange-200">
                {{ (int) $sequence }}
            </span>
            @if($isFirst)
                <span class="badge badge-sm badge-warning gap-1 text-[10px] font-bold">Start</span>
            @elseif($isLast)
                <span class="badge badge-sm badge-success gap-1 text-[10px] font-bold">Finisher</span>
            @endif
        </div>
    </div>

    @if($themes !== [] || $rating !== null)
        <div class="mt-4 flex flex-wrap items-center gap-2">
            @if($rating !== null)
                <span class="badge badge-outline border-neutral-300 text-xs font-semibold text-neutral-600">
                    {{ (int) $rating }} ELO
                </span>
            @endif
            @foreach($themes as $theme)
                <span class="badge badge-ghost text-xs font-medium text-neutral-600">
                    {{ \Illuminate\Support\Str::headline($theme) }}
                </span>
            @endforeach
        </div>
    @endif

    <div class="mt-6 flex items-center gap-2 text-xs text-neutral-500">
        <span class="inline-flex h-6 w-6 items-center justify-center rounded bg-base-200 text-base font-bold text-neutral-600">♟</span>
        <span>Solve to advance</span>
    </div>
</div>
