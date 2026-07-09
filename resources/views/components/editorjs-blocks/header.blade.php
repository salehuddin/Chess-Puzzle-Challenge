@php
    $level = $data['level'] ?? 2;
    $classes = match($level) {
        1 => 'font-display text-4xl font-black text-stone-900',
        2 => 'font-display text-3xl font-black text-stone-900',
        3 => 'font-display text-2xl font-bold text-stone-900',
        4 => 'font-display text-xl font-bold text-stone-800',
        5 => 'font-display text-lg font-semibold text-stone-800',
        6 => 'font-display text-base font-semibold text-stone-700',
        default => 'font-display text-3xl font-black text-stone-900',
    };
@endphp

<{{ 'h' . $level }} class="{{ $classes }}">{!! $data['text'] !!}</{{ 'h' . $level }}>
