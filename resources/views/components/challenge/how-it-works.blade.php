{{--
    "How this challenge works" — 4-step breakdown tailored to a single challenge.

    Adapted from the landing page's 4-step section (06-how-it-works.blade.php) but
    personalised for a specific challenge: the steps reference this challenge's
    level, puzzle count, sticker art and medal art instead of generic series copy.

    Props:
        name            (string)        — challenge name (used in step copy + alt text).
        puzzleTotal     (int)           — number of puzzles (replaces the hard-coded 100).
        orderLabel      (string)        — "Sequential order" / "Free order" — used in step 2 copy.
        timeLimit       (int)           — time limit in minutes; 0 = no overall cap.
        levelData       (array)         — [icon, label, badgeClass] from the hero badge.
        stickerArtworkUrl (string|null) — step 3 visual.
        medalArtworkUrl   (string|null) — step 4 visual.
        posterImageUrl    (string|null) — step 1 visual (falls back to nothing).
        enrollHref      (string)        — anchor on the last step CTA (default "#enroll").

    Renders nothing dangerous if optional media is missing — the cards degrade
    gracefully to icon-only visuals.
--}}
@props([
    'name' => '',
    'puzzleTotal' => 0,
    'orderLabel' => 'Sequential order',
    'timeLimit' => 0,
    'levelData' => ['♟', 'Challenge', 'badge-primary'],
    'stickerArtworkUrl' => null,
    'medalArtworkUrl' => null,
    'posterImageUrl' => null,
    'enrollHref' => '#enroll',
])

@php
    [$levelIcon, $levelLabel] = $levelData;

    $puzzleWord = \Illuminate\Support\Str::plural('puzzle', (int) $puzzleTotal);

    $steps = [
        [
            'step' => '01',
            'icon' => '🎯',
            'title' => 'Pick this challenge',
            'desc' => "You've landed on the {$levelLabel} track of Chess Puzzle Challenge. {$puzzleTotal} hand-curated {$puzzleWord}, filtered by rating band and tactical theme, ready for you to tackle.",
            'features' => [
                [$levelIcon, "Level: {$levelLabel}"],
                ['♟', "{$puzzleTotal} {$puzzleWord} — hand-picked from Lichess"],
                ['🎯', 'Single tactical theme, focused practice'],
            ],
        ],
        [
            'step' => '02',
            'icon' => '♟',
            'title' => 'Solve every puzzle',
            'desc' => "Play in your browser, {$orderLabel}.".($timeLimit > 0 ? " Soft time cap of {$timeLimit} minutes — finish in one sitting or spread it across the week." : " No overall time cap — take a week or take a year."),
            'features' => [
                ['↩', 'Undo and reset without penalty'],
                ['💡', 'Visual hint highlights the piece to move'],
                ['💾', 'Progress autosaves to your account'],
            ],
        ],
        [
            'step' => '03',
            'icon' => '✦',
            'title' => 'Unlock your sticker',
            'desc' => "The moment you finish the last puzzle, your unique digital sticker drops into your Hall of Fame — proof you mastered the {$levelLabel} tactical theme of {$name}.",
            'features' => [
                ['⚡', 'Instant sticker unlock on completion'],
                ['🖼', 'Sticker is unique to this challenge'],
                ['📢', 'Share your achievement with friends'],
            ],
        ],
        [
            'step' => '04',
            'icon' => '🏅',
            'title' => 'Claim your medal',
            'desc' => "Request your medal in-app. We custom-make and ship it worldwide. The design is unique to {$name} — a real trophy for your real wall, tied to the theme you just mastered.",
            'features' => [
                ['🌍', 'Worldwide shipping — 80+ countries'],
                ['🏛', 'Custom-designed medal for this series'],
                ['📦', 'Tracking number emailed when shipped'],
            ],
        ],
    ];
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
    @foreach($steps as $i => $step)
        @php $isLast = $i === 3; @endphp
        <article class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-neutral-200 bg-white p-6 shadow-warm transition hover:-translate-y-1 hover:shadow-warm-lg">
            {{-- Step number + icon --}}
            <div class="flex items-center gap-3">
                <div class="grid h-11 w-11 place-items-center rounded-full bg-neutral-900 text-sm font-black text-brand ring-4 ring-brand/30">
                    {{ $step['step'] }}
                </div>
                <span class="text-2xl" aria-hidden="true">{{ $step['icon'] }}</span>
            </div>

            {{-- Visual strip (challenge-specific art where available) --}}
            <div class="mt-5 overflow-hidden rounded-xl bg-neutral-50 ring-1 ring-neutral-900/10">
                @if($i === 0 && $posterImageUrl)
                    <img src="{{ $posterImageUrl }}" alt="{{ $name }} cover" class="aspect-[16/7] w-full object-cover" loading="lazy">
                @elseif($i === 1)
                    <div class="flex aspect-[16/7] items-center justify-center gap-4 p-4">
                        <div class="grid aspect-square w-1/2 max-w-[120px] grid-cols-4 overflow-hidden rounded-lg bg-neutral-900">
                            @for ($r = 0; $r < 4; $r++)
                                @for ($c = 0; $c < 4; $c++)
                                    <div class="aspect-square {{ ($r + $c) % 2 === 0 ? 'bg-brand' : 'bg-neutral-900' }}"></div>
                                @endfor
                            @endfor
                        </div>
                        <div class="flex-1 space-y-2">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-neutral-400">Progress</p>
                            <div class="h-2 overflow-hidden rounded-full bg-neutral-200">
                                <div class="h-full bg-brand" style="width: 35%"></div>
                            </div>
                            <p class="text-xs text-neutral-500">Puzzle 35 / {{ $puzzleTotal }}</p>
                        </div>
                    </div>
                @elseif($i === 2 && $stickerArtworkUrl)
                    <div class="flex aspect-[16/7] items-center justify-center p-4">
                        <img src="{{ $stickerArtworkUrl }}" alt="{{ $name }} sticker" class="h-full w-auto max-w-[60%] object-contain drop-shadow" loading="lazy">
                    </div>
                @elseif($i === 3 && $medalArtworkUrl)
                    <div class="flex aspect-[16/7] items-center justify-center bg-neutral-900 p-4">
                        <img src="{{ $medalArtworkUrl }}" alt="{{ $name }} medal" class="h-full w-auto max-w-[55%] object-contain drop-shadow-2xl" loading="lazy">
                    </div>
                @else
                    <div class="flex aspect-[16/7] items-center justify-center text-4xl text-neutral-300">
                        {{ $step['icon'] }}
                    </div>
                @endif
            </div>

            <h3 class="mt-5 font-display text-xl font-black text-neutral-900">
                {{ $step['title'] }}
            </h3>

            <p class="mt-2 text-sm leading-relaxed text-neutral-600">
                {{ $step['desc'] }}
            </p>

            <ul class="mt-4 space-y-2">
                @foreach($step['features'] as $feature)
                    <li class="flex items-start gap-2.5 text-sm text-neutral-700">
                        <span class="mt-0.5 font-bold text-neutral-900">{{ $feature[0] }}</span>
                        <span>{{ $feature[1] }}</span>
                    </li>
                @endforeach
            </ul>

            @if($isLast)
                <a href="{{ $enrollHref }}" class="mt-6 inline-flex w-fit items-center gap-2 rounded-xl bg-neutral-900 px-5 py-2.5 text-sm font-semibold text-white shadow-warm transition hover:bg-neutral-800">
                    Enroll to start
                    <span aria-hidden="true" class="transition-transform group-hover:translate-x-0.5">→</span>
                </a>
            @endif
        </article>
    @endforeach
</div>
