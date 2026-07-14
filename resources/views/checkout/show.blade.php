<x-app-layout>
    <x-slot name="title">Checkout — {{ config('app.name') }}</x-slot>

    <div class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold font-serif text-green-900 mb-2">Checkout</h1>
        <p class="text-neutral-600 mb-8">Review your order and complete the sandbox payment to unlock your challenge.</p>

        <div class="card bg-white shadow-xl border border-neutral-100 mb-8">
            <div class="card-body">
                <h2 class="card-title text-xl font-serif text-neutral-900 mb-4">Order Summary</h2>

                <div class="divide-y divide-neutral-100">
                    @foreach($order->items as $item)
                        <div class="py-4 flex justify-between items-start gap-4">
                            <div>
                                <p class="font-semibold text-neutral-900">{{ $item->name_snapshot }}</p>
                                <p class="text-sm text-neutral-500">SKU: {{ $item->sku_snapshot }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-neutral-900">{{ number_format((float) $item->line_total, 2) }} {{ $order->currency }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-neutral-200 mt-4">
                    <span class="text-lg font-bold text-neutral-900">Total</span>
                    <span class="text-2xl font-black text-green-700">{{ number_format((float) $order->total_amount, 2) }} {{ $order->currency }}</span>
                </div>
            </div>
        </div>

        <div class="card bg-orange-50 border border-orange-200 shadow-md mb-8">
            <div class="card-body">
                <h3 class="card-title text-lg text-orange-900">Sandbox Payment Mode</h3>
                <p class="text-sm text-neutral-600 mb-4">No real money will be charged. Click the button below to simulate a successful payment.</p>

                <form method="POST" action="{{ route('checkout.pay', $order) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-lg w-full">
                        Pay {{ number_format((float) $order->total_amount, 2) }} {{ $order->currency }} with Sandbox
                    </button>
                </form>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('dashboard') }}" class="text-sm text-neutral-500 hover:text-neutral-700 underline">Return to dashboard</a>
        </div>
    </div>
</x-app-layout>
