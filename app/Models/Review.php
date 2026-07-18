<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'enrollment_id',
    'challenge_id',
    'user_id',
    'puzzle_rating',
    'platform_rating',
    'title',
    'body',
    'is_public',
    'is_featured',
    'status',
    'submitted_at',
])]
class Review extends Model
{
    use HasFactory;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_public' => false,
        'is_featured' => false,
        'status' => 'pending',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'puzzle_rating' => 'integer',
            'platform_rating' => 'integer',
            'is_public' => 'boolean',
            'is_featured' => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }

    /**
     * Scope: reviews that have been submitted (not pending).
     */
    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Scope: reviews approved for public display.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope: admin-curated featured reviews.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Whether the review has been submitted by the player.
     */
    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
