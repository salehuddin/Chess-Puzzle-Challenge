{{--
    Grid of embeddable videos for the challenge page.

    Props:
        videos (array<int, array{title:string, url:string, embed_url:string}>) — list of videos.

    Renders nothing if empty.
--}}
@props([
    'videos' => [],
])

@if($videos !== [])
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
                <div class="p-5">
                    <h3 class="text-lg font-bold text-stone-900">{{ $video['title'] }}</h3>
                    <a href="{{ $video['url'] }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-primary hover:underline">
                        Open source video
                        <span aria-hidden="true">↗</span>
                    </a>
                </div>
            </article>
        @endforeach
    </div>
@endif
