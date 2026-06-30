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

        $attachedCount = $challenge->puzzles()->count();

        $enrollments = Enrollment::query()->where('challenge_id', $challenge->id);

        $activeCount = (clone $enrollments)->whereIn('status', ['active', 'completed'])->count();
        $completedCount = (clone $enrollments)->where('status', 'completed')->count();
        $completionRate = $activeCount > 0 ? round(($completedCount / $activeCount) * 100, 1) : 0;
        $revenueUsd = (float) $challenge->price_usd * $activeCount;

        $reserved = (clone $enrollments)
            ->where('status', 'completed')
            ->whereHas('fulfillment', fn ($query) => $query->where('status', 'ready_to_ship'))
            ->count();

        $onHand = (int) $challenge->medal_stock_on_hand;
        $available = max(0, $onHand - $reserved);

        $stockDescription = match (true) {
            $available <= 0 => 'Out of stock',
            $available <= (int) $challenge->medal_reorder_threshold => "Low stock (threshold: {$challenge->medal_reorder_threshold})",
            default => "{$reserved} reserved by ready-to-ship fulfillments",
        };

        $stockColor = match (true) {
            $available <= 0 => 'danger',
            $available <= (int) $challenge->medal_reorder_threshold => 'warning',
            default => 'success',
        };

        return [
            Stat::make('Players', number_format($activeCount))
                ->description('Active and completed enrollments')
                ->color('primary'),
            Stat::make('Completions', number_format($completedCount))
                ->description($completionRate.'% completion rate')
                ->color('success'),
            Stat::make('Puzzles', number_format($attachedCount))
                ->description('Currently attached to this challenge')
                ->color('info'),
            Stat::make('Projected USD', '$'.number_format($revenueUsd, 2))
                ->description('Based on current active enrollments')
                ->color('warning'),
            Stat::make('Medals Available', number_format($available))
                ->description($stockDescription)
                ->descriptionIcon('heroicon-o-cube')
                ->color($stockColor),
        ];
    }
}
