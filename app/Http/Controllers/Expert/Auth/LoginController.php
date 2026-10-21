<?php

namespace App\Http\Controllers\Expert\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function create()
    {
        try {
            return view('expert.auth.login');
        } catch (\Exception $e) {
            return back()->with('error', checkResponse($e));
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()]);
        }

        try {
            $user = \App\Models\User::where('email', $request->email)->first();

            if ($user && \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
                $expert = \App\Models\Expert::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->first();

                if ($expert) {
                    Auth::guard('expert')->login($expert);
                    $request->session()->regenerate();
                    app(\App\Services\SessionLogoutService::class)->logoutOtherDevices($expert->id);

                    return response()->json([
                        'success' => true,
                        'message' => 'Login successful',
                        'redirect' => route('expert.dashboard')
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => ['email' => ['No active expert account found for this user.']]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => ['email' => ['The provided credentials do not match our records.']]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => checkResponse($e)]);
        }
    }

    public function destroy(Request $request)
    {
        try {
            Auth::guard('expert')->logout();
            $request->session()->regenerate();
            return redirect()->route('expert.login');
        } catch (\Exception $e) {
            return back()->with('error', checkResponse($e));
        }
    }
}
