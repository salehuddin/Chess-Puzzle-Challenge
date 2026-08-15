<?php

namespace App\Notifications;

use App\Models\Enrollment;

class EnrollmentActivatedNotification extends LifecycleNotification
{
    public function __construct(Enrollment $enrollment)
    {
        $name = $enrollment->challenge->name;

        parent::__construct([
            'title' => 'Your challenge is ready',
            'body' => "You are enrolled in {$name}. Start solving whenever you are ready.",
            'icon' => 'heroicon-o-play',
            'color' => 'success',
            'url' => route('play', $enrollment),
            'replacements' => ['{{user_name}}' => $enrollment->user->name, '{{challenge_name}}' => $name],
        ], 'enrollment_activated', 'challenge_enrollment');
    }
}
