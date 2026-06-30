<div class="space-y-3">
    @if($movements->isEmpty())
        <x-filament::section>
            <p class="text-sm text-gray-500">No stock movements recorded yet for {{ $challenge->name }}.</p>
        </x-filament::section>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-gray-500 border-b border-gray-200">
                        <th class="py-2 pr-4">Date</th>
                        <th class="py-2 pr-4">Type</th>
                        <th class="py-2 pr-4 text-right">Qty</th>
                        <th class="py-2 pr-4 text-right">Balance</th>
                        <th class="py-2 pr-4">Reference</th>
                        <th class="py-2 pr-4">Note</th>
                        <th class="py-2">By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($movements as $movement)
                        @php
                            $typeColor = match($movement->type) {
                                'initial' => 'gray',
                                'restock' => 'success',
                                'shipment' => 'warning',
                                'adjustment' => 'info',
                                'return' => 'primary',
                                default => 'gray',
                            };
                            $qtyClass = $movement->quantity >= 0 ? 'text-emerald-600' : 'text-red-600';
                        @endphp
                        <tr>
                            <td class="py-2 pr-4 whitespace-nowrap text-gray-600">{{ $movement->created_at->format('M j, Y g:i A') }}</td>
                            <td class="py-2 pr-4">
                                <x-filament::badge color="{{ $typeColor }}">
                                    {{ ucfirst($movement->type) }}
                                </x-filament::badge>
                            </td>
                            <td class="py-2 pr-4 text-right font-mono font-semibold {{ $qtyClass }}">
                                {{ $movement->quantity >= 0 ? '+' : '' }}{{ $movement->quantity }}
                            </td>
                            <td class="py-2 pr-4 text-right font-mono">{{ $movement->balance_after }}</td>
                            <td class="py-2 pr-4 text-gray-600">{{ $movement->reference ?? '-' }}</td>
                            <td class="py-2 pr-4 text-gray-600">{{ $movement->note ?? '-' }}</td>
                            <td class="py-2 text-gray-600">{{ $movement->user?->name ?? 'System' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
