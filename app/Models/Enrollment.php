<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'challenge_id',
    'order_item_id',
    'status',
    'activated_at',
    'completed_at',
])]
class Enrollment extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function fulfillment(): HasOne
    {
        return $this->hasOne(Fulfillment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    /**
     * The earned sticker for this enrollment's challenge (if any).
     *
     * Stickers are keyed by (user_id, challenge_id) rather than enrollment_id,
     * so we scope the relation to the matching user+challenge pair and require
     * `unlocked_at` to be set (a row with `unlocked_at = null` is a "locked"
     * placeholder that shouldn't surface as an earned sticker here).
     */
    public function sticker(): HasOne
    {
        return $this->hasOne(Sticker::class, 'challenge_id', 'challenge_id')
            ->where('user_id', $this->user_id)
            ->whereNotNull('unlocked_at');
    }

    /**
     * This user's puzzle-by-puzzle solve progress for this enrollment's challenge.
     */
    public function puzzleProgress(): HasMany
    {
        return $this->hasMany(PuzzleProgress::class, 'challenge_id', 'challenge_id')
            ->where('user_id', $this->user_id)
            ->whereNotNull('solved_at');
    }

    /**
     * Single source of truth for the enrollment's display status.
     *
     * Consolidates the triply-duplicated logic that previously lived in
     * Dashboard::buildCards(), OrderTracking::buildTrackingData(), and
     * ChallengeShow::loadUserEnrollment() into one method on the model.
     *
     * Returns one of: 'pending', 'active', 'completed', 'medal_pending',
     * 'preparing', 'shipped'.
     */
    public function derivedStatus(): string
    {
        $order = $this->orderItem?->order;

        if ($order?->status === 'pending') {
            return 'pending';
        }

        $fulfillment = $this->fulfillment;

        return match (true) {
            in_array($fulfillment?->status, ['shipped', 'delivered'], true) => 'shipped',
            $fulfillment?->status === 'ready_to_ship' => 'preparing',
            $this->status === 'completed' && $fulfillment?->status === 'pending' => 'medal_pending',
            $this->status === 'completed' => 'completed',
            default => 'active',
        };
    }
}
