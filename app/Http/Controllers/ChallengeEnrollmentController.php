<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\Enrollment;
use App\Models\Order;
use App\Services\CommerceHierarchyService;
use App\Services\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChallengeEnrollmentController extends Controller
{
    public function __invoke(Request $request, Challenge $challenge): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('register', [
                'redirect_to' => route('challenges.enroll', $challenge, absolute: false),
            ]);
        }

        $existing = Enrollment::query()
            ->whereBelongsTo($user)
            ->where('challenge_id', $challenge->id)
            ->first();

        if ($existing) {
            if ($existing->status === 'active') {
                return redirect()->route('play', $existing);
            }

            return redirect()->route('orders.track', $existing);
        }

        $orderStatus = $user->isAdmin() ? 'paid' : 'pending';

        $order = null;

        DB::transaction(function () use ($user, $challenge, $orderStatus, &$order) {
            $order = Order::create([
                'user_id' => $user->id,
                'status' => $orderStatus,
                'currency' => 'USD',
                'subtotal_amount' => (float) ($challenge->price_usd ?? 0),
                'total_amount' => (float) ($challenge->price_usd ?? 0),
            ]);

            app(CommerceHierarchyService::class)->syncFromSelections($order, [
                ['item_type' => 'challenge', 'item_id' => $challenge->id],
            ]);
        });

        if ($user->isAdmin()) {
            $enrollment = Enrollment::query()
                ->whereBelongsTo($user)
                ->where('challenge_id', $challenge->id)
                ->first();

            if ($enrollment) {
                return redirect()->route('play', $enrollment);
            }
        }

        if (Settings::isPaymentSandbox() && $order && $order->status === 'pending') {
            return redirect()->route('checkout.show', $order)
                ->with('status', 'Please complete the sandbox payment to unlock your challenge.');
        }

        return redirect()->route('dashboard')
            ->with('status', 'Your enrollment has been created. Complete payment to start playing.');
    }
}
