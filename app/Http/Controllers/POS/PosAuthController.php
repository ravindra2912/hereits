<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\BusinessSetting;

class PosAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('pos')->check()) {
            return redirect()->route('pos.dashboard');
        }
        return view('pos.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('pos')->attempt($credentials, $request->filled('remember'))) {
            $user = Auth::guard('pos')->user();

            $isAuthorized = false;
            $businessId = $user->business_id;

            if ($user->role === 'Business' && $user->status === 'active') {
                $isAuthorized = true;
                if (!$businessId) {
                    $businessId = Business::where('owner_id', $user->id)->value('id');
                    if ($businessId) {
                        $user->update(['business_id' => $businessId]);
                    }
                }
            } else if ($user->role === 'User' && $user->status === 'active') {
                $businessUser = BusinessUser::where('user_id', $user->id)->first();
                if ($businessUser) {
                    $isAuthorized = true;
                    $businessId = $businessUser->business_id;
                    $user->update(['business_id' => $businessId]);
                }
            }

            if ($isAuthorized && $businessId) {
                // Check if business has POS access enabled
                $businessSetting = BusinessSetting::where('business_id', $businessId)->first();

                if (!$businessSetting || !$businessSetting->is_pos_access) {
                    Auth::guard('pos')->logout();
                    return response()->json([
                        'success' => false,
                        'message' => 'POS access is not enabled for this business. Please contact admin.'
                    ], 403);
                }

                $request->session()->regenerate();

                // Store permissions in session for efficiency
                $user->syncPermissionsToSession();

                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect' => route('pos.dashboard')
                ]);
            }

            Auth::guard('pos')->logout();
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials.'
        ], 401);
    }

    public function logout(Request $request)
    {
        Auth::guard('pos')->logout();

        // Remove session permission keys
        $request->session()->forget(['permissions', 'role_name', 'business_name']);

        // Regenerate the session ID for security without clearing other guard data
        $request->session()->regenerate();

        return redirect()->route('pos.login');
    }
}
