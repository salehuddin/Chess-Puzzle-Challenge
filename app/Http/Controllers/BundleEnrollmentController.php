<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\Order;
use App\Services\CommerceHierarchyService;
use App\Services\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BundleEnrollmentController extends Controller
{
    public function __invoke(Request $request, Bundle $bundle): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('register', [
                'redirect_to' => route('bundles.enroll', $bundle, absolute: false),
            ]);
        }

        $orderStatus = $user->isAdmin() ? 'paid' : 'pending';
        $totalAmount = (float) ($bundle->price_usd ?? 0);
        $order = null;

        DB::transaction(function () use ($user, $bundle, $orderStatus, $totalAmount, &$order) {
            $order = Order::create([
                'user_id' => $user->id,
                'status' => $orderStatus,
                'currency' => 'USD',
                'subtotal_amount' => $totalAmount,
                'total_amount' => $totalAmount,
            ]);

            app(CommerceHierarchyService::class)->syncFromSelections($order, [
                ['item_type' => 'bundle', 'item_id' => $bundle->id],
            ]);
        });

        if ($orderStatus === 'paid') {
            return redirect()->route('dashboard')
                ->with('status', 'Bundle purchased! Your challenges are ready to play.');
        }

        if (Settings::isPaymentSandbox() && $order && $order->status === 'pending') {
            return redirect()->route('checkout.show', $order)
                ->with('status', 'Please complete the sandbox payment to unlock your bundle.');
        }

        return redirect()->route('dashboard')
            ->with('status', 'Your bundle order has been created. Complete payment to unlock all challenges.');
    }
}
