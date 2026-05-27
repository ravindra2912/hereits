<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Admin;
use App\Http\Middleware\Business;
use App\Http\Middleware\Front;
use App\Http\Middleware\Expert;
use App\Http\Middleware\CheckBusinessModule;
use App\Http\Middleware\MinifyHtml;
use App\Http\Middleware\POS;
use App\Http\Middleware\CheckBusinessPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::prefix('nimda')->group(base_path('routes/admin.php'));
            Route::prefix('business-manager')->group(base_path('routes/business.php'));
            Route::prefix('expert-manager')->group(base_path('routes/expert.php'));
            Route::prefix('pos-manager')->group(base_path('routes/pos.php'));
            Route::middleware('web')->group(base_path('routes/web.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => Admin::class,
            'business' => Business::class,
            'expert' => Expert::class,
            'front' => Front::class,
            'pos' => POS::class,
            'checkModule' => CheckBusinessModule::class,
            'checkBusinessPerm' => CheckBusinessPermission::class,
        ]);

        $middleware->redirectTo(
            guests: function (Request $request) {
                if ($request->is('expert-manager/*') || $request->is('expert-manager')) {
                    return route('expert.login');
                }
                if ($request->is('nimda/*') || $request->is('nimda')) {
                    return route('admin.login');
                }
                if ($request->is('business-manager/*') || $request->is('business-manager')) {
                    return route('business.login');
                }
                if ($request->is('pos-manager/*') || $request->is('pos-manager')) {
                    return route('pos.login');
                }
                return route('home');
            },
            users: function (Request $request) {
                if (Auth::guard('expert')->check()) {
                    return route('expert.dashboard');
                }
                if (Auth::guard('admin')->check()) {
                    return route('admin.dashboard');
                }
                if (Auth::guard('pos')->check()) {
                    return route('pos.dashboard');
                }
                if (Auth::check()) {
                    return route('business.dashboard');
                }
                return route('home');
            }
        );

        $middleware->web(append: [
            MinifyHtml::class,
            \App\Http\Middleware\TrackLastBusinessVisit::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
