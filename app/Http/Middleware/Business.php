<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Business
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // dd(Auth::user());
        // return $next($request);
        if (Auth::check()) {
            $user = Auth::user();
            $isBusinessOwner = ($user->role === 'Business');
            $isStaff = \App\Models\BusinessUser::where('user_id', $user->id)
                ->where('business_id', $user->business_id)
                ->exists();

            if (!$isBusinessOwner && !$isStaff) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Un-Authenticated Access', 'data' => array()]);
                } else {
                    return redirect()->route('business.login');
                }
            } else {
                // Ensure permissions are synchronized to session
                if (!session()->has('permissions')) {
                    $user->syncPermissionsToSession();
                }

                $business = $isBusinessOwner 
                    ? $user->getBusinessDetails 
                    : \App\Models\Business::find($user->business_id);

                if ($business && ($business->status == 'baned' || $business->status == 'in-active')) {
                    exit('Your business is not active, Please contact to Admin');
                }
                return $next($request);
            }
        } else {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Session Expired', 'data' => array()]);
            } else {
                return redirect()->route('business.login');
            }
        }
    }
}
