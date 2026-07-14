{{--
    FAQ accordion (Alpine).

    Props:
        items (array<int, array{question: string, answer: string}>) — list of FAQ entries.
        defaultOpen (int) — index that should be open on first render (-1 = none, default).

    Renders nothing if `$items` is empty.
--}}
@props([
    'items' => [],
    'defaultOpen' => -1,
])

@php
    $items = array_values(array_filter(
        $items,
        fn ($i) => is_array($i) && trim((string) ($i['question'] ?? '')) !== '' && trim((string) ($i['answer'] ?? '')) !== ''
    ));
@endphp

@if($items !== [])
    <div
        x-data="{ open: {{ (int) $defaultOpen }} }"
        class="divide-y divide-neutral-200 overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-warm"
    >
        @foreach($items as $i => $item)
            <div class="group" wire:key="faq-{{ md5($item['question']) }}">
                <button
                    type="button"
                    x-on:click="open === {{ $i }} ? open = -1 : open = {{ $i }}"
                    :aria-expanded="open === {{ $i }}"
                    class="flex w-full items-center justify-between gap-4 px-5 py-5 text-left transition hover:bg-orange-50/50 sm:px-7"
                >
                    <span class="font-display text-base font-bold text-neutral-900 sm:text-lg">
                        {{ $item['question'] }}
                    </span>
                    <span
                        :class="open === {{ $i }} ? 'rotate-180 bg-orange-100 text-orange-700' : 'rotate-0 bg-base-200 text-neutral-500'"
                        class="grid h-9 w-9 shrink-0 place-items-center rounded-full transition-all duration-300"
                        aria-hidden="true"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </span>
                </button>
                <div
                    x-show="open === {{ $i }}"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="px-5 pb-5 sm:px-7"
                >
                    <p class="prose prose-neutral max-w-none text-sm leading-relaxed text-neutral-600 sm:text-base">
                        {{ $item['answer'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
@endif
