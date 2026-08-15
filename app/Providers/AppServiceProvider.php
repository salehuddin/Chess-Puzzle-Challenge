<?php

namespace App\Providers;

use App\Models\Enrollment;
use App\Models\Fulfillment;
use App\Models\Review;
use App\Observers\EnrollmentObserver;
use App\Observers\FulfillmentObserver;
use App\Observers\ReviewObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Enrollment::observe(EnrollmentObserver::class);
        Fulfillment::observe(FulfillmentObserver::class);
        Review::observe(ReviewObserver::class);

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
