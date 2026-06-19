<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'challenge_id',
    'puzzle_id',
    'solved_at',
])]
class PuzzleProgress extends Model
{
    use HasFactory;

    protected $table = 'puzzle_progress';

    protected function casts(): array
    {
        return [
            'solved_at' => 'datetime',
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

    public function puzzle(): BelongsTo
    {
        return $this->belongsTo(Puzzle::class);
    }

    public function isSolved(): bool
    {
        return $this->solved_at !== null;
    }
}
