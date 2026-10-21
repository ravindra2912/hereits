<?php

namespace App\Http\Controllers\Business;

use App\Models\User;
use App\Models\Business;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Redirect;

class AuthController extends Controller
{
    /**
     * Display the business login form.
     */
    public function index(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role == "Business") {
                return redirect()->route('business.dashboard');
            } else {
                $hasAccess = \App\Models\BusinessUser::with('role')
                    ->where('user_id', $user->id)
                    ->get()
                    ->filter(function ($bu) {
                        return !empty($bu->role?->permissions['business_access']);
                    })
                    ->isNotEmpty();

                if ($hasAccess) {
                    return redirect()->route('business.dashboard');
                }

                return redirect()->route('home');
            }
        }
        return view('business.auth.login');
    }



    /**
     * Handle an incoming authentication request.
     */
    // public function store(Request $request)
    // {
    //     // Attempt to log the user in
    //     if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'role'=>1])) {
    //         return redirect()->route('admin.dashboard');
    //     }

    //     $msg = 'Incorrect username or password.';
    //         return view('admin.auth.login', compact('msg'));
    // }

    public function store(LoginRequest $request): RedirectResponse
    {
        $loginInput = $request->email;
        $user = User::where(function ($q) use ($loginInput) {
            $q->where('email', $loginInput);
            if (is_numeric($loginInput)) {
                $q->orWhere('contact', $loginInput);
            }
        })->first();

        if ($user && Hash::check($request['password'], $user->password)) {
            $hasAccess = false;
            $targetBusinessId = null;

            if ($user->role === 'Business') {
                $hasAccess = true;
                $targetBusinessId = $user->business_id;
                if (!$targetBusinessId) {
                    $business = Business::where('owner_id', $user->id)->first();
                    if ($business) {
                        $targetBusinessId = $business->id;
                        $user->business_id = $targetBusinessId;
                        $user->save();
                    }
                }
            } else {
                // Check all business permissions for this staff user
                $accessibleBusinessUsers = \App\Models\BusinessUser::with('role')
                    ->where('user_id', $user->id)
                    ->get()
                    ->filter(function ($bu) {
                        return !empty($bu->role?->permissions['business_access']);
                    });

                if ($accessibleBusinessUsers->isNotEmpty()) {
                    $hasAccess = true;

                    // If user's current business_id is valid for this user, keep it; otherwise set to the first valid one
                    if ($user->business_id && $accessibleBusinessUsers->contains('business_id', $user->business_id)) {
                        $targetBusinessId = $user->business_id;
                    } else {
                        $targetBusinessId = $accessibleBusinessUsers->first()->business_id;
                        $user->business_id = $targetBusinessId;
                        $user->save();
                    }
                }
            }

            if (!$hasAccess) {
                return redirect()->back()->with('error', 'You do not have permission to access the business panel.');
            }

            $request->authenticate();
            $request->session()->regenerate();

            // Enforce single device/browser login per user
            app(\App\Services\SessionLogoutService::class)->logoutOtherDevices($user->id);

            // Clear old session and sync fresh business session data & permissions
            app(\App\Services\BusinessSynchronizationService::class)->clearBusinessSession();
            $user->syncBusinessSessionData($targetBusinessId);

            return redirect()->intended(route('business.dashboard', absolute: false));
        } else {
            return redirect()->back()->with('error', 'invalid credentials!');
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->regenerate();

        return redirect()->route('business.login');
    }
}
