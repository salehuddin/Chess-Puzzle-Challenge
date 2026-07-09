@php
    $alignment = $data['alignment'] ?? 'left';
    $alignClass = $alignment === 'center' ? 'text-center mx-auto' : '';
@endphp

<blockquote class="border-l-4 border-stone-300 pl-4 {{ $alignClass }}">
    <p class="font-display text-lg font-medium italic text-stone-800">{!! $data['text'] !!}</p>
    @if (! empty($data['caption']))
        <footer class="mt-1 text-sm text-stone-500">{!! $data['caption'] !!}</footer>
    @endif
</blockquote>
