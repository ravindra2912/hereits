<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Admin;
use App\Http\Middleware\Business;
use App\Http\Middleware\Front;
use App\Http\Middleware\EnsureContactIsVerified;
use App\Http\Middleware\Expert;
use App\Http\Middleware\CheckBusinessModule;
use App\Http\Middleware\MinifyHtml;
use App\Http\Middleware\POS;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
            Route::middleware('web')->group(base_path('routes/documents.php'));
            Route::middleware('web')->group(base_path('routes/web.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'admin' => Admin::class,
            'business' => Business::class,
            'expert' => Expert::class,
            'front' => Front::class,
            'pos' => POS::class,
            'checkModule' => CheckBusinessModule::class,
            'verified.contact' => EnsureContactIsVerified::class,
        ]);

        $middleware->redirectTo(
            guests: function (Request $request) {
                if ($request->is('api/*') || $request->expectsJson()) {
                    return null;
                }
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
            EnsureContactIsVerified::class,
        ]);

        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'status_code' => 401,
                    'message' => 'Unauthenticated or invalid API token.',
                ], 401);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'status_code' => 404,
                    'message' => 'Requested API route or resource not found.',
                ], 404);
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'status_code' => 422,
                    'message' => $e->validator->errors()->first() ?? 'Validation failed.',
                    'errors' => $e->validator->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            $correlationId = (string) Str::uuid();

            // Log detailed error and stack trace strictly to server-side logs
            Log::error("Unhandled Exception [Correlation ID: {$correlationId}]: " . $e->getMessage(), [
                'correlation_id' => $correlationId,
                'exception'      => get_class($e),
                'file'           => $e->getFile(),
                'line'           => $e->getLine(),
                'trace'          => $e->getTraceAsString(),
                'url'            => $request->fullUrl(),
                'method'         => $request->method(),
                'ip'             => $request->ip(),
            ]);

            $statusCode = 500;
            if ($e instanceof HttpExceptionInterface) {
                $statusCode = $e->getStatusCode();
            }

            // Clean, sanitized API / JSON response
            if ($request->is('api/*') || $request->expectsJson() || $request->ajax()) {
                $clientMessage = match ($statusCode) {
                    401 => 'Unauthenticated access.',
                    403 => 'Access forbidden.',
                    404 => 'Resource not found.',
                    429 => 'Too many requests. Please slow down.',
                    default => 'An unexpected server error occurred. Please contact support with the correlation ID if the issue persists.',
                };

                return response()->json([
                    'success' => false,
                    'status_code' => $statusCode,
                    'message' => $clientMessage,
                    'correlation_id' => $correlationId,
                ], $statusCode)->header('X-Correlation-ID', $correlationId);
            }

            // Web HTML responses for server errors (500-level)
            if ($statusCode >= 500) {
                return response()->view('errors.500', [
                    'correlationId' => $correlationId,
                ], $statusCode)->header('X-Correlation-ID', $correlationId);
            }
        });
    })->create();
