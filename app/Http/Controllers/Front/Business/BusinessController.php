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
use App\Models\HomeSection;
use App\Models\Label;
use App\Models\BusinessHomePopup;
use App\Models\BusinessCampaignLead;
use App\Http\Requests\Front\SubmitCampaignLeadRequest;
use App\Models\Quotation;
use App\Repositories\Eloquent\QuotationRepository;
use App\Traits\GoogleSheet;
use App\Services\BusinessAnalyticsService;

class BusinessController extends Controller
{
    use GoogleSheet;

    public function __construct(
        protected BusinessAnalyticsService $analyticsService,
        protected QuotationRepository $quotationRepository
    ) {}

    public function legalPageDetails(Request $request, $slug, $type): View
    {
        $business = Business::select('id', 'template', 'name', 'slug', 'business_image', 'business_logo', 'address', 'contact', 'business_category_id', 'latitude', 'longitude', 'country_id', 'state_id', 'city_id', 'area', 'seo_description', 'seo_keyword', 'rating', 'pincode', 'facebook', 'twitter', 'instagram', 'linkedin', 'youtube')
            ->with([
                'businessCategory',
                'country',
                'state',
                'city'
            ])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $legalPage = \App\Models\BusinessLeaglePage::where('business_id', $business->id)
            ->where('type', $type)
            ->where('status', 'active')
            ->firstOrFail();

        $setting = getBusinessSettings($business->id);

        $allTypes = [
            'privacy_policy' => 'Privacy Policy',
            'term_and_condition' => 'Terms & Conditions',
            'refuncd_policy' => 'Refund Policy',
            'quotation_policy' => 'Quotation Policy'
        ];
        $pageTitle = $allTypes[$type] ?? ucwords(str_replace('_', ' ', $type));

        $template = $business->template ?: 'template1';
        $view = "front.business.{$template}.legal_page";
        if (!view()->exists($view)) {
            $view = "front.business.template1.legal_page";
        }

        return view($view, compact('business', 'legalPage', 'setting', 'pageTitle'));
    }

    public function businessDetails(Request $request, $slug): View
    {
        $business = Business::select('id', 'template', 'name', 'slug', 'business_image', 'business_logo', 'address', 'contact', 'business_category_id', 'latitude', 'longitude', 'country_id', 'state_id', 'city_id', 'area', 'seo_description', 'seo_keyword', 'rating', 'pincode', 'facebook', 'twitter', 'instagram', 'linkedin', 'youtube')
            ->with([
                'businessCategory',
                'country',
                'state',
                'city'
            ])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $this->analyticsService->trackView($business, 'business', $business->id, $request);
        if ($business) {
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
        $business->fulladdress = $this->getBusinessAddress($business);

        $setting = getBusinessSettings($business->id);

        // Custom Home Sections
        $customHomeSections = cache()->remember("business_{$business->id}_custom_home_sections", 600, function () use ($business, $setting) {
            $allowedSectionTypes = ['banner_custom', 'slider_banner'];
            $isEcommerce = !empty($setting->is_ecommerce_system);
            $isService = !empty($setting->is_service_system);
            $isAppointment = !empty($setting->is_appointment_system);
            $isLabel = !empty($setting->is_label);

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
                                ->with(['department', 'user:id,first_name,last_name,profile', 'business:id,name,slug,address,latitude,longitude,business_image'])
                                ->where('status', 1);
                        };
                    }

                    $q->with($itemRelations);
                }])
                ->orderBy('sort_order', 'asc')
                ->get();

            foreach ($sections as $section) {
                if ($section->item_selection_type === 'random' || $section->items->isEmpty()) {
                    if ($section->section_type === 'product_categories') {
                        $section->custom_items = Category::where('business_id', $business->id)
                            ->where('type', 'Products')
                            ->whereNull('parent_id')
                            ->where('status', 'active')
                            ->orderBy('sort_order', 'ASC')
                            ->limit(15)
                            ->get();
                    } elseif ($section->section_type === 'product_category_items') {
                        $section->custom_items = Category::where('business_id', $business->id)
                            ->where('type', 'Products')
                            ->whereNull('parent_id')
                            ->where('status', 'active')
                            ->limit(3)
                            ->whereHas('products', fn($q) => $q->where('status', 'active')->where('business_id', $business->id))
                            ->with(['products' => fn($q) => $q->select('id', 'name', 'slug', 'description', 'price', 'sell_price', 'max_price', 'min_price', 'price_type', 'category_id', 'label_id', 'business_id')
                                ->with(['firstTwoImage:id,product_id,image_url', 'category:id,name'])
                                ->where('status', 'active')
                                ->where('business_id', $business->id)])
                            ->orderBy('sort_order', 'ASC')
                            ->get()
                            ->each(function ($category) {
                                $category->setRelation('products', $category->products->take(6));
                            });
                    } elseif ($section->section_type === 'service_categories') {
                        $section->custom_items = Category::where('business_id', $business->id)
                            ->where('type', 'Services')
                            ->whereNull('parent_id')
                            ->where('status', 'active')
                            ->orderBy('sort_order', 'ASC')
                            ->limit(15)
                            ->get();
                    } elseif ($section->section_type === 'service_category_items') {
                        $section->custom_items = Category::where('business_id', $business->id)
                            ->where('type', 'Services')
                            ->whereNull('parent_id')
                            ->where('status', 'active')
                            ->limit(2)
                            ->whereHas('services', fn($q) => $q->where('status', 'active')->where('business_id', $business->id))
                            ->with(['services' => fn($q) => $q->select('id', 'name', 'slug', 'description', 'price_type', 'price', 'max_price', 'min_price', 'category_id', 'image_url', 'business_id')
                                ->with('category:id,name')
                                ->where('status', 'active')
                                ->where('business_id', $business->id)])
                            ->orderBy('sort_order', 'ASC')
                            ->get()
                            ->each(function ($category) {
                                $category->setRelation('services', $category->services->take(4));
                            });
                    } elseif ($section->section_type === 'products') {
                        $section->custom_items = Product::select('id', 'name', 'slug', 'description', 'price', 'sell_price', 'max_price', 'min_price', 'price_type', 'category_id', 'label_id', 'business_id')
                            ->with(['firstTwoImage:id,product_id,image_url', 'category:id,name'])
                            ->where('business_id', $business->id)
                            ->where('status', 'active')
                            ->inRandomOrder()
                            ->limit(6)
                            ->get();
                    } elseif ($section->section_type === 'services') {
                        $section->custom_items = Service::select('id', 'name', 'slug', 'description', 'price_type', 'price', 'max_price', 'min_price', 'category_id', 'image_url', 'business_id')
                            ->with('category:id,name')
                            ->where('business_id', $business->id)
                            ->where('status', 'active')
                            ->inRandomOrder()
                            ->limit(4)
                            ->get();
                    } elseif ($section->section_type === 'product_labels') {
                        $section->custom_items = Label::select('id', 'name', 'slug', 'image', 'business_id', 'short_order')
                            ->where('business_id', $business->id)
                            ->where('status', 'active')
                            ->orderBy('short_order', 'asc')
                            ->limit(15)
                            ->get();
                    } elseif ($section->section_type === 'experts') {
                        $section->custom_items = Expert::where('business_id', $business->id)
                            ->where('status', 1)
                            ->with('user:id,first_name,last_name,profile')
                            ->select('id', 'business_id', 'user_id', 'title', 'slug', 'rating', 'description', 'is_appointment_book_with_time_slot')
                            ->limit(4)
                            ->get();
                    }
                }
            }

            return $sections;
        });

        // MOCK DATA FOR DESIGN IMPLEMENTATION
        // Banners (Hero Slider)
        $banners = cache()->remember("business_{$business->id}_banners", 1800, function () use ($business) {
            $urls = Banner::where('business_id', $business->id)->where('status', 'active')->pluck('image_url')->toArray();
            return array_map(function ($url) {
                return getImage($url);
            }, $urls);
        });

        // Gallery
        $galleries = cache()->remember("business_{$business->id}_galleries", 1800, function () use ($business) {
            return Gallery::select('id', 'image_url', 'title', 'business_id', 'type')
                ->where('business_id', $business->id)
                ->where('status', 'active')
                ->latest()
                ->limit(4)
                ->get();
        });

        // Blogs
        $blogs = cache()->remember("business_{$business->id}_blogs", 1800, function () use ($business) {
            return \App\Models\Blog::select('id', 'title', 'slug', 'short_description', 'image', 'published_at', 'business_id')
                ->where('business_id', $business->id)
                ->where('status', 'active')
                ->where('published_at', '<=', now())
                ->latest('published_at')
                ->limit(4)
                ->get();
        });

        return ['view' => 'front.business.template1.businessHome', 'data' => compact('business', 'setting', 'banners', 'galleries', 'customHomeSections', 'blogs')];

        // return view('front.business.template1.businessHome', compact('business', 'setting', 'departments', 'experts', 'banners', 'details', 'galleries', 'totalExperts'));

    }

    public function cabBooking(Request $request, $business)
    {
        return ['view' => 'front.business.cabBooking.businessHome', 'data' => compact('business')];
    }



    public function submitCampaignLead(SubmitCampaignLeadRequest $request, ?string $business_slug = null)
    {
        $validated = $request->validated();
        $popup = BusinessHomePopup::select(['id', 'business_id', 'title', 'is_web_hook_for_google_sheet', 'google_sheet_webhook_url'])
            ->with(['business:id,slug'])
            ->findOrFail($validated['popup_id']);

        $fields = $validated['fields'] ?? [];
        $contact = $validated['contact'] ?? null;
        $email = $validated['email'] ?? null;

        if (is_array($fields)) {
            foreach ($fields as $field) {
                $label = strtolower($field['label'] ?? '');
                $val = $field['value'] ?? '';
                if (!$contact && (str_contains($label, 'phone') || str_contains($label, 'contact') || str_contains($label, 'mobile'))) {
                    $contact = $val;
                }
                if (!$email && str_contains($label, 'email')) {
                    $email = $val;
                }
            }
        }

        $lead = BusinessCampaignLead::create([
            'business_id' => $popup->business_id,
            'popup_id' => $popup->id,
            'contact' => $contact,
            'email' => $email,
            'details' => $fields,
            'ip_address' => $request->ip(),
            'status' => 'new',
        ]);

        // Send lead info to Google Sheets Webhook
        if ($popup->is_web_hook_for_google_sheet && !empty($popup->google_sheet_webhook_url)) {
            try {
                $webhookFields = $fields;
                $webhookFields[] = [
                    'label' => 'Campaign Name',
                    'value' => $popup->title,
                ];
                $webhookFields[] = [
                    'label' => 'Business URL',
                    'value' => route('business-details', ['business_slug' => $popup->business->slug]),
                ];
                $webhookFields[] = [
                    'label' => 'date',
                    'value' => now()->format('d-m-Y H:i:s'),
                ];

                $res = $this->storeToGoogleSheet($webhookFields, $popup->google_sheet_webhook_url);
                if (isset($res['status']) && $res['status'] >= 200 && $res['status'] < 300) {
                    $lead->update([
                        'is_webhook_sent' => true,
                        'webhook_response' => is_array($res['body']) ? json_encode($res['body']) : ($res['body'] ?? 'Success')
                    ]);
                } else {
                    $lead->update([
                        'is_webhook_sent' => false,
                        'webhook_response' => is_array($res['body']) ? json_encode($res['body']) : ($res['body'] ?? 'Failed with status ' . ($res['status'] ?? 'unknown'))
                    ]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Google Sheet Lead Webhook Error: ' . $e->getMessage());
                $lead->update([
                    'is_webhook_sent' => false,
                    'webhook_response' => checkResponse($e)
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Lead submitted successfully.',
            'data' => [
                'lead_id' => $lead->id,
                'redirect_url' => $popup->redirect_url,
            ]
        ]);
    }

    public function quotationDetails(Request $request, $slug, $quotation_no): View
    {
        $business = Business::select('id', 'template', 'name', 'slug', 'business_image', 'business_logo', 'address', 'contact', 'business_category_id', 'latitude', 'longitude', 'country_id', 'state_id', 'city_id', 'area', 'seo_description', 'seo_keyword', 'rating', 'pincode', 'facebook', 'twitter', 'instagram', 'linkedin', 'youtube')
            ->with([
                'businessCategory',
                'country',
                'state',
                'city'
            ])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $setting = getBusinessSettings($business->id);

        $quotation = Quotation::with([
            'items.product:id,name,sku,sell_price',
            'customer:id,customer_name,contact,business_name,address,city,state,pincode,customer_role_id,customer_id',
            'customer.customer:id,first_name,last_name,email',
            'creator:id,first_name,last_name,email',
            'order:id,invoice_number',
            'luckyDrawEntry.luckyDraw:id,title,type',
            'luckyDrawEntry.wonPrize:id,name,image_path',
            'luckyDrawEntry.createdBy:id,first_name,last_name'
        ])
            ->where('business_id', $business->id)
            ->where('quotation_no', $quotation_no)
            ->firstOrFail();

        $quotationPolicy = \App\Models\BusinessLeaglePage::where('business_id', $business->id)
            ->where('type', 'quotation_policy')
            ->where('status', 'active')
            ->first();

        $template = $business->template ?: 'template1';
        $view = "front.business.{$template}.quotation_details";
        if (!view()->exists($view)) {
            $view = "front.business.template1.quotation_details";
        }

        return view($view, compact('business', 'setting', 'quotation', 'quotationPolicy'));
    }

    public function quotationPdf(Request $request, $slug, $quotation_no)
    {
        $business = Business::select('id', 'slug')
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $quotation = $this->quotationRepository->findByNo($quotation_no, $business->id);
        $business = $quotation->business;
        $businessSetting = $business ? $business->businessSetting : null;
        $quotationPolicy = $business && $business->leaglePages ? $business->leaglePages->where('type', 'quotation_policy')->first() : null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pos.quotation.pdf', compact('quotation', 'business', 'businessSetting', 'quotationPolicy'))
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true);

        return $pdf->download('Quotation-' . $quotation->quotation_no . '.pdf');
    }

    public function updateQuotationQty(Request $request, $slug, $quotation_no)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:quotation_items,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        $business = Business::select('id', 'slug')
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $quotation = Quotation::where('business_id', $business->id)
            ->where('quotation_no', $quotation_no)
            ->firstOrFail();

        $subtotal = 0;
        foreach ($request->items as $itemData) {
            $quotationItem = \App\Models\QuotationItem::where('quotation_id', $quotation->id)
                ->where('id', $itemData['id'])
                ->first();

            if ($quotationItem) {
                $qty = max(1, (int)$itemData['qty']);
                $quotationItem->qty = $qty;
                $quotationItem->save();
                $subtotal += ($quotationItem->price * $qty);
            }
        }

        $quotation->subtotal = $subtotal;
        $quotation->total = max(0, $subtotal - (float)$quotation->discount + (float)$quotation->shipping_charge + (float)$quotation->tax);
        if ($quotation->status === Quotation::STATUS_PENDING) {
            $quotation->status = Quotation::STATUS_INPROGRESS;
        }
        $quotation->save();

        return response()->json([
            'success' => true,
            'message' => 'Quotation updated successfully.',
            'data' => [
                'subtotal' => number_format($quotation->subtotal, 2),
                'total' => number_format($quotation->total, 2),
            ]
        ]);
    }

    function getBusinessAddress($business): string
    {
        $address = $business->address;
        if (isset($business->city) && !empty($business->city->name)) {
            $address .= ', ' . $business->city->name;
        }
        if (isset($business->state) && !empty($business->state->name)) {
            $address .= ', ' . $business->state->name;
        }
        if (isset($business->country) && !empty($business->country->name)) {
            $address .= ', ' . $business->country->name;
        }
        if (isset($business->pincode) && !empty($business->pincode)) {
            $address .= '-' . $business->pincode;
        }
        return $address;
    }

    public function blogList(Request $request, $slug): View
    {
        $business = Business::select('id', 'name', 'slug', 'business_image', 'business_logo', 'contact', 'address', 'city_id', 'state_id', 'facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'seo_description', 'seo_keyword')
            ->with(['city', 'state'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $blogs = \App\Models\Blog::select('id', 'title', 'slug', 'short_description', 'image', 'published_at', 'business_id')
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->paginate(12);

        $setting = getBusinessSettings($business->id);

        return view('front.business.template1.blog_list', compact('business', 'blogs', 'setting'));
    }

    public function blogDetails(Request $request, $slug, $blog_slug): View
    {
        $business = Business::select('id', 'name', 'slug', 'business_image', 'business_logo', 'contact', 'address', 'city_id', 'state_id', 'facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'seo_description', 'seo_keyword')
            ->with(['city', 'state'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $blog = \App\Models\Blog::where('business_id', $business->id)
            ->where('slug', $blog_slug)
            ->where('status', 'active')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        $setting = getBusinessSettings($business->id);

        // Fetch recent/popular blogs for a sidebar (e.g. 5 blogs)
        $recentBlogs = \App\Models\Blog::select('id', 'title', 'slug', 'image', 'published_at', 'business_id')
            ->where('business_id', $business->id)
            ->where('id', '!=', $blog->id)
            ->where('status', 'active')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->limit(5)
            ->get();

        return view('front.business.template1.blog_details', compact('business', 'blog', 'setting', 'recentBlogs'));
    }
}
