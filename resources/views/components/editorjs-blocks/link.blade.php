@php
    $url = $data['link'] ?? '';
    $title = $data['meta']['title'] ?? '';
    $description = $data['meta']['description'] ?? '';
    $imageUrl = $data['meta']['image']['url'] ?? '';
@endphp

@if ($url)
    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="flex max-w-lg items-stretch overflow-hidden rounded-xl border border-neutral-200 bg-white transition hover:border-neutral-300 hover:shadow-md">
        @if ($imageUrl)
            <div class="flex w-32 shrink-0 items-center bg-neutral-100">
                <img src="{{ $imageUrl }}" alt="" class="h-full w-full object-cover" loading="lazy">
            </div>
        @endif
        <div class="flex flex-col justify-center p-3">
            @if ($title)
                <span class="font-display text-sm font-bold text-neutral-900">{{ $title }}</span>
            @endif
            @if ($description)
                <span class="mt-1 text-xs text-neutral-500 line-clamp-2">{{ $description }}</span>
            @endif
            <span class="mt-1 text-xs text-green-600">{{ parse_url($url, PHP_URL_HOST) }}</span>
        </div>
    </a>
@endif
