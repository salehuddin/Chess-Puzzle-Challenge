{{--
    Grid of <x-challenge.milestone-card /> for every puzzle in the challenge.

    Props:
        puzzles (iterable) — collection/list of puzzle models (or pivot-bearing models) in order.
                             Each item should expose: rating, themes, and pivot.sequence.

    The grid renders nothing if `$puzzles` is empty.
--}}
@props([
    'puzzles' => [],
])

@php
    $list = collect($puzzles);
    $total = $list->count();
@endphp

@if($total > 0)
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($list as $puzzle)
            @php
                $sequence = (int) ($puzzle->pivot->sequence ?? $loop->iteration);
                $isFirst = $sequence === 1;
                $isLast  = $sequence === $total;
            @endphp
            <x-challenge.milestone-card
                :sequence="$sequence"
                :total="$total"
                :title="$puzzle->title ?? ''"
                :themes="$puzzle->themes ?? []"
                :rating="$puzzle->rating ?? null"
                :isFirst="$isFirst"
                :isLast="$isLast"
            />
        @endforeach
    </div>
@endif
