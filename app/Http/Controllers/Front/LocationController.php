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


        // Manage location history (last 5 unique locations)
        $history = json_decode(Cookie::get('location_history', '[]'), true);
        
        // Remove existing occurrence of this location to move it to top
        $history = array_filter($history, function($item) use ($request) {
            return $item['location_name'] !== $request->location_name;
        });

        // Add new location to the beginning
        array_unshift($history, $locationData);

        // Keep only last 5
        $history = array_slice($history, 0, 5);

        // Store cookies for 1 week
        $locationCookie = cookie('user_location', json_encode($locationData), 7 * 24 * 60);
        $historyCookie = cookie('location_history', json_encode($history), 7 * 24 * 60);

        return response()->json(['status' => 'success', 'message' => 'Location updated successfully'])
            ->withCookie($locationCookie)
            ->withCookie($historyCookie);

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
