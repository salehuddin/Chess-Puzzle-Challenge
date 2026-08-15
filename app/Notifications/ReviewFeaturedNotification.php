<?php

namespace App\Notifications;

use App\Models\Review;

class ReviewFeaturedNotification extends LifecycleNotification
{
    public function __construct(Review $review)
    {
        $name = $review->challenge->name;

        parent::__construct([
            'title' => 'Your review is now public',
            'body' => "Your review for {$name} is now featured on Chess Puzzle Challenge.",
            'icon' => 'heroicon-o-star',
            'color' => 'warning',
            'url' => route('home'),
            'replacements' => ['{{user_name}}' => $review->user->name, '{{challenge_name}}' => $name],
        ], 'review_featured', 'review_featured');
    }
}
