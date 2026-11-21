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
        // Exempt verification routes, logout routes, and admin panel
        if ($this->shouldExempt($request)) {
            return $next($request);
        }

        // Only enforce for logged-in users across guards (Expert, POS, Web)
        if (!Auth::check() && !Auth::guard('expert')->check() && !Auth::guard('pos')->check()) {
            return $next($request);
        }

        // Exempt admin panel and admin-authenticated sessions
        if (Auth::guard('admin')->check() || $request->is('nimda*')) {
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
     * Determine if the current request should be exempt from verification checks.
     *
     * @param Request $request
     * @return bool
     */
    protected function shouldExempt(Request $request): bool
    {
        // Path-based exemptions
        if ($request->is(
            'verify-contact',
            'verify-contact/*',
            'logout',
            '*/logout',
            'expert-manager/logout',
            'pos-manager/logout',
            'business-manager/logout',
            'nimda',
            'nimda/*',
            'api/*',
            'save-fcm-token',
            'set-location'
        )) {
            return true;
        }

        // Route-name exemptions
        $routeName = $request->route()?->getName();
        if ($routeName && (
            str_starts_with($routeName, 'verify.contact') ||
            str_contains($routeName, 'logout') ||
            str_starts_with($routeName, 'admin.')
        )) {
            return true;
        }

        return false;
    }
}

