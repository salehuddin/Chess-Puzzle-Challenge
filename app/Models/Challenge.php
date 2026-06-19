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
    'rules',
    'price_usd',
    'price_myr',
    'puzzle_count',
    'is_active',
    'medal_weight',
    'medal_length',
    'medal_width',
    'medal_height',
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
            'price_usd' => 'decimal:2',
            'price_myr' => 'decimal:2',
            'puzzle_count' => 'integer',
            'is_active' => 'boolean',
            'medal_weight' => 'decimal:2',
            'medal_length' => 'decimal:2',
            'medal_width' => 'decimal:2',
            'medal_height' => 'decimal:2',
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
     * Scope to only active challenges.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
