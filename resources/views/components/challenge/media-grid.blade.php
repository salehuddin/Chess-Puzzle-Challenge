{{--
    Image gallery grid for challenge image_gallery.

    Props:
        images (array<int,string>) — list of image URLs.
        alt    (string)            — base alt text for each image (suffix with index).
        columns (string)           — Tailwind grid-cols class for lg breakpoint (default 'lg:grid-cols-3').
--}}
@props([
    'images' => [],
    'alt' => 'Gallery image',
    'columns' => 'lg:grid-cols-3',
])

@php
    $images = array_values(array_filter($images, fn ($i) => is_string($i) && trim($i) !== ''));
@endphp

@if($images !== [])
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 {{ $columns }}">
        @foreach($images as $i => $src)
            <figure class="group relative overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-warm">
                <img
                    src="{{ $src }}"
                    alt="{{ $alt }} #{{ $i + 1 }}"
                    loading="lazy"
                    class="h-64 w-full object-cover transition duration-500 group-hover:scale-105 sm:h-72"
                >
                <figcaption class="absolute inset-x-0 bottom-0 translate-y-full bg-gradient-to-t from-stone-900/80 to-transparent p-4 text-xs font-semibold text-white transition duration-300 group-hover:translate-y-0">
                    {{ $alt }} #{{ $i + 1 }}
                </figcaption>
            </figure>
        @endforeach
    </div>
@endif
