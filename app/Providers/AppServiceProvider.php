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

            $businessColumns = [
                'id',
                'name',
                'slug',
                'business_logo',
                'contact',
                'address',
                'area',
                'city_id',
                'state_id',
                'country_id',
                'pincode',
                'status',
            ];

            $businessRelations = [
                'city:id,name',
                'state:id,name',
                'country:id,name',
            ];

            $currentBusiness = $user->getBusinessDetails()
                ->select($businessColumns)
                ->with($businessRelations)
                ->first();

            $businesses = $user->getBusinesses()
                ->select($businessColumns)
                ->with($businessRelations)
                ->whereIn('status', ['active', 'pending'])
                ->orderBy('name')
                ->get();

            $view->with([
                'currentBusiness' => $currentBusiness,
                'businesses' => $businesses,
            ]);
        });
    }
}
