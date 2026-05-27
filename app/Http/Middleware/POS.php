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
            return redirect()->route('pos.login');
        }

        $user = Auth::guard('pos')->user();
        if (!in_array($user->role, ['Business', 'User']) || $user->status !== 'active') {
            Auth::guard('pos')->logout();
            return redirect()->route('pos.login')->with('error', 'Unauthorized access.');
        }

        // Sync permissions if not in session
        if (!session()->has('permissions') || !session()->has('role_name') || !session()->has('business_name')) {
            $user->syncPermissionsToSession();
        }

        return $next($request);
    }
}
