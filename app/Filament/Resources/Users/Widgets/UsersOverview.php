<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Sticker;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UsersOverview extends BaseWidget
{
    protected ?string $heading = 'Users Overview';

    protected static ?int $sort = 1;

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $totalUsers = User::query()->count();
        $verifiedUsers = User::query()->whereNotNull('email_verified_at')->count();
        $newThisWeek = User::query()->where('created_at', '>=', now()->subDays(7))->count();

        $payingUsers = Order::query()
            ->where('status', 'paid')
            ->distinct('user_id')
            ->count('user_id');

        $activeEnrollments = Enrollment::query()->where('status', 'active')->count();
        $completedChallenges = Enrollment::query()->where('status', 'completed')->count();
        $medalsEarned = Sticker::query()->whereNotNull('unlocked_at')->count();

        $lifetimeRevenueUsd = (float) Order::query()
            ->where('status', 'paid')
            ->where('currency', 'USD')
            ->sum('total_amount');

        $newUsersTrendStart = now()->copy()->startOfDay()->subDays(6);

        $dailyNewUsers = User::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->where('created_at', '>=', $newUsersTrendStart)
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $newUsersChart = [];

        foreach (range(0, 6) as $offset) {
            $day = $newUsersTrendStart->copy()->addDays($offset)->toDateString();
            $newUsersChart[] = (int) ($dailyNewUsers[$day] ?? 0);
        }

        return [
            Stat::make('Total Users', number_format($totalUsers))
                ->description(number_format($newThisWeek).' joined in the last 7 days')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary')
                ->chart($newUsersChart),
            Stat::make('Verified Users', number_format($verifiedUsers))
                ->description($totalUsers > 0 ? round(($verifiedUsers / $totalUsers) * 100, 1).'% verified' : '0% verified')
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),
            Stat::make('Paying Users', number_format($payingUsers))
                ->description('Users with at least one paid order')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('warning'),
            Stat::make('Lifetime Revenue (USD)', '$'.number_format($lifetimeRevenueUsd, 2))
                ->description('From all paid USD orders')
                ->descriptionIcon('heroicon-o-chart-bar-square')
                ->color('success'),
            Stat::make('Active Enrollments', number_format($activeEnrollments))
                ->description('Users currently progressing through challenges')
                ->descriptionIcon('heroicon-o-play-circle')
                ->color('info'),
            Stat::make('Challenges Completed', number_format($completedChallenges))
                ->description('Total challenge completions across all users')
                ->descriptionIcon('heroicon-o-trophy')
                ->color('success'),
            Stat::make('Medals Earned', number_format($medalsEarned))
                ->description('Digital stickers unlocked by users')
                ->descriptionIcon('heroicon-o-star')
                ->color('warning'),
        ];
    }
}
