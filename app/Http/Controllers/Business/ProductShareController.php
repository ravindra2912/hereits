<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessProductShareSetting;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductShareController extends Controller
{
    public function index(Request $request)
    {
        $business_id = getBusinessId();

        if ($request->ajax()) {
            $data = BusinessProductShareSetting::query()
                ->select(['id', 'source_business_id', 'target_business_id', 'status', 'created_at'])
                ->with(['targetBusiness:id,name,business_image,contact,address'])
                ->where('source_business_id', $business_id);

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('target_business', function ($row) {
                    if (!$row->targetBusiness) {
                        return '<span class="text-muted">Business Not Found</span>';
                    }
                    $img = getImage($row->targetBusiness->business_image);
                    $name = e($row->targetBusiness->name);
                    $contact = e($row->targetBusiness->contact ?? 'N/A');
                    $address = e($row->targetBusiness->address ?: 'No address provided');

                    return '
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle overflow-hidden border shadow-sm me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; min-width: 45px;">
                            <img src="' . $img . '" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div>
                            <div class="fw-bold text-dark">' . $name . ' <span class="badge bg-light text-dark ms-1 border">' . $contact . '</span></div>
                            <div class="small text-muted text-truncate" style="max-width: 300px;">' . $address . '</div>
                        </div>
                    </div>';
                })
                ->addColumn('status_info', function ($row) {
                    $checked = $row->status === 'active' ? 'checked' : '';
                    return '<div class="d-flex justify-content-center m-0">
                                <div class="form-check form-switch text-switch m-0 p-0">
                                    <input class="form-check-input status-toggle m-0" type="checkbox" role="switch" data-id="' . $row->id . '" data-on="Active" data-off="In-Active" ' . $checked . '>
                                </div>
                            </div>';
                })
                ->addColumn('action', function ($row) {
                    $manageUrl = route('business.product.share.manage', $row->id);
                    return '<div class="btn-group">
                                <a href="' . $manageUrl . '" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2" title="Manage Products">
                                    <i class="bi bi-sliders me-1"></i> Manage Products
                                </a>
                                <button type="button" class="btn btn-light btn-sm rounded-pill px-2 border shadow-sm" onclick="deleteShare(' . $row->id . ')" title="Remove Share">
                                    <i class="bi bi-trash text-danger"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['target_business', 'status_info', 'action'])
                ->make(true);
        }

        return view('business.product_share.index');
    }

    public function searchBusinesses(Request $request): JsonResponse
    {
        $search = trim($request->get('q', ''));
        $business_id = getBusinessId();

        // Get target businesses already configured
        $existingTargetIds = BusinessProductShareSetting::where('source_business_id', $business_id)
            ->pluck('target_business_id')
            ->toArray();

        $businesses = Business::query()
            ->where('id', '!=', $business_id)
            ->where('status', 'active')
            ->whereNotIn('id', $existingTargetIds);

        if (!empty($search)) {
            $businesses->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('contact', 'like', "%{$search}%");
            });
        }

        $results = $businesses->limit(15)->get(['id', 'name', 'contact', 'business_image', 'address']);

        $formatted = $results->map(function ($business) {
            return [
                'id' => $business->id,
                'text' => $business->name,
                'name' => $business->name,
                'contact' => $business->contact,
                'address' => $business->address,
                'image' => getImage($business->business_image)
            ];
        });

        return response()->json($formatted);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'target_business_id' => 'required|exists:businesses,id',
            'status' => 'required|in:active,inactive'
        ]);

        $source_id = getBusinessId();

        if ((int) $request->target_business_id === (int) $source_id) {
            return response()->json(['success' => false, 'message' => 'You cannot share products with your own business.']);
        }

        $exists = BusinessProductShareSetting::where('source_business_id', $source_id)
            ->where('target_business_id', $request->target_business_id)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Product share setting already exists for this business.']);
        }

        BusinessProductShareSetting::create([
            'source_business_id' => $source_id,
            'target_business_id' => $request->target_business_id,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product share setting added successfully.',
            'redirect' => route('business.product.share.index')
        ]);
    }

    public function destroy($id): JsonResponse
    {
        try {
            $source_id = getBusinessId();
            $share = BusinessProductShareSetting::where('id', $id)
                ->where('source_business_id', $source_id)
                ->firstOrFail();

            $share->delete();

            return response()->json(['success' => true, 'message' => 'Product share setting removed successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to remove share setting.'], 500);
        }
    }

    public function manage($id)
    {
        $business_id = getBusinessId();
        $share = BusinessProductShareSetting::with('targetBusiness')
            ->where('id', $id)
            ->where('source_business_id', $business_id)
            ->firstOrFail();

        $target_business_id = $share->target_business_id;

        // Fetch shared products in target business mapped to source business
        $target_products = Product::query()
            ->select(['id', 'parent_product_id', 'share_type'])
            ->where('business_id', $target_business_id)
            ->where('parent_product_business_id', $business_id)
            ->whereNotNull('parent_product_id')
            ->get()
            ->keyBy('parent_product_id');

        $shared_product_ids = $target_products->keys()->toArray();

        // Left list: Available original products (not yet shared/copied to this target)
        $unshared_products_list = Product::with('firstImage')
            ->where('business_id', $business_id)
            ->whereNull('parent_product_id')
            ->whereNotIn('id', $shared_product_ids)
            ->get();

        // Right list: Currently shared/copied products from source
        $shared_products_list = Product::with('firstImage')
            ->where('business_id', $business_id)
            ->whereIn('id', $shared_product_ids)
            ->get();

        // Attach share_flag from target product to the source list items
        foreach ($shared_products_list as $prod) {
            $prod->share_flag = $target_products[$prod->id]->share_type ?? 'shared';
        }

        return view('business.product_share.manage', compact('share', 'unshared_products_list', 'shared_products_list'));
    }

    public function action(Request $request, $id): JsonResponse
    {
        $request->validate([
            'action_type' => 'required|in:add,add_all,remove,remove_all',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer',
            'share_mode' => 'nullable|in:share,copy',
        ]);

        $business_id = getBusinessId();
        $share = BusinessProductShareSetting::where('id', $id)
            ->where('source_business_id', $business_id)
            ->firstOrFail();

        $target_business_id = $share->target_business_id;
        $action = $request->input('action_type');
        $product_ids = $request->input('product_ids', []);
        $share_mode = $request->input('share_mode', 'share');

        DB::beginTransaction();
        try {
            if ($action === 'add' || $action === 'add_all') {
                if ($action === 'add_all') {
                    $alreadySharedIds = Product::where('business_id', $target_business_id)
                        ->where('parent_product_business_id', $business_id)
                        ->whereNotNull('parent_product_id')
                        ->pluck('parent_product_id')
                        ->toArray();

                    $productsToShare = Product::with(['images', 'category'])
                        ->where('business_id', $business_id)
                        ->whereNull('parent_product_id')
                        ->whereNotIn('id', $alreadySharedIds)
                        ->get();
                } else {
                    $productsToShare = Product::with(['images', 'category'])
                        ->where('business_id', $business_id)
                        ->whereIn('id', $product_ids)
                        ->get();
                }

                foreach ($productsToShare as $product) {
                    // 1. Resolve Category for target business
                    $targetCategoryId = null;
                    if ($product->category_id && $product->category) {
                        $sourceCategory = $product->category;
                        $targetCategory = Category::where('business_id', $target_business_id)
                            ->where('name', $sourceCategory->name)
                            ->where('type', $sourceCategory->type)
                            ->first();

                        if (!$targetCategory) {
                            $targetCategory = Category::create([
                                'business_id' => $target_business_id,
                                'name' => $sourceCategory->name,
                                'image_url' => $sourceCategory->image_url,
                                'type' => $sourceCategory->type,
                                'status' => $sourceCategory->status ?? 'active',
                                'sort_order' => $sourceCategory->sort_order ?? 0,
                            ]);
                        }
                        $targetCategoryId = $targetCategory->id;
                    }

                    // 2. Check if already exists in target business
                    $targetProduct = Product::where('business_id', $target_business_id)
                        ->where('parent_product_id', $product->id)
                        ->where('parent_product_business_id', $business_id)
                        ->first();

                    $shareTypeVal = ($share_mode === 'copy') ? 'copied' : 'shared';

                    if ($targetProduct) {
                        // Update existing shared product
                        $targetProduct->update([
                            'category_id' => $targetCategoryId,
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'description' => $product->description,
                            'price_type' => $product->price_type,
                            'price' => $product->price,
                            'sell_price' => $product->sell_price,
                            'min_price' => $product->min_price,
                            'max_price' => $product->max_price,
                            'quantity' => $product->quantity,
                            'status' => $product->status,
                            'share_type' => $shareTypeVal,
                        ]);
                    } else {
                        $uniqueSlug = generateUniqueSlug(Product::class, $product->name, 'slug', $target_business_id);

                        $targetProduct = Product::create([
                            'business_id' => $target_business_id,
                            'parent_product_id' => $product->id,
                            'parent_product_business_id' => $business_id,
                            'share_type' => $shareTypeVal,
                            'category_id' => $targetCategoryId,
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'slug' => $uniqueSlug,
                            'description' => $product->description,
                            'price_type' => $product->price_type,
                            'price' => $product->price,
                            'sell_price' => $product->sell_price,
                            'min_price' => $product->min_price,
                            'max_price' => $product->max_price,
                            'quantity' => $product->quantity,
                            'status' => $product->status,
                        ]);
                    }

                    // 3. Copy image database records
                    ProductImage::where('product_id', $targetProduct->id)->delete();
                    foreach ($product->images as $image) {
                        ProductImage::create([
                            'product_id' => $targetProduct->id,
                            'type' => $image->type ?? 'image',
                            'image_url' => $image->image_url,
                            'sort_order' => $image->sort_order ?? 0,
                        ]);
                    }
                }

                $message = ($share_mode === 'copy')
                    ? 'Products copied successfully.'
                    : 'Products shared successfully.';
            } elseif ($action === 'remove' || $action === 'remove_all') {
                $query = Product::where('business_id', $target_business_id)
                    ->where('parent_product_business_id', $business_id)
                    ->whereNotNull('parent_product_id');

                if ($action === 'remove' && !empty($product_ids)) {
                    $query->whereIn('parent_product_id', $product_ids);
                }

                $targetProducts = $query->get();
                foreach ($targetProducts as $tp) {
                    if ($tp->share_type === 'copied') {
                        // For copied products, detach parent link and keep independent product
                        $tp->update([
                            'parent_product_id' => null,
                            'parent_product_business_id' => null,
                            'share_type' => null,
                        ]);
                    } else {
                        // For shared products, delete images and remove product
                        $tp->images()->delete();
                        $tp->delete();
                    }
                }

                $message = 'Products removed from share successfully.';
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product Share Action Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function statusToggle(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        try {
            $share = BusinessProductShareSetting::where('id', $request->id)
                ->where('source_business_id', getBusinessId())
                ->firstOrFail();

            $share->status = ($share->status === 'active') ? 'inactive' : 'active';
            $share->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'status' => $share->status
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update status.'], 500);
        }
    }
}
