<?php

namespace App\Providers;

use App\Models\AppointmentBooking;
use App\Models\Business;
use App\Models\ReviewAndRating;
use App\Models\User;
use App\Observers\AppointmentBookingObserver;
use App\Observers\BusinessObserver;
use Illuminate\Support\ServiceProvider;
use App\Observers\ReviewAndRatingObserver;
use App\Observers\UserObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ReviewAndRating::observe(ReviewAndRatingObserver::class);
        User::observe(UserObserver::class);
        //AppointmentBooking::observe(AppointmentBookingObserver::class);
        Business::observe(BusinessObserver::class);
    }
}
