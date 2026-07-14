@php
    $url = $data['file']['url'] ?? '';
    $caption = $data['caption'] ?? '';
    $stretched = $data['stretched'] ?? false;
    $withBackground = $data['withBackground'] ?? false;
@endphp

@if ($url)
    <figure class="@if ($stretched) w-full @else max-w-2xl @endif @if ($withBackground) rounded-lg bg-neutral-100 p-4 @endif">
        <img src="{{ $url }}" alt="{{ $caption }}" class="w-full rounded-lg @if ($data['withBorder'] ?? false) border border-neutral-200 @endif" loading="lazy">
        @if ($caption)
            <figcaption class="mt-2 text-center text-sm text-neutral-500">{!! $caption !!}</figcaption>
        @endif
    </figure>
@endif
