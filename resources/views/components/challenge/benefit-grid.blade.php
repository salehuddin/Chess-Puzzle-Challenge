{{--
    "Plus all this" 4-card benefit grid (static placeholder copy).

    Copy is intentionally generic and labeled as placeholder so admins know
    to replace it with challenge-specific wording later. When a per-challenge
    benefits data field is added, this component should accept an array prop
    and fall back to the placeholders below.

    Slots: none — the 4 benefits are rendered in order. The view is a fixed
    "curated subset" per the public Challenge page plan.
--}}

{{-- PLACEHOLDER COPY — replace with challenge-specific benefits once a `benefits` field exists. --}}
@php
    $benefits = [
        [
            'icon'  => '📱',
            'title' => 'Solve anywhere',
            'body'  => 'Play on web or mobile. Your progress syncs automatically so you can pick up from any device.',
        ],
        [
            'icon'  => '📈',
            'title' => 'Multiple difficulty levels',
            'body'  => 'Curated puzzles span beginner through advanced ELO ratings, matched to the challenge level.',
        ],
        [
            'icon'  => '⏱',
            'title' => 'Set your own pace',
            'body'  => 'No deadlines. Take a week or take a year — your enrollment stays open until you finish.',
        ],
        [
            'icon'  => '👥',
            'title' => 'Solo or with a team',
            'body'  => 'Take on the challenge by yourself, or invite friends and share the puzzle count.',
        ],
    ];
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
    @foreach($benefits as $benefit)
        <div class="flex h-full flex-col rounded-2xl border border-stone-200 bg-white p-6 shadow-warm">
            <div class="grid h-12 w-12 place-items-center rounded-xl bg-amber-50 text-2xl">
                {{ $benefit['icon'] }}
            </div>
            <h3 class="mt-4 font-display text-lg font-black text-stone-900">
                {{ $benefit['title'] }}
            </h3>
            <p class="mt-2 text-sm leading-relaxed text-stone-600">
                {{ $benefit['body'] }}
            </p>
        </div>
    @endforeach
</div>

<p class="mt-4 text-center text-[10px] font-semibold uppercase tracking-[0.2em] text-stone-400">
    Placeholder copy — replace with challenge-specific benefits
</p>
