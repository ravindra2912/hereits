<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationApiController extends Controller
{
    public function setLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:current_location,search',
            'location_name' => 'required|string',
            'full_address' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'nullable|numeric|min:1',
            'area_lat_long' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'success' => false,
                'message' => $validator->errors()->first(),
                'data' => null
            ], 422);
        }

        $locationData = [
            'type' => $request->type,
            'location_name' => $request->location_name,
            'full_address' => $request->full_address ?? $request->location_name,
            'latitude' => (float)$request->latitude,
            'longitude' => (float)$request->longitude,
            'radius' => (float)($request->radius ?? 100),
            'area_lat_long' => $request->area_lat_long,
        ];

        return response()->json([
            'status_code' => 200,
            'success' => true,
            'message' => 'Location updated successfully',
            'data' => $locationData
        ]);
    }

    public function searchCities(Request $request)
    {
        try {
            $query = $request->get('q', '');

            $cities = City::with('state:id,name')
                ->where('status', 'active')
                ->when($query !== '', function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%");
                })
                ->take(20)
                ->get(['id', 'name', 'state_id']);

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Cities fetched',
                'data' => $cities
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
