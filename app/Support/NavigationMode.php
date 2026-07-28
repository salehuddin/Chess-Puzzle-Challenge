<?php

namespace App\Support;

enum NavigationMode: string
{
    case Strict = 'strict';
    case Free = 'free';
    case StrictSkip = 'strict_skip';
    case StrictAutosolve = 'strict_autosolve';

    public function label(): string
    {
        return match ($this) {
            self::Strict => 'Strict (locked sequential)',
            self::Free => 'Free (any order)',
            self::StrictSkip => 'Strict + skip tokens',
            self::StrictAutosolve => 'Strict + auto-solve after K tries',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Strict => 'Player must solve puzzle N before puzzle N+1 unlocks. No skipping. Purest experience.',
            self::Free => 'Player can solve any puzzle in any order. Progress grid is clickable. Completion requires all solved.',
            self::StrictSkip => 'Strict ordering, but the player receives a limited number of skip tokens. Skipped puzzles can be revisited later and must be solved to complete the challenge.',
            self::StrictAutosolve => 'Strict ordering. After K failed attempts, the player may reveal the solution. The puzzle counts as solved-with-help (distinct on the progress grid).',
        };
    }

    public function allowsFreeOrder(): bool
    {
        return $this === self::Free;
    }

    public function allowsSkip(): bool
    {
        return $this === self::StrictSkip;
    }

    public function allowsAutoSolve(): bool
    {
        return $this === self::StrictAutosolve;
    }
}
