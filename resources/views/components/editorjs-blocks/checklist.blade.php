<ul class="space-y-2">
    @foreach ($data['items'] ?? [] as $item)
        <li class="flex items-start gap-2 text-neutral-700">
            @if ($item['checked'])
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            @else
                <span class="mt-0.5 block h-5 w-5 shrink-0 rounded border border-neutral-300"></span>
            @endif
            <span class="text-base leading-relaxed">{{ $item['text'] }}</span>
        </li>
    @endforeach
</ul>
