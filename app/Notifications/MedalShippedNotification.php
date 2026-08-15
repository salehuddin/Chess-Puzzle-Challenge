<?php

namespace App\Notifications;

use App\Models\Fulfillment;

class MedalShippedNotification extends LifecycleNotification
{
    public function __construct(Fulfillment $fulfillment)
    {
        $name = $fulfillment->enrollment->challenge->name;
        $url = $fulfillment->tracking_url ?: route('enrollments.show', $fulfillment->enrollment);

        parent::__construct([
            'title' => 'Your medal has shipped',
            'body' => "Your {$name} medal is on its way.",
            'icon' => 'heroicon-o-truck',
            'color' => 'info',
            'url' => $url,
            'replacements' => [
                '{{user_name}}' => $fulfillment->enrollment->user->name,
                '{{challenge_name}}' => $name,
                '{{tracking_url}}' => $url,
            ],
        ], 'medal_shipped', 'medal_shipped');
    }
}
