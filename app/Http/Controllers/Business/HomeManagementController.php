<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use App\Models\HomeSectionItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\Service;
use App\Models\Label;
use App\Models\Expert;
use App\Models\BusinessHomePopup;
use App\Http\Requests\Business\StoreBusinessHomePopupRequest;
use App\Http\Requests\Business\UpdateBusinessHomePopupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Traits\CacheManagement;

class HomeManagementController extends Controller
{
    use CacheManagement;
    public function index()
    {
        if (checkBusinessPermission('view_home_layouts')) {
            return redirect()->route('business.home-management.layouts');
        } elseif (checkBusinessPermission('view_home_settings')) {
            return redirect()->route('business.home-management.settings');
        } elseif (checkBusinessPermission('view_campaign_leads')) {
            return redirect()->route('business.home-management.leads');
        }

        abort(403, 'Unauthorized. You do not have permission to access Home Management.');
    }

    public function settingsIndex()
    {
        $businessId = getBusinessId();
        $setting = getBusinessSettings($businessId);
        $popups = BusinessHomePopup::where('business_id', $businessId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('business.home-management.settings', compact('setting', 'popups'));
    }

    public function googleSheetsSetupHelp()
    {
        return view('business.home-management.google-sheets-setup');
    }

    public function settingsStore(StoreBusinessHomePopupRequest $request)
    {
        $businessId = getBusinessId();
        $data = $request->validated();
        $data['business_id'] = $businessId;

        if (isset($data['popup_type']) && $data['popup_type'] === 'media') {
            if (isset($data['media_type']) && $data['media_type'] === 'youtube') {
                $data['image_url'] = null;
                $data['redirect_url'] = null;
            } else {
                $data['youtube_url'] = null;
                if ($request->hasFile('image')) {
                    $data['image_url'] = fileUploadStorage($request->file('image'), 'home_popups');
                }
            }
        } else {
            $data['media_type'] = null;
            $data['youtube_url'] = null;
            if ($request->hasFile('image')) {
                $data['image_url'] = fileUploadStorage($request->file('image'), 'home_popups');
            }
        }

        if (isset($data['form_input_fields']) && is_array($data['form_input_fields'])) {
            $data['form_input_fields'] = array_values(array_filter($data['form_input_fields'], function ($field) {
                return !empty($field['label']);
            }));
        }

        BusinessHomePopup::create($data);
        $this->clearBusinessHomePopupCache($businessId);

        return response()->json([
            'status' => 'success',
            'message' => 'Popup campaign created successfully.'
        ]);
    }

    public function settingsEdit($id)
    {
        $businessId = getBusinessId();
        $popup = BusinessHomePopup::where('business_id', $businessId)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => $popup
        ]);
    }

    public function settingsUpdate(UpdateBusinessHomePopupRequest $request, $id)
    {
        $businessId = getBusinessId();
        $popup = BusinessHomePopup::where('business_id', $businessId)
            ->where('id', $id)
            ->firstOrFail();

        $data = $request->validated();

        if (isset($data['popup_type']) && $data['popup_type'] === 'media') {
            if (isset($data['media_type']) && $data['media_type'] === 'youtube') {
                if (!empty($popup->image_url)) {
                    fileRemoveStorage($popup->image_url);
                }
                $data['image_url'] = null;
                $data['redirect_url'] = null;
            } else {
                $data['youtube_url'] = null;
                if ($request->hasFile('image')) {
                    if (!empty($popup->image_url)) {
                        fileRemoveStorage($popup->image_url);
                    }
                    $data['image_url'] = fileUploadStorage($request->file('image'), 'home_popups');
                }
            }
        } else {
            $data['media_type'] = null;
            $data['youtube_url'] = null;
            if ($request->hasFile('image')) {
                if (!empty($popup->image_url)) {
                    fileRemoveStorage($popup->image_url);
                }
                $data['image_url'] = fileUploadStorage($request->file('image'), 'home_popups');
            }
        }

        if (isset($data['form_input_fields']) && is_array($data['form_input_fields'])) {
            $data['form_input_fields'] = array_values(array_filter($data['form_input_fields'], function ($field) {
                return !empty($field['label']);
            }));
        } else {
            $data['form_input_fields'] = null;
        }

        $popup->update($data);
        $this->clearBusinessHomePopupCache($businessId);

        return response()->json([
            'status' => 'success',
            'message' => 'Popup campaign updated successfully.'
        ]);
    }

    public function settingsToggleStatus($id)
    {
        $businessId = getBusinessId();
        $popup = BusinessHomePopup::where('business_id', $businessId)
            ->where('id', $id)
            ->firstOrFail();

        $newStatus = $popup->status === 'active' ? 'inactive' : 'active';
        $popup->update(['status' => $newStatus]);
        $this->clearBusinessHomePopupCache($businessId);

        return response()->json([
            'status' => 'success',
            'new_status' => $newStatus,
            'message' => 'Popup status updated to ' . $newStatus . '.'
        ]);
    }

    public function settingsDestroy($id)
    {
        $businessId = getBusinessId();
        $popup = BusinessHomePopup::where('business_id', $businessId)
            ->where('id', $id)
            ->firstOrFail();

        if (!empty($popup->image_url)) {
            fileRemoveStorage($popup->image_url);
        }

        $popup->delete();
        $this->clearBusinessHomePopupCache($businessId);

        return response()->json([
            'status' => 'success',
            'message' => 'Popup campaign deleted successfully.'
        ]);
    }

    public function layoutsIndex()
    {
        $businessId = getBusinessId();

        $sections = HomeSection::where('business_id', $businessId)
            ->with(['items.category', 'items.product', 'items.service', 'items.label', 'items.expert'])
            ->orderBy('sort_order', 'asc')
            ->get();

        $setting = getBusinessSettings($businessId);

        return view('business.home-management.layouts', compact('sections', 'setting'));
    }

    public function layoutsSearchItems(Request $request)
    {
        $businessId = getBusinessId();
        $type = $request->input('type');
        $search = trim($request->input('q', ''));
        $selectedIds = array_filter(array_map('intval', (array) $request->input('selected_ids', [])));

        $items = collect();

        if (in_array($type, ['products'])) {
            $query = Product::where('business_id', $businessId)
                ->where('status', 'active')
                ->select('id', 'name');

            if (mb_strlen($search) >= 2) {
                $query->where('name', 'like', "%{$search}%");
            }

            $items = $query->limit(10)->get();

            if (!empty($selectedIds)) {
                $selectedItems = Product::where('business_id', $businessId)
                    ->whereIn('id', $selectedIds)
                    ->select('id', 'name')
                    ->get();
                $items = $selectedItems->merge($items)->unique('id')->values();
            }
        } elseif (in_array($type, ['services'])) {
            $query = Service::where('business_id', $businessId)
                ->where('status', 'active')
                ->select('id', 'name');

            if (mb_strlen($search) >= 2) {
                $query->where('name', 'like', "%{$search}%");
            }

            $items = $query->limit(10)->get();

            if (!empty($selectedIds)) {
                $selectedItems = Service::where('business_id', $businessId)
                    ->whereIn('id', $selectedIds)
                    ->select('id', 'name')
                    ->get();
                $items = $selectedItems->merge($items)->unique('id')->values();
            }
        } elseif (in_array($type, ['product_categories', 'product_category_items'])) {
            $query = Category::where('business_id', $businessId)
                ->where('type', 'Products')
                ->whereNull('parent_id')
                ->where('status', 'active')
                ->select('id', 'name');

            if (mb_strlen($search) >= 2) {
                $query->where('name', 'like', "%{$search}%");
            }

            $items = $query->limit(10)->get();

            if (!empty($selectedIds)) {
                $selectedItems = Category::where('business_id', $businessId)
                    ->whereIn('id', $selectedIds)
                    ->select('id', 'name')
                    ->get();
                $items = $selectedItems->merge($items)->unique('id')->values();
            }
        } elseif (in_array($type, ['service_categories', 'service_category_items'])) {
            $query = Category::where('business_id', $businessId)
                ->where('type', 'Services')
                ->whereNull('parent_id')
                ->where('status', 'active')
                ->select('id', 'name');

            if (mb_strlen($search) >= 2) {
                $query->where('name', 'like', "%{$search}%");
            }

            $items = $query->limit(10)->get();

            if (!empty($selectedIds)) {
                $selectedItems = Category::where('business_id', $businessId)
                    ->whereIn('id', $selectedIds)
                    ->select('id', 'name')
                    ->get();
                $items = $selectedItems->merge($items)->unique('id')->values();
            }
        } elseif (in_array($type, ['product_labels'])) {
            $query = Label::where('business_id', $businessId)
                ->where('status', 'active')
                ->select('id', 'name')
                ->orderBy('short_order', 'asc');

            if (mb_strlen($search) >= 2) {
                $query->where('name', 'like', "%{$search}%");
            }

            $items = $query->limit(10)->get();

            if (!empty($selectedIds)) {
                $selectedItems = Label::where('business_id', $businessId)
                    ->whereIn('id', $selectedIds)
                    ->select('id', 'name')
                    ->get();
                $items = $selectedItems->merge($items)->unique('id')->values();
            }
        } elseif (in_array($type, ['experts'])) {
            $query = Expert::where('business_id', $businessId)
                ->where('status', 1)
                ->with('user:id,first_name,last_name')
                ->select('id', 'user_id');

            if (mb_strlen($search) >= 2) {
                $query->whereHas('user', function ($uq) use ($search) {
                    $uq->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%")
                       ->orWhere('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%");
                });
            }

            $items = $query->limit(10)->get()->map(function ($exp) {
                return (object) ['id' => $exp->id, 'name' => $exp->user?->name ?? ''];
            });

            if (!empty($selectedIds)) {
                $selectedItems = Expert::where('business_id', $businessId)
                    ->whereIn('id', $selectedIds)
                    ->with('user:id,first_name,last_name')
                    ->select('id', 'user_id')
                    ->get()
                    ->map(function ($exp) {
                        return (object) ['id' => $exp->id, 'name' => $exp->user?->name ?? ''];
                    });
                $items = $selectedItems->merge($items)->unique('id')->values();
            }
        }

        return response()->json([
            'success' => true,
            'items' => $items
        ]);
    }

    public function layoutsStore(Request $request)
    {
        $sectionType = $request->input('section_type', 'banner_custom');

        $businessId = getBusinessId();
        $setting = getBusinessSettings($businessId);

        $sectionLimit = (int) config('const.home_section_limit', 15);
        $currentSectionCount = HomeSection::where('business_id', $businessId)->count();
        if ($currentSectionCount >= $sectionLimit) {
            return response()->json([
                'success' => false,
                'message' => "Maximum section limit of {$sectionLimit} reached. Please remove an existing section before creating a new one."
            ], 422);
        }

        $allowedTypes = ['banner_custom', 'slider_banner'];
        if (isset($setting->is_ecommerce_system) && $setting->is_ecommerce_system) {
            $allowedTypes = array_merge($allowedTypes, ['product_categories', 'product_category_items', 'products']);
            if (isset($setting->is_label) && $setting->is_label) {
                $allowedTypes[] = 'product_labels';
            }
        }
        if (isset($setting->is_service_system) && $setting->is_service_system) {
            $allowedTypes = array_merge($allowedTypes, ['service_categories', 'service_category_items', 'services']);
        }
        if (isset($setting->is_appointment_system) && $setting->is_appointment_system) {
            $allowedTypes[] = 'experts';
        }

        $rules = [
            'section_type' => 'required|string|in:' . implode(',', $allowedTypes),
            'title' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:active,inactive',
        ];

        if ($sectionType === 'banner_custom') {
            $imageCount = (int)$request->input('image_count', 1);
            $rules['image_count'] = 'required|integer|in:1,2,3,4,6';
            $rules['ui_style'] = 'required|string|in:style_1,style_2,style_3';
            $rules['images'] = 'required|array|size:' . $imageCount;
            $rules['images.*'] = 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120';
            $rules['redirect_urls'] = 'nullable|array';
        } elseif ($sectionType === 'slider_banner') {
            $imageCount = (int)$request->input('image_count', 1);
            $rules['image_count'] = 'required|integer|in:1,2,3,4,6';
            $rules['ui_style'] = 'required|string|in:style_1,style_2,style_3';
            $rules['images'] = 'required|array|min:1|max:20';
            $rules['images.*'] = 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120';
            $rules['redirect_urls'] = 'nullable|array';
            $rules['slide_interval'] = 'nullable|integer|between:3000,5000';
        } else {
            $selectionMode = $request->input('item_selection_type', $request->input('ui_style', 'random'));
            $rules['item_selection_type'] = 'nullable|string|in:manual,random';
            if ($selectionMode === 'manual') {
                if (in_array($sectionType, ['product_categories', 'product_category_items', 'service_categories', 'service_category_items'])) {
                    $rules['category_ids'] = 'required|array|min:1';
                    $rules['category_ids.*'] = 'required|exists:categories,id';
                } elseif ($sectionType === 'products') {
                    $rules['product_ids'] = 'required|array|min:1|max:6';
                    $rules['product_ids.*'] = 'required|exists:products,id';
                } elseif ($sectionType === 'services') {
                    $rules['service_ids'] = 'required|array|min:1|max:4';
                    $rules['service_ids.*'] = 'required|exists:services,id';
                } elseif ($sectionType === 'product_labels') {
                    $rules['label_ids'] = 'required|array|min:1';
                    $rules['label_ids.*'] = 'required|exists:labels,id';
                } elseif ($sectionType === 'experts') {
                    $rules['expert_ids'] = 'required|array|min:1';
                    $rules['expert_ids.*'] = 'required|exists:experts,id';
                }
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $businessId = getBusinessId();

        try {
            DB::beginTransaction();

            $maxSortOrder = HomeSection::where('business_id', $businessId)->max('sort_order') ?? 0;
            $status = $request->input('status', 'active');
            if (!in_array($status, ['active', 'inactive'])) {
                $status = 'active';
            }

            if (in_array($sectionType, ['banner_custom', 'slider_banner'])) {
                $imageCount = (int)$request->input('image_count', 1);
                $dimensions = [
                    1 => ['w' => 1800, 'h' => 600],
                    2 => ['w' => 900, 'h' => 600],
                    3 => ['w' => 600, 'h' => 450],
                    4 => ['w' => 450, 'h' => 450],
                    6 => ['w' => 300, 'h' => 300],
                ];

                $dim = $dimensions[$imageCount] ?? ['w' => 1800, 'h' => 600];

                $settingsData = null;
                if ($sectionType === 'slider_banner') {
                    $settingsData = [
                        'enable_slider' => $request->boolean('enable_slider'),
                        'auto_play' => $request->boolean('auto_play'),
                        'slide_interval' => (int)$request->input('slide_interval', 3000),
                        'infinite_loop' => $request->boolean('infinite_loop'),
                        'navigation_arrows' => $request->boolean('navigation_arrows'),
                        'pagination_dots' => $request->boolean('pagination_dots'),
                        'slide_slot_wise' => $request->boolean('slide_slot_wise'),
                    ];
                }

                $section = HomeSection::create([
                    'business_id' => $businessId,
                    'section_type' => $sectionType,
                    'title' => $request->title,
                    'image_count' => $imageCount,
                    'ui_style' => $request->ui_style,
                    'settings' => $settingsData,
                    'item_selection_type' => null,
                    'sort_order' => $maxSortOrder + 1,
                    'status' => $status,
                ]);

                $images = $request->file('images', []);
                $redirectUrls = $request->input('redirect_urls', []);
                $statuses = $request->input('statuses', []);

                foreach ($images as $index => $imageFile) {
                    if ($imageFile && $imageFile->isValid()) {
                        $path = fileUploadStorage($imageFile, 'home_sections', $dim['w'], $dim['h']);
                        $url = isset($redirectUrls[$index]) ? $redirectUrls[$index] : null;
                        $status = isset($statuses[$index]) && $statuses[$index] === 'inactive' ? 'inactive' : 'active';

                        HomeSectionItem::create([
                            'home_section_id' => $section->id,
                            'image_path' => $path,
                            'redirect_url' => $url,
                            'status' => $status,
                            'sort_order' => $index + 1,
                        ]);
                    }
                }
            } else {
                $selectionMode = $request->input('ui_style', 'random');
                $selectionMode = $request->input('item_selection_type', 'random');

                $section = HomeSection::create([
                    'business_id' => $businessId,
                    'section_type' => $sectionType,
                    'title' => $request->title,
                    'image_count' => null,
                    'ui_style' => null,
                    'item_selection_type' => $selectionMode,
                    'sort_order' => $maxSortOrder + 1,
                    'status' => $status,
                ]);

                if ($selectionMode === 'manual') {
                    if (in_array($sectionType, ['product_categories', 'product_category_items', 'service_categories', 'service_category_items'])) {
                        $categoryIds = $request->input('category_ids', []);
                        foreach ($categoryIds as $index => $catId) {
                            HomeSectionItem::create([
                                'home_section_id' => $section->id,
                                'category_id' => $catId,
                                'sort_order' => $index + 1,
                            ]);
                        }
                    } elseif ($sectionType === 'products') {
                        $itemIds = $request->input('product_ids', []);
                        foreach ($itemIds as $index => $pId) {
                            HomeSectionItem::create([
                                'home_section_id' => $section->id,
                                'product_id' => $pId,
                                'sort_order' => $index + 1,
                            ]);
                        }
                    } elseif ($sectionType === 'services') {
                        $itemIds = $request->input('service_ids', []);
                        foreach ($itemIds as $index => $sId) {
                            HomeSectionItem::create([
                                'home_section_id' => $section->id,
                                'service_id' => $sId,
                                'sort_order' => $index + 1,
                            ]);
                        }
                    } elseif ($sectionType === 'product_labels') {
                        $itemIds = $request->input('label_ids', []);
                        foreach ($itemIds as $index => $lId) {
                            HomeSectionItem::create([
                                'home_section_id' => $section->id,
                                'label_id' => $lId,
                                'sort_order' => $index + 1,
                            ]);
                        }
                    } elseif ($sectionType === 'experts') {
                        $itemIds = $request->input('expert_ids', []);
                        foreach ($itemIds as $index => $eId) {
                            HomeSectionItem::create([
                                'home_section_id' => $section->id,
                                'expert_id' => $eId,
                                'sort_order' => $index + 1,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            $this->clearBusinessCustomeHomeSectionCache($businessId);

            return response()->json([
                'success' => true,
                'message' => 'Custom layout section created successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => checkResponse($e, 'Failed to create section.')
            ], 500);
        }
    }

    public function layoutsEdit($id)
    {
        $businessId = getBusinessId();

        $section = HomeSection::where('business_id', $businessId)
            ->where('id', $id)
            ->with(['items.category', 'items.product', 'items.service', 'items.label', 'items.expert'])
            ->first();

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $section
        ]);
    }

    public function layoutsUpdate(Request $request, $id)
    {
        $sectionType = $request->input('section_type', 'banner_custom');

        $businessId = getBusinessId();
        $setting = getBusinessSettings($businessId);

        $allowedTypes = ['banner_custom', 'slider_banner'];
        if (isset($setting->is_ecommerce_system) && $setting->is_ecommerce_system) {
            $allowedTypes = array_merge($allowedTypes, ['product_categories', 'product_category_items', 'products']);
            if (isset($setting->is_label) && $setting->is_label) {
                $allowedTypes[] = 'product_labels';
            }
        }
        if (isset($setting->is_service_system) && $setting->is_service_system) {
            $allowedTypes = array_merge($allowedTypes, ['service_categories', 'service_category_items', 'services']);
        }
        if (isset($setting->is_appointment_system) && $setting->is_appointment_system) {
            $allowedTypes[] = 'experts';
        }

        $rules = [
            'section_type' => 'required|string|in:' . implode(',', $allowedTypes),
            'title' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:active,inactive',
        ];

        if ($sectionType === 'banner_custom') {
            $imageCount = (int)$request->input('image_count', 1);
            $rules['image_count'] = 'required|integer|in:1,2,3,4,6';
            $rules['ui_style'] = 'required|string|in:style_1,style_2,style_3';
            $rules['images'] = 'nullable|array';
            $rules['images.*'] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120';
            $rules['redirect_urls'] = 'nullable|array';
        } elseif ($sectionType === 'slider_banner') {
            $imageCount = (int)$request->input('image_count', 1);
            $rules['image_count'] = 'required|integer|in:1,2,3,4,6';
            $rules['ui_style'] = 'required|string|in:style_1,style_2,style_3';
            $rules['images'] = 'nullable|array|max:20';
            $rules['images.*'] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120';
            $rules['existing_images'] = 'nullable|array';
            $rules['existing_images.*'] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120';
            $rules['redirect_urls'] = 'nullable|array';
            $rules['slide_interval'] = 'nullable|integer|between:3000,5000';
        } else {
            $selectionMode = $request->input('item_selection_type', 'random');
            $rules['item_selection_type'] = 'nullable|string|in:manual,random';
            $rules['ui_style'] = 'nullable|string|in:style_1,style_2,style_3';
            if ($selectionMode === 'manual') {
                if (in_array($sectionType, ['product_categories', 'product_category_items', 'service_categories', 'service_category_items'])) {
                    $rules['category_ids'] = 'required|array|min:1';
                    $rules['category_ids.*'] = 'required|exists:categories,id';
                } elseif ($sectionType === 'products') {
                    $rules['product_ids'] = 'required|array|min:1|max:6';
                    $rules['product_ids.*'] = 'required|exists:products,id';
                } elseif ($sectionType === 'services') {
                    $rules['service_ids'] = 'required|array|min:1|max:4';
                    $rules['service_ids.*'] = 'required|exists:services,id';
                } elseif ($sectionType === 'product_labels') {
                    $rules['label_ids'] = 'required|array|min:1';
                    $rules['label_ids.*'] = 'required|exists:labels,id';
                } elseif ($sectionType === 'experts') {
                    $rules['expert_ids'] = 'required|array|min:1';
                    $rules['expert_ids.*'] = 'required|exists:experts,id';
                }
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $businessId = getBusinessId();

        $section = HomeSection::where('business_id', $businessId)->where('id', $id)->with('items')->first();

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found'
            ], 404);
        }

        try {
            DB::beginTransaction();

            $status = $request->input('status', $section->status);
            if (!in_array($status, ['active', 'inactive'])) {
                $status = 'active';
            }

            if ($section->section_type !== $sectionType) {
                foreach ($section->items as $oldItem) {
                    if ($oldItem->image_path) {
                        fileRemoveStorage($oldItem->image_path);
                    }
                }
                $section->items()->delete();
                $section->unsetRelation('items');
            }

            $settingsData = null;
            if ($sectionType === 'slider_banner') {
                $settingsData = [
                    'enable_slider' => $request->boolean('enable_slider'),
                    'auto_play' => $request->boolean('auto_play'),
                    'slide_interval' => (int)$request->input('slide_interval', 3000),
                    'infinite_loop' => $request->boolean('infinite_loop'),
                    'navigation_arrows' => $request->boolean('navigation_arrows'),
                    'pagination_dots' => $request->boolean('pagination_dots'),
                    'slide_slot_wise' => $request->boolean('slide_slot_wise'),
                ];
            }

            if ($sectionType === 'banner_custom') {
                $imageCount = (int)$request->input('image_count', 1);
                $dimensions = [
                    1 => ['w' => 1800, 'h' => 600],
                    2 => ['w' => 900, 'h' => 600],
                    3 => ['w' => 600, 'h' => 450],
                    4 => ['w' => 450, 'h' => 450],
                    6 => ['w' => 300, 'h' => 300],
                ];

                $dim = $dimensions[$imageCount] ?? ['w' => 1800, 'h' => 600];

                $section->update([
                    'section_type' => 'banner_custom',
                    'title' => $request->title,
                    'image_count' => $imageCount,
                    'ui_style' => $request->ui_style,
                    'settings' => null,
                    'item_selection_type' => null,
                    'status' => $status,
                ]);

                $images = $request->file('images', []);
                $redirectUrls = $request->input('redirect_urls', []);
                $statuses = $request->input('statuses', []);

                for ($i = 0; $i < $imageCount; $i++) {
                    $sortOrder = $i + 1;
                    $existingItem = HomeSectionItem::where('home_section_id', $section->id)
                        ->where('sort_order', $sortOrder)
                        ->first();

                    $redirectUrl = isset($redirectUrls[$i]) ? $redirectUrls[$i] : null;
                    $status = isset($statuses[$i]) && $statuses[$i] === 'inactive' ? 'inactive' : 'active';

                    if (isset($images[$i]) && $images[$i]->isValid()) {
                        if ($existingItem && $existingItem->image_path) {
                            fileRemoveStorage($existingItem->image_path);
                        }

                        $path = fileUploadStorage($images[$i], 'home_sections', $dim['w'], $dim['h']);

                        if ($existingItem) {
                            $existingItem->update([
                                'category_id' => null,
                                'product_id' => null,
                                'service_id' => null,
                                'image_path' => $path,
                                'redirect_url' => $redirectUrl,
                                'status' => $status,
                            ]);
                        } else {
                            HomeSectionItem::create([
                                'home_section_id' => $section->id,
                                'image_path' => $path,
                                'redirect_url' => $redirectUrl,
                                'status' => $status,
                                'sort_order' => $sortOrder,
                            ]);
                        }
                    } else {
                        if ($existingItem && $existingItem->image_path) {
                            $existingItem->update([
                                'redirect_url' => $redirectUrl,
                                'status' => $status,
                            ]);
                        } else {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => 'An image is required for slot #' . $sortOrder
                            ], 422);
                        }
                    }
                }

                $itemsToDelete = HomeSectionItem::where('home_section_id', $section->id)
                    ->where('sort_order', '>', $imageCount)
                    ->get();

                foreach ($itemsToDelete as $delItem) {
                    if ($delItem->image_path) {
                        fileRemoveStorage($delItem->image_path);
                    }
                    $delItem->delete();
                }
            } elseif ($sectionType === 'slider_banner') {
                $imageCount = (int)$request->input('image_count', 1);
                $dimensions = [
                    1 => ['w' => 1800, 'h' => 600],
                    2 => ['w' => 900, 'h' => 600],
                    3 => ['w' => 600, 'h' => 450],
                    4 => ['w' => 450, 'h' => 450],
                    6 => ['w' => 300, 'h' => 300],
                ];

                $dim = $dimensions[$imageCount] ?? ['w' => 1800, 'h' => 600];

                $images = $request->file('images', []);
                $existingItemIds = array_map('intval', $request->input('existing_item_ids', []));
                $existingRedirectUrls = $request->input('existing_redirect_urls', []);
                $existingStatuses = $request->input('existing_statuses', []);
                $newRedirectUrls = $request->input('redirect_urls', []);
                $newStatuses = $request->input('new_statuses', []);

                $totalCount = count($existingItemIds) + count($images);
                if ($totalCount < 1 || $totalCount > 20) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please provide between 1 and 20 images in total for the Slider Banner.'
                    ], 422);
                }

                $section->update([
                    'section_type' => 'slider_banner',
                    'title' => $request->title,
                    'image_count' => $imageCount,
                    'ui_style' => $request->ui_style,
                    'settings' => $settingsData,
                    'item_selection_type' => null,
                    'status' => $status,
                ]);

                $existingDbItems = HomeSectionItem::where('home_section_id', $section->id)->get();

                // Delete items removed by user
                foreach ($existingDbItems as $dbItem) {
                    if (!in_array($dbItem->id, $existingItemIds)) {
                        if ($dbItem->image_path) {
                            fileRemoveStorage($dbItem->image_path);
                        }
                        $dbItem->delete();
                    }
                }

                // Process retained existing items
                $itemOrder = 0;
                foreach ($existingItemIds as $existingId) {
                    $dbItem = HomeSectionItem::find($existingId);
                    if ($dbItem) {
                        $itemOrder++;
                        $dbItem->redirect_url = $existingRedirectUrls[$existingId] ?? null;
                        $dbItem->status = isset($existingStatuses[$existingId]) && $existingStatuses[$existingId] === 'inactive' ? 'inactive' : 'active';
                        $dbItem->sort_order = $itemOrder;

                        // Check if a new image file is uploaded to replace this existing item's image
                        if ($request->hasFile("existing_images.{$existingId}")) {
                            $imageFile = $request->file("existing_images.{$existingId}");
                            if ($imageFile && $imageFile->isValid()) {
                                if ($dbItem->image_path) {
                                    fileRemoveStorage($dbItem->image_path);
                                }
                                $path = fileUploadStorage($imageFile, 'home_sections', $dim['w'], $dim['h']);
                                $dbItem->image_path = $path;
                            }
                        }

                        $dbItem->save();
                    }
                }

                // Process newly added bulk images
                foreach ($images as $k => $imageFile) {
                    if ($imageFile && $imageFile->isValid()) {
                        $itemOrder++;
                        $redirectUrl = $newRedirectUrls[$k] ?? null;
                        $path = fileUploadStorage($imageFile, 'home_sections', $dim['w'], $dim['h']);
                        $status = isset($newStatuses[$k]) && $newStatuses[$k] === 'inactive' ? 'inactive' : 'active';

                        HomeSectionItem::create([
                            'home_section_id' => $section->id,
                            'image_path' => $path,
                            'redirect_url' => $redirectUrl,
                            'status' => $status,
                            'sort_order' => $itemOrder,
                        ]);
                    }
                }
            } else {
                $selectionMode = $request->input('item_selection_type', 'random');

                $section->update([
                    'section_type' => $sectionType,
                    'title' => $request->title,
                    'image_count' => null,
                    'ui_style' => null,
                    'item_selection_type' => $selectionMode,
                    'status' => $status,
                ]);

                foreach ($section->items as $delItem) {
                    if ($delItem->image_path) {
                        fileRemoveStorage($delItem->image_path);
                    }
                }
                $section->items()->delete();

                if ($selectionMode === 'manual') {
                    if (in_array($sectionType, ['product_categories', 'product_category_items', 'service_categories', 'service_category_items'])) {
                        $categoryIds = $request->input('category_ids', []);
                        foreach ($categoryIds as $index => $catId) {
                            HomeSectionItem::create([
                                'home_section_id' => $section->id,
                                'category_id' => $catId,
                                'sort_order' => $index + 1,
                            ]);
                        }
                    } elseif ($sectionType === 'products') {
                        $itemIds = $request->input('product_ids', []);
                        foreach ($itemIds as $index => $pId) {
                            HomeSectionItem::create([
                                'home_section_id' => $section->id,
                                'product_id' => $pId,
                                'sort_order' => $index + 1,
                            ]);
                        }
                    } elseif ($sectionType === 'services') {
                        $itemIds = $request->input('service_ids', []);
                        foreach ($itemIds as $index => $sId) {
                            HomeSectionItem::create([
                                'home_section_id' => $section->id,
                                'service_id' => $sId,
                                'sort_order' => $index + 1,
                            ]);
                        }
                    } elseif ($sectionType === 'product_labels') {
                        $itemIds = $request->input('label_ids', []);
                        foreach ($itemIds as $index => $lId) {
                            HomeSectionItem::create([
                                'home_section_id' => $section->id,
                                'label_id' => $lId,
                                'sort_order' => $index + 1,
                            ]);
                        }
                    } elseif ($sectionType === 'experts') {
                        $itemIds = $request->input('expert_ids', []);
                        foreach ($itemIds as $index => $eId) {
                            HomeSectionItem::create([
                                'home_section_id' => $section->id,
                                'expert_id' => $eId,
                                'sort_order' => $index + 1,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            $this->clearBusinessCustomeHomeSectionCache($businessId);

            return response()->json([
                'success' => true,
                'message' => 'Custom layout section updated successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => checkResponse($e, 'Failed to update section.')
            ], 500);
        }
    }

    public function layoutsDestroy($id)
    {
        $businessId = getBusinessId();

        $section = HomeSection::where('business_id', $businessId)->where('id', $id)->first();

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found'
            ], 404);
        }

        try {
            DB::beginTransaction();

            foreach ($section->items as $item) {
                if ($item->image_path) {
                    fileRemoveStorage($item->image_path);
                }
            }

            $section->items()->delete();
            $section->delete();

            DB::commit();
            $this->clearBusinessCustomeHomeSectionCache($businessId);

            return response()->json([
                'success' => true,
                'message' => 'Section deleted successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => checkResponse($e, 'Failed to delete section.')
            ], 500);
        }
    }

    public function layoutsUpdateOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orders' => 'required|array',
            'orders.*' => 'required|exists:home_sections,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $businessId = getBusinessId();

        try {
            DB::beginTransaction();

            foreach ($request->orders as $index => $sectionId) {
                HomeSection::where('business_id', $businessId)
                    ->where('id', $sectionId)
                    ->update(['sort_order' => $index + 1]);
            }

            DB::commit();
            $this->clearBusinessCustomeHomeSectionCache($businessId);

            return response()->json([
                'success' => true,
                'message' => 'Section order updated successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => checkResponse($e, 'Failed to update section order.')
            ], 500);
        }
    }

    public function layoutsItemToggleStatus($id)
    {
        $businessId = getBusinessId();

        $item = HomeSectionItem::whereHas('homeSection', function ($q) use ($businessId) {
            $q->where('business_id', $businessId);
        })->where('id', $id)->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Image item not found.'
            ], 404);
        }

        $newStatus = $item->status === 'active' ? 'inactive' : 'active';
        $item->update(['status' => $newStatus]);
        $this->clearBusinessCustomeHomeSectionCache($businessId);

        return response()->json([
            'success' => true,
            'new_status' => $newStatus,
            'message' => 'Image status updated to ' . $newStatus . '.'
        ]);
    }

    public function layoutsToggleStatus($id)
    {
        $businessId = getBusinessId();

        $section = HomeSection::where('business_id', $businessId)->where('id', $id)->first();

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Layout section not found.'
            ], 404);
        }

        $newStatus = $section->status === 'active' ? 'inactive' : 'active';
        $section->update(['status' => $newStatus]);
        $this->clearBusinessCustomeHomeSectionCache($businessId);

        return response()->json([
            'success' => true,
            'new_status' => $newStatus,
            'message' => 'Layout section status updated to ' . $newStatus . '.'
        ]);
    }
}
