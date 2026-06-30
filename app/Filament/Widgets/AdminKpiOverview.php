<?php

namespace App\Filament\Widgets;

use App\Models\Challenge;
use App\Models\Enrollment;
use App\Models\Fulfillment;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminKpiOverview extends BaseWidget
{
    protected ?string $heading = 'Business KPI Overview';

    protected static ?int $sort = 1;

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $paidOrders = Order::query()
            ->where('status', 'paid')
            ->count();

        $inProgress = Enrollment::query()
            ->where('status', 'active')
            ->count();

        $awaitingShipment = Fulfillment::query()
            ->where('status', 'ready_to_ship')
            ->count();

        $shipped = Fulfillment::query()
            ->where('status', 'shipped')
            ->count();

        $pendingPayment = Order::query()
            ->where('status', 'pending')
            ->count();

        $projectedRevenueUsd = (float) Order::query()
            ->where('status', 'paid')
            ->where('currency', 'USD')
            ->sum('total_amount');

        $trendStart = now()->copy()->startOfDay()->subDays(6);

        $dailyPaid = Order::query()
            ->selectRaw('DATE(COALESCE(paid_at, created_at)) as day, COUNT(*) as total')
            ->where('status', 'paid')
            ->where(function ($query) use ($trendStart): void {
                $query
                    ->where('paid_at', '>=', $trendStart)
                    ->orWhere(function ($inner) use ($trendStart): void {
                        $inner
                            ->whereNull('paid_at')
                            ->where('created_at', '>=', $trendStart);
                    });
            })
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $paidTrendChart = [];

        foreach (range(0, 6) as $offset) {
            $day = $trendStart->copy()->addDays($offset)->toDateString();
            $paidTrendChart[] = (int) ($dailyPaid[$day] ?? 0);
        }

        $paidLast7Days = array_sum($paidTrendChart);

        $readyToShipReserved = Fulfillment::query()
            ->where('fulfillments.status', 'ready_to_ship')
            ->join('enrollments', 'fulfillments.enrollment_id', '=', 'enrollments.id')
            ->selectRaw('enrollments.challenge_id, COUNT(*) as reserved_count')
            ->groupBy('enrollments.challenge_id')
            ->pluck('reserved_count', 'challenge_id')
            ->all();

        $challenges = Challenge::query()
            ->select(['id', 'medal_stock_on_hand', 'medal_reorder_threshold'])
            ->get();

        $outOfStockCount = 0;
        $lowStockCount = 0;

        foreach ($challenges as $challenge) {
            $reserved = $readyToShipReserved[$challenge->id] ?? 0;
            $available = max(0, $challenge->medal_stock_on_hand - $reserved);

            if ($available <= 0) {
                $outOfStockCount++;
            } elseif ($available <= $challenge->medal_reorder_threshold) {
                $lowStockCount++;
            }
        }

        return [
            Stat::make('Paid Orders', number_format($paidOrders))
                ->description('Orders with successful payment')
                ->color('primary')
                ->chart($paidTrendChart),
            Stat::make('Active Enrollments', number_format($inProgress))
                ->description('Users currently progressing through challenges')
                ->descriptionIcon('heroicon-o-play-circle')
                ->color('info'),
            Stat::make('Ready To Ship', number_format($awaitingShipment))
                ->description('Completed enrollments pending dispatch')
                ->descriptionIcon('heroicon-o-cube')
                ->color('warning'),
            Stat::make('Shipped', number_format($shipped))
                ->description('Rewards fulfilled and shipped')
                ->descriptionIcon('heroicon-o-truck')
                ->color('success'),
            Stat::make('Pending Payment', number_format($pendingPayment))
                ->description('Orders not yet paid')
                ->descriptionIcon('heroicon-o-clock')
                ->color('gray'),
            Stat::make('Paid Revenue (USD)', '$'.number_format($projectedRevenueUsd, 2))
                ->description('From paid USD orders')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),
            Stat::make('7-Day New Paid Orders', number_format($paidLast7Days))
                ->description('Trailing 7-day paid orders')
                ->descriptionIcon('heroicon-o-chart-bar-square')
                ->color('primary')
                ->chart($paidTrendChart),
            Stat::make('Low Stock SKUs', number_format($lowStockCount))
                ->description('Challenges below reorder threshold')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color('warning'),
            Stat::make('Out of Stock SKUs', number_format($outOfStockCount))
                ->description('Challenges with no medals available')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }
}
