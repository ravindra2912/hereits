<?php

namespace App\Http\Controllers;

use App\Models\UserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationTokenController extends Controller
{
    /**
     * Store or update FCM Token for current authenticated user/admin.
     */
    public function saveFcmToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string',
            'device_id' => 'nullable|string',
            'platform' => 'nullable|in:web,android,ios',
            'device_name' => 'nullable|string|max:100',
        ]);

        $token = $validated['fcm_token'];

        // 1. Admin Guard Check
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            $admin->update(['fcm_token' => $token]);

            return response()->json([
                'status' => true,
                'message' => 'Admin FCM token registered successfully',
            ]);
        }

        // 2. Web / User Guard Check (Customer or Business)
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            $user->update(['fcm_token' => $token]);

            // If device_id provided, sync into user_devices table for multi-device support
            $deviceId = $validated['device_id'] ?? md5($request->userAgent() ?? 'web');
            $platform = $validated['platform'] ?? 'web';

            UserDevice::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'device_id' => $deviceId,
                ],
                [
                    'fcm_token' => $token,
                    'platform' => $platform,
                    'device_name' => $validated['device_name'] ?? ($request->header('User-Agent') ? substr($request->header('User-Agent'), 0, 50) : 'Browser'),
                    'last_seen_at' => now(),
                    'is_active' => true,
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'User FCM token registered successfully',
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Unauthenticated user',
        ], 401);
    }
}
