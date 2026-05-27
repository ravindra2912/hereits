<?php

namespace App\Http\Controllers\Business;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use App\Models\BusinessSetting;

class ProductController extends Controller
{
    use \App\Traits\ManageListingLimits;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $business_id = getBusinessId();
        if (request()->ajax()) {
            $data = Product::with(['firstImage', 'category'])
                ->where('business_id', $business_id);

            if (request()->has('category_id') && !empty(request()->category_id)) {
                $data->where('category_id', request()->category_id);
            }

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('product_info', function ($row) {
                    $img = getImage($row->firstImage?->image_url);
                    return '
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle overflow-hidden border shadow-sm me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; min-width: 45px;">
                            <img src="' . $img . '" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div>
                            <div class="fw-bold text-dark">' . $row->name . '</div>
                            <div class="small text-muted">' . (Str::limit($row->sku, 30) ?: 'No SKU') . '</div>
                        </div>
                    </div>';
                })
                ->addColumn('category_info', function ($row) {
                    return '
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-1 me-2 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                            <i class="bi bi-tag small"></i>
                        </div>
                        <span class="text-dark">' . ($row->category?->name ?? 'Uncategorized') . '</span>
                    </div>';
                })
                ->addColumn('price_info', function ($row) {
                    if ($row->price_type == 'FixPrice') {
                        return '<div class="text-dark">₹' . number_format($row->price, 2) . '</div>';
                    } elseif ($row->price_type == 'PriceInRange') {
                        return '<div class="text-dark">₹' . number_format($row->min_price, 0) . ' - ₹' . number_format($row->max_price, 0) . '</div>';
                    } else {
                        return '<span class="text-muted small">Contact for Price</span>';
                    }
                })
                ->addColumn('status_info', function ($row) {
                    $class = $row->status == 'active' ? 'bg-success' : 'bg-danger';
                    $label = $row->status == 'active' ? 'Active' : 'Hidden';
                    return '<span class="badge rounded-pill ' . $class . ' px-3 py-1 small">' . $label . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    if (checkBusinessPermission('product', 'products', 'update') || checkBusinessPermission('product', 'products', 'view')) {
                        $btn .= '<a href="' . route('business.product.edit', $row->id) . '" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2">Edit</a>';
                    }
                    if (checkBusinessPermission('product', 'products', 'delete')) {
                        $btn .= '<button type="button" class="btn btn-light btn-sm rounded-pill px-2 border shadow-sm" onclick="deleteProduct(' . $row->id . ')" title="Delete Product"><i class="bi bi-trash text-danger"></i></button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['product_info', 'category_info', 'price_info', 'status_info', 'action'])
                ->make(true);
        }

        $businessSetting = BusinessSetting::where('business_id', $business_id)->first('is_product_import_export');
        $categories = getProductCategory();
        $totalProducts = Product::where('business_id', $business_id)->count();
        $limit = $this->getEffectiveLimit($business_id, 'product');

        return view('business.product.index', compact('categories', 'totalProducts', 'limit', 'businessSetting'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = getProductCategory();
        return view('business.product.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = route('business.product.index');
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'name' => 'required|string|max:255',
                'sku' => 'required|string|max:100',
                'category_id' => 'required|exists:categories,id',
                'quantity' => 'required|integer|min:0',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else if (Product::where('sku', $request->sku)->where('business_id', getBusinessId())->exists()) {
                $message = 'SKU already exists.';
            } else {
                $business_id = getBusinessId();
                $limitCheck = $this->checkListingLimit($business_id, Product::class, 'product');
                if ($limitCheck) {
                    return response()->json(['success' => false, 'message' => $limitCheck]);
                }

                $product = Product::create([
                    'business_id' => $business_id,
                    'category_id' => $request->category_id,
                    'name' => $request->name,
                    'sku' => $request->sku,
                    'quantity' => $request->quantity ?? 0,
                    'slug' => generateUniqueSlug(Product::class, $request->name, 'slug', $business_id),
                ]);

                $success = true;
                $message = 'Product created successfully.';
                $redirect = route('business.product.edit', $product->id);
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }

        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $product = Product::where('id', $id)->where('business_id', Auth::user()->business_id)->with(['images' => function ($query) {
            $query->orderBy('sort_order', 'asc');
        }])->firstOrFail();
        $categories = getProductCategory();
        $image_limit = config('const.product_images_upload_limit');
        $video_limit = config('const.product_videos_upload_limit');
        return view('business.product.edit', compact('product', 'categories', 'image_limit', 'video_limit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = route('business.product.index');
        $data = array();

        try {
            DB::beginTransaction();
            $business_id = getBusinessId();
            $product = Product::where('id', $id)->where('business_id', $business_id)->lockForUpdate()->firstOrFail();

            $rules = [
                'name' => 'required|string|max:255',
                'sku' => 'required|string|max:100',
                'category_id' => 'required|exists:categories,id',
                'description' => 'nullable|string',
                'price_type' => 'required|in:FixPrice,PriceInRange,WithoutPrice',
                'price' => 'nullable|numeric',
                'sell_price' => 'nullable|numeric',
                'status' => 'required|in:active,in-active',
            ];

            if ($request->price_type == 'FixPrice') {
                $rules['price'] = 'required|numeric|min:1';
                $rules['sell_price'] = 'required|numeric|min:1';
            } elseif ($request->price_type == 'PriceInRange') {
                $rules['min_price'] = 'required|numeric|min:1';
                $rules['max_price'] = 'required|numeric|min:1';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else if (Product::where('sku', $request->sku)->where('business_id', $business_id)->where('id', '!=', $id)->exists()) {
                $message = 'SKU already exists.';
            } else {

                if ($product->status == 'in-active' && $request->status == 'active') {
                    $limitCheck = $this->checkListingLimit($business_id, Product::class, 'product');
                    if ($limitCheck) {
                        return response()->json(['success' => false, 'message' => $limitCheck]);
                    }
                }

                $product->update([
                    'name' => $request->name,
                    'sku' => $request->sku,
                    'category_id' => $request->category_id,
                    'description' => $request->description,
                    'price_type' => $request->price_type,
                    'price' => $request->price ?? 0,
                    'sell_price' => $request->sell_price ?? 0,
                    'min_price' => $request->min_price ?? 0,
                    'max_price' => $request->max_price ?? 0,
                    'status' => $request->status,
                ]);

                $success = true;
                $message = 'Product updated successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }

        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $product = Product::where('id', $id)->where('business_id', Auth::user()->business_id)->lockForUpdate()->firstOrFail();

            // Delete images from storage
            foreach ($product->images as $image) {

                $exists = ProductImage::query()
                    ->where('product_id', '!=', $image->product_id)
                    ->where('image_url', $image->image_url)
                    ->exists();
                if (!$exists) {
                    fileRemoveStorage($image->image_url);
                }

                $image->delete();
            }

            $product->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Product deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteImage($id)
    {
        try {
            DB::beginTransaction();
            $image = ProductImage::findOrFail($id);
            // Check if product belongs to user's business
            $product = Product::where('id', $image->product_id)->where('business_id', Auth::user()->business_id)->exists();

            if ($product) {
                $exists = ProductImage::query()
                    ->where('product_id', '!=', $image->product_id)
                    ->where('image_url', $image->image_url)
                    ->exists();
                if (!$exists) {
                    fileRemoveStorage($image->image_url);
                }
                $image->delete();
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Image deleted successfully.']);
            }

            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeImage(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'video_url' => ['nullable', 'url', 'regex:/^(?:https?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/.*$/'],
        ]);

        try {
            DB::beginTransaction();
            $product = Product::where('id', $request->product_id)->where('business_id', Auth::user()->business_id)->lockForUpdate()->firstOrFail();

            $currentImagesCount = $product->images()->where('type', 'image')->count();
            $currentVideosCount = $product->images()->where('type', 'video')->count();

            $imageLimit = config('const.product_images_upload_limit');
            $videoLimit = config('const.product_videos_upload_limit');

            $uploadedImages = [];

            // Handle Images
            if ($request->hasFile('images')) {
                $newImagesCount = count($request->file('images'));
                if ($currentImagesCount + $newImagesCount > $imageLimit) {
                    return response()->json(['success' => false, 'message' => 'You can only upload ' . ($imageLimit - $currentImagesCount) . ' more image(s).'], 422);
                }

                foreach ($request->file('images') as $image) {
                    $path = fileUploadStorage($image, 'product', 900, 900);

                    $productImage = ProductImage::create([
                        'product_id' => $product->id,
                        'type' => 'image',
                        'image_url' => $path,
                    ]);

                    $uploadedImages[] = [
                        'id' => $productImage->id,
                        'url' => getImage($productImage->image_url),
                    ];
                }
            }

            // Handle Video URL
            if ($request->filled('video_url')) {
                if ($currentVideosCount + 1 > $videoLimit) {
                    return response()->json(['success' => false, 'message' => 'You can only add up to ' . $videoLimit . ' video links.'], 422);
                }

                $productImage = ProductImage::create([
                    'product_id' => $product->id,
                    'type' => 'video',
                    'image_url' => $request->video_url,
                ]);

                $uploadedImages[] = [
                    'id' => $productImage->id,
                    'url' => getYoutubeThumbnail($request->video_url) ?? getImage(null),
                    'is_video' => true
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Media uploaded successfully.',
                'images' => $uploadedImages,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function reorderImages(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:product_images,id',
        ]);

        try {
            DB::beginTransaction();
            foreach ($request->order as $index => $imageId) {
                ProductImage::where('id', $imageId)
                    ->whereHas('product', function ($query) {
                        $query->where('business_id', Auth::user()->business_id);
                    })
                    ->update(['sort_order' => $index + 1]);
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Images reordered successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function export()
    {
        return Excel::download(new ProductsExport, 'products-' . date('Y-m-d-His') . '.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            Excel::import(new ProductsImport, $request->file('file'));
            return response()->json(['success' => true, 'message' => 'Products imported successfully.', 'redirect' => route('business.product.index')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error importing products: ' . $e->getMessage()]);
        }
    }


    // =========== Inventoty start ====================

    public function inventory()
    {
        $business_id = getBusinessId();
        if (request()->ajax()) {
            $data = Product::select('id', 'name', 'sku', 'quantity', 'category_id')
                ->with(['firstImage', 'category'])
                ->where('business_id', $business_id);

            if (request()->has('category_id') && !empty(request()->category_id)) {
                $data->where('category_id', request()->category_id);
            }

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('product_info', function ($row) {
                    $img = getImage($row->firstImage?->image_url);
                    return '
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle overflow-hidden border shadow-sm me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; min-width: 45px;">
                            <img src="' . $img . '" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div>
                            <div class="fw-bold text-dark">' . $row->name . '</div>
                            <div class="small text-muted">' . (Str::limit($row->sku, 30) ?: 'No SKU') . '</div>
                        </div>
                    </div>';
                })
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="addQty(' . $row->id . ', \'' . addslashes($row->name) . '\')">Add Qty</button>';
                })
                ->rawColumns(['product_info', 'action'])
                ->make(true);
        }

        $categories = getProductCategory();
        return view('business.product.inventory', compact('categories'));
    }

    public function updateQuantity(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:products,id',
            'quantity' => 'required|integer',
        ]);

        try {
            $business_id = getBusinessId();
            $product = Product::where('id', $request->id)
                ->where('business_id', $business_id)
                ->firstOrFail();

            $product->increment('quantity', $request->quantity);

            return response()->json([
                'success' => true,
                'message' => 'Quantity updated successfully.',
                'new_quantity' => $product->quantity
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // =========== Inventoty end ====================
}
