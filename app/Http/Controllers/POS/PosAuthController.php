<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\BusinessSetting;
use App\Services\PosSessionService;

class PosAuthController extends Controller
{
    protected PosSessionService $posSessionService;

    public function __construct(PosSessionService $posSessionService)
    {
        $this->posSessionService = $posSessionService;
    }

    public function showLogin(Request $request)
    {
        if (Auth::guard('pos')->check()) {
            return redirect()->route('pos.dashboard');
        }
        $section = $request->get('section');
        return view('pos.auth.login', compact('section'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required',
        ]);

        $loginInput = $request->email;
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'contact';
        $credentials = [
            $fieldType => $loginInput,
            'password' => $request->password,
        ];

        if (Auth::guard('pos')->attempt($credentials, $request->filled('remember'))) {
            $user = Auth::guard('pos')->user();

            if (!in_array($user->role, ['Business', 'User']) || $user->status !== 'active') {
                Auth::guard('pos')->logout();
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized or inactive account.'
                ], 403);
            }

            // Synchronize all POS session data via PosSessionService
            $this->posSessionService->syncAll($user);

            $posBusinessId = $this->posSessionService->getPosBusinessId();

            if (!$posBusinessId) {
                Auth::guard('pos')->logout();
                return response()->json([
                    'success' => false,
                    'message' => 'No active business associated with your account.'
                ], 403);
            }

            // Check if active business has POS access enabled
            $businessSetting = getBusinessSettings($posBusinessId);

            if (!$businessSetting || !$businessSetting->is_pos_access) {
                Auth::guard('pos')->logout();
                return response()->json([
                    'success' => false,
                    'message' => 'POS access is not enabled for this business. Please contact admin.'
                ], 403);
            }

            $request->session()->regenerate();

            // Enforce single device/browser login per user
            app(\App\Services\SessionLogoutService::class)->logoutOtherDevices($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'redirect' => route('pos.dashboard')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials.'
        ], 401);
    }

    public function switchBusiness(Request $request)
    {
        $request->validate([
            'business_id' => 'required|integer',
        ]);

        $user = Auth::guard('pos')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $targetBusinessId = (int) $request->business_id;

        // Verify authorization for target business
        $hasAccess = false;
        if ($user->role === 'Business') {
            $hasAccess = Business::where('id', $targetBusinessId)->where('owner_id', $user->id)->exists();
        } else {
            $hasAccess = BusinessUser::where('business_id', $targetBusinessId)->where('user_id', $user->id)->exists();
        }

        if (!$hasAccess) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to switch to this business.'], 403);
        }

        // Check if POS access is enabled for target business
        $businessSetting = BusinessSetting::where('business_id', $targetBusinessId)->first();
        if (!$businessSetting || !$businessSetting->is_pos_access) {
            return response()->json([
                'success' => false,
                'message' => 'POS access is not enabled for the selected business.'
            ], 403);
        }

        // Synchronize session for target business via PosSessionService
        $this->posSessionService->syncAll($user, $targetBusinessId);

        return response()->json([
            'success' => true,
            'message' => 'Switched business successfully!',
            'redirect' => route('pos.dashboard')
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('pos')->logout();

        // Clear all POS session data via PosSessionService
        $this->posSessionService->clearPosSession();

        $request->session()->regenerate();

        return redirect()->route('pos.login');
    }
}
