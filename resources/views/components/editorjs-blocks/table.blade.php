@php
    $rows = $data['content'] ?? [];
@endphp

@if (! empty($rows))
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <tbody>
                @foreach ($rows as $rowIndex => $row)
                    <tr class="@if ($rowIndex % 2 === 0) bg-stone-50 @endif border-b border-stone-200">
                        @foreach ($row as $cell)
                            <td class="px-4 py-2 text-stone-700">{!! $cell !!}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
