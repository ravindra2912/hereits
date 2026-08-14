<?php

namespace App\Http\Controllers\Front\Business;

use App\Models\Business;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\Expert;
use App\Http\Controllers\Controller;
use App\Models\AppointmentDepartment;
use App\Models\Product;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Service;
use App\Models\Gallery;
use App\Services\BusinessAnalyticsService;

class BusinessController extends Controller
{

    public function businessDetails(Request $request, $slug): View
    {
        $business = Business::select('id', 'template', 'name', 'slug', 'business_image', 'business_logo', 'address', 'contact', 'business_category_id', 'latitude', 'longitude', 'country_id', 'state_id', 'city_id', 'area', 'seo_description', 'seo_keyword', 'rating', 'pincode', 'facebook', 'twitter', 'instagram', 'linkedin', 'youtube')
            ->with([
                'businessCategory',
                'country',
                'state',
                'city'
            ])
            ->when(auth()->check(), function ($query) {
                $query->withExists(['favorites as is_favorited' => function ($q) {
                    $q->where('user_id', auth()->id());
                }]);
            })
            // ->whereHas('businessSetting', function ($query) {
            //     $query->where('subscription_expiry_date', '>=', now());
            // })
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();
        if ($business) {
            app(BusinessAnalyticsService::class)->trackBusinessView($business, $request);

            if ($business->template == null || $business->template == 'common') {
                $info = $this->template1($request, $business);
            } else {
                $info = $this->cabBooking($request, $business);
            }
            // $info = $this->cabBooking($request, $business);
            return view($info['view'], $info['data']);
        } else {
            return view('errors.404');
        }
    }
    public function template1(Request $request, $business)
    {
        $setting = getBusinessSettings($business->id);
        $isSubscriptionActive = $setting->subscription_expiry_date > now();

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
        $banners = Banner::where('business_id', $business->id)->where('status', 'active')->pluck('image_url')->toArray();
        $banners = array_map(function ($url) {
            return getImage($url);
        }, $banners);

        // Gallery
        $galleries = Gallery::select('id', 'image_url', 'title', 'business_id', 'type')
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->latest()
            ->limit(4)
            ->get();

        if ($isSubscriptionActive) {
            if ($setting->is_appointment_with_department) {
                $departments = AppointmentDepartment::select('id', 'department_name')->where('business_id', $business->id)->get();
            }

            // Experts
            $experts = Expert::select('id', 'business_id', 'title', 'expert_name', 'expert_image', 'department_id', 'slug', 'rating', 'description', 'is_appointment_book_with_time_slot')
                ->with([
                    'department',
                    'business:id,name,slug,address,latitude,longitude,business_image'
                ])
                ->when(auth()->check(), function ($query) {
                    $query->withExists(['favorites as is_favorited' => function ($q) {
                        $q->where('user_id', auth()->id());
                    }]);
                })
                ->where('business_id', $business->id)
                ->where('status', 'active')
                ->limit(4)
                ->get();

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

                // category with products
                $details['categoriesWithProducts'] = Category::select('id', 'name', 'image_url', 'business_id')
                    ->with(['products' => function ($query) {
                        $query->select('id', 'name', 'slug', 'description', 'price', 'sell_price', 'max_price', 'min_price', 'price_type', 'category_id', 'business_id')
                            ->with(['firstTwoImages:id,product_id,image_url', 'category:id,name'])
                            ->when(auth()->check(), function ($query) {
                                $query->withExists(['favorites as is_favorited' => function ($q) {
                                    $q->where('user_id', auth()->id());
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

                if ($details['categoriesWithProducts']->count() == 0) {
                    $details['products'] = Product::select('id', 'name', 'slug', 'description', 'price', 'sell_price', 'max_price', 'min_price', 'price_type', 'category_id', 'business_id')
                        ->with(['firstTwoImages:id,product_id,image_url', 'category:id,name'])
                        ->when(auth()->check(), function ($query) {
                            $query->withExists(['favorites as is_favorited' => function ($q) {
                                $q->where('user_id', auth()->id());
                            }]);
                        })
                        ->where('business_id', $business->id)
                        ->where('status', 'active')
                        ->limit(6)
                        ->get();
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

                // category with services
                $details['categoriesWithServices'] = Category::select('id', 'name', 'image_url', 'business_id')
                    ->with(['services' => function ($query) {
                        $query->select('id', 'name', 'slug', 'description', 'price_type', 'price', 'max_price', 'min_price', 'category_id', 'image_url', 'business_id')
                            ->with('category:id,name')
                            ->when(auth()->check(), function ($query) {
                                $query->withExists(['favorites as is_favorited' => function ($q) {
                                    $q->where('user_id', auth()->id());
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

                if ($details['categoriesWithServices']->count() == 0) {
                    $details['services'] = Service::select('id', 'name', 'slug', 'description', 'price_type', 'price', 'max_price', 'min_price', 'category_id', 'image_url', 'business_id')
                        ->with('category:id,name')
                        ->when(auth()->check(), function ($query) {
                            $query->withExists(['favorites as is_favorited' => function ($q) {
                                $q->where('user_id', auth()->id());
                            }]);
                        })
                        ->where('business_id', $business->id)
                        ->where('status', 'active')
                        ->limit(4)
                        ->get();
                }
            }
        }

        return ['view' => 'front.business.template1.businessHome', 'data' => compact('business', 'setting', 'departments', 'experts', 'banners', 'details', 'galleries', 'totalExperts', 'isSubscriptionActive')];

        // return view('front.business.template1.businessHome', compact('business', 'setting', 'departments', 'experts', 'banners', 'details', 'galleries', 'totalExperts'));

    }

    public function cabBooking(Request $request, $business)
    {
        return ['view' => 'front.business.cabBooking.businessHome', 'data' => compact('business')];
    }



    function getBusinessAddress($business): string
    {
        return $business->full_address;
    }
}
