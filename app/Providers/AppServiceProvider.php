<?php

namespace App\Providers;

use App\Models\AppointmentBooking;
use App\Models\Business;
use App\Models\ReviewAndRating;
use App\Models\User;
use App\Observers\AppointmentBookingObserver;
use App\Observers\BusinessObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Observers\ReviewAndRatingObserver;
use App\Observers\UserObserver;
use Laravel\Passport\Passport;

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
        if ($forwardedHost = request()->header('X-Forwarded-Host')) {

            $incomingHash = (string) request()->header('X-Proxy-Hash-Key');
            $expectedHash = (string) config('services.proxy.hash_key');

            if (!empty($expectedHash) && hash_equals($expectedHash, $incomingHash)) {
                URL::forceScheme('https');
                URL::forceRootUrl('https://' . $forwardedHost);

                URL::defaults(['tenant' => '']);
                config(['session.domain' => null]);
            } else {
                // Abort with proper 403 HTTP status code
                abort(403, 'Unauthorized proxy access.');
            }
        }

        ReviewAndRating::observe(ReviewAndRatingObserver::class);
        User::observe(UserObserver::class);
        //AppointmentBooking::observe(AppointmentBookingObserver::class);
        Business::observe(BusinessObserver::class);

        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        \Illuminate\Support\Facades\Gate::define('viewPulse', function ($user = null) {
            return \Illuminate\Support\Facades\Auth::guard('admin')->check() ||
                (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->role === 'Admin');
        });

        \Illuminate\Support\Facades\View::composer('front.business.template1.layouts.main', function ($view) {
            $viewData = $view->getData();
            $business = $viewData['business'] ?? null;
            if ($business && !isset($viewData['homePopups'])) {
                $homePopups = cache()->remember("business_{$business->id}_home_popups", 600, function () use ($business) {
                    return \App\Models\BusinessHomePopup::where('business_id', $business->id)
                        ->where('status', 'active')
                        ->get();
                });
                $view->with('homePopups', $homePopups);
            }
        });

        \Illuminate\Support\Facades\View::composer([
            'front.business.template1.layouts.main'
        ], function ($view) {
            $viewData = $view->getData();
            $business = $viewData['business'] ?? null;
            if ($business && !isset($viewData['activeLegalPages'])) {
                $activeLegalPages = cache()->remember("business_{$business->id}_active_legal_pages", 600, function () use ($business) {
                    return \App\Models\BusinessLeaglePage::where('business_id', $business->id)
                        ->where('status', 'active')
                        ->get();
                });
                $view->with('activeLegalPages', $activeLegalPages);
            }
        });
    }
}
