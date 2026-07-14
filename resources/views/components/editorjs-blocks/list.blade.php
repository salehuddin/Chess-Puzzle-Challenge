@php
    $style = $data['style'] ?? 'unordered';
    $listTag = $style === 'ordered' ? 'ol' : 'ul';
    $listClass = $style === 'ordered' ? 'list-decimal' : 'list-disc';
@endphp

<{{ $listTag }} class="ml-6 space-y-2 {{ $listClass }} text-neutral-700">
    @foreach ($data['items'] ?? [] as $item)
        <li>
            {!! $item['content'] !!}
            @if (! empty($item['items']))
                <{{ $listTag }} class="ml-6 mt-2 space-y-2 {{ $listClass }}">
                    @foreach ($item['items'] as $subItem)
                        <li>{!! $subItem['content'] !!}</li>
                    @endforeach
                </{{ $listTag }}>
            @endif
        </li>
    @endforeach
</{{ $listTag }}>
