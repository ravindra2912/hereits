<?php

namespace App\Http\Controllers\Business;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Business;
use Illuminate\Support\Facades\Redirect;


class AuthController extends Controller
{

    /**
     * Display the admin login form.
     */
    public function index(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        if (Auth::check()) {
            if (Auth::user()->role == "Business") {
                return redirect()->route('business.dashboard');
            } else {
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
        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request['password'], $user->password)) {
            $isBusinessOwner = ($user->role === 'Business');
            $isStaff = \App\Models\BusinessUser::where('user_id', $user->id)->exists();

            if (!$isBusinessOwner && !$isStaff) {
                return redirect()->back()->with('error', 'You do not have access to the business panel.');
            }

            if ($isBusinessOwner) {
                if ($user->business_id == null) {
                    $business = Business::select('id', 'owner_id', 'name', 'business_image')
                        ->with(['businessSetting:id,business_id,subscription_expiry_date'])
                        ->where('owner_id', $user->id)->first();
                    if ($business) {
                        $user->business_id = $business->id;
                        $user->save();
                        $user->getBusinessDetails = $business;
                    } else {
                        return redirect()->back()->with('error', 'Business not found!');
                    }
                }
            } else {
                // For staff, get and sync their associated business ID context
                $businessUser = \App\Models\BusinessUser::where('user_id', $user->id)->first();
                if ($businessUser) {
                    $user->business_id = $businessUser->business_id;
                    $user->save();
                } else {
                    return redirect()->back()->with('error', 'No associated business found for staff!');
                }
            }

            $request->authenticate();
            $request->session()->regenerate();

            $user->syncBusinessContextToSession();
            // Synchronize permissions to session immediately upon successful login
            $user->syncPermissionsToSession($user->business_id);

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
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('business.login');
    }
}
