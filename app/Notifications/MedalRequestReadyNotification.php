<?php

namespace App\Notifications;

use App\Models\Fulfillment;

class MedalRequestReadyNotification extends LifecycleNotification
{
    public function __construct(Fulfillment $fulfillment)
    {
        $name = $fulfillment->enrollment->challenge->name;

        parent::__construct([
            'title' => 'Medal request ready to ship',
            'body' => "A player requested the {$name} medal. Open the fulfillment queue to dispatch it.",
            'icon' => 'heroicon-o-archive-box',
            'color' => 'warning',
            'url' => url('/admin/operations/fulfillment-queue'),
            'replacements' => ['{{challenge_name}}' => $name],
        ], 'medal_request_ready', 'medal_request_ready');
    }
}
