<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductShareController extends Controller
{
    use \App\Traits\CacheManagement;

    private function refreshTargetBusinessCache($targetBusinessId)
    {
        if (!$targetBusinessId) {
            return;
        }

        $this->clearBusinessProductCache($targetBusinessId);
        $this->clearBusinessCategoryCache($targetBusinessId);

        if (function_exists('getBusinessSettings')) {
            getBusinessSettings($targetBusinessId, true);
        }
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $business_id = getBusinessId();

        if ($request->ajax()) {
            $data = \App\Models\BusinessProductShareSetting::with('targetBusiness')
                ->where('source_business_id', $business_id);

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('target_business', function ($row) {
                    if (!$row->targetBusiness) {
                        return 'N/A';
                    }
                    $img = getImage($row->targetBusiness->business_image);
                    $name = $row->targetBusiness->name;
                    $contact = $row->targetBusiness->contact;
                    $address = $row->targetBusiness->address ? $row->targetBusiness->address : 'No address provided';

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
                    $canEdit = checkBusinessPermission('edit_product_sharing');
                    $checked = $row->status == 'active' ? 'checked' : '';
                    $disabled = $canEdit ? '' : 'disabled title="You do not have permission to edit product sharing"';
                    return '<div class="d-flex justify-content-center m-0">
                                <div class="form-check form-switch text-switch m-0 p-0">
                                    <input class="form-check-input status-toggle m-0" type="checkbox" role="switch" data-id="' . $row->id . '" data-on="Active" data-off="In-Active" ' . $checked . ' ' . $disabled . '>
                                </div>
                            </div>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    if (checkBusinessPermission('edit_product_sharing')) {
                        $btn .= '<a href="' . route('business.product.share.manage', $row->id) . '" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2" title="Manage Products">Manage Products</a>';
                    }
                    if (checkBusinessPermission('delete_product_sharing')) {
                        $btn .= '<button type="button" class="btn btn-light btn-sm rounded-pill px-2 border shadow-sm" onclick="deleteShare(' . $row->id . ')" title="Remove Share"><i class="bi bi-trash text-danger"></i></button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['target_business', 'status_info', 'action'])
                ->make(true);
        }

        return view('business.product_share.index');
    }

    public function searchBusinesses(\Illuminate\Http\Request $request)
    {
        $search = $request->get('q');
        $business_id = getBusinessId();

        $businesses = \App\Models\Business::where('id', '!=', $business_id)
            ->where('status', 'active');

        if (!empty($search)) {
            $businesses->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('contact', 'like', "%{$search}%");
            });
        }

        $results = $businesses->limit(10)->get(['id', 'name', 'contact', 'business_image', 'address']);

        $formatted = [];
        foreach ($results as $business) {
            $formatted[] = [
                'id' => $business->id,
                'text' => $business->name, // For the selected value
                'name' => $business->name,
                'contact' => $business->contact,
                'address' => $business->address,
                'image' => getImage($business->business_image)
            ];
        }

        return response()->json($formatted);
    }

    public function store(\Illuminate\Http\Request $request)
    {
        if (!checkBusinessPermission('add_product_sharing')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to add product sharing.'], 403);
        }

        $request->validate([
            'target_business_id' => 'required|exists:businesses,id',
            'status' => 'required|in:active,inactive'
        ]);

        $source_id = getBusinessId();

        $exists = \App\Models\BusinessProductShareSetting::where('source_business_id', $source_id)
            ->where('target_business_id', $request->target_business_id)
            ->first();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Product share already exists for this business.']);
        }

        \App\Models\BusinessProductShareSetting::create([
            'source_business_id' => $source_id,
            'target_business_id' => $request->target_business_id,
            'status' => $request->status,
        ]);

        $this->refreshTargetBusinessCache($request->target_business_id);

        return response()->json(['success' => true, 'message' => 'Product share setting added successfully.', 'redirect' => route('business.product.share.index')]);
    }

    public function statusToggle(Request $request)
    {
        if (!checkBusinessPermission('edit_product_sharing')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit product sharing.'], 403);
        }

        try {
            $source_id = getBusinessId();
            $share = \App\Models\BusinessProductShareSetting::where('id', $request->id)
                ->where('source_business_id', $source_id)
                ->firstOrFail();

            $share->status = $request->status;
            $share->save();

            $this->refreshTargetBusinessCache($share->target_business_id);

            return response()->json(['success' => true, 'message' => 'Status updated successfully.', 'status' => $share->status]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update status.']);
        }
    }

    public function destroy($id)
    {
        if (!checkBusinessPermission('delete_product_sharing')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to delete product sharing.'], 403);
        }

        try {
            $source_id = getBusinessId();
            $share = \App\Models\BusinessProductShareSetting::where('id', $id)->where('source_business_id', $source_id)->firstOrFail();
            $target_business_id = $share->target_business_id;
            $share->delete();

            $this->refreshTargetBusinessCache($target_business_id);

            return response()->json(['success' => true, 'message' => 'Product share setting removed successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => checkResponse($e)], 500);
        }
    }

    public function manage($id)
    {
        if (!checkBusinessPermission('edit_product_sharing')) {
            abort(403, 'Unauthorized. You do not have permission to manage product sharing.');
        }

        $business_id = getBusinessId();
        $share = \App\Models\BusinessProductShareSetting::with('targetBusiness')->where('id', $id)->where('source_business_id', $business_id)->firstOrFail();
        $target_business_id = $share->target_business_id;

        // Shared and Copied products (in target business, with parent_product_business_id = current business)
        $target_products = \App\Models\Product::where('business_id', $target_business_id)
            ->where('parent_product_business_id', $business_id)
            ->whereNotNull('parent_product_id')
            ->get(['parent_product_id', 'share_type'])
            ->keyBy('parent_product_id');

        $shared_product_ids = $target_products->keys()->toArray();

        // Left list: Unshared products
        $unshared_products_list = \App\Models\Product::with('firstImage')
            ->where('business_id', $business_id)
            ->where('parent_product_id', null)
            ->where('parent_product_business_id', null)
            ->whereNotIn('id', $shared_product_ids)
            ->get();

        // Right list: Shared products (get from source to show their original names)
        $shared_products_list = \App\Models\Product::with('firstImage')
            ->where('business_id', $business_id)
            ->whereIn('id', $shared_product_ids)
            ->get();

        // Attach share_flag from target product to the source list items
        foreach ($shared_products_list as $prod) {
            $prod->share_flag = $target_products[$prod->id]->share_type ?? 'shared';
        }

        return view('business.product_share.manage', compact('share', 'unshared_products_list', 'shared_products_list'));
    }

    public function action(\Illuminate\Http\Request $request, $id)
    {
        if (!checkBusinessPermission('edit_product_sharing')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit product sharing.'], 403);
        }

        $business_id = getBusinessId();
        $share = \App\Models\BusinessProductShareSetting::where('id', $id)->where('source_business_id', $business_id)->firstOrFail();
        $target_business_id = $share->target_business_id;

        $action = $request->input('action_type'); // 'add', 'remove', 'add_all', 'remove_all'
        $product_ids = $request->input('product_ids', []); // Array of source product IDs
        $share_mode = $request->input('share_mode', 'share'); // 'share' or 'copy'

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            if ($action == 'add' || $action == 'add_all') {
                if ($action == 'add_all') {
                    $shared_products = \App\Models\Product::where('business_id', $target_business_id)
                        ->where('parent_product_business_id', $business_id)
                        ->whereNotNull('parent_product_id')
                        ->pluck('parent_product_id')
                        ->toArray();

                    $productsToShare = \App\Models\Product::where('business_id', $business_id)
                        ->whereNotIn('id', $shared_products)
                        ->get();
                } else {
                    $productsToShare = \App\Models\Product::where('business_id', $business_id)->whereIn('id', $product_ids)->get();
                }

                $skipped = [];
                foreach ($productsToShare as $product) {
                    // 1. Resolve Category for target business (only attach if category already exists in target business)
                    $targetCategoryId = null;
                    if ($product->category_id) {
                        $sourceCategory = \App\Models\Category::find($product->category_id);
                        if ($sourceCategory) {
                            $targetCategory = \App\Models\Category::where('business_id', $target_business_id)
                                ->where(function ($query) use ($sourceCategory) {
                                    if (!empty($sourceCategory->slug)) {
                                        $query->where('slug', $sourceCategory->slug);
                                    } else {
                                        $query->where('name', $sourceCategory->name);
                                    }
                                })
                                ->first();

                            if ($targetCategory) {
                                $targetCategoryId = $targetCategory->id;
                            }
                        }
                    }

                    // 2. Resolve Labels for target business
                    $targetLabelIds = [];
                    $sourceLabelIds = collect($product->label_id ?? [])
                        ->filter(fn($labelId) => !empty($labelId))
                        ->map(fn($labelId) => (int) $labelId)
                        ->unique()
                        ->values();

                    if ($sourceLabelIds->isNotEmpty()) {
                        $sourceLabels = \App\Models\Label::whereIn('id', $sourceLabelIds)->get()->keyBy('id');

                        foreach ($sourceLabelIds as $sourceLabelId) {
                            $sourceLabel = $sourceLabels->get($sourceLabelId);
                            if (!$sourceLabel) {
                                continue;
                            }

                            $targetLabel = \App\Models\Label::where('business_id', $target_business_id)
                                ->where('name', $sourceLabel->name)
                                ->where('slug', $sourceLabel->slug)
                                ->first();

                            if (!$targetLabel) {
                                $targetLabel = $sourceLabel->replicate();
                                $targetLabel->business_id = $target_business_id;
                                $targetLabel->save();
                            }

                            $targetLabelIds[] = $targetLabel->id;
                        }
                    }

                    // 3. Check for existing soft-deleted product in target business
                    $deletedProduct = \App\Models\Product::onlyTrashed()
                        ->where('business_id', $target_business_id)
                        ->where(function ($q) use ($product) {
                            $q->where('slug', $product->slug)
                                ->orWhere('parent_product_id', $product->id);
                        })
                        ->first();

                    if ($deletedProduct) {
                        // Restore soft-deleted product and update details
                        $deletedProduct->restore();

                        $deletedProduct->business_id = $target_business_id;
                        $deletedProduct->parent_product_id = $product->id;
                        $deletedProduct->parent_product_business_id = $business_id;
                        $deletedProduct->name = $product->name;
                        $deletedProduct->sku = $product->sku;
                        $deletedProduct->description = $product->description;
                        $deletedProduct->price_type = $product->price_type;
                        $deletedProduct->price = $product->price;
                        $deletedProduct->sell_price = $product->sell_price;
                        $deletedProduct->min_price = $product->min_price;
                        $deletedProduct->max_price = $product->max_price;
                        $deletedProduct->quantity = $product->quantity;
                        $deletedProduct->length = $product->length;
                        $deletedProduct->width = $product->width;
                        $deletedProduct->height = $product->height;
                        $deletedProduct->weight = $product->weight;
                        $deletedProduct->status = $product->status;
                        $deletedProduct->category_id = $targetCategoryId;
                        $deletedProduct->label_id = !empty($targetLabelIds) ? array_values(array_unique($targetLabelIds)) : null;

                        if ($share_mode === 'copy') {
                            $deletedProduct->share_type = 'copied';
                            $deletedProduct->slug = $product->slug;
                        } else {
                            $deletedProduct->share_type = 'shared';
                            $deletedProduct->slug = generateUniqueSlug(\App\Models\Product::class, $product->name, 'slug', $target_business_id);
                        }

                        $deletedProduct->save();
                        $targetProduct = $deletedProduct;

                        // Delete existing images to refresh from source
                        \App\Models\ProductImage::where('product_id', $targetProduct->id)->delete();
                    } else {
                        if ($share_mode === 'copy') {
                            // Check if an active product with the same slug exists in the target business
                            $existsSlug = \App\Models\Product::where('business_id', $target_business_id)
                                ->where('slug', $product->slug)
                                ->exists();
                            if ($existsSlug) {
                                $skipped[] = $product->name;
                                continue;
                            }
                        }

                        // Create copy of the product for the target business
                        $newProduct = $product->replicate();
                        $newProduct->business_id = $target_business_id;
                        $newProduct->parent_product_id = $product->id;
                        $newProduct->parent_product_business_id = $business_id;

                        if ($share_mode === 'copy') {
                            $newProduct->share_type = 'copied';
                            $newProduct->slug = $product->slug;
                        } else {
                            $newProduct->share_type = 'shared';
                            $newProduct->slug = generateUniqueSlug(\App\Models\Product::class, $product->name, 'slug', $target_business_id);
                        }

                        $newProduct->category_id = $targetCategoryId;
                        $newProduct->label_id = !empty($targetLabelIds) ? array_values(array_unique($targetLabelIds)) : null;
                        $newProduct->save();
                        $targetProduct = $newProduct;
                    }

                    // Copy the image database records
                    foreach ($product->images as $image) {
                        \App\Models\ProductImage::create([
                            'product_id' => $targetProduct->id,
                            'type' => $image->type ?? 'image',
                            'image_url' => $image->image_url,
                            'sort_order' => $image->sort_order,
                        ]);
                    }
                }

                if ($share_mode === 'copy') {
                    if (count($skipped) > 0) {
                        $message = 'Products copied. Note: ' . implode(', ', $skipped) . ' could not be copied because their slugs already exist in the target business.';
                    } else {
                        $message = 'Products copied successfully.';
                    }
                } else {
                    $message = 'Products shared successfully.';
                }
            } elseif ($action == 'remove' || $action == 'remove_all') {
                $query = \App\Models\Product::where('business_id', $target_business_id)
                    ->where('parent_product_business_id', $business_id)
                    ->whereNotNull('parent_product_id');

                if ($action == 'remove' && !empty($product_ids)) {
                    $query->whereIn('parent_product_id', $product_ids);
                }

                $targetProducts = $query->get();
                foreach ($targetProducts as $tp) {
                    if ($tp->share_type === 'copied') {
                        // For copied products, do not delete them. Only detach the parent link.
                        $tp->parent_product_id = null;
                        $tp->parent_product_business_id = null;
                        $tp->share_type = null;
                        $tp->save();
                    } else {
                        // For shared products, delete the product and its images.
                        $tp->images()->forceDelete();
                        $tp->Delete();
                    }
                }
                $message = 'Products removed from share successfully.';
            }

            \Illuminate\Support\Facades\DB::commit();

            $this->refreshTargetBusinessCache($target_business_id);

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => checkResponse($e)], 500);
        }
    }


    public function syncCategories(\Illuminate\Http\Request $request, $id)
    {
        if (!checkBusinessPermission('edit_product_sharing')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit product sharing.'], 403);
        }

        $business_id = getBusinessId();
        $share = \App\Models\BusinessProductShareSetting::where('id', $id)
            ->where('source_business_id', $business_id)
            ->firstOrFail();
        $target_business_id = $share->target_business_id;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $sourceCategories = \App\Models\Category::where('business_id', $business_id)->get();

            $categoryMap = [];

            // STEP 1: Add missing categories to target business or restore soft-deleted ones matching slug or name
            foreach ($sourceCategories as $sourceCat) {
                if (empty($sourceCat->slug)) {
                    $sourceCat->slug = \Illuminate\Support\Str::slug($sourceCat->name);
                    $sourceCat->save();
                }

                $catSlug = $sourceCat->slug;
                $catName = $sourceCat->name;

                $targetCat = \App\Models\Category::withTrashed()
                    ->where('business_id', $target_business_id)
                    ->where(function ($q) use ($catSlug, $catName) {
                        $q->where('slug', $catSlug);
                        if (!empty($catName)) {
                            $q->orWhere('name', $catName);
                        }
                    })
                    ->first();

                if ($targetCat) {
                    if ($targetCat->trashed()) {
                        $targetCat->restore();
                    }
                    $targetCat->name = $sourceCat->name;
                    $targetCat->slug = $catSlug;
                    $targetCat->image_url = $sourceCat->image_url;
                    $targetCat->type = $sourceCat->type;
                    $targetCat->sort_order = $sourceCat->sort_order;
                    $targetCat->show_in_home = $sourceCat->show_in_home;
                    $targetCat->show_in_home_with_items = $sourceCat->show_in_home_with_items;
                    $targetCat->status = 'active';
                    $targetCat->save();
                } else {
                    $targetCat = $sourceCat->replicate();
                    $targetCat->business_id = $target_business_id;
                    $targetCat->slug = $catSlug;
                    $targetCat->parent_id = null;
                    $targetCat->status = 'active';
                    $targetCat->save();
                }

                $categoryMap[$sourceCat->id] = $targetCat;
            }

            // STEP 2: Arrange parent and child category hierarchy in target business
            foreach ($sourceCategories as $sourceCat) {
                $targetCat = $categoryMap[$sourceCat->id] ?? null;
                if (!$targetCat) {
                    continue;
                }

                if ($sourceCat->parent_id) {
                    $targetParentCat = $categoryMap[$sourceCat->parent_id] ?? null;
                    $targetCat->parent_id = $targetParentCat ? $targetParentCat->id : null;
                } else {
                    $targetCat->parent_id = null;
                }

                $targetCat->save();
            }

            \Illuminate\Support\Facades\DB::commit();

            $this->refreshTargetBusinessCache($target_business_id);

            return response()->json(['success' => true, 'message' => 'Categories synchronized successfully. All categories updated in target business.']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => checkResponse($e)], 500);
        }
    }
}
