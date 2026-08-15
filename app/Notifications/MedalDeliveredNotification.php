<?php

namespace App\Notifications;

use App\Models\Fulfillment;

class MedalDeliveredNotification extends LifecycleNotification
{
    public function __construct(Fulfillment $fulfillment)
    {
        $name = $fulfillment->enrollment->challenge->name;

        parent::__construct([
            'title' => 'Your medal was delivered',
            'body' => "Your {$name} medal has been marked as delivered.",
            'icon' => 'heroicon-o-gift',
            'color' => 'success',
            'url' => route('enrollments.show', $fulfillment->enrollment),
            'replacements' => ['{{user_name}}' => $fulfillment->enrollment->user->name, '{{challenge_name}}' => $name],
        ], 'medal_delivered', 'medal_delivered');
    }
}
