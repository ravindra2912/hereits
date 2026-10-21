<?php

namespace App\Services\Api\V1;

use App\Http\Resources\Api\V1\ApiV1BannerResource;
use App\Http\Resources\Api\V1\ApiV1BusinessCategoryResource;
use App\Http\Resources\Api\V1\ApiV1BusinessDetailResource;
use App\Http\Resources\Api\V1\ApiV1BusinessResource;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessTiming;
use App\Models\Category;
use App\Models\Expert;
use App\Models\Gallery;
use App\Models\HomeSection;
use App\Models\Label;
use App\Models\Product;
use App\Models\ReviewAndRating;
use App\Models\Service;
use App\Services\ExpertService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ApiV1HomeService
{
    public function __construct(
        protected ExpertService $expertService
    ) {}
    /**
     * Resolve location parameters from request headers or query parameters.
     *
     * @param Request $request
     * @return array|null
     */
    public function resolveLocationFromRequest(Request $request): ?array
    {
        $lat = $request->header('X-Latitude') ?? $request->get('latitude');
        $lng = $request->header('X-Longitude') ?? $request->get('longitude');
        $areaLatLong = $request->header('X-Area-Lat-Long') ?? $request->get('area_lat_long');
        $radius = $request->header('X-Radius') ?? $request->get('radius');

        $location = null;
        if ($areaLatLong || ($lat && $lng)) {
            $location = [
                'area_lat_long' => $areaLatLong,
                'latitude' => $lat ? (float)$lat : null,
                'longitude' => $lng ? (float)$lng : null,
                'radius' => !empty($radius) ? (float)$radius : 100,
            ];
        }

        return $location;
    }

    /**
     * Fetch aggregated home screen data (banners, categories, featured businesses).
     *
     * @param array|null $location
     * @return array
     */
    public function getHomeData(?array $location = null): array
    {
        $banners = Banner::query()
            ->select(['id', 'business_id', 'image_url', 'status'])
            ->where(function ($q) {
                $q->where('status', 'active');
            })
            ->latest('id')
            ->limit(5)
            ->get();

        $categoriesQuery = BusinessCategory::query()
            ->select(['id', 'name', 'slug', 'image', 'status'])
            ->where(function ($q) {
                $q->where('status', 'active');
            })
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
            })
            ->limit(12);

        $categories = $categoriesQuery->get();

        $businesses = Business::query()
            ->select([
                'id',
                'name',
                'slug',
                'business_type',
                'business_category_id',
                'city_id',
                'state_id',
                'address',
                'area',
                'pincode',
                'contact',
                'business_image',
                'business_logo',
                'status',
                'rating',
                'latitude',
                'longitude',
            ])
            ->with([
                'businessCategory:id,name,image',
                'city:id,name',
                'state:id,name',
            ])
            ->public()
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
            })
            ->when(!$location, function ($q) {
                $q->orderByDesc('rating');
            })
            ->limit(10)
            ->get();

        return [
            'banners' => ApiV1BannerResource::collection($banners),
            'categories' => ApiV1BusinessCategoryResource::collection($categories),
            'featured_businesses' => ApiV1BusinessResource::collection($businesses),
        ];
    }

    /**
     * Get all active business categories.
     *
     * @param array|null $location
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getCategories(?array $location = null)
    {
        $categoriesQuery = BusinessCategory::query()
            ->select(['id', 'name', 'slug', 'image', 'status'])
            ->where(function ($q) {
                $q->where('status', 'active')->orWhere('status', 1);
            })
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

        $categories = $categoriesQuery->get();

        if ($location && $categories->isEmpty()) {
            $categories = BusinessCategory::query()
                ->select(['id', 'name', 'slug', 'image', 'status'])
                ->where(function ($q) {
                    $q->where('status', 'active')->orWhere('status', 1);
                })
                ->get();
        }

        return ApiV1BusinessCategoryResource::collection($categories);
    }

    /**
     * Fetch paginated businesses with filters.
     *
     * @param array $filters
     * @param array|null $location
     * @return LengthAwarePaginator
     */
    public function getBusinesses(array $filters, ?array $location = null): LengthAwarePaginator
    {
        $query = Business::query()
            ->select([
                'id',
                'name',
                'slug',
                'business_type',
                'business_category_id',
                'city_id',
                'state_id',
                'address',
                'area',
                'pincode',
                'contact',
                'business_image',
                'business_logo',
                'status',
                'rating',
                'latitude',
                'longitude',
            ])
            ->with([
                'businessCategory:id,name,image',
                'city:id,name',
                'state:id,name',
            ])
            ->public()
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

        if (!empty($filters['category_id'])) {
            $query->where('business_category_id', $filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('area', 'like', "%{$search}%")
                    ->orWhere('pincode', 'like', "%{$search}%");
            });
        }

        if (!$location) {
            $query->latest('id');
        }

        $perPage = !empty($filters['per_page']) ? (int)$filters['per_page'] : 15;

        return $query->paginate($perPage);
    }

    /**
     * Fetch single business details by ID or slug.
     *
     * @param int|string $id
     * @return array|null
     */
    public function getBusinessDetail(int|string $id): ?array
    {
        $business = Business::query()
            ->select([
                'id',
                'name',
                'slug',
                'business_type',
                'business_category_id',
                'country_id',
                'city_id',
                'state_id',
                'address',
                'area',
                'pincode',
                'contact',
                'business_image',
                'business_logo',
                'status',
                'rating',
                'latitude',
                'longitude',
                'seo_description',
                'seo_keyword',
                'facebook',
                'instagram',
                'twitter',
                'linkedin',
                'youtube',
                'template',
            ])
            ->with([
                'businessCategory:id,name,image',
                'country:id,name',
                'city:id,name',
                'state:id,name',
            ])
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', $id)->orWhere('slug', $id);
                } else {
                    $q->where('slug', $id);
                }
            })
            ->first();

        if (!$business) {
            return null;
        }

        $setting = function_exists('getBusinessSettings') ? getBusinessSettings($business->id) : null;

        // Banners
        $bannerRecords = Banner::where('business_id', $business->id)
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->get(['id', 'image_url']);

        $banners = $bannerRecords->map(function ($b) {
            return [
                'id' => $b->id,
                'image_url' => function_exists('getImage') ? getImage($b->image_url) : $b->image_url,
            ];
        })->values()->all();

        // Custom Home Sections (Same logic as website BusinessController::template1)
        $customSections = $this->buildCustomSections($business, $setting);

        // Fallback Details (for fallback when custom home sections not configured)
        $details = $this->buildFallbackDetails($business, $setting);

        // Experts
        $experts = Expert::where('business_id', $business->id)
            ->where('status', 1)
            ->with(['department:id,department_name', 'user:id,first_name,last_name,profile'])
            ->get(['id', 'business_id', 'user_id', 'title', 'department_id', 'slug', 'rating', 'description', 'is_appointment_book_with_time_slot'])
            ->map(function ($exp) use ($business) {
                $availability = $this->expertService->isExpertAvailable((int)$exp->id, (int)$business->id);
                return [
                    'id' => $exp->id,
                    'expert_name' => $exp->user?->name ?? '',
                    'title' => $exp->title,
                    'slug' => $exp->slug,
                    'rating' => (float)($exp->rating ?? 0),
                    'description' => $exp->description,
                    'expert_image' => function_exists('getImage') && !empty($exp->user?->profile) ? getImage($exp->user->profile, 'expert') : null,
                    'department' => $exp->department ? [
                        'id' => $exp->department->id,
                        'name' => $exp->department->department_name,
                    ] : null,
                    'availability' => $availability['status'] ?? 'open',
                ];
            })->values()->all();

        // Galleries
        $galleries = Gallery::select('id', 'image_url', 'title', 'business_id', 'type')
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->latest()
            ->limit(12)
            ->get()
            ->map(function ($g) {
                $isYt = ($g->type ?? 'image') === 'youtube_url';
                return [
                    'id' => $g->id,
                    'title' => $g->title,
                    'type' => $g->type ?? 'image',
                    'image_url' => $isYt ? (getYoutubeThumbnail($g->image_url) ?: getImage(null)) : (function_exists('getImage') ? getImage($g->image_url) : $g->image_url),
                    'video_url' => $isYt ? $g->image_url : null,
                ];
            })->values()->all();

        // Blogs
        $blogs = Blog::select('id', 'title', 'slug', 'short_description', 'image', 'published_at', 'business_id')
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->limit(6)
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'title' => $b->title,
                    'slug' => $b->slug,
                    'short_description' => $b->short_description,
                    'image' => function_exists('getImage') ? getImage($b->image) : null,
                    'published_at' => $b->published_at ? $b->published_at->format('M d, Y') : null,
                ];
            })->values()->all();

        // Reviews
        $reviews = ReviewAndRating::with(['user:id,first_name,last_name,profile_image'])
            ->where('business_id', $business->id)
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'rating' => (float)($r->rating ?? 5),
                    'review' => $r->review,
                    'created_at' => $r->created_at ? $r->created_at->diffForHumans() : null,
                    'user' => [
                        'id' => $r->user?->id,
                        'name' => trim(($r->user?->first_name ?? '') . ' ' . ($r->user?->last_name ?? '')) ?: 'Anonymous',
                        'profile_image' => $r->user?->profile_image ? (function_exists('getImage') ? getImage($r->user->profile_image) : null) : null,
                    ],
                ];
            })->values()->all();

        // Business Timings
        $timings = BusinessTiming::where('business_id', $business->id)
            ->whereNull('expert_id')
            ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->get(['day', 'start_time', 'end_time'])
            ->map(function ($t) {
                return [
                    'day' => $t->day,
                    'start_time' => $t->start_time,
                    'end_time' => $t->end_time,
                ];
            })->values()->all();

        $settingData = [
            'is_ecommerce_system' => !empty($setting?->is_ecommerce_system),
            'is_service_system' => !empty($setting?->is_service_system),
            'is_appointment_system' => !empty($setting?->is_appointment_system),
            'is_label' => !empty($setting?->is_label),
            'is_show_contact' => $setting ? (!isset($setting->is_show_contact) || (bool)$setting->is_show_contact) : true,
            'is_verified' => !empty($setting?->is_verified),
            'about_us_text' => $setting?->about_us_text,
            'about_us_image' => !empty($setting?->about_us_image) && function_exists('getImage') ? getImage($setting->about_us_image) : null,
            'is_open' => function_exists('isBusinessOpen') ? isBusinessOpen($business->id) : false,
            'accept_online_payments' => !empty($setting?->accept_online_payments),
            'accept_cash_on_delivery' => !empty($setting?->accept_cash_on_delivery),
        ];

        return [
            'business' => new ApiV1BusinessDetailResource($business),
            'setting' => $settingData,
            'banners' => $banners,
            'sections' => $customSections,
            'details' => $details,
            'experts' => $experts,
            'galleries' => $galleries,
            'blogs' => $blogs,
            'reviews' => $reviews,
            'timings' => $timings,
        ];
    }

    /**
     * Build ordered custom home sections matching the website structure.
     */
    protected function buildCustomSections(Business $business, mixed $setting): array
    {
        $allowedSectionTypes = ['banner_custom', 'slider_banner'];
        $isEcommerce = !empty($setting?->is_ecommerce_system);
        $isService = !empty($setting?->is_service_system);
        $isAppointment = !empty($setting?->is_appointment_system);
        $isLabel = !empty($setting?->is_label);

        if ($isEcommerce) {
            array_push($allowedSectionTypes, 'product_categories', 'product_category_items', 'products');
            if ($isLabel) {
                $allowedSectionTypes[] = 'product_labels';
            }
        }
        if ($isService) {
            array_push($allowedSectionTypes, 'service_categories', 'service_category_items', 'services');
        }
        if ($isAppointment) {
            $allowedSectionTypes[] = 'experts';
        }

        $sections = HomeSection::where('business_id', $business->id)
            ->where('status', 'active')
            ->whereIn('section_type', $allowedSectionTypes)
            ->with(['items' => function ($q) use ($isEcommerce, $isService, $isAppointment, $isLabel) {
                $q->where('status', 'active');
                $itemRelations = [];

                if ($isEcommerce || $isService) {
                    $itemRelations['category'] = function ($cq) use ($isEcommerce, $isService) {
                        $categoryRelations = [];
                        if ($isEcommerce) {
                            $categoryRelations['products'] = function ($pq) {
                                $pq->select('id', 'name', 'slug', 'description', 'price', 'sell_price', 'max_price', 'min_price', 'price_type', 'category_id', 'label_id', 'business_id')
                                    ->with(['firstTwoImage:id,product_id,image_url', 'category:id,name'])
                                    ->where('status', 'active')
                                    ->limit(6);
                            };
                        }
                        if ($isService) {
                            $categoryRelations['services'] = function ($sq) {
                                $sq->select('id', 'name', 'slug', 'description', 'price_type', 'price', 'max_price', 'min_price', 'category_id', 'image_url', 'business_id')
                                    ->with('category:id,name')
                                    ->where('status', 'active')
                                    ->limit(4);
                            };
                        }
                        $cq->with($categoryRelations);
                    };
                }

                if ($isEcommerce) {
                    $itemRelations['product'] = function ($pq) {
                        $pq->select('id', 'name', 'slug', 'description', 'price', 'sell_price', 'max_price', 'min_price', 'price_type', 'category_id', 'label_id', 'business_id')
                            ->with(['firstTwoImage:id,product_id,image_url', 'category:id,name'])
                            ->where('status', 'active');
                    };
                }

                if ($isService) {
                    $itemRelations['service'] = function ($sq) {
                        $sq->select('id', 'name', 'slug', 'description', 'price_type', 'price', 'max_price', 'min_price', 'category_id', 'image_url', 'business_id')
                            ->with('category:id,name')
                            ->where('status', 'active');
                    };
                }

                if ($isEcommerce && $isLabel) {
                    $itemRelations['label'] = function ($lq) {
                        $lq->select('id', 'name', 'slug', 'image', 'business_id', 'short_order')
                            ->where('status', 'active');
                    };
                }

                if ($isAppointment) {
                    $itemRelations['expert'] = function ($eq) {
                        $eq->select('id', 'business_id', 'user_id', 'title', 'department_id', 'slug', 'rating', 'description', 'is_appointment_book_with_time_slot', 'status')
                            ->with(['department:id,department_name', 'user:id,first_name,last_name,profile'])
                            ->where('status', 1);
                    };
                }

                $q->with($itemRelations);
            }])
            ->orderBy('sort_order', 'asc')
            ->get();

        $formattedSections = [];

        foreach ($sections as $section) {
            $secType = $section->section_type ?: 'banner_custom';
            $isRandom = $section->item_selection_type === 'random' || $section->items->isEmpty();

            $sectionData = [
                'id' => $section->id,
                'title' => $section->title,
                'section_type' => $secType,
                'image_count' => (int)($section->image_count ?: 1),
                'ui_style' => $section->ui_style ?: 'style_1',
                'sort_order' => (int)$section->sort_order,
                'settings' => is_array($section->settings) ? $section->settings : (json_decode($section->settings ?? '[]', true) ?: []),
            ];

            if ($secType === 'banner_custom' || $secType === 'slider_banner') {
                $sectionData['items'] = $section->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'image_url' => function_exists('getImage') && !empty($item->image_path) ? getImage($item->image_path) : null,
                        'redirect_url' => $item->redirect_url,
                    ];
                })->filter(fn($i) => !empty($i['image_url']))->values()->all();
            } elseif ($secType === 'product_categories') {
                $cats = $isRandom
                    ? Category::where('business_id', $business->id)->where('type', 'Products')->whereNull('parent_id')->where('status', 'active')->orderBy('sort_order', 'asc')->limit(15)->get()
                    : $section->items->pluck('category')->filter()->values();

                $sectionData['data'] = $cats->map(function ($cat) {
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'slug' => $cat->slug,
                        'image_url' => function_exists('getImage') && !empty($cat->image_url) ? getImage($cat->image_url) : null,
                    ];
                })->values()->all();
            } elseif ($secType === 'product_category_items') {
                $catsWithProducts = $isRandom
                    ? Category::where('business_id', $business->id)
                        ->where('type', 'Products')
                        ->whereNull('parent_id')
                        ->where('status', 'active')
                        ->limit(3)
                        ->whereHas('products', fn($q) => $q->where('status', 'active')->where('business_id', $business->id))
                        ->with(['products' => fn($q) => $q->select('id', 'name', 'slug', 'description', 'price', 'sell_price', 'max_price', 'min_price', 'price_type', 'category_id', 'label_id', 'business_id')
                            ->with(['firstTwoImage:id,product_id,image_url', 'category:id,name'])
                            ->where('status', 'active')
                            ->where('business_id', $business->id)])
                        ->orderBy('sort_order', 'asc')
                        ->get()
                    : $section->items->pluck('category')->filter()->values();

                $sectionData['data'] = $catsWithProducts->map(function ($cat) {
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'slug' => $cat->slug,
                        'products' => ($cat->products ?? collect())->take(6)->map(fn($p) => $this->formatProduct($p))->values()->all(),
                    ];
                })->filter(fn($c) => !empty($c['products']))->values()->all();
            } elseif ($secType === 'service_categories') {
                $cats = $isRandom
                    ? Category::where('business_id', $business->id)->where('type', 'Services')->whereNull('parent_id')->where('status', 'active')->orderBy('sort_order', 'asc')->limit(15)->get()
                    : $section->items->pluck('category')->filter()->values();

                $sectionData['data'] = $cats->map(function ($cat) {
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'slug' => $cat->slug,
                        'image_url' => function_exists('getImage') && !empty($cat->image_url) ? getImage($cat->image_url) : null,
                    ];
                })->values()->all();
            } elseif ($secType === 'service_category_items') {
                $catsWithServices = $isRandom
                    ? Category::where('business_id', $business->id)
                        ->where('type', 'Services')
                        ->whereNull('parent_id')
                        ->where('status', 'active')
                        ->limit(3)
                        ->whereHas('services', fn($q) => $q->where('status', 'active')->where('business_id', $business->id))
                        ->with(['services' => fn($q) => $q->select('id', 'name', 'slug', 'description', 'price_type', 'price', 'max_price', 'min_price', 'category_id', 'image_url', 'business_id')
                            ->with('category:id,name')
                            ->where('status', 'active')
                            ->where('business_id', $business->id)])
                        ->orderBy('sort_order', 'asc')
                        ->get()
                    : $section->items->pluck('category')->filter()->values();

                $sectionData['data'] = $catsWithServices->map(function ($cat) {
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'slug' => $cat->slug,
                        'services' => ($cat->services ?? collect())->take(4)->map(fn($s) => $this->formatService($s))->values()->all(),
                    ];
                })->filter(fn($c) => !empty($c['services']))->values()->all();
            } elseif ($secType === 'products') {
                $prods = $isRandom
                    ? Product::select('id', 'name', 'slug', 'description', 'price', 'sell_price', 'max_price', 'min_price', 'price_type', 'category_id', 'label_id', 'business_id')
                        ->with(['firstTwoImage:id,product_id,image_url', 'category:id,name'])
                        ->where('business_id', $business->id)
                        ->where('status', 'active')
                        ->inRandomOrder()
                        ->limit(6)
                        ->get()
                    : $section->items->pluck('product')->filter()->values();

                $sectionData['data'] = $prods->map(fn($p) => $this->formatProduct($p))->values()->all();
            } elseif ($secType === 'services') {
                $servs = $isRandom
                    ? Service::select('id', 'name', 'slug', 'description', 'price_type', 'price', 'max_price', 'min_price', 'category_id', 'image_url', 'business_id')
                        ->with('category:id,name')
                        ->where('business_id', $business->id)
                        ->where('status', 'active')
                        ->inRandomOrder()
                        ->limit(4)
                        ->get()
                    : $section->items->pluck('service')->filter()->values();

                $sectionData['data'] = $servs->map(fn($s) => $this->formatService($s))->values()->all();
            } elseif ($secType === 'product_labels') {
                $lbls = $isRandom
                    ? Label::select('id', 'name', 'slug', 'image', 'business_id', 'short_order')
                        ->where('business_id', $business->id)
                        ->where('status', 'active')
                        ->orderBy('short_order', 'asc')
                        ->limit(15)
                        ->get()
                    : $section->items->pluck('label')->filter()->values();

                $sectionData['data'] = $lbls->map(function ($l) {
                    return [
                        'id' => $l->id,
                        'name' => $l->name,
                        'slug' => $l->slug,
                        'image_url' => function_exists('getImage') && !empty($l->image) ? getImage($l->image) : null,
                    ];
                })->values()->all();
            } elseif ($secType === 'experts') {
                $exps = $isRandom
                    ? Expert::where('business_id', $business->id)
                        ->where('status', 1)
                        ->with(['department:id,department_name', 'user:id,first_name,last_name,profile'])
                        ->limit(4)
                        ->get()
                    : $section->items->pluck('expert')->filter()->values();

                $sectionData['data'] = $exps->map(function ($exp) use ($business) {
                    $avail = $this->expertService->isExpertAvailable((int)$exp->id, (int)$business->id);
                    return [
                        'id' => $exp->id,
                        'expert_name' => $exp->user?->name ?? '',
                        'title' => $exp->title,
                        'slug' => $exp->slug,
                        'rating' => (float)($exp->rating ?? 0),
                        'description' => $exp->description,
                        'expert_image' => function_exists('getImage') && !empty($exp->user?->profile) ? getImage($exp->user->profile, 'expert') : null,
                        'department' => $exp->department ? [
                            'id' => $exp->department->id,
                            'name' => $exp->department->department_name,
                        ] : null,
                        'availability' => $avail['status'] ?? 'open',
                    ];
                })->values()->all();
            }

            $formattedSections[] = $sectionData;
        }

        return $formattedSections;
    }

    /**
     * Format a product model into API response shape.
     */
    protected function formatProduct($product): array
    {
        $firstImage = $product->firstTwoImage?->first();
        $imageUrl = null;
        if ($firstImage && !empty($firstImage->image_url)) {
            $imageUrl = function_exists('getImage') ? getImage($firstImage->image_url) : $firstImage->image_url;
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'price_type' => $product->price_type,
            'price' => $product->price,
            'sell_price' => $product->sell_price,
            'min_price' => $product->min_price,
            'max_price' => $product->max_price,
            'image_url' => $imageUrl,
            'category_name' => $product->category?->name,
        ];
    }

    /**
     * Format a service model into API response shape.
     */
    protected function formatService($service): array
    {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'slug' => $service->slug,
            'description' => $service->description,
            'price_type' => $service->price_type,
            'price' => $service->price,
            'min_price' => $service->min_price,
            'max_price' => $service->max_price,
            'image_url' => function_exists('getImage') && !empty($service->image_url) ? getImage($service->image_url) : null,
            'category_name' => $service->category?->name,
        ];
    }

    /**
     * Build fallback details when custom home sections are not yet configured.
     */
    protected function buildFallbackDetails(Business $business, mixed $setting): array
    {
        $isEcommerce = !empty($setting?->is_ecommerce_system);
        $isService = !empty($setting?->is_service_system);

        $productCategories = [];
        $categoriesWithProducts = [];
        $products = [];

        if ($isEcommerce) {
            $productCategories = Category::where('business_id', $business->id)
                ->where('type', 'Products')
                ->whereNull('parent_id')
                ->where('status', 'active')
                ->orderBy('sort_order', 'asc')
                ->limit(15)
                ->get(['id', 'name', 'slug', 'image_url'])
                ->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'image_url' => function_exists('getImage') && !empty($c->image_url) ? getImage($c->image_url) : null,
                ])->values()->all();

            $categoriesWithProducts = Category::where('business_id', $business->id)
                ->where('type', 'Products')
                ->whereNull('parent_id')
                ->where('status', 'active')
                ->limit(3)
                ->whereHas('products', fn($q) => $q->where('status', 'active')->where('business_id', $business->id))
                ->with(['products' => fn($q) => $q->select('id', 'name', 'slug', 'description', 'price', 'sell_price', 'max_price', 'min_price', 'price_type', 'category_id', 'label_id', 'business_id')
                    ->with(['firstTwoImage:id,product_id,image_url', 'category:id,name'])
                    ->where('status', 'active')
                    ->where('business_id', $business->id)
                    ->limit(6)])
                ->get()
                ->map(fn($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'products' => ($cat->products ?? collect())->map(fn($p) => $this->formatProduct($p))->values()->all(),
                ])->values()->all();

            $products = Product::select('id', 'name', 'slug', 'description', 'price', 'sell_price', 'max_price', 'min_price', 'price_type', 'category_id', 'label_id', 'business_id')
                ->with(['firstTwoImage:id,product_id,image_url', 'category:id,name'])
                ->where('business_id', $business->id)
                ->where('status', 'active')
                ->latest()
                ->limit(6)
                ->get()
                ->map(fn($p) => $this->formatProduct($p))->values()->all();
        }

        $serviceCategories = [];
        $categoriesWithServices = [];
        $services = [];

        if ($isService) {
            $serviceCategories = Category::where('business_id', $business->id)
                ->where('type', 'Services')
                ->whereNull('parent_id')
                ->where('status', 'active')
                ->orderBy('sort_order', 'asc')
                ->limit(15)
                ->get(['id', 'name', 'slug', 'image_url'])
                ->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'image_url' => function_exists('getImage') && !empty($c->image_url) ? getImage($c->image_url) : null,
                ])->values()->all();

            $categoriesWithServices = Category::where('business_id', $business->id)
                ->where('type', 'Services')
                ->whereNull('parent_id')
                ->where('status', 'active')
                ->limit(3)
                ->whereHas('services', fn($q) => $q->where('status', 'active')->where('business_id', $business->id))
                ->with(['services' => fn($q) => $q->select('id', 'name', 'slug', 'description', 'price_type', 'price', 'max_price', 'min_price', 'category_id', 'image_url', 'business_id')
                    ->with('category:id,name')
                    ->where('status', 'active')
                    ->where('business_id', $business->id)
                    ->limit(4)])
                ->get()
                ->map(fn($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'services' => ($cat->services ?? collect())->map(fn($s) => $this->formatService($s))->values()->all(),
                ])->values()->all();

            $services = Service::select('id', 'name', 'slug', 'description', 'price_type', 'price', 'max_price', 'min_price', 'category_id', 'image_url', 'business_id')
                ->with('category:id,name')
                ->where('business_id', $business->id)
                ->where('status', 'active')
                ->latest()
                ->limit(4)
                ->get()
                ->map(fn($s) => $this->formatService($s))->values()->all();
        }

        return [
            'productCategories' => $productCategories,
            'categoriesWithProducts' => $categoriesWithProducts,
            'products' => $products,
            'serviceCategories' => $serviceCategories,
            'categoriesWithServices' => $categoriesWithServices,
            'services' => $services,
        ];
    }
}
