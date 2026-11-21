<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Expert
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Always allow logout without contact verification
        if ($request->is('expert-manager/logout', '*/logout', 'logout') || str_contains((string) $request->route()?->getName(), 'logout')) {
            return $next($request);
        }

        if (Auth::guard('expert')->check()) {
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
            return $next($request);
        } else {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Session Expired', 'data' => array()]);
            } else {
                return redirect()->route('expert.login');
            }
        }
    }
}
