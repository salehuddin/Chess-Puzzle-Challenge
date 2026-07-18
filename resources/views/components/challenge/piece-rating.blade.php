@props([
    'label' => null,
    'selected' => '0',
    'onPick' => null,
    'size' => 'md',
])

@php
    $sizes = ['sm' => 'w-8 h-8', 'md' => 'w-10 h-10 sm:w-12 sm:h-12', 'lg' => 'w-12 h-12 sm:w-14 sm:h-14'];

    $pieces = [
        1 => '<path d="M22.5 9c-2.21 0-4 1.79-4 4 0 .89.29 1.71.78 2.38C17.33 16.5 16 18.59 16 21c0 2.03.94 3.84 2.41 5.03C15.41 27.09 11 31.42 11 39h23c0-7.58-4.41-11.91-7.41-12.97C28.06 24.84 29 23.03 29 21c0-2.41-1.33-4.5-3.28-5.62.49-.67.78-1.49.78-2.38 0-2.21-1.79-4-4-4z" fill="currentColor"/>',
        2 => '<g fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10c10.5 1 16.5 8 16 29H15c0-9 10-6.5 8-21" fill="currentColor"/><path d="M24 18c.5 2.5-1 4-1 4" /><path d="M20.5 13.5c.5 2.5-1 4-1 4" /><path d="M15 25c0-2.5 2-4 2-4" /><path d="M14.5 31.5c1.5-5 5.5-5 6 0" /><path d="M32 14c-1.5 3-3 7-3 7" /><path d="M30 22c0 3-2 5-2 5" /><path d="M27 29c0 2-1 3-1 3" /></g><path d="M12 39h21v3H12z" fill="currentColor"/>',
        3 => '<g fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 36c12.5-3.5 16-13 16-13s3.5 9.5 16 13" fill="currentColor"/><path d="M9 36c0-3 1-5 1-5" /><path d="M16 31c0-3 2-5 2-5" /><path d="M23 28c0-3 2-5 2-5" /><path d="M30 31c0-3 2-5 2-5" /><path d="M36 36c0-3 1-5 1-5" /><path d="M12 17.5c4-3.5 9-3.5 13.5 2.5 1 1.5 2 5.5 2 5.5" /></g><path d="M11 38h23v2H11z" fill="currentColor"/>',
        4 => '<g fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 36h27v3H9z" fill="currentColor"/><path d="M14 32.5c1-3 4-6 4-6s-3-3-3-5 2-4 2-4-2-2-2-5 3-5 3-5" /><path d="M22 32.5c1-3 4-6 4-6s-3-3-3-5 2-4 2-4-2-2-2-5 3-5 3-5" /><path d="M30 32.5c1-3 4-6 4-6s-3-3-3-5 2-4 2-4" /></g><path d="M11 7h23v3H11z" fill="currentColor"/><rect x="13.5" y="2" width="3" height="5" fill="currentColor"/><rect x="21" y="2" width="3" height="5" fill="currentColor"/><rect x="28.5" y="2" width="3" height="5" fill="currentColor"/>',
        5 => '<g fill="currentColor" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="12" r="2.5" /><circle cx="14" cy="9" r="2.5" /><circle cx="22.5" cy="8" r="2.5" /><circle cx="31" cy="9" r="2.5" /><circle cx="39" cy="12" r="2.5" /><path d="M9 26c8.5-1.5 21-1.5 27 0 2.5 8 2.5 9 0 10-8.5 1.5-18.5 1.5-27 0-2.5-1-2.5-2 0-10z" /><path d="M9 26c0-2 1-4 1-4s3 2 4 0c0 0 1 4 0 4" /><path d="M36 26c0-2-1-4-1-4s-3 2-4 0c0 0-1 4 0 4" /><path d="M9 36c8.5-1.5 21-1.5 27 0v3H9z" /></g>',
    ];
@endphp

<div class="inline-flex items-center gap-1.5 sm:gap-2" x-data="{ hover: 0 }">
    @if($label)
        <span class="sr-only">{{ $label }}</span>
    @endif

    @for($i = 1; $i <= 5; $i++)
        <button
            type="button"
            @mouseover="hover = {{ $i }}"
            @mouseleave="hover = 0"
            @if($onPick) @click="{{ $onPick }}({{ $i }})" @endif
            class="transition-transform duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-brand/40 rounded-md"
            aria-label="Rate {{ $i }} of 5"
        >
            <svg
                viewBox="0 0 45 45"
                class="{{ $sizes[$size] }} transition-colors duration-200"
                :class="hover > 0 ? (hover >= {{ $i }} ? 'text-brand' : 'text-neutral-300') : ({{ $selected }} >= {{ $i }} ? 'text-brand' : 'text-neutral-300')"
                fill="currentColor"
            >
                {!! $pieces[$i] !!}
            </svg>
        </button>
    @endfor
</div>
