<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\BusinessCustomerRole;
use App\Models\BusinessOffer;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class OfferController extends Controller
{
    /**
     * Display a listing of offers or Datatable response.
     */
    public function index(Request $request)
    {
        if (!checkBusinessPermission('view_offers')) {
            abort(403, 'You do not have permission to view offers.');
        }

        $businessId = getBusinessId();

        if ($request->ajax()) {
            $offers = BusinessOffer::where('business_id', $businessId)
                ->with([
                    'product:id,name,sell_price,price',
                    'category:id,name,parent_id',
                    'category.parent:id,name'
                ])
                ->select(
                    'id',
                    'business_id',
                    'type',
                    'item_id',
                    'is_role_wise',
                    'role_wise_detail',
                    'price_type',
                    'value',
                    'is_price_reflected_on_front',
                    'is_active',
                    'created_at'
                );

            // Pre-fetch roles to display role names in role_wise_detail column efficiently
            $roles = BusinessCustomerRole::where('business_id', $businessId)
                ->pluck('role_name', 'id')
                ->toArray();

            return DataTables::of($offers)
                ->addIndexColumn()
                ->addColumn('target_type', function ($row) {
                    $badge = $row->type === 'product' ? 'bg-primary' : 'bg-info text-dark';
                    $label = $row->type === 'product' ? 'Product' : 'Product Category';
                    return '<span class="badge ' . $badge . ' rounded-pill px-2.5 py-1">' . $label . '</span>';
                })
                ->addColumn('item_name', function ($row) {
                    if ($row->type === 'product' && $row->product) {
                        $sellPrice = $row->product->sell_price ?? $row->product->price ?? 0;
                        return '<div>' . e($row->product->name) . ' <div class="small text-muted mt-0.5"><i class="bi bi-tag-fill me-1 text-primary"></i>Sell Price: <strong>₹' . number_format($sellPrice, 2) . '</strong></div></div>';
                    }
                    return e($row->category->display_name ?? 'N/A');
                })
                ->filterColumn('item_name', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where(function ($subQ) use ($keyword) {
                            $subQ->where('business_offers.type', 'product')
                                ->whereHas('product', function ($pQ) use ($keyword) {
                                    $pQ->where('name', 'like', "%{$keyword}%")
                                        ->orWhere('sku', 'like', "%{$keyword}%");
                                    });
                        })->orWhere(function ($subQ) use ($keyword) {
                            $subQ->where('business_offers.type', 'product_category')
                                ->whereHas('category', function ($cQ) use ($keyword) {
                                    $cQ->where('name', 'like', "%{$keyword}%")
                                        ->orWhereHas('parent', function ($parentQ) use ($keyword) {
                                            $parentQ->where('name', 'like', "%{$keyword}%");
                                        });
                                });
                        });
                    });
                })
                ->addColumn('mode', function ($row) {
                    if ($row->is_role_wise) {
                        return '<span class="badge bg-purple text-white rounded-pill px-2 py-1" style="background-color: #6f42c1;"><i class="bi bi-people me-1"></i>Role Wise</span>';
                    }
                    return '<span class="badge bg-secondary rounded-pill px-2 py-1">General</span>';
                })
                ->addColumn('pricing_details', function ($row) use ($roles) {
                    if ($row->is_role_wise && !empty($row->role_wise_detail)) {
                        $html = '<div class="d-flex flex-column gap-1">';
                        foreach ($row->role_wise_detail as $roleId => $detail) {
                            $roleName = $roles[$roleId] ?? 'Role #' . $roleId;
                            $pType = ucfirst(str_replace('_', ' ', $detail['price_type'] ?? ''));
                            $val = $detail['price_type'] === 'percentage' ? ($detail['value'] ?? 0) . '%' : '₹' . number_format($detail['value'] ?? 0, 2);
                            $html .= '<span class="badge bg-light text-dark border text-start">'
                                . '<strong>' . e($roleName) . ':</strong> ' . $pType . ' (' . $val . ')</span>';
                        }
                        $html .= '</div>';
                        return $html;
                    }

                    $pType = ucfirst(str_replace('_', ' ', $row->price_type ?? ''));
                    $val = $row->price_type === 'percentage' ? ($row->value ?? 0) . '%' : '₹' . number_format($row->value ?? 0, 2);
                    return '<span class="fw-bold text-dark">' . $pType . '</span> <span class="text-primary">(' . $val . ')</span>';
                })
                ->addColumn('front_reflected', function ($row) {
                    if ($row->is_price_reflected_on_front) {
                        return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">Yes</span>';
                    }
                    return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1">No</span>';
                })
                ->addColumn('status_badge', function ($row) {
                    $badgeClass = $row->is_active ? 'bg-success' : 'bg-secondary';
                    $statusText = $row->is_active ? 'Active' : 'Inactive';
                    if (!checkBusinessPermission('edit_offers')) {
                        return '<span class="badge ' . $badgeClass . ' rounded-pill">' . $statusText . '</span>';
                    }
                    return '<span class="badge ' . $badgeClass . ' rounded-pill cursor-pointer" style="cursor: pointer;" onclick="toggleOfferStatus(' . $row->id . ')">'
                        . $statusText . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $canEdit = checkBusinessPermission('edit_offers');
                    $canDelete = checkBusinessPermission('delete_offers');
                    if (!$canEdit && !$canDelete) {
                        return '<div class="text-center text-muted small">-</div>';
                    }
                    $html = '<div class="text-center">';
                    if ($canEdit) {
                        $html .= '<button type="button" onclick="editOffer(' . $row->id . ')" class="btn btn-outline-primary btn-sm me-1" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>';
                    }
                    if ($canDelete) {
                        $html .= '<button type="button" onclick="deleteOffer(' . $row->id . ')" class="btn btn-outline-danger btn-sm" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['target_type', 'item_name', 'mode', 'pricing_details', 'front_reflected', 'status_badge', 'action'])
                ->make(true);
        }

        $customerRoles = BusinessCustomerRole::where('business_id', $businessId)
            ->where('status', 'active')
            ->select('id', 'role_name')
            ->orderBy('role_name')
            ->get();

        $businessSetting = getBusinessSettings();

        return view('business.offers.index', compact('customerRoles', 'businessSetting'));
    }

    /**
     * AJAX search products or categories for offer target selection.
     */
    public function searchItems(Request $request)
    {
        if (!checkBusinessPermission('view_offers') && !checkBusinessPermission('add_offers') && !checkBusinessPermission('edit_offers')) {
            return response()->json(['results' => []], 403);
        }

        $businessId = getBusinessId();
        $type = $request->get('type', 'product');
        $search = trim($request->get('q', ''));
        $id = $request->get('id');

        $results = [];

        if ($type === 'product') {
            $query = Product::where('business_id', $businessId)
                ->where('status', 'active');

            if (!empty($id)) {
                $query->where('id', $id);
            } elseif (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            }

            $products = $query->select('id', 'name', 'sell_price', 'price')
                ->orderBy('name')
                ->limit(10)
                ->get();

            foreach ($products as $prod) {
                $sellPrice = $prod->sell_price ?? $prod->price ?? 0;
                $results[] = [
                    'id' => $prod->id,
                    'text' => $prod->name . ' (Sell Price: ₹' . number_format($sellPrice, 2) . ')',
                    'sell_price' => (float) $sellPrice,
                ];
            }
        } else {
            $query = Category::where('business_id', $businessId)
                ->where('type', 'Products')
                ->where('status', 'active')
                ->with('parent:id,name');

            if (!empty($id)) {
                $query->where('id', $id);
            } elseif (!empty($search)) {
                $query->where('name', 'like', "%{$search}%")
                    ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
                    ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', ["{$search}%"])
                    ->orderBy('name', 'asc');
            } else {
                $query->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
                    ->orderBy('name', 'asc');
            }

            $categories = $query->select('id', 'name', 'parent_id')->limit(15)->get();

            foreach ($categories as $cat) {
                $results[] = [
                    'id' => $cat->id,
                    'text' => $cat->display_name,
                ];
            }
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Store a newly created offer.
     */
    public function store(Request $request)
    {
        if (!checkBusinessPermission('add_offers')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to add offers.'], 403);
        }

        $businessId = getBusinessId();

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:product,product_category',
            'item_id' => 'required|integer',
            'is_role_wise' => 'nullable|boolean',
            'price_type' => 'nullable|required_if:is_role_wise,0|in:fixed_price,discount,percentage',
            'value' => 'nullable|required_if:is_role_wise,0|numeric|min:0',
            'role_wise_detail' => 'nullable|array',
            'is_price_reflected_on_front' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $conflictMessage = $this->validateOfferConflicts($businessId, $request->type, $request->item_id);
        if ($conflictMessage) {
            return response()->json([
                'success' => false,
                'message' => $conflictMessage,
            ], 422);
        }

        $isRoleWise = $request->boolean('is_role_wise');
        $roleWiseDetail = null;

        if ($isRoleWise && $request->has('role_wise_detail')) {
            $roleWiseDetail = [];
            foreach ($request->input('role_wise_detail', []) as $roleId => $detail) {
                if (!empty($detail['price_type']) && isset($detail['value']) && $detail['value'] !== '') {
                    $roleWiseDetail[$roleId] = [
                        'price_type' => $detail['price_type'],
                        'value' => (float) $detail['value'],
                    ];
                }
            }
        }

        $offer = BusinessOffer::create([
            'business_id' => $businessId,
            'type' => $request->type,
            'item_id' => $request->item_id,
            'is_role_wise' => $isRoleWise,
            'role_wise_detail' => $roleWiseDetail,
            'price_type' => $isRoleWise ? 'discount' : $request->price_type,
            'value' => $isRoleWise ? null : $request->value,
            'is_price_reflected_on_front' => $request->boolean('is_price_reflected_on_front'),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Offer created successfully.',
            'data' => $offer,
        ]);
    }

    /**
     * Display specified offer.
     */
    public function show($id)
    {
        if (!checkBusinessPermission('view_offers') && !checkBusinessPermission('edit_offers')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to view offer details.'], 403);
        }

        $businessId = getBusinessId();
        $offer = BusinessOffer::where('business_id', $businessId)
            ->with(['product:id,name,sell_price,price', 'category.parent'])
            ->findOrFail($id);

        if ($offer->category) {
            $offer->category->append('display_name');
        }

        return response()->json(['success' => true, 'data' => $offer]);
    }

    /**
     * Update specified offer.
     */
    public function update(Request $request, $id)
    {
        if (!checkBusinessPermission('edit_offers')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to edit offers.'], 403);
        }

        $businessId = getBusinessId();
        $offer = BusinessOffer::where('business_id', $businessId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:product,product_category',
            'item_id' => 'required|integer',
            'is_role_wise' => 'nullable|boolean',
            'price_type' => 'nullable|required_if:is_role_wise,0|in:fixed_price,discount,percentage',
            'value' => 'nullable|required_if:is_role_wise,0|numeric|min:0',
            'role_wise_detail' => 'nullable|array',
            'is_price_reflected_on_front' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $conflictMessage = $this->validateOfferConflicts($businessId, $request->type, $request->item_id, $id);
        if ($conflictMessage) {
            return response()->json([
                'success' => false,
                'message' => $conflictMessage,
            ], 422);
        }

        $isRoleWise = $request->boolean('is_role_wise');
        $roleWiseDetail = null;

        if ($isRoleWise && $request->has('role_wise_detail')) {
            $roleWiseDetail = [];
            foreach ($request->input('role_wise_detail', []) as $roleId => $detail) {
                if (!empty($detail['price_type']) && isset($detail['value']) && $detail['value'] !== '') {
                    $roleWiseDetail[$roleId] = [
                        'price_type' => $detail['price_type'],
                        'value' => (float) $detail['value'],
                    ];
                }
            }
        }

        $offer->update([
            'type' => $request->type,
            'item_id' => $request->item_id,
            'is_role_wise' => $isRoleWise,
            'role_wise_detail' => $roleWiseDetail,
            'price_type' => $isRoleWise ? 'discount' : $request->price_type,
            'value' => $isRoleWise ? null : $request->value,
            'is_price_reflected_on_front' => $request->boolean('is_price_reflected_on_front'),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $offer->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Offer updated successfully.',
            'data' => $offer,
        ]);
    }

    /**
     * Toggle offer status.
     */
    public function toggleStatus($id)
    {
        if (!checkBusinessPermission('edit_offers')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to edit offers.'], 403);
        }

        $businessId = getBusinessId();
        $offer = BusinessOffer::where('business_id', $businessId)->findOrFail($id);

        $offer->update([
            'is_active' => !$offer->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Offer status updated to ' . ($offer->is_active ? 'Active' : 'Inactive') . '.',
            'data' => $offer,
        ]);
    }

    /**
     * Remove specified offer.
     */
    public function destroy($id)
    {
        if (!checkBusinessPermission('delete_offers')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to delete offers.'], 403);
        }

        $businessId = getBusinessId();
        $offer = BusinessOffer::where('business_id', $businessId)->findOrFail($id);
        $offer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Offer deleted successfully.',
        ]);
    }

    /**
     * Validate product and category offer conflicts.
     */
    private function validateOfferConflicts($businessId, $type, $itemId, $currentOfferId = null)
    {
        if ($type === 'product') {
            // 1. Check if offer already exists for this exact product
            $existingProductOffer = BusinessOffer::where('business_id', $businessId)
                ->where('type', 'product')
                ->where('item_id', $itemId)
                ->when($currentOfferId, function ($q) use ($currentOfferId) {
                    $q->where('id', '!=', $currentOfferId);
                })
                ->first();

            if ($existingProductOffer) {
                return 'An offer already exists for this product.';
            }

            // 2. Check if product's category or parent category already has a category-level offer
            $product = Product::where('business_id', $businessId)
                ->with('category.parent')
                ->find($itemId);

            if ($product && $product->category) {
                // 2a. Check if direct category has an active offer
                $categoryOffer = BusinessOffer::where('business_id', $businessId)
                    ->where('type', 'product_category')
                    ->where('item_id', $product->category_id)
                    ->when($currentOfferId, function ($q) use ($currentOfferId) {
                        $q->where('id', '!=', $currentOfferId);
                    })
                    ->with('category:id,name')
                    ->first();

                if ($categoryOffer) {
                    $categoryName = $categoryOffer->category->name ?? 'its category';
                    return "Cannot create offer for this product because its category ('{$categoryName}') already has an active offer.";
                }

                // 2b. Check if parent category has an active offer (if category is a sub-category)
                if ($product->category->parent_id) {
                    $parentCategoryOffer = BusinessOffer::where('business_id', $businessId)
                        ->where('type', 'product_category')
                        ->where('item_id', $product->category->parent_id)
                        ->when($currentOfferId, function ($q) use ($currentOfferId) {
                            $q->where('id', '!=', $currentOfferId);
                        })
                        ->with('category:id,name')
                        ->first();

                    if ($parentCategoryOffer) {
                        $parentName = $parentCategoryOffer->category->name ?? 'its parent category';
                        return "Cannot create offer for this product because its parent category ('{$parentName}') already has an active offer.";
                    }
                }
            }
        } elseif ($type === 'product_category') {
            // 1. Check if offer already exists for this exact category
            $existingCatOffer = BusinessOffer::where('business_id', $businessId)
                ->where('type', 'product_category')
                ->where('item_id', $itemId)
                ->when($currentOfferId, function ($q) use ($currentOfferId) {
                    $q->where('id', '!=', $currentOfferId);
                })
                ->first();

            if ($existingCatOffer) {
                return 'An offer already exists for this category.';
            }

            // 2. If this is a sub-category, check if its parent category has an active offer
            $category = Category::where('business_id', $businessId)
                ->with('parent')
                ->find($itemId);

            if ($category && $category->parent_id) {
                $parentOffer = BusinessOffer::where('business_id', $businessId)
                    ->where('type', 'product_category')
                    ->where('item_id', $category->parent_id)
                    ->when($currentOfferId, function ($q) use ($currentOfferId) {
                        $q->where('id', '!=', $currentOfferId);
                    })
                    ->with('category:id,name')
                    ->first();

                if ($parentOffer) {
                    $parentName = $parentOffer->category->name ?? 'its parent category';
                    return "Cannot create offer for this sub-category because its parent category ('{$parentName}') already has an active offer.";
                }
            }

            // 3. If this is a parent category, check if any sub-category under it has an active offer
            $subCategoryIds = Category::where('business_id', $businessId)
                ->where('parent_id', $itemId)
                ->pluck('id')
                ->toArray();

            if (!empty($subCategoryIds)) {
                $subCatOffer = BusinessOffer::where('business_id', $businessId)
                    ->where('type', 'product_category')
                    ->whereIn('item_id', $subCategoryIds)
                    ->when($currentOfferId, function ($q) use ($currentOfferId) {
                        $q->where('id', '!=', $currentOfferId);
                    })
                    ->with('category:id,name')
                    ->first();

                if ($subCatOffer) {
                    $subCatName = $subCatOffer->category->name ?? 'a sub-category';
                    return "Cannot create offer for this parent category because sub-category ('{$subCatName}') under it already has an active offer.";
                }
            }

            // 4. Check if any product inside this category (or its sub-categories) already has an individual offer
            $allCategoryIds = array_merge([$itemId], $subCategoryIds ?? []);
            $productIds = Product::where('business_id', $businessId)
                ->whereIn('category_id', $allCategoryIds)
                ->pluck('id')
                ->toArray();

            if (!empty($productIds)) {
                $productOffer = BusinessOffer::where('business_id', $businessId)
                    ->where('type', 'product')
                    ->whereIn('item_id', $productIds)
                    ->when($currentOfferId, function ($q) use ($currentOfferId) {
                        $q->where('id', '!=', $currentOfferId);
                    })
                    ->with('product:id,name')
                    ->first();

                if ($productOffer) {
                    $productName = $productOffer->product->name ?? 'a product';
                    return "Cannot create offer for this category because product ('{$productName}') under it already has an individual offer.";
                }
            }
        }

        return null;
    }
}
