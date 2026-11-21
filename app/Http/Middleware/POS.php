<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class POS
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('pos')->check()) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired. Please log in again.',
                    'redirect' => route('pos.login')
                ], 401);
            }
            return redirect()->route('pos.login');
        }

        $user = Auth::guard('pos')->user();
        if (!in_array($user->role, ['Business', 'User']) || $user->status !== 'active') {
            Auth::guard('pos')->logout();
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.',
                    'redirect' => route('pos.login')
                ], 403);
            }
            return redirect()->route('pos.login')->with('error', 'Unauthorized access.');
        }

        // Always allow logout without contact verification
        if ($request->is('pos-manager/logout', '*/logout', 'logout') || str_contains((string) $request->route()?->getName(), 'logout')) {
            return $next($request);
        }

        $target = app(\App\Services\ContactVerificationService::class)->getPendingVerificationTarget($request);
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

        // Sync session data if missing, or perform periodic permissions re-sync every 10 mins for non-owners
        if (!session()->has('pos_business_details') || !session()->has('pos_user_businesses') || !session()->has('pos_permissions')) {
            app(\App\Services\PosSessionService::class)->syncAll($user);
        } else {
            app(\App\Services\PosSessionService::class)->checkPeriodicSync($user);
        }

        return $next($request);
    }
}
