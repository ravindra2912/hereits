<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Product;
use App\Models\Service;
use App\Models\ReviewAndRating;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    public function show($id)
    {
        try {
            $business = Business::select('id', 'name', 'slug', 'business_type', 'business_category_id', 'address', 'latitude', 'longitude', 'country_id', 'state_id', 'city_id', 'area', 'pincode', 'contact', 'rating', 'business_image', 'business_logo')
                ->with([
                    'businessCategory:id,name',
                    'city:id,name',
                    'state:id,name',
                    'businessSetting:id,business_id,is_verified',
                    'experts:id,business_id,expert_name,slug,expert_image',
                    'reviews:id,business_id,user_id,rating,review,created_at',
                    'reviews.user:id,first_name,last_name'
                ])->find($id);

            if (!$business) {
                return response()->json([
                    'status_code' => 404,
                    'success' => false,
                    'message' => 'Business not found',
                    'data' => null
                ], 404);
            }

            $business->business_image = getImage($business->business_image, 'business');
            $business->business_logo = getImage($business->business_logo, 'business');

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Business details retrieved',
                'data' => $business
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function services($id)
    {
        try {
            $services = Service::where('business_id', $id)
                ->where('status', 'active')
                ->get(['id', 'business_id', 'category_id', 'name', 'description', 'price', 'status']);
            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Services retrieved',
                'data' => $services
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function products($id)
    {
        try {
            $products = Product::where('business_id', $id)
                ->where('status', 'active')
                ->get(['id', 'business_id', 'category_id', 'name', 'description', 'price', 'status']);
            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Products retrieved',
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function reviews($id)
    {
        try {
            $reviews = ReviewAndRating::with('user:id,first_name,last_name')
                ->where('business_id', $id)
                ->latest()
                ->get(['id', 'business_id', 'user_id', 'rating', 'review', 'created_at']);

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Reviews retrieved',
                'data' => $reviews
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
