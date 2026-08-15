<?php

namespace App\Observers;

use App\Models\Review;
use App\Models\User;
use App\Notifications\ReviewAwaitingModerationNotification;
use App\Notifications\ReviewFeaturedNotification;

class ReviewObserver
{
    public function updated(Review $review): void
    {
        if ($review->wasChanged('status') && $review->status === 'submitted') {
            $this->notifyStaff(new ReviewAwaitingModerationNotification($review), ['super_admin', 'editor']);
        }

        $becamePublic = $review->wasChanged('is_public') && $review->is_public;
        $becameFeatured = $review->wasChanged('is_featured') && $review->is_featured;

        if ($becamePublic || $becameFeatured) {
            $review->user->notify(new ReviewFeaturedNotification($review));
        }
    }

    private function notifyStaff(object $notification, array $roles): void
    {
        User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', $roles)->where('guard_name', 'web'))
            ->get()
            ->each(fn (User $user) => $user->notify($notification));
    }
}
