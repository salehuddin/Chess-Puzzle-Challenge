<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Models\PuzzleProgress;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserOverview extends BaseWidget
{
    public ?User $record = null;

    protected ?string $heading = 'User Statistics';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $user = $this->record;

        if (! $user) {
            return [];
        }

        $ordersCount = $user->paidOrdersCount();
        $revenueUsd = $user->paidRevenue('USD');
        $revenueMyr = $user->paidRevenue('MYR');

        $activeEnrollments = $user->enrollments()->where('status', 'active')->count();
        $completedChallenges = $user->completedChallengesCount();
        $solvedPuzzles = $user->solvedPuzzlesCount();
        $stickers = $user->stickersCount();

        $totalEnrollments = $activeEnrollments + $completedChallenges;
        $completionRate = $totalEnrollments > 0
            ? round(($completedChallenges / $totalEnrollments) * 100, 1)
            : 0;

        $solvedTrendStart = now()->copy()->startOfDay()->subDays(13);

        $dailySolved = PuzzleProgress::query()
            ->selectRaw('DATE(solved_at) as day, COUNT(*) as total')
            ->where('user_id', $user->id)
            ->whereNotNull('solved_at')
            ->where('solved_at', '>=', $solvedTrendStart)
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $solvedChart = [];

        foreach (range(0, 13) as $offset) {
            $day = $solvedTrendStart->copy()->addDays($offset)->toDateString();
            $solvedChart[] = (int) ($dailySolved[$day] ?? 0);
        }

        return [
            Stat::make('Paid Orders', number_format($ordersCount))
                ->description('$'.number_format($revenueUsd, 2).' USD / RM'.number_format($revenueMyr, 2).' MYR spent')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('primary'),
            Stat::make('Active Enrollments', number_format($activeEnrollments))
                ->description('Challenges currently in progress')
                ->descriptionIcon('heroicon-o-play-circle')
                ->color('info'),
            Stat::make('Challenges Completed', number_format($completedChallenges))
                ->description($completionRate.'% completion rate')
                ->descriptionIcon('heroicon-o-trophy')
                ->color('success'),
            Stat::make('Puzzles Solved', number_format($solvedPuzzles))
                ->description('Across all enrolled challenges')
                ->descriptionIcon('heroicon-o-puzzle-piece')
                ->color('warning')
                ->chart($solvedChart),
            Stat::make('Medals / Stickers', number_format($stickers))
                ->description('Digital badges unlocked')
                ->descriptionIcon('heroicon-o-star')
                ->color('success'),
        ];
    }
}
