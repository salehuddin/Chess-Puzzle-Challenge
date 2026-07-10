<?php

namespace App\Filament\Widgets;

use App\Models\Fulfillment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FulfillmentSnapshot extends BaseWidget
{
    protected ?string $heading = 'Fulfillment Snapshot';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $readyToShip = Fulfillment::query()
            ->where('status', 'ready_to_ship')
            ->count();

        $shippedMissingTracking = Fulfillment::query()
            ->where('status', 'shipped')
            ->where(function ($query): void {
                $query
                    ->where(function ($innerQuery): void {
                        $innerQuery
                            ->whereNull('tracking_number')
                            ->orWhere('tracking_number', '');
                    })
                    ->where(function ($innerQuery): void {
                        $innerQuery
                            ->whereNull('tracking_url')
                            ->orWhere('tracking_url', '');
                    });
            })
            ->count();

        $missingAddressSnapshot = Fulfillment::query()
            ->whereIn('status', ['pending', 'ready_to_ship', 'shipped'])
            ->where(function ($query): void {
                $query
                    ->whereNull('address_snapshot')
                    ->orWhere('address_snapshot', '[]');
            })
            ->count();

        $shippedLast7Days = Fulfillment::query()
            ->where('status', 'shipped')
            ->where('shipped_at', '>=', now()->subDays(7))
            ->count();

        return [
            Stat::make('Ready To Ship', number_format($readyToShip))
                ->description('Completed enrollments pending dispatch')
                ->descriptionIcon('heroicon-o-inbox-stack')
                ->color('warning'),
            Stat::make('Shipped Missing Tracking', number_format($shippedMissingTracking))
                ->description('Needs logistics cleanup')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color('danger'),
            Stat::make('Missing Address Snapshot', number_format($missingAddressSnapshot))
                ->description('Fulfillments with empty address snapshot')
                ->descriptionIcon('heroicon-o-map-pin')
                ->color('gray'),
            Stat::make('Shipped (Last 7 Days)', number_format($shippedLast7Days))
                ->description('Recent fulfillment throughput')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('success'),
        ];
    }
}
