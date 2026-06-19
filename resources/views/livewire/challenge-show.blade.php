<x-slot name="title">{{ $challenge->name }} — Chess Puzzle Challenge</x-slot>

@php
    $levelData = match (true) {
        str_contains(strtolower($challenge->name), 'beginner') => ['🌱', 'Beginner', 'badge-success'],
        str_contains(strtolower($challenge->name), 'intermediate') => ['⚡', 'Intermediate', 'badge-warning'],
        str_contains(strtolower($challenge->name), 'advanced') => ['🔥', 'Advanced', 'badge-error'],
        default => ['♟', 'Challenge', 'badge-primary'],
    };

    $rules = is_array($challenge->rules) ? $challenge->rules : [];
    $puzzleTotal = max((int) ($challenge->puzzles_count ?? $challenge->puzzle_count ?? 0), 0);
    $timeLimit = (int) ($rules['time_limit_minutes'] ?? 0);
    $orderLabel = ($rules['order'] ?? 'sequential') === 'sequential' ? 'Sequential order' : 'Free order';
    $descriptionHtml = (string) ($challenge->description ?? '');
    $hasDescription = trim(strip_tags($descriptionHtml)) !== '';
@endphp

<div class="bg-base-200">
    <section class="relative overflow-hidden bg-stone-900 text-white">
        @if($posterImageUrl)
            <img src="{{ $posterImageUrl }}" alt="{{ $challenge->name }} poster" class="absolute inset-0 h-full w-full object-cover opacity-35">
        @endif

        <div class="absolute inset-0 bg-gradient-to-r from-stone-950/90 via-stone-900/80 to-stone-900/70"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="max-w-3xl">
                <a href="{{ route('challenges.index') }}" class="mb-5 inline-flex items-center gap-2 text-sm font-semibold text-amber-200/90 transition hover:text-amber-100">
                    <span>←</span>
                    <span>Back to Challenges</span>
                </a>

                <div class="mb-4 flex flex-wrap items-center gap-3">
                    <span class="badge {{ $levelData[2] }} gap-1 font-semibold">
                        {{ $levelData[0] }} {{ $levelData[1] }}
                    </span>
                    <span class="badge badge-outline border-white/30 text-white/90">{{ $puzzleTotal }} puzzles</span>
                    <span class="badge badge-outline border-white/30 text-white/90">{{ $orderLabel }}</span>
                </div>

                <h1 class="font-display text-4xl font-black leading-tight text-white lg:text-6xl">{{ $challenge->name }}</h1>

                @if($hasDescription)
                    <div class="prose prose-invert mt-5 max-w-2xl text-base leading-relaxed lg:text-lg">
                        {!! $descriptionHtml !!}
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="card border border-stone-200 bg-white shadow-warm">
                <div class="card-body">
                    <p class="text-xs font-semibold uppercase tracking-widest text-stone-400">Puzzles</p>
                    <p class="text-4xl font-black text-stone-900">{{ $puzzleTotal }}</p>
                    <p class="text-sm text-stone-500">Curated positions to solve.</p>
                </div>
            </div>

            <div class="card border border-stone-200 bg-white shadow-warm">
                <div class="card-body">
                    <p class="text-xs font-semibold uppercase tracking-widest text-stone-400">Rules</p>
                    <p class="text-xl font-black text-stone-900">{{ $orderLabel }}</p>
                    @if($timeLimit > 0)
                        <p class="text-sm text-stone-500">Time limit: {{ $timeLimit }} minutes</p>
                    @else
                        <p class="text-sm text-stone-500">No overall time cap configured.</p>
                    @endif
                </div>
            </div>

            <div class="card border border-stone-200 bg-white shadow-warm">
                <div class="card-body">
                    <p class="text-xs font-semibold uppercase tracking-widest text-stone-400">Price</p>
                    <p class="text-3xl font-black text-stone-900">MYR {{ number_format((float) $challenge->price_myr, 2) }}</p>
                    <p class="text-sm text-stone-500">or USD {{ number_format((float) $challenge->price_usd, 2) }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-8 sm:px-6 lg:px-8">
        @php
            $enrollUrl = route('challenges.enroll', ['challenge' => $challenge], absolute: false);
        @endphp

        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 shadow-warm lg:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-amber-700">Enrollment</p>
                    <h2 class="mt-1 font-display text-2xl font-black text-stone-900">Join this challenge</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-stone-600">
                        Create an account or sign in to enroll. Admin users can enroll instantly without payment.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    @guest
                        <a href="{{ route('register', ['redirect_to' => $enrollUrl]) }}" class="btn btn-primary gap-2">
                            Register to Enroll
                        </a>
                        <a href="{{ route('login', ['redirect_to' => $enrollUrl]) }}" class="btn btn-outline btn-primary gap-2">
                            Sign In
                        </a>
                    @else
                        <a href="{{ $enrollUrl }}" class="btn btn-primary gap-2">
                            {{ auth()->user()->isAdmin() ? 'Enroll as Admin' : 'Enroll Now' }}
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    @if($videos !== [])
        <section class="mx-auto max-w-7xl px-4 pb-6 sm:px-6 lg:px-8">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="font-display text-3xl font-black text-stone-900">Videos</h2>
                <span class="text-sm font-medium text-stone-500">{{ count($videos) }} clips</span>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                @foreach($videos as $video)
                    <article class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-warm" wire:key="video-{{ md5($video['url']) }}">
                        <div class="aspect-video w-full bg-stone-100">
                            <iframe
                                src="{{ $video['embed_url'] }}"
                                title="{{ $video['title'] }}"
                                loading="lazy"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                                referrerpolicy="strict-origin-when-cross-origin"
                                class="h-full w-full border-0"
                            ></iframe>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-bold text-stone-900">{{ $video['title'] }}</h3>
                            <a href="{{ $video['url'] }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-primary hover:underline">
                                Open source video
                                <span>↗</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if($imageGallery !== [])
        <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="font-display text-3xl font-black text-stone-900">Challenge Images</h2>
                <span class="text-sm font-medium text-stone-500">{{ count($imageGallery) }} images</span>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($imageGallery as $image)
                    <figure class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-warm" wire:key="gallery-{{ md5($image) }}">
                        <img src="{{ $image }}" alt="{{ $challenge->name }} gallery image" loading="lazy" class="h-64 w-full object-cover transition duration-300 hover:scale-105">
                    </figure>
                @endforeach
            </div>
        </section>
    @endif

    @if($medalImages !== [])
        <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="font-display text-3xl font-black text-stone-900">Medal Gallery</h2>
                <span class="text-sm font-medium text-stone-500">{{ count($medalImages) }} images</span>
            </div>

            <div class="grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-4">
                @foreach($medalImages as $image)
                    <figure class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-warm" wire:key="medal-{{ md5($image) }}">
                        <img src="{{ $image }}" alt="{{ $challenge->name }} medal" loading="lazy" class="h-48 w-full object-cover transition duration-300 hover:scale-105">
                    </figure>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h2 class="mb-5 font-display text-3xl font-black text-stone-900">Challenge Details</h2>

        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-warm lg:p-8">
            @if($contentBlocks !== [])
                <article class="space-y-6">
                    @foreach($contentBlocks as $index => $block)
                        @php
                            $type = $block['type'] ?? '';
                            $data = is_array($block['data'] ?? null) ? $block['data'] : [];
                            $tag = ($data['level'] ?? 2) >= 3 ? 'h3' : 'h2';
                        @endphp

                        @if($type === 'header')
                            @if($tag === 'h3')
                                <h3 class="font-display text-2xl font-black text-stone-900" wire:key="content-block-{{ $index }}">{{ strip_tags((string) ($data['text'] ?? '')) }}</h3>
                            @else
                                <h2 class="font-display text-3xl font-black text-stone-900" wire:key="content-block-{{ $index }}">{{ strip_tags((string) ($data['text'] ?? '')) }}</h2>
                            @endif
                        @elseif($type === 'paragraph')
                            <p class="text-base leading-relaxed text-stone-700" wire:key="content-block-{{ $index }}">{{ strip_tags((string) ($data['text'] ?? '')) }}</p>
                        @elseif($type === 'list')
                            @php
                                $items = is_array($data['items'] ?? null) ? $data['items'] : [];
                                $style = ($data['style'] ?? 'unordered') === 'ordered' ? 'list-decimal' : 'list-disc';
                            @endphp
                            <ul class="ml-6 space-y-2 {{ $style }} text-stone-700" wire:key="content-block-{{ $index }}">
                                @foreach($items as $item)
                                    <li>{{ strip_tags((string) $item) }}</li>
                                @endforeach
                            </ul>
                        @elseif($type === 'delimiter')
                            <hr class="border-stone-200" wire:key="content-block-{{ $index }}">
                        @endif
                    @endforeach
                </article>
            @elseif($hasDescription)
                <article class="prose max-w-none prose-stone text-base leading-relaxed">
                    {!! $descriptionHtml !!}
                </article>
            @else
                <p class="text-base text-stone-500">No additional challenge content has been added yet.</p>
            @endif
        </div>
    </section>
</div>
