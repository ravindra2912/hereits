<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Front
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            if (Auth::user()->role == 'Business' || Auth::user()->role == 'User') {
                return $next($request);
            } else {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Un-Authenticated Access', 'data' => array()]);
                } else {
                    return redirect()->route('home');
                }
            }
        } else {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Session Expired', 'data' => array()]);
            } else {
                return redirect()->route('home');
            }
        }
    }
}
