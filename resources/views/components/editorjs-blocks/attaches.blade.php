@php
    $url = $data['file']['url'] ?? '';
    $title = $data['file']['title'] ?? $data['title'] ?? '';
    $size = $data['file']['size'] ?? 0;
    $extension = strtoupper($data['file']['extension'] ?? '');
    $fileSize = $size > 0 ? round($size / 1024, 1) . ' KB' : '';
@endphp

@if ($url)
    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 rounded-xl border border-stone-200 bg-white p-4 transition hover:border-stone-300 hover:shadow-md">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <span class="block truncate font-display text-sm font-bold text-stone-900">{{ $title }}</span>
            <span class="text-xs text-stone-500">
                @if ($extension) {{ $extension }}@endif
                @if ($fileSize) — {{ $fileSize }}@endif
            </span>
        </div>
    </a>
@endif
