<?php

namespace App\Observers;

use App\Models\Enrollment;
use App\Notifications\ChallengeCompletedNotification;
use App\Notifications\EnrollmentActivatedNotification;

class EnrollmentObserver
{
    public function created(Enrollment $enrollment): void
    {
        if ($enrollment->status === 'active') {
            $enrollment->user->notify(new EnrollmentActivatedNotification($enrollment));
        }
    }

    public function updated(Enrollment $enrollment): void
    {
        if ($enrollment->wasChanged('status') && $enrollment->status === 'completed') {
            $enrollment->user->notify(new ChallengeCompletedNotification($enrollment));
        }
    }
}
