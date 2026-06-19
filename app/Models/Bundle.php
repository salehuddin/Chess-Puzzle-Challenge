<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'name',
    'slug',
    'sku',
    'description',
    'price_usd',
    'price_myr',
    'is_active',
])]
class Bundle extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected static function booted(): void
    {
        static::created(function (Bundle $bundle) {
            if (blank($bundle->sku)) {
                $bundle->sku = sprintf('BUND-%05d', $bundle->id);
                $bundle->saveQuietly();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'sku' => 'string',
            'price_usd' => 'decimal:2',
            'price_myr' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The challenges included in this bundle.
     */
    public function challenges(): BelongsToMany
    {
        return $this->belongsToMany(Challenge::class)
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    /**
     * Scope to only active bundles.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
