<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\Favorite;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $location = null;
            if ($request->filled('area_lat_long') || ($request->filled('latitude') && $request->filled('longitude'))) {
                $location = [
                    'area_lat_long' => $request->get('area_lat_long'),
                    'latitude' => $request->get('latitude'),
                    'longitude' => $request->get('longitude'),
                    'radius' => $request->get('radius'),
                ];
            }

            $banners = Banner::where('status', 'active')->get(['id', 'business_id', 'image_url', 'status']);
            $categories = BusinessCategory::where('status', 'active')
            ->whereHas('businesses', function ($query) use ($location) {
                    $query->where('status', 'active')
                        ->when($location, function ($q) use ($location) {
                            if (!empty($location['area_lat_long'])) {
                                $coords = explode(',', $location['area_lat_long']);
                                if (count($coords) === 4) {
                                    return $q->inBoundaries($coords[0], $coords[1], $coords[2], $coords[3]);
                                }
                            }
                            if (isset($location['latitude']) && isset($location['longitude'])) {
                                return $q->nearby($location['latitude'], $location['longitude'], $location['radius'] ?? 100);
                            }
                        });
                })
                ->withCount(['businesses' => function ($query) use ($location) {
                    $query->where('status', 'active')
                        ->when($location, function ($q) use ($location) {
                            if (!empty($location['area_lat_long'])) {
                                $coords = explode(',', $location['area_lat_long']);
                                if (count($coords) === 4) {
                                    return $q->inBoundaries($coords[0], $coords[1], $coords[2], $coords[3]);
                                }
                            }
                            if (isset($location['latitude']) && isset($location['longitude'])) {
                                return $q->nearby($location['latitude'], $location['longitude'], $location['radius'] ?? 100);
                            }
                        });
                }])
            ->take(12)->get(['id', 'name', 'slug', 'image']);
            
            $query = Business::select('id', 'owner_id', 'name', 'slug', 'business_type', 'business_category_id', 'address', 'latitude', 'longitude', 'city_id', 'rating', 'business_image', 'business_logo')
                ->with(['businessCategory:id,name', 'city:id,name'])
                ->where('status', 'active');

            if ($request->filled('area_lat_long')) {
                $coords = explode(',', $request->area_lat_long);
                if (count($coords) === 4) {
                    $query->inBoundaries($coords[0], $coords[1], $coords[2], $coords[3]);
                }
            } elseif ($request->filled('latitude') && $request->filled('longitude')) {
                $radius = $request->get('radius', 100);
                $query->nearby($request->latitude, $request->longitude, $radius);
            }

            $featuredBusinesses = $query->take(10)->get()->map(function($business) {
                $business->business_image = getImage($business->business_image, 'business');
                $business->business_logo = getImage($business->business_logo, 'business');
                return $business;
            });

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Home data fetched successfully',
                'data' => [
                    'banners' => $banners,
                    'categories' => $categories,
                    'featured_businesses' => $featuredBusinesses,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function categories()
    {
        try {
            $categories = BusinessCategory::where('status', 'active')->get(['id', 'name', 'slug', 'image']);
            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Categories retrieved',
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function businesses(Request $request)
    {
        try {
            $query = Business::select('id', 'owner_id', 'name', 'slug', 'business_type', 'business_category_id', 'address', 'latitude', 'longitude', 'city_id', 'rating', 'business_image', 'business_logo')
                ->with(['businessCategory:id,name', 'city:id,name', 'reviews'])
                ->where('status', 'active');

            if ($request->filled('area_lat_long')) {
                $coords = explode(',', $request->area_lat_long);
                if (count($coords) === 4) {
                    $query->inBoundaries($coords[0], $coords[1], $coords[2], $coords[3]);
                }
            } elseif ($request->filled('latitude') && $request->filled('longitude')) {
                $radius = $request->get('radius', 100);
                $query->nearby($request->latitude, $request->longitude, $radius);
            }

            if ($request->has('category_id') && $request->category_id != '') {
                $query->where('business_category_id', $request->category_id);
            }

            if ($request->has('search') && $request->search != '') {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
                });
            }

            $businesses = $query->paginate(15);
            $businesses->getCollection()->transform(function($business) {
                $business->business_image = getImage($business->business_image, 'business');
                $business->business_logo = getImage($business->business_logo, 'business');
                return $business;
            });

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Businesses retrieved',
                'data' => $businesses
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function toggleFavorite(Request $request)
    {
        try {
            $request->validate([
                'business_id' => 'required|exists:businesses,id',
            ]);

            $user = $request->user();
            $favorite = Favorite::where('user_id', $user->id)
                ->where('business_id', $request->business_id)
                ->first();

            if ($favorite) {
                $favorite->delete();
                $isFavorite = false;
                $msg = 'Removed from favorites';
            } else {
                Favorite::create([
                    'user_id' => $user->id,
                    'business_id' => $request->business_id,
                ]);
                $isFavorite = true;
                $msg = 'Added to favorites';
            }

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => $msg,
                'data' => ['is_favorite' => $isFavorite]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
