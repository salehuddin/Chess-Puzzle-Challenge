@php
    $embedUrl = $data['embed'] ?? '';
    $caption = $data['caption'] ?? '';
    $width = $data['width'] ?? 0;
    $height = $data['height'] ?? 0;
@endphp

@if ($embedUrl)
    <figure class="overflow-hidden rounded-xl">
        <div class="aspect-video w-full">
            <iframe
                src="{{ $embedUrl }}"
                @if ($width) width="{{ $width }}" @endif
                @if ($height) height="{{ $height }}" @endif
                class="h-full w-full border-0"
                allowfullscreen
                loading="lazy"
            ></iframe>
        </div>
        @if ($caption)
            <figcaption class="mt-2 text-center text-sm text-stone-500">{!! $caption !!}</figcaption>
        @endif
    </figure>
@endif
