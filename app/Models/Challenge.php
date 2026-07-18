<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'sku',
    'status',
    'description',
    'meta_title',
    'meta_description',
    'medal_artwork',
    'sticker_artwork',
    'poster_image',
    'medal_images',
    'content_blocks',
    'image_gallery',
    'videos',
    'terms_and_conditions',
    'faq',
    'rules',
    'price_usd',
    'price_myr',
    'is_active',
    'medal_weight',
    'medal_length',
    'medal_width',
    'medal_height',
    'medal_stock_on_hand',
    'medal_reorder_threshold',
    'medal_reorder_quantity',
])]
class Challenge extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected static function booted(): void
    {
        static::created(function (Challenge $challenge) {
            if (blank($challenge->sku)) {
                $challenge->sku = sprintf('CHAL-%05d', $challenge->id);
                $challenge->saveQuietly();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'sku' => 'string',
            'status' => 'string',
            'rules' => 'array',
            'medal_images' => 'array',
            'content_blocks' => 'array',
            'image_gallery' => 'array',
            'videos' => 'array',
            'faq' => 'array',
            'price_usd' => 'decimal:2',
            'price_myr' => 'decimal:2',
            'is_active' => 'boolean',
            'medal_weight' => 'decimal:2',
            'medal_length' => 'decimal:2',
            'medal_width' => 'decimal:2',
            'medal_height' => 'decimal:2',
            'medal_stock_on_hand' => 'integer',
            'medal_reorder_threshold' => 'integer',
            'medal_reorder_quantity' => 'integer',
        ];
    }

    /**
     * The puzzles in this challenge, ordered by sequence.
     */
    public function puzzles(): BelongsToMany
    {
        return $this->belongsToMany(Puzzle::class)
            ->withPivot('sequence')
            ->orderByPivot('sequence');
    }

    /**
     * The bundles this challenge belongs to.
     */
    public function bundles(): BelongsToMany
    {
        return $this->belongsToMany(Bundle::class)
            ->withPivot('sort_order');
    }

    /**
     * The puzzle progress records for this challenge.
     */
    public function puzzleProgress(): HasMany
    {
        return $this->hasMany(PuzzleProgress::class);
    }

    /**
     * The V2 enrollments for this challenge.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * The stickers earned for completing this challenge.
     */
    public function stickers(): HasMany
    {
        return $this->hasMany(Sticker::class);
    }

    /**
     * Player reviews submitted for this challenge.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * The stock movement audit log for this challenge's medal.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(MedalStockMovement::class);
    }

    /**
     * Total puzzles attached to this challenge.
     *
     * Derived dynamically from the puzzles relationship rather than a stored
     * column so it always reflects the actual attached puzzle set. Prefers
     * the `puzzles_count` aggregate (via `withCount`/`loadCount`) when present,
     * or the loaded relation collection, falling back to a live query.
     */
    public function getPuzzleCountAttribute(): int
    {
        if (array_key_exists('puzzles_count', $this->attributes)) {
            return (int) $this->attributes['puzzles_count'];
        }

        if ($this->relationLoaded('puzzles')) {
            return $this->puzzles->count();
        }

        return (int) $this->puzzles()->count();
    }

    /**
     * Medals reserved by fulfillments that are ready to ship but not yet shipped.
     */
    public function getMedalStockReservedAttribute(): int
    {
        return (int) Fulfillment::query()
            ->where('status', 'ready_to_ship')
            ->whereHas('enrollment', fn ($query) => $query->where('challenge_id', $this->id))
            ->count();
    }

    /**
     * Medals available to dispatch = on hand - reserved.
     */
    public function getMedalStockAvailableAttribute(): int
    {
        return max(0, (int) $this->medal_stock_on_hand - $this->medal_stock_reserved);
    }

    /**
     * Whether this medal is out of stock (no available medals).
     */
    public function getMedalIsOutOfStockAttribute(): bool
    {
        return $this->medal_stock_available <= 0;
    }

    /**
     * Whether this medal is below the reorder threshold (but not out of stock).
     */
    public function getMedalIsLowStockAttribute(): bool
    {
        return $this->medal_stock_available > 0
            && $this->medal_stock_available <= $this->medal_reorder_threshold;
    }

    /**
     * Scope to only active challenges.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
