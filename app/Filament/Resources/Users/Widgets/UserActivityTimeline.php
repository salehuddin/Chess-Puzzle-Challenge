<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Models\Enrollment;
use App\Models\Fulfillment;
use App\Models\Order;
use App\Models\PuzzleProgress;
use App\Models\Sticker;
use App\Models\User;
use Filament\Widgets\Widget;

class UserActivityTimeline extends Widget
{
    public ?User $record = null;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    /**
     * @var view-string
     */
    protected string $view = 'filament.users.activity-timeline';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'events' => $this->getEvents(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getEvents(): array
    {
        $user = $this->record;

        if (! $user) {
            return [];
        }

        $events = collect();

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        foreach ($orders as $order) {
            $events->push([
                'datetime' => $order->created_at,
                'icon' => 'heroicon-o-shopping-cart',
                'color' => 'primary',
                'title' => 'Order #'.$order->id.' placed',
                'description' => strtoupper((string) $order->currency).' '.number_format((float) $order->total_amount, 2).' · status: '.ucfirst((string) $order->status),
            ]);

            if ($order->paid_at) {
                $events->push([
                    'datetime' => $order->paid_at,
                    'icon' => 'heroicon-o-banknotes',
                    'color' => 'success',
                    'title' => 'Payment received for order #'.$order->id,
                    'description' => strtoupper((string) $order->currency).' '.number_format((float) $order->total_amount, 2).' via '.($order->payment_provider ?: 'payment provider'),
                ]);
            }
        }

        $enrollments = Enrollment::query()
            ->with('challenge')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        foreach ($enrollments as $enrollment) {
            $challengeName = $enrollment->challenge?->name ?? 'Challenge #'.$enrollment->challenge_id;

            $events->push([
                'datetime' => $enrollment->created_at,
                'icon' => 'heroicon-o-user-plus',
                'color' => 'info',
                'title' => 'Enrolled in '.$challengeName,
                'description' => 'Enrollment status: '.ucfirst((string) $enrollment->status),
            ]);

            if ($enrollment->activated_at) {
                $events->push([
                    'datetime' => $enrollment->activated_at,
                    'icon' => 'heroicon-o-play-circle',
                    'color' => 'info',
                    'title' => 'Started '.$challengeName,
                    'description' => 'Challenge activated',
                ]);
            }

            if ($enrollment->completed_at) {
                $events->push([
                    'datetime' => $enrollment->completed_at,
                    'icon' => 'heroicon-o-trophy',
                    'color' => 'success',
                    'title' => 'Completed '.$challengeName,
                    'description' => 'Challenge finished',
                ]);
            }
        }

        $progress = PuzzleProgress::query()
            ->with('challenge', 'puzzle')
            ->where('user_id', $user->id)
            ->whereNotNull('solved_at')
            ->orderByDesc('solved_at')
            ->limit(50)
            ->get();

        foreach ($progress as $solved) {
            $challengeName = $solved->challenge?->name ?? 'Challenge #'.$solved->challenge_id;
            $puzzleLabel = $solved->puzzle?->id ?? $solved->puzzle_id;

            $events->push([
                'datetime' => $solved->solved_at,
                'icon' => 'heroicon-o-puzzle-piece',
                'color' => 'warning',
                'title' => 'Solved puzzle #'.$puzzleLabel.' in '.$challengeName,
                'description' => 'Puzzle completed',
            ]);
        }

        $stickers = Sticker::query()
            ->with('challenge')
            ->where('user_id', $user->id)
            ->whereNotNull('unlocked_at')
            ->orderByDesc('unlocked_at')
            ->limit(50)
            ->get();

        foreach ($stickers as $sticker) {
            $challengeName = $sticker->challenge?->name ?? 'Challenge #'.$sticker->challenge_id;

            $events->push([
                'datetime' => $sticker->unlocked_at,
                'icon' => 'heroicon-o-star',
                'color' => 'warning',
                'title' => 'Medal/sticker unlocked for '.$challengeName,
                'description' => 'Digital badge earned',
            ]);
        }

        $fulfillments = Fulfillment::query()
            ->whereHas('enrollment', fn ($query) => $query->where('user_id', $user->id))
            ->with('enrollment.challenge')
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        foreach ($fulfillments as $fulfillment) {
            $challengeName = $fulfillment->enrollment?->challenge?->name ?? 'a challenge';

            if ($fulfillment->shipped_at) {
                $events->push([
                    'datetime' => $fulfillment->shipped_at,
                    'icon' => 'heroicon-o-truck',
                    'color' => 'primary',
                    'title' => 'Medal shipped for '.$challengeName,
                    'description' => 'Courier: '.($fulfillment->courier ?: 'unspecified').($fulfillment->tracking_number ? ' · '.$fulfillment->tracking_number : ''),
                ]);
            }

            if ($fulfillment->delivered_at) {
                $events->push([
                    'datetime' => $fulfillment->delivered_at,
                    'icon' => 'heroicon-o-gift',
                    'color' => 'success',
                    'title' => 'Medal delivered for '.$challengeName,
                    'description' => 'Fulfillment completed',
                ]);
            }
        }

        return $events
            ->sortByDesc('datetime')
            ->take(50)
            ->values()
            ->map(fn (array $event): array => [
                ...$event,
                'datetime' => optional($event['datetime'])->toDateTimeString(),
                'when' => $event['datetime']?->diffForHumans(),
            ])
            ->all();
    }
}
