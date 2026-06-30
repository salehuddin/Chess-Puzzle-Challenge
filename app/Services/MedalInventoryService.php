<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\Fulfillment;
use App\Models\MedalStockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MedalInventoryService
{
    /**
     * Set the initial stock for a challenge. Used when first configuring inventory.
     * Records an `initial` movement and sets the on-hand balance.
     */
    public function setInitialStock(Challenge $challenge, int $quantity, ?string $note = null, ?User $actor = null): Challenge
    {
        return DB::transaction(function () use ($challenge, $quantity, $note, $actor): Challenge {
            $locked = Challenge::query()->lockForUpdate()->findOrFail($challenge->id);

            $previousBalance = (int) $locked->medal_stock_on_hand;
            $newBalance = max(0, $quantity);

            $locked->medal_stock_on_hand = $newBalance;
            $locked->save();

            $this->recordMovement(
                $locked,
                'initial',
                $newBalance - $previousBalance,
                $newBalance,
                reference: null,
                note: $note,
                actor: $actor,
            );

            return $locked->fresh();
        });
    }

    /**
     * Add stock to a challenge (restock from supplier).
     */
    public function restock(Challenge $challenge, int $quantity, ?string $note = null, ?User $actor = null): Challenge
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Restock quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($challenge, $quantity, $note, $actor): Challenge {
            $locked = Challenge::query()->lockForUpdate()->findOrFail($challenge->id);

            $newBalance = (int) $locked->medal_stock_on_hand + $quantity;

            $locked->medal_stock_on_hand = $newBalance;
            $locked->save();

            $this->recordMovement(
                $locked,
                'restock',
                $quantity,
                $newBalance,
                reference: null,
                note: $note,
                actor: $actor,
            );

            return $locked->fresh();
        });
    }

    /**
     * Decrement stock when a fulfillment is dispatched (shipped).
     * Does nothing if stock is already zero (allows shipping to proceed without blocking).
     */
    public function decrementForShipment(Challenge $challenge, Fulfillment $fulfillment, ?User $actor = null): Challenge
    {
        return DB::transaction(function () use ($challenge, $fulfillment, $actor): Challenge {
            $locked = Challenge::query()->lockForUpdate()->findOrFail($challenge->id);

            $current = (int) $locked->medal_stock_on_hand;

            if ($current <= 0) {
                return $locked->fresh();
            }

            $newBalance = $current - 1;

            $locked->medal_stock_on_hand = $newBalance;
            $locked->save();

            $this->recordMovement(
                $locked,
                'shipment',
                -1,
                $newBalance,
                reference: "Fulfillment #{$fulfillment->id}",
                note: null,
                actor: $actor,
            );

            return $locked->fresh();
        });
    }

    /**
     * Set stock to an absolute value (admin correction). Records an adjustment
     * with the signed delta between the new and previous balance.
     */
    public function adjust(Challenge $challenge, int $newBalance, ?string $note = null, ?User $actor = null): Challenge
    {
        return DB::transaction(function () use ($challenge, $newBalance, $note, $actor): Challenge {
            $locked = Challenge::query()->lockForUpdate()->findOrFail($challenge->id);

            $newBalance = max(0, $newBalance);
            $delta = $newBalance - (int) $locked->medal_stock_on_hand;

            $locked->medal_stock_on_hand = $newBalance;
            $locked->save();

            $this->recordMovement(
                $locked,
                'adjustment',
                $delta,
                $newBalance,
                reference: null,
                note: $note,
                actor: $actor,
            );

            return $locked->fresh();
        });
    }

    /**
     * Increment stock when a shipped medal is returned or undelivered.
     */
    public function recordReturn(Challenge $challenge, Fulfillment $fulfillment, ?string $note = null, ?User $actor = null): Challenge
    {
        return DB::transaction(function () use ($challenge, $fulfillment, $note, $actor): Challenge {
            $locked = Challenge::query()->lockForUpdate()->findOrFail($challenge->id);

            $newBalance = (int) $locked->medal_stock_on_hand + 1;

            $locked->medal_stock_on_hand = $newBalance;
            $locked->save();

            $this->recordMovement(
                $locked,
                'return',
                1,
                $newBalance,
                reference: "Fulfillment #{$fulfillment->id}",
                note: $note,
                actor: $actor,
            );

            return $locked->fresh();
        });
    }

    /**
     * @param  'initial'|'restock'|'shipment'|'adjustment'|'return'  $type
     */
    protected function recordMovement(
        Challenge $challenge,
        string $type,
        int $quantity,
        int $balanceAfter,
        ?string $reference = null,
        ?string $note = null,
        ?User $actor = null,
    ): void {
        MedalStockMovement::create([
            'challenge_id' => $challenge->id,
            'type' => $type,
            'quantity' => $quantity,
            'balance_after' => $balanceAfter,
            'reference' => $reference,
            'note' => $note,
            'user_id' => $actor?->id,
        ]);
    }
}
