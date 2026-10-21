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
use App\Exports\ProductStockPriceExport;
use App\Imports\ProductStockPriceImport;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    use \App\Traits\CacheManagement;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $business_id = getBusinessId();
        if (request()->ajax()) {
            $data = Product::select('products.*')
                ->with(['firstImage:id,product_id,image_url', 'category:id,name'])
                ->where('products.business_id', $business_id);

            if (request()->has('category_id') && !empty(request()->category_id)) {
                $data->where('products.category_id', request()->category_id);
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
                            <div class="fw-bold text-dark">' . e($row->name) . '</div>
                            <div class="small text-muted">' . e(Str::limit($row->sku, 30) ?: 'No SKU') . '</div>
                        </div>
                    </div>';
                })
                ->addColumn('category_info', function ($row) {
                    return '
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-1 me-2 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                            <i class="bi bi-tag small"></i>
                        </div>
                        <span class="text-dark">' . e($row->category?->name ?? 'Uncategorized') . '</span>
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
                    $canEdit = checkBusinessPermission('edit_products');
                    $checked = $row->status == 'active' ? 'checked' : '';
                    $disabled = $canEdit ? '' : 'disabled title="You do not have permission to edit products"';
                    return '<div class="d-flex justify-content-center m-0">
                                <div class="form-check form-switch text-switch m-0 p-0">
                                    <input class="form-check-input status-toggle m-0" type="checkbox" role="switch" data-id="' . $row->id . '" data-on="Active" data-off="In-Active" ' . $checked . ' ' . $disabled . '>
                                </div>
                            </div>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    if ($row->parent_product_id && $row->share_type !== 'copied') {
                        // Shared product - show detail icon only
                        $btn .= '<button type="button" class="btn btn-outline-info btn-sm rounded-pill px-3 me-2" onclick="showProductDetail(' . $row->id . ')" title="View Details"><i class="bi bi-eye me-1"></i>Details</button>';
                    } else {
                        if (checkBusinessPermission('edit_products')) {
                            $btn .= '<a href="' . route('business.product.edit', $row->id) . '" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2">Edit</a>';
                        }
                        if (checkBusinessPermission('delete_products')) {
                            $btn .= '<button type="button" class="btn btn-light btn-sm rounded-pill px-2 border shadow-sm" onclick="deleteProduct(' . $row->id . ')" title="Delete Product"><i class="bi bi-trash text-danger"></i></button>';
                        }
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['product_info', 'category_info', 'price_info', 'status_info', 'action'])
                ->make(true);
        }

        $businessSetting = BusinessSetting::where('business_id', $business_id)->first(['is_product_import_export', 'is_update_stock_and_price_using_exel']);
        $categories = getProductCategory();
        $totalProducts = Product::where('business_id', $business_id)->count();

        return view('business.product.index', compact('categories', 'totalProducts', 'businessSetting'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = getProductCategory();
        $businessSetting = getBusinessSettings();
        $labels = collect();
        if ($businessSetting->is_label) {
            $labels = \App\Models\Label::where('business_id', getBusinessId())->where('status', 'active')->orderBy('short_order', 'asc')->get();
        }
        return view('business.product.create', compact('categories', 'labels', 'businessSetting'));
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
                'label_id' => 'nullable|array',
                'label_id.*' => 'exists:labels,id',
                'quantity' => 'required|integer|min:0'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else if (Product::where('sku', $request->sku)->where('business_id', getBusinessId())->exists()) {
                $message = 'SKU already exists.';
                $business_id = getBusinessId();

                $product = Product::create([
                    'business_id' => $business_id,
                    'category_id' => $request->category_id,
                    'label_id' => $request->label_id ? array_map('intval', (array)$request->label_id) : null,
                    'name' => $request->name,
                    'sku' => $request->sku,
                    'quantity' => $request->quantity ?? 0,
                    'length' => $request->length,
                    'width' => $request->width,
                    'height' => $request->height,
                    'weight' => $request->weight,
                    'slug' => generateUniqueSlug(Product::class, $request->name, 'slug', $business_id),
                ]);

                $success = true;
                $message = 'Product created successfully.';
                $redirect = route('business.product.edit', $product->id);
                DB::commit();
                $this->removeProductCashe();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = checkResponse($e);
        }

        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    /**
     * Return product details as JSON for the detail modal (shared products).
     */
    public function showDetail($id)
    {
        $business_id = getBusinessId();
        $product = Product::with(['firstImage', 'images', 'category'])
            ->where('id', $id)
            ->where('business_id', $business_id)
            ->whereNotNull('parent_product_id')
            ->where('share_type', '!=', 'copied')
            ->firstOrFail();

        $images = $product->images->where('type', 'image')->map(fn($img) => getImage($img->image_url));
        $firstImg = getImage($product->firstImage?->image_url);

        return response()->json([
            'success' => true,
            'product' => [
                'id'         => $product->id,
                'name'       => $product->name,
                'sku'        => $product->sku,
                'category'   => $product->category?->name,
                'label'      => $product->labelsList->pluck('name')->implode(', '),
                'price_type' => $product->price_type,
                'price'      => $product->price,
                'sell_price' => $product->sell_price,
                'min_price'  => $product->min_price,
                'max_price'  => $product->max_price,
                'quantity'   => $product->quantity,
                'length'     => $product->length,
                'width'      => $product->width,
                'height'     => $product->height,
                'weight'     => $product->weight,
                'status'     => $product->status,
                'description' => $product->description,
                'first_image' => $firstImg,
                'images'     => $images,
            ]
        ]);
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
        $businessSetting = getBusinessSettings();
        $labels = collect();
        if ($businessSetting->is_label) {
            $labels = \App\Models\Label::where('business_id', getBusinessId())->where('status', 'active')->orderBy('short_order', 'asc')->get();
        }
        $image_limit = config('const.product_images_upload_limit');
        return view('business.product.edit', compact('product', 'categories', 'labels', 'image_limit', 'businessSetting'));
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

            $businessSetting = getBusinessSettings($business_id);
            $dimensionRule = ($businessSetting && $businessSetting->show_delivery_charges) ? 'required|numeric|min:0' : 'nullable|numeric|min:0';

            $rules = [
                'name' => 'required|string|max:255',
                'sku' => 'required|string|max:100',
                'category_id' => 'required|exists:categories,id',
                'label_id' => 'nullable|array',
                'label_id.*' => 'exists:labels,id',
                'description' => 'nullable|string',
                'price_type' => 'required|in:FixPrice,PriceInRange,WithoutPrice',
                'price' => 'nullable|numeric',
                'sell_price' => 'required|numeric|min:1',
                'status' => 'required|in:active,in-active',
                'length' => $dimensionRule,
                'width' => $dimensionRule,
                'height' => $dimensionRule,
                'weight' => $dimensionRule,
            ];

            if ($request->price_type == 'FixPrice') {
                $rules['price'] = 'required|numeric|min:0||gt:sell_price';
            } elseif ($request->price_type == 'PriceInRange') {
                $rules['min_price'] = 'required|numeric|min:1';
                $rules['max_price'] = 'required|numeric|min:1||gt:min_price';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else if (Product::where('sku', $request->sku)->where('business_id', $business_id)->where('id', '!=', $id)->exists()) {
                $message = 'SKU already exists.';
            } else {

                $sellPrice = $request->filled('sell_price')
                    ? (float)$request->sell_price
                    : ($request->filled('price') ? (float)$request->price : (float)($product->sell_price ?? 0));

                $price = $request->filled('price')
                    ? (float)$request->price
                    : (float)($product->price ?? $sellPrice);

                $updateData = [
                    'name' => $request->name,
                    'sku' => $request->sku,
                    'description' => $request->description,
                    'price_type' => $request->price_type,
                    'price' => $price,
                    'sell_price' => $sellPrice,
                    'min_price' => $request->min_price ?? 0,
                    'max_price' => $request->max_price ?? 0,
                    'length' => $request->length,
                    'width' => $request->width,
                    'height' => $request->height,
                    'weight' => $request->weight,
                    'status' => $request->status,
                ];

                $product->update(array_merge($updateData, [
                    'category_id' => $request->category_id,
                    'label_id' => $request->label_id ? array_map('intval', (array)$request->label_id) : null,
                ]));

                // Update shared children products (excluding category and label)
                Product::where('parent_product_id', $product->id)->where('share_type', '!=', 'copied')->update($updateData);

                $success = true;
                $message = 'Product updated successfully.';
                DB::commit();
                $this->removeProductCashe($product->id);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = checkResponse($e);
        }

        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (!checkBusinessPermission('delete_products')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to delete products.'], 403);
        }

        try {
            DB::beginTransaction();
            $product = Product::where('id', $id)->where('business_id', Auth::user()->business_id)->lockForUpdate()->firstOrFail();

            // Delete images from storage
            foreach ($product->images as $image) {
                if (($image->type ?? 'image') !== 'youtube_url') {
                    $exists = ProductImage::query()
                        ->where('product_id', '!=', $image->product_id)
                        ->where('image_url', $image->image_url)
                        ->exists();
                    if (!$exists) {
                        fileRemoveStorage($image->image_url);
                    }
                }

                $image->delete();
            }

            $this->removeProductCashe($product->id);
            $product->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Product deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => checkResponse($e)], 500);
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
                // Remove this image from all shared child products first
                ProductImage::whereIn('product_id', function ($query) use ($image) {
                    $query->select('id')
                        ->from('products')
                        ->where('parent_product_id', $image->product_id)
                        ->where('share_type', '!=', 'copied');
                })->where('image_url', $image->image_url)->delete();

                if (($image->type ?? 'image') !== 'youtube_url') {
                    $exists = ProductImage::query()
                        ->where('product_id', '!=', $image->product_id)
                        ->where('image_url', $image->image_url)
                        ->exists();
                    if (!$exists) {
                        fileRemoveStorage($image->image_url);
                    }
                }
                $isYoutube = ($image->type ?? 'image') === 'youtube_url';
                $image->delete();
                DB::commit();
                $this->removeProductCashe($image->product_id);
                return response()->json(['success' => true, 'message' => ($isYoutube ? 'Video' : 'Image') . ' deleted successfully.']);
            }

            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => checkResponse($e)], 500);
        }
    }

    public function storeImage(Request $request)
    {
        $type = $request->input('type', 'image');

        if ($type === 'youtube_url') {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'youtube_url' => 'required|string|url|max:500',
            ]);

            $youtubeId = getYoutubeId($request->youtube_url);
            if (!$youtubeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid YouTube URL. Please provide a valid YouTube video link.',
                ], 422);
            }
        } else {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'images' => 'required|array',
                'images.*' => 'required|image|mimes:jpeg,png,jpg,webp',
            ]);
        }

        try {
            DB::beginTransaction();
            $product = Product::where('id', $request->product_id)->where('business_id', Auth::user()->business_id)->lockForUpdate()->firstOrFail();

            $currentCount = $product->images()->count();
            $limit = config('const.product_images_upload_limit');

            if ($currentCount >= $limit) {
                return response()->json(['success' => false, 'message' => 'You can only upload up to ' . $limit . ' images/videos.'], 422);
            }

            $uploadedImages = [];
            $sharedProducts = Product::where('parent_product_id', $product->id)->where('share_type', '!=', 'copied')->get();
            $maxSortOrder = (int) $product->images()->max('sort_order');

            if ($type === 'youtube_url') {
                $youtubeUrl = trim($request->youtube_url);
                $sortOrder = $maxSortOrder + 1;

                $productImage = ProductImage::create([
                    'product_id' => $product->id,
                    'type' => 'youtube_url',
                    'image_url' => $youtubeUrl,
                    'sort_order' => $sortOrder,
                ]);

                // Copy image to all shared child products
                foreach ($sharedProducts as $sharedProduct) {
                    ProductImage::create([
                        'product_id' => $sharedProduct->id,
                        'type' => 'youtube_url',
                        'image_url' => $youtubeUrl,
                        'sort_order' => $sortOrder,
                    ]);
                }

                $thumb = getYoutubeThumbnail($youtubeUrl) ?: getImage(null);
                $uploadedImages[] = [
                    'id' => $productImage->id,
                    'type' => 'youtube_url',
                    'url' => $thumb,
                    'image_url' => $productImage->image_url,
                ];
            } else {
                // Check if new images would exceed limit
                $newImagesCount = count($request->file('images'));
                if ($currentCount + $newImagesCount > $limit) {
                    return response()->json(['success' => false, 'message' => 'You can only upload ' . ($limit - $currentCount) . ' more image(s).'], 422);
                }

                foreach ($request->file('images') as $image) {
                    $path = fileUploadStorage($image, 'product', 900, 900);
                    $sortOrder = ++$maxSortOrder;

                    $productImage = ProductImage::create([
                        'product_id' => $product->id,
                        'type' => 'image',
                        'image_url' => $path,
                        'sort_order' => $sortOrder,
                    ]);

                    // Copy image to all shared child products
                    foreach ($sharedProducts as $sharedProduct) {
                        ProductImage::create([
                            'product_id' => $sharedProduct->id,
                            'type' => 'image',
                            'image_url' => $path,
                            'sort_order' => $sortOrder,
                        ]);
                    }

                    $uploadedImages[] = [
                        'id' => $productImage->id,
                        'type' => 'image',
                        'url' => getImage($productImage->image_url),
                        'image_url' => $productImage->image_url,
                    ];
                }
            }

            DB::commit();
            $this->removeProductCashe($product->id);
            return response()->json([
                'success' => true,
                'message' => ($type === 'youtube_url' ? 'YouTube video' : 'Images') . ' added successfully.',
                'images' => $uploadedImages,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => checkResponse($e)], 500);
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
            $order = $request->input('order', []);
            foreach ($order as $index => $imageId) {
                ProductImage::where('id', $imageId)
                    ->whereHas('product', function ($query) {
                        $query->where('business_id', Auth::user()->business_id);
                    })
                    ->update(['sort_order' => $index + 1]);
            }
            DB::commit();

            $firstImageId = $order[0] ?? null;
            $firstImage = $firstImageId ? ProductImage::find($firstImageId) : null;
            $this->removeProductCashe($firstImage ? $firstImage->product_id : null);
            return response()->json(['success' => true, 'message' => 'Images reordered successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => checkResponse($e)], 500);
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
            $this->removeProductCashe();
            return response()->json(['success' => true, 'message' => 'Products imported successfully.', 'redirect' => route('business.product.index')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => checkResponse($e, 'Error importing products.')]);
        }
    }

    public function exportStockPrice()
    {
        return Excel::download(new ProductStockPriceExport, 'product-stock-price-' . date('Y-m-d-His') . '.xlsx');
    }

    public function importStockPrice(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt|max:5000'
        ]);

        try {
            $import = new ProductStockPriceImport();
            Excel::import($import, $request->file('file'));

            $this->removeProductCashe();

            $downloadUrl = null;
            if ($import->notFoundCount > 0 || $import->unupdatedCount > 0 || !empty($import->reportRows)) {
                $output = fopen('php://temp', 'r+');
                fputcsv($output, ['sku', 'name', 'weight', 'price', 'quantity', 'status']);
                foreach ($import->reportRows as $r) {
                    fputcsv($output, [
                        $r['sku'] ?? '',
                        $r['name'] ?? '',
                        $r['weight'] ?? '',
                        $r['price'] ?? '',
                        $r['quantity'] ?? '',
                        $r['status'] ?? ''
                    ]);
                }
                rewind($output);
                $csvContent = stream_get_contents($output);
                fclose($output);

                $downloadUrl = 'data:text/csv;base64,' . base64_encode($csvContent);
            }

            $message = 'Import process completed. ' . $import->updatedCount . ' product(s) updated.';
            if ($import->notFoundCount > 0) {
                $message .= ' ' . $import->notFoundCount . ' sku(s) not found.';
            }
            if ($import->unupdatedCount > 0) {
                $message .= ' ' . $import->unupdatedCount . ' product(s) not updated.';
            }
            if ($import->notFoundCount > 0 || $import->unupdatedCount > 0 || !empty($import->reportRows)) {
                $message .= ' Report file downloaded.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'download_url' => $downloadUrl,
                'has_not_found' => ($import->notFoundCount > 0 || $import->unupdatedCount > 0 || !empty($import->reportRows)),
                'redirect' => route('business.product.index')
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => checkResponse($e, 'Error importing stock & price.')]);
        }
    }



    // =========== Inventoty start ====================

    public function inventory()
    {
        $business_id = getBusinessId();
        if (request()->ajax()) {
            $data = Product::select('products.id', 'products.name', 'products.sku', 'products.quantity', 'products.category_id')
                ->with(['firstImage:id,product_id,image_url', 'category:id,name'])
                ->where('products.business_id', $business_id);

            if (request()->has('category_id') && !empty(request()->category_id)) {
                $data->where('products.category_id', request()->category_id);
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
                            <div class="fw-bold text-dark">' . e($row->name) . '</div>
                            <div class="small text-muted">' . e(Str::limit($row->sku, 30) ?: 'No SKU') . '</div>
                        </div>
                    </div>';
                })
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="addQty(' . (int) $row->id . ', \'' . e(addslashes($row->name)) . '\')">Add Qty</button>';
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
                'message' => checkResponse($e)
            ], 500);
        }
    }

    // =========== Inventoty end ====================

    public function statusToggle(Request $request)
    {
        if (!checkBusinessPermission('edit_products')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit products.'], 403);
        }

        try {
            $product = Product::where('id', $request->id)
                ->where('business_id', Auth::user()->business_id)
                ->firstOrFail();

            if ($product->status == 'active') {
                $product->status = 'in-active';
            } else {
                $product->status = 'active';
            }

            $product->save();

            // Sync status to shared children
            Product::where('parent_product_id', $product->id)->where('share_type', '!=', 'copied')->update(['status' => $product->status]);

            $this->removeProductCashe($product->id);

            return response()->json(['success' => true, 'message' => 'Status updated successfully.', 'status' => $product->status]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update status.']);
        }
    }

    public function removeProductCashe($productId = null)
    {
        $id = getBusinessId();
        $this->clearBusinessProductCache($id);

        if ($productId) {
            $sharedBusinessIds = Product::where('parent_product_id', $productId)
                ->where('share_type', '!=', 'copied')
                ->pluck('business_id')
                ->unique()
                ->toArray();

            foreach ($sharedBusinessIds as $sharedId) {
                $this->clearBusinessProductCache($sharedId);
            }
        }
    }
}
