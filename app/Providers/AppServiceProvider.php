<?php

namespace App\Providers;

use App\Models\AppointmentBooking;
use App\Models\Business;
use App\Models\BusinessInquiry;
use App\Models\ReviewAndRating;
use App\Models\User;
use App\Observers\AppointmentBookingObserver;
use App\Observers\BusinessObserver;
use App\Observers\BusinessInquiryObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Observers\ReviewAndRatingObserver;
use App\Observers\UserObserver;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use PDO;
use RuntimeException;

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
        $this->validateCriticalEnvironmentVariables();

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perHour(3)->by($request->ip());
        });

        RateLimiter::for('signup', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
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

        Vite::useScriptTagAttributes([
            'data-cfasync' => 'false',
        ]);

        ReviewAndRating::observe(ReviewAndRatingObserver::class);
        User::observe(UserObserver::class);
        AppointmentBooking::observe(AppointmentBookingObserver::class);
        Business::observe(BusinessObserver::class);
        BusinessInquiry::observe(BusinessInquiryObserver::class);

        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addDays(30));

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

    /**
     * Validate critical environment variables and security settings before application processing.
     * The app refuses to start if a critical variable or production security check fails.
     */
    protected function validateCriticalEnvironmentVariables(): void
    {
        // Bypass checks during essential artisan scaffolding/key generation commands
        if ($this->app->runningInConsole() && isset($_SERVER['argv'][1])) {
            $allowedCommands = ['key:generate', 'package:discover', 'vendor:publish', 'optimize:clear', 'config:clear'];
            if (in_array($_SERVER['argv'][1], $allowedCommands, true)) {
                return;
            }
        }

        // 1. Critical Variable: APP_KEY (Auth secret & encryption)
        $appKey = config('app.key');
        if (empty($appKey)) {
            throw new RuntimeException(
                'CRITICAL ENVIRONMENT ERROR: APP_KEY is not set. The application refuses to start without an encryption key. Run "php artisan key:generate".'
            );
        }

        // 2. Critical Variable: Database Configuration
        $defaultConn = config('database.default');
        if (empty($defaultConn)) {
            throw new RuntimeException(
                'CRITICAL ENVIRONMENT ERROR: DB_CONNECTION is not configured. The application refuses to start without a valid database connection.'
            );
        }

        $dbConfig = config("database.connections.{$defaultConn}");
        if (!$dbConfig) {
            throw new RuntimeException(
                "CRITICAL ENVIRONMENT ERROR: Database connection '{$defaultConn}' is not defined in config/database.php."
            );
        }

        if ($defaultConn === 'sqlite') {
            $database = $dbConfig['database'] ?? null;
            if (empty($database)) {
                throw new RuntimeException(
                    'CRITICAL ENVIRONMENT ERROR: SQLite database path (DB_DATABASE) is not set.'
                );
            }
        } else {
            if (empty($dbConfig['host']) && empty($dbConfig['url']) && empty($dbConfig['unix_socket'])) {
                throw new RuntimeException(
                    "CRITICAL ENVIRONMENT ERROR: Database host (DB_HOST or DB_URL) is not set for '{$defaultConn}'."
                );
            }
            if (empty($dbConfig['database'])) {
                throw new RuntimeException(
                    "CRITICAL ENVIRONMENT ERROR: Database name (DB_DATABASE) is not set for '{$defaultConn}'."
                );
            }
            if (!isset($dbConfig['username']) || $dbConfig['username'] === '') {
                throw new RuntimeException(
                    "CRITICAL ENVIRONMENT ERROR: Database username (DB_USERNAME) is not set for '{$defaultConn}'."
                );
            }
        }

        // 3. Strict Security Checks for Production Environment
        if ($this->app->environment('production')) {
            // Debug mode must be disabled
            if (config('app.debug') === true) {
                throw new RuntimeException(
                    'CRITICAL SECURITY ERROR: APP_DEBUG is enabled in production. Debug mode must default to OFF.'
                );
            }

            // Database credentials: Reject default root with empty password
            if (in_array($defaultConn, ['mysql', 'mariadb', 'pgsql', 'sqlsrv'], true)) {
                $user = strtolower((string) ($dbConfig['username'] ?? ''));
                $pass = (string) ($dbConfig['password'] ?? '');
                if ($user === 'root' && empty($pass)) {
                    throw new RuntimeException(
                        'CRITICAL SECURITY ERROR: Database credentials must not use default "root" with an empty password in production.'
                    );
                }
            }

            // Database connection must use TLS/SSL in production
            if (in_array($defaultConn, ['mysql', 'mariadb'], true)) {
                $sslCa = $dbConfig['options'][PDO::MYSQL_ATTR_SSL_CA] ?? env('MYSQL_ATTR_SSL_CA');
                $sslMode = strtolower((string) (env('DB_SSL_MODE') ?? ''));
                $hasSsl = !empty($sslCa) || in_array($sslMode, ['require', 'verify-ca', 'verify-full'], true);
                if (!$hasSsl) {
                    throw new RuntimeException(
                        'CRITICAL SECURITY ERROR: Database connection must use TLS/SSL in production. Please set MYSQL_ATTR_SSL_CA or set DB_SSL_MODE=require.'
                    );
                }
            } elseif ($defaultConn === 'pgsql') {
                $sslMode = strtolower((string) ($dbConfig['sslmode'] ?? ''));
                if (!in_array($sslMode, ['require', 'verify-ca', 'verify-full'], true)) {
                    throw new RuntimeException(
                        'CRITICAL SECURITY ERROR: PostgreSQL database connection must use TLS/SSL (sslmode=require, verify-ca, or verify-full) in production.'
                    );
                }
            }

            // CORS wildcard rejection in production
            $allowedOrigins = config('cors.allowed_origins', []);
            if (in_array('*', $allowedOrigins, true)) {
                throw new RuntimeException(
                    'CRITICAL SECURITY ERROR: CORS allowed_origins must not allow wildcard (*) in production. Restrict to specific frontend domain(s).'
                );
            }
        }
    }
}

