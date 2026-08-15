<?php

namespace App\Notifications;

use App\Models\Review;

class ReviewAwaitingModerationNotification extends LifecycleNotification
{
    public function __construct(Review $review)
    {
        $name = $review->challenge->name;

        parent::__construct([
            'title' => 'Review awaiting moderation',
            'body' => "A new review for {$name} is ready for moderation.",
            'icon' => 'heroicon-o-chat-bubble-left-right',
            'color' => 'info',
            'url' => url('/admin/reviews'),
            'replacements' => ['{{challenge_name}}' => $name],
        ], 'review_awaiting_moderation', 'review_awaiting_moderation');
    }
}
