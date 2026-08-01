<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\Favorite;
use Illuminate\Http\Request;

class ApiV1HomeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $lat = $request->header('X-Latitude') ?? $request->get('latitude');
            $lng = $request->header('X-Longitude') ?? $request->get('longitude');
            $areaLatLong = $request->header('X-Area-Lat-Long') ?? $request->get('area_lat_long');
            $radius = $request->header('X-Radius') ?? $request->get('radius');

            $location = null;
            if ($areaLatLong || ($lat && $lng)) {
                $location = [
                    'area_lat_long' => $areaLatLong,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'radius' => !empty($radius) ? $radius : 100,
                ];
            }
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
            ->take(12)->get(['id', 'name', 'slug', 'image'])->map(function($cat) {
                $cat->image = getImage($cat->image, 'category');
                return $cat;
            });
            
            $query = Business::select('id', 'owner_id', 'name', 'slug', 'business_type', 'business_category_id', 'area', 'latitude', 'longitude', 'city_id', 'rating', 'business_image')
                ->with(['businessCategory:id,name', 'city:id,name', 'businessSetting:id,business_id,is_verified'])
                ->where('status', 'active');

            if ($areaLatLong) {
                $coords = explode(',', $areaLatLong);
                if (count($coords) === 4) {
                    $query->inBoundaries($coords[0], $coords[1], $coords[2], $coords[3]);
                }
            } elseif ($lat && $lng) {
                $radius = !empty($radius) ? $radius : 100;
                $query->nearby($lat, $lng, $radius);
            }

            $featuredBusinesses = $query->take(10)->get()->map(function($business) {
                $business->business_image = getImage($business->business_image, 'business');
                $business->is_verified = $business->businessSetting?->is_verified ? 1 : 0;
                return $business;
            });

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Home data fetched successfully',
                'data' => [
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
            $query = Business::select('id', 'owner_id', 'name', 'slug', 'business_type', 'business_category_id', 'area', 'latitude', 'longitude', 'city_id', 'rating', 'business_image')
                ->with(['businessCategory:id,name', 'city:id,name', 'reviews', 'businessSetting:id,business_id,is_verified'])
                ->where('status', 'active');

            $lat = $request->header('X-Latitude') ?? $request->get('latitude');
            $lng = $request->header('X-Longitude') ?? $request->get('longitude');
            $areaLatLong = $request->header('X-Area-Lat-Long') ?? $request->get('area_lat_long');
            $radius = $request->header('X-Radius') ?? $request->get('radius');

            if ($areaLatLong) {
                $coords = explode(',', $areaLatLong);
                if (count($coords) === 4) {
                    $query->inBoundaries($coords[0], $coords[1], $coords[2], $coords[3]);
                }
            } elseif ($lat && $lng) {
                $radius = !empty($radius) ? $radius : 100;
                $query->nearby($lat, $lng, $radius);
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

            $businesses = $query->paginate(12);
            $businesses->getCollection()->transform(function($business) {
                $business->business_image = getImage($business->business_image, 'business');
                $business->is_verified = $business->businessSetting?->is_verified ? 1 : 0;
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
                'type' => 'nullable|in:business,product,service,expert',
                'item_id' => 'nullable|integer',
            ]);

            $user = $request->user();
            $type = $request->input('type', 'business');
            $itemId = $request->input('item_id', $request->business_id);
            $businessId = $request->business_id;

            $favorite = Favorite::where('user_id', $user->id)
                ->where('business_id', $businessId)
                ->where('favorite_type', $type)
                ->where('favorite_item_id', $itemId)
                ->first();

            if ($favorite) {
                $favorite->delete();
                $isFavorite = false;
                $msg = 'Removed from favorites';
            } else {
                Favorite::create([
                    'user_id' => $user->id,
                    'business_id' => $businessId,
                    'favorite_type' => $type,
                    'favorite_item_id' => $itemId,
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
