<?php

namespace App\Providers;

use App\Models\AppointmentBooking;
use App\Models\Business;
use App\Models\ReviewAndRating;
use App\Models\User;
use App\Observers\AppointmentBookingObserver;
use App\Observers\BusinessObserver;
use App\Observers\ReviewAndRatingObserver;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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

        View::composer('business.layouts.*', function ($view) {
            if (!Auth::check()) {
                $view->with([
                    'currentBusiness' => null,
                    'businesses' => collect(),
                ]);

                return;
            }

            $user = Auth::user();

            if (!session()->has('businesses') || !session()->has('currentBusiness')) {
                $user->syncBusinessContextToSession();
            }

            $currentBusiness = session('currentBusiness');
            $businesses = collect(session('businesses', []));

            if (
                !filled(data_get($currentBusiness, 'full_address'))
                || $businesses->contains(function ($business) {
                    return !filled(data_get($business, 'full_address'));
                })
            ) {
                $user->syncBusinessContextToSession();
                $currentBusiness = session('currentBusiness');
                $businesses = collect(session('businesses', []));
            }

            $view->with([
                'currentBusiness' => $currentBusiness ? (object) $currentBusiness : null,
                'businesses' => $businesses->map(function ($business) {
                    return (object) $business;
                }),
            ]);
        });
    }
}
