<?php

namespace App\Filament\Resources\Challenges\Widgets;

use App\Models\Challenge;
use App\Models\Enrollment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ChallengeQuickGlance extends StatsOverviewWidget
{
    public ?Challenge $record = null;

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $challenge = $this->record;

        if (! $challenge) {
            return [];
        }

        $enrollments = Enrollment::query()->where('challenge_id', $challenge->id);

        $activeCount = (clone $enrollments)->whereIn('status', ['active', 'completed'])->count();
        $completedCount = (clone $enrollments)->where('status', 'completed')->count();
        $completionRate = $activeCount > 0 ? round(($completedCount / $activeCount) * 100, 1) : 0;
        $revenueUsd = (float) $challenge->price_usd * $activeCount;

        return [
            Stat::make('Players', number_format($activeCount))
                ->description('Active and completed enrollments')
                ->color('primary'),
            Stat::make('Completions', number_format($completedCount))
                ->description($completionRate . '% completion rate')
                ->color('success'),
            Stat::make('Puzzle Slots', number_format((int) $challenge->puzzle_count))
                ->description(number_format($challenge->puzzles()->count()) . ' currently attached')
                ->color('info'),
            Stat::make('Projected USD', '$' . number_format($revenueUsd, 2))
                ->description('Based on current active enrollments')
                ->color('warning'),
        ];
    }
}
