<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    /**
     * Set the user location cookie.
     */
    public function setLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:current_location,search',
            'location_name' => 'required|string',
            'full_address' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'nullable|numeric|min:5',
            'area_lat_long' => 'nullable|string', // Format: "lat,long" or similar
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $locationData = [
            'type' => $request->type,
            'location_name' => $request->location_name,
            'full_address' => $request->full_address ?? $request->location_name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ];

        if ($request->type === 'current_location') {
            $locationData['radius'] = $request->radius ?? 5;
        } else {
            $locationData['area_lat_long'] = $request->area_lat_long;
        }


        // Store cookie for 1 week (7 * 24 * 60 minutes)
        $cookie = cookie('user_location', json_encode($locationData), 7 * 24 * 60);

        return response()->json(['status' => 'success', 'message' => 'Location updated successfully'])
            ->withCookie($cookie);
    }

    /**
     * Get the user location from cookie.
     */
    public static function getUserLocation()
    {
        $location = Cookie::get('user_location');
        return $location ? json_decode($location, true) : null;
    }
}
