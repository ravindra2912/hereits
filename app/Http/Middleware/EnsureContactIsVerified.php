<?php

namespace App\Http\Middleware;

use App\Services\ContactVerificationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureContactIsVerified
{
    protected ContactVerificationService $verificationService;

    public function __construct(ContactVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    /**
     * Handle an incoming request.
     * Enforces contact number verification across Front, Business, POS, and Expert panels.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Always allow logout requests immediately without verification check
        if ($this->isLogoutRequest($request)) {
            return $next($request);
        }

        // 2. Exempt verification routes, admin panel (nimda*), assets and webhook endpoints
        if ($this->shouldExempt($request)) {
            return $next($request);
        }

        // 3. Only enforce for authenticated users across active guards (Expert, POS, Web)
        if (!Auth::check() && !Auth::guard('expert')->check() && !Auth::guard('pos')->check()) {
            return $next($request);
        }

        $target = $this->verificationService->getPendingVerificationTarget($request);

        if ($target !== null) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contact verification is required to continue.',
                    'redirect' => route('verify.contact'),
                ], 403);
            }

            if ($request->isMethod('GET')) {
                session(['contact_verify_return_url' => $request->fullUrl()]);
            }

            return redirect()->route('verify.contact');
        }

        return $next($request);
    }

    /**
     * Determine if the request is a logout action.
     *
     * @param Request $request
     * @return bool
     */
    protected function isLogoutRequest(Request $request): bool
    {
        $uri = trim($request->path(), '/');
        $routeName = (string) $request->route()?->getName();

        return $request->is(
            'logout',
            '*/logout',
            'expert-manager/logout',
            'pos-manager/logout',
            'business-manager/logout',
            'nimda/logout'
        )
        || str_ends_with($uri, '/logout')
        || $uri === 'logout'
        || str_contains($routeName, 'logout')
        || str_ends_with($routeName, '.logout');
    }

    /**
     * Determine if the current request should be exempt from verification checks.
     *
     * @param Request $request
     * @return bool
     */
    protected function shouldExempt(Request $request): bool
    {
        // Exempt admin portal routes specifically
        if ($request->is('nimda', 'nimda/*')) {
            return true;
        }

        // Path-based exemptions
        if ($request->is(
            'verify-contact',
            'verify-contact/*',
            'api/*',
            'save-fcm-token',
            'set-location'
        )) {
            return true;
        }

        // Route-name exemptions
        $routeName = (string) $request->route()?->getName();
        if ($routeName !== '' && (
            str_starts_with($routeName, 'verify.contact') ||
            str_starts_with($routeName, 'admin.')
        )) {
            return true;
        }

        return false;
    }
}

