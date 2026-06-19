<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CommerceHierarchyService;
use App\Services\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(Request $request, Order $order): View|RedirectResponse
    {
        if ($order->user_id !== $request->user()?->id) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return $this->redirectAfterPayment($order);
        }

        if (! Settings::isPaymentSandbox()) {
            return redirect()->route('dashboard')
                ->with('status', 'Sandbox payments are currently disabled. Please contact support to complete your order.');
        }

        $order->load('items');

        return view('checkout.show', [
            'order' => $order,
        ]);
    }

    public function pay(Request $request, Order $order): RedirectResponse
    {
        if ($order->user_id !== $request->user()?->id) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return $this->redirectAfterPayment($order);
        }

        if (! Settings::isPaymentSandbox()) {
            return redirect()->route('dashboard')
                ->with('status', 'Sandbox payments are currently disabled. Please contact support to complete your order.');
        }

        DB::transaction(function () use ($order): void {
            $order->status = 'paid';
            $order->payment_provider = 'sandbox';
            $order->paid_at = now();
            $order->save();

            app(CommerceHierarchyService::class)->syncFromOrder($order);
        });

        return $this->redirectAfterPayment($order)
            ->with('status', 'Sandbox payment successful! Your challenge is now unlocked.');
    }

    protected function redirectAfterPayment(Order $order): RedirectResponse
    {
        $order->load('items.enrollments');

        $enrollment = $order->items
            ->flatMap(fn ($item) => $item->enrollments)
            ->first();

        if ($enrollment) {
            return redirect()->route('play', $enrollment);
        }

        return redirect()->route('dashboard');
    }
}
