<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class ApiV1LocationApiController extends Controller
{

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
