<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Product;
use App\Models\Service;
use App\Models\ReviewAndRating;
use Illuminate\Http\Request;
use App\Models\Favorite;

use App\Models\Expert;
use App\Models\AppointmentDepartment;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Gallery;
use Illuminate\Support\Facades\Auth;

class BusinessController extends Controller
{
    public function show($id)
    {
        try {
            $userId = Auth::guard('api')->id();
            $business = Business::select('id', 'template', 'name', 'slug', 'business_image', 'business_logo', 'address', 'contact', 'business_category_id', 'latitude', 'longitude', 'country_id', 'state_id', 'city_id', 'area', 'seo_description', 'seo_keyword', 'rating', 'pincode', 'facebook', 'twitter', 'instagram', 'linkedin', 'youtube')
                ->with([
                    'businessCategory',
                    'country',
                    'state',
                    'city'
                ])
                ->when($userId, function ($query) use ($userId) {
                    $query->withExists(['favorites as is_favorited' => function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    }]);
                })
                ->where('status', 'active')
                ->find($id);

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

            $setting = getBusinessSettings($business->id);
            $isSubscriptionActive = $setting && ($setting->subscription_expiry_date > now());

            $departments = array();
            $banners = array();
            $experts = collect();
            $totalExperts = 0;
            $details = [
                'productCategories' => collect(),
                'products' => collect(),
                'categoriesWithProducts' => collect(),
                'serviceCategories' => collect(),
                'services' => collect(),
                'categoriesWithServices' => collect(),
            ];
            $galleries = collect();

            // Banners (Hero Slider)
            // $banners = Banner::where('business_id', $business->id)->where('status', 'active')->pluck('image_url')->toArray();
            // $banners = array_map(function ($url) {
            //     return getImage($url);
            // }, $banners);

            // Gallery
            $galleries = Gallery::select('id', 'image_url', 'title', 'business_id', 'type')
                ->where('business_id', $business->id)
                ->where('status', 'active')
                ->latest()
                ->limit(4)
                ->get();
            foreach ($galleries as $g) {
                $g->image_url = getImage($g->image_url);
            }

            if ($isSubscriptionActive) {
                if ($setting->is_appointment_with_department) {
                    // $departments = AppointmentDepartment::select('id', 'department_name')->where('business_id', $business->id)->get();
                }

                // Experts
                $experts = Expert::select('id', 'business_id', 'title', 'expert_name', 'expert_image', 'department_id', 'slug', 'rating', 'description', 'is_appointment_book_with_time_slot')
                    ->with([
                        'department',
                        'business:id,name,slug,address,latitude,longitude,business_image'
                    ])
                    ->when($userId, function ($query) use ($userId) {
                        $query->withExists(['favorites as is_favorited' => function ($q) use ($userId) {
                            $q->where('user_id', $userId);
                        }]);
                    })
                    ->where('business_id', $business->id)
                    ->where('status', 'active')
                    ->limit(4)
                    ->get();
                foreach ($experts as $exp) {
                    $exp->expert_image = getImage($exp->expert_image);
                }

                $totalExperts = $experts->count();

                // Products
                if ($setting->is_ecommerce_system) {
                    //product categories
                    $details['productCategories'] = Category::select('id', 'name', 'image_url', 'business_id')
                        ->where('business_id', $business->id)
                        ->where('type', 'Products')
                        ->where('show_in_home', true)
                        ->where('status', 'active')
                        ->get();
                    foreach ($details['productCategories'] as $cat) {
                        $cat->image_url = getImage($cat->image_url);
                    }

                    // category with products
                     $details['categoriesWithProducts'] = Category::select('id', 'name', 'image_url', 'business_id')
                        ->with(['products' => function ($query) use ($userId) {
                            $query->select('id', 'name', 'slug', 'description', 'price', 'sell_price', 'max_price', 'min_price', 'price_type', 'category_id', 'business_id')
                                ->with(['firstImage:id,product_id,image_url', 'category:id,name'])
                                ->when($userId, function ($query) use ($userId) {
                                    $query->withExists(['favorites as is_favorited' => function ($q) use ($userId) {
                                        $q->where('user_id', $userId);
                                    }]);
                                })
                                ->where('status', 'active')
                                ->limit(6);
                        }])
                        ->whereHas('products', function ($query) {
                            $query->where('status', 'active');
                        })
                        ->where('business_id', $business->id)
                        ->where('type', 'Products')
                        ->where('show_in_home_with_items', true)
                        ->where('status', 'active')
                        ->get();
                    foreach ($details['categoriesWithProducts'] as $cat) {
                        $cat->image_url = getImage($cat->image_url);
                        foreach ($cat->products as $p) {
                            if ($p->firstImage) {
                                $p->firstImage->image_url = getImage($p->firstImage->image_url);
                            }
                        }
                    }

                    if ($details['categoriesWithProducts']->count() == 0) {
                        $details['products'] = Product::select('id', 'name', 'slug', 'description', 'price', 'sell_price', 'max_price', 'min_price', 'price_type', 'category_id', 'business_id')
                            ->with(['firstImage:id,product_id,image_url', 'category:id,name'])
                            ->when($userId, function ($query) use ($userId) {
                                $query->withExists(['favorites as is_favorited' => function ($q) use ($userId) {
                                    $q->where('user_id', $userId);
                                }]);
                            })
                            ->where('business_id', $business->id)
                            ->where('status', 'active')
                            ->limit(6)
                            ->get();
                        foreach ($details['products'] as $p) {
                            if ($p->firstImage) {
                                $p->firstImage->image_url = getImage($p->firstImage->image_url);
                            }
                        }
                    }
                }

                // Services
                if ($setting->is_service_system) {
                    //service categories
                    $details['serviceCategories'] = Category::select('id', 'name', 'image_url', 'business_id')
                        ->where('business_id', $business->id)
                        ->where('type', 'Services')
                        ->where('show_in_home', true)
                        ->where('status', 'active')
                        ->get();
                    foreach ($details['serviceCategories'] as $cat) {
                        $cat->image_url = getImage($cat->image_url);
                    }

                    // category with services
                    $details['categoriesWithServices'] = Category::select('id', 'name', 'image_url', 'business_id')
                        ->with(['services' => function ($query) use ($userId) {
                            $query->select('id', 'name', 'slug', 'description', 'price_type', 'price', 'max_price', 'min_price', 'category_id', 'image_url', 'business_id')
                                ->with('category:id,name')
                                ->when($userId, function ($query) use ($userId) {
                                    $query->withExists(['favorites as is_favorited' => function ($q) use ($userId) {
                                        $q->where('user_id', $userId);
                                    }]);
                                })
                                ->where('status', 'active')
                                ->limit(4);
                        }])
                        ->whereHas('services', function ($query) {
                            $query->where('status', 'active');
                        })
                        ->where('business_id', $business->id)
                        ->where('type', 'Services')
                        ->where('show_in_home_with_items', true)
                        ->where('status', 'active')
                        ->get();
                    foreach ($details['categoriesWithServices'] as $cat) {
                        $cat->image_url = getImage($cat->image_url);
                        foreach ($cat->services as $s) {
                            $s->image_url = getImage($s->image_url);
                        }
                    }

                    if ($details['categoriesWithServices']->count() == 0) {
                        $details['services'] = Service::select('id', 'name', 'slug', 'description', 'price_type', 'price', 'max_price', 'min_price', 'category_id', 'image_url', 'business_id')
                            ->with('category:id,name')
                            ->when($userId, function ($query) use ($userId) {
                                $query->withExists(['favorites as is_favorited' => function ($q) use ($userId) {
                                    $q->where('user_id', $userId);
                                }]);
                            })
                            ->where('business_id', $business->id)
                            ->where('status', 'active')
                            ->limit(4)
                            ->get();
                        foreach ($details['services'] as $s) {
                            $s->image_url = getImage($s->image_url);
                        }
                    }
                }
            }

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Business details retrieved',
                'data' => [
                    'business' => $business,
                    'setting' => $setting,
                    'isSubscriptionActive' => $isSubscriptionActive,
                    // 'departments' => $departments,
                    // 'banners' => $banners,
                    'experts' => $experts,
                    'details' => $details,
                    'galleries' => $galleries,
                    'totalExperts' => $totalExperts,
                ]
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

    public function productDetails($id)
    {
        try {
            $product = Product::where('id', $id)
                ->orWhere('slug', $id)
                ->with(['business', 'category', 'images' => function ($query) {
                    $query->select('id', 'product_id', 'image_url');
                }])
                ->where('status', 'active')
                ->firstOrFail();

            foreach ($product->images as $img) {
                $img->image_url = getImage($img->image_url);
            }

            $business = $product->business;
            $setting = getBusinessSettings($business->id);

            // Related Products
            $relatedProducts = Product::select('id', 'name', 'slug', 'price', 'sell_price', 'max_price', 'min_price', 'price_type', 'business_id')
                ->where('business_id', $business->id)
                ->where('id', '!=', $product->id)
                ->where('status', 'active')
                ->with(['firstImage:id,product_id,image_url'])
                ->inRandomOrder()
                ->limit(4)
                ->get();

            foreach ($relatedProducts as $rp) {
                if ($rp->firstImage) {
                    $rp->firstImage->image_url = getImage($rp->firstImage->image_url);
                }
            }

            $userId = Auth::guard('api')->id();
            if ($userId) {
                $favoriteProductIds = Favorite::where('user_id', $userId)
                    ->where('favorite_type', 'product')
                    ->pluck('favorite_item_id')
                    ->toArray();

                $product->is_favorited = in_array($product->id, $favoriteProductIds);

                $relatedProducts->each(function ($p) use ($favoriteProductIds) {
                    $p->is_favorited = in_array($p->id, $favoriteProductIds);
                });
            }

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Product details retrieved',
                'data' => [
                    'product' => $product,
                    'business' => $business,
                    'setting' => $setting,
                    'relatedProducts' => $relatedProducts
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function serviceDetails($id)
    {
        try {
            $service = Service::where('id', $id)
                ->orWhere('slug', $id)
                ->with(['business', 'category'])
                ->where('status', 'active')
                ->firstOrFail();

            $service->image_url = getImage($service->image_url);

            $business = $service->business;
            $setting = getBusinessSettings($business->id);

            // Recommended Services
            $recommendedServices = Service::where('business_id', $business->id)
                ->where('id', '!=', $service->id)
                ->where('status', 'active')
                ->inRandomOrder()
                ->limit(4)
                ->get();

            foreach ($recommendedServices as $rs) {
                $rs->image_url = getImage($rs->image_url);
            }

            $userId = Auth::guard('api')->id();
            if ($userId) {
                $favoriteServiceIds = Favorite::where('user_id', $userId)
                    ->where('favorite_type', 'service')
                    ->pluck('favorite_item_id')
                    ->toArray();

                $service->is_favorited = in_array($service->id, $favoriteServiceIds);

                $recommendedServices->each(function ($s) use ($favoriteServiceIds) {
                    $s->is_favorited = in_array($s->id, $favoriteServiceIds);
                });
            }

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Service details retrieved',
                'data' => [
                    'service' => $service,
                    'business' => $business,
                    'setting' => $setting,
                    'recommendedServices' => $recommendedServices
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
