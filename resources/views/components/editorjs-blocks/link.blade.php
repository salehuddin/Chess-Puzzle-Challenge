@php
    $url = $data['link'] ?? '';
    $title = $data['meta']['title'] ?? '';
    $description = $data['meta']['description'] ?? '';
    $imageUrl = $data['meta']['image']['url'] ?? '';
@endphp

@if ($url)
    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="flex max-w-lg items-stretch overflow-hidden rounded-xl border border-stone-200 bg-white transition hover:border-stone-300 hover:shadow-md">
        @if ($imageUrl)
            <div class="flex w-32 shrink-0 items-center bg-stone-100">
                <img src="{{ $imageUrl }}" alt="" class="h-full w-full object-cover" loading="lazy">
            </div>
        @endif
        <div class="flex flex-col justify-center p-3">
            @if ($title)
                <span class="font-display text-sm font-bold text-stone-900">{{ $title }}</span>
            @endif
            @if ($description)
                <span class="mt-1 text-xs text-stone-500 line-clamp-2">{{ $description }}</span>
            @endif
            <span class="mt-1 text-xs text-emerald-600">{{ parse_url($url, PHP_URL_HOST) }}</span>
        </div>
    </a>
@endif
