<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'lichess_id',
    'fen',
    'moves',
    'rating',
    'rating_deviation',
    'popularity',
    'nb_plays',
    'themes',
    'game_url',
])]
class Puzzle extends Model
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
            'moves' => 'array',
            'themes' => 'array',
            'rating' => 'integer',
            'rating_deviation' => 'integer',
            'popularity' => 'integer',
            'nb_plays' => 'integer',
        ];
    }

    /**
     * The challenges this puzzle belongs to.
     */
    public function challenges(): BelongsToMany
    {
        return $this->belongsToMany(Challenge::class)
            ->withPivot('sequence')
            ->orderByPivot('sequence');
    }
}
