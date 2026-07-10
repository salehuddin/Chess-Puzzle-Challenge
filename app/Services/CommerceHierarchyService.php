<?php

namespace App\Services;

use App\Models\Bundle;
use App\Models\Challenge;
use App\Models\Enrollment;
use App\Models\Fulfillment;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class CommerceHierarchyService
{
    /**
     * @param  array<int, array{item_type: string, item_id: int}>  $selections
     */
    public function syncFromSelections(Order $order, array $selections): void
    {
        DB::transaction(function () use ($order, $selections): void {
            foreach ($selections as $selection) {
                $itemType = $selection['item_type'];
                $itemId = $selection['item_id'];

                [$purchasable, $challengeIds] = $this->resolvePurchasable($itemType, $itemId);

                $order->items()->create([
                    'item_type' => $itemType,
                    'item_id' => $purchasable->getKey(),
                    'name_snapshot' => (string) $purchasable->name,
                    'sku_snapshot' => $purchasable->sku ?? ($itemType.':'.$purchasable->getKey()),
                    'unit_price' => (float) ($purchasable->price_usd ?? 0),
                    'quantity' => 1,
                    'line_total' => (float) ($purchasable->price_usd ?? 0),
                    'meta' => array_filter([
                        'source' => 'admin-order-flow',
                        'challenge_ids' => $challengeIds,
                    ]),
                ]);
            }

            $this->syncFromOrder($order->fresh('items'));
        });
    }

    public function syncFromSelection(Order $order, string $itemType, int $itemId): void
    {
        $this->syncFromSelections($order, [
            ['item_type' => $itemType, 'item_id' => $itemId],
        ]);
    }

    public function syncFromOrder(Order $order): void
    {
        if ($order->status !== 'paid') {
            return;
        }

        $order->loadMissing('items');

        DB::transaction(function () use ($order): void {
            foreach ($order->items as $orderItem) {
                foreach ($this->resolveChallengesForOrderItem($orderItem) as $challengeId) {
                    $enrollment = Enrollment::query()->firstOrNew([
                        'user_id' => $order->user_id,
                        'challenge_id' => $challengeId,
                    ]);

                    $enrollment->fill([
                        'order_item_id' => $orderItem->id,
                        'status' => $enrollment->status ?: 'active',
                        'activated_at' => $enrollment->activated_at ?: ($order->paid_at ?? now()),
                    ]);

                    $enrollment->save();

                    $this->syncFulfillmentForEnrollment($enrollment);
                }
            }
        });
    }

    public function syncFulfillmentForEnrollment(Enrollment $enrollment): void
    {
        if ($enrollment->status !== 'completed') {
            return;
        }

        $enrollment->loadMissing('user', 'fulfillment');

        $fulfillment = Fulfillment::query()->firstOrNew([
            'enrollment_id' => $enrollment->id,
        ]);

        if (! $fulfillment->exists) {
            $fulfillment->status = 'ready_to_ship';
        } elseif ($fulfillment->status === 'pending') {
            $fulfillment->status = 'ready_to_ship';
        }

        if (blank($fulfillment->address_snapshot) && $enrollment->user) {
            $fulfillment->address_snapshot = $enrollment->user->addressSnapshot();
        }

        $fulfillment->save();
    }

    /**
     * @return array{0: Challenge|Bundle, 1: array<int, int>}
     */
    protected function resolvePurchasable(string $itemType, int $itemId): array
    {
        if ($itemType === 'bundle') {
            $bundle = Bundle::query()
                ->with('challenges:id')
                ->findOrFail($itemId);

            return [$bundle, $bundle->challenges->pluck('id')->all()];
        }

        $challenge = Challenge::query()->findOrFail($itemId);

        return [$challenge, [$challenge->id]];
    }

    /**
     * @return array<int, int>
     */
    protected function resolveChallengesForOrderItem(OrderItem $orderItem): array
    {
        if ($orderItem->item_type === 'bundle') {
            return Bundle::query()
                ->findOrFail($orderItem->item_id)
                ->challenges()
                ->pluck('challenges.id')
                ->all();
        }

        return [$orderItem->item_id];
    }
}
