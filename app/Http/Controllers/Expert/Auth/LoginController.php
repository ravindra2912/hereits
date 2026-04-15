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
            return back()->with('error', $e->getMessage());
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
            if (Auth::guard('expert')->attempt($request->only('email', 'password'))) {
                $request->session()->regenerate();
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect' => route('expert.dashboard')
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => ['email' => ['The provided credentials do not match our records.']]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function destroy(Request $request)
    {
        try {
            Auth::guard('expert')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('expert.login');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
