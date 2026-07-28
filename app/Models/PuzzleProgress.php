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
    'solved_with_help',
    'skipped_at',
    'attempts',
    'hints_used',
])]
class PuzzleProgress extends Model
{
    use HasFactory;

    protected $table = 'puzzle_progress';

    protected function casts(): array
    {
        return [
            'solved_at' => 'datetime',
            'skipped_at' => 'datetime',
            'solved_with_help' => 'boolean',
            'attempts' => 'integer',
            'hints_used' => 'integer',
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

    public function isSkipped(): bool
    {
        return $this->skipped_at !== null && $this->solved_at === null;
    }

    /**
     * Solved without any help — the strongest solve state.
     */
    public function isFullySolved(): bool
    {
        return $this->solved_at !== null && ! $this->solved_with_help;
    }

    /**
     * Solved, but only after revealing the solution (strict_autosolve mode).
     */
    public function isSolvedWithHelp(): bool
    {
        return $this->solved_at !== null && $this->solved_with_help;
    }
}
