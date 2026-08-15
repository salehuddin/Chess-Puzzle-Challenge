<?php

namespace App\Observers;

use App\Models\Fulfillment;
use App\Models\User;
use App\Notifications\MedalDeliveredNotification;
use App\Notifications\MedalRequestReadyNotification;
use App\Notifications\MedalShippedNotification;

class FulfillmentObserver
{
    public function created(Fulfillment $fulfillment): void
    {
        if ($fulfillment->status === 'ready_to_ship') {
            $this->notifyStaff(new MedalRequestReadyNotification($fulfillment), ['super_admin', 'fulfillment']);
        }
    }

    public function updated(Fulfillment $fulfillment): void
    {
        if (! $fulfillment->wasChanged('status')) {
            return;
        }

        match ($fulfillment->status) {
            'ready_to_ship' => $this->notifyStaff(new MedalRequestReadyNotification($fulfillment), ['super_admin', 'fulfillment']),
            'shipped' => $fulfillment->enrollment->user->notify(new MedalShippedNotification($fulfillment)),
            'delivered' => $fulfillment->enrollment->user->notify(new MedalDeliveredNotification($fulfillment)),
            default => null,
        };
    }

    private function notifyStaff(object $notification, array $roles): void
    {
        User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', $roles)->where('guard_name', 'web'))
            ->get()
            ->each(fn (User $user) => $user->notify($notification));
    }
}
