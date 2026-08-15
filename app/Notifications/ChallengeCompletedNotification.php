<?php

namespace App\Notifications;

use App\Models\Enrollment;

class ChallengeCompletedNotification extends LifecycleNotification
{
    public function __construct(Enrollment $enrollment)
    {
        $name = $enrollment->challenge->name;

        parent::__construct([
            'title' => 'Challenge complete!',
            'body' => "Congratulations, you completed {$name}. Your sticker is unlocked. You can now request your medal and leave a review.",
            'icon' => 'heroicon-o-trophy',
            'color' => 'success',
            'url' => route('enrollments.show', $enrollment),
            'replacements' => [
                '{{user_name}}' => $enrollment->user->name,
                '{{challenge_name}}' => $name,
                '{{fulfillment_status}}' => 'pending',
            ],
        ], 'challenge_completed', 'challenge_completion');
    }
}
