<?php

namespace App\Http\Controllers\Business;

use App\Models\Service;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    use \App\Traits\ManageListingLimits;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $business_id = Auth::user()->business_id;
            $data = Service::with('category')->where('business_id', $business_id);

            if (request()->has('category_id') && !empty(request()->category_id)) {
                $data->where('category_id', request()->category_id);
            }

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('service_info', function ($row) {
                    $img = getImage($row->image_url);
                    return '
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle overflow-hidden border shadow-sm me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; min-width: 45px;">
                            <img src="' . $img . '" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div>
                            <div class="fw-bold text-dark">' . $row->name . '</div>
                            <div class="small text-muted">' . (Str::limit($row->description, 30) ?: 'No description') . '</div>
                        </div>
                    </div>';
                })
                ->addColumn('category_info', function ($row) {
                    return '
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-1 me-2 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                            <i class="bi bi-tag small"></i>
                        </div>
                        <span class="text-dark">' . ($row->category?->name ?? 'Uncategorized') . '</span>
                    </div>';
                })
                ->addColumn('price_info', function ($row) {
                    if ($row->price_type == 'FixPrice') {
                        return '<span class="text-dark">₹' . number_format($row->price, 2) . '</span>';
                    } elseif ($row->price_type == 'PriceInRange') {
                        return '<span class="text-dark">₹' . number_format($row->min_price, 0) . ' - ₹' . number_format($row->max_price, 0) . '</span>';
                    } else {
                        return '<span class="text-muted small">No Price</span>';
                    }
                })
                ->addColumn('status_info', function ($row) {
                    return renderStatusControl(
                        route('business.service.status.update', $row->id),
                        $row->status,
                        $row->id,
                        checkBusinessPermission('service', 'service_list', 'update'),
                        [
                            'active_label' => 'Active',
                            'inactive_label' => 'Inactive',
                        ]
                    );
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    if (checkBusinessPermission('service', 'service_list', 'update') || checkBusinessPermission('service', 'service_list', 'view')) {
                        $btn .= '<a href="' . route('business.service.edit', $row->id) . '" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2">Edit</a>';
                    }
                    if (checkBusinessPermission('service', 'service_list', 'delete')) {
                        $btn .= '<button type="button" class="btn btn-light btn-sm rounded-pill px-2 border shadow-sm" onclick="deleteService(' . $row->id . ')" title="Delete Service"><i class="bi bi-trash text-danger"></i></button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['service_info', 'category_info', 'price_info', 'status_info', 'action'])
                ->make(true);
        }
        $categories = getServiceCategory();
        $business_id = Auth::user()->business_id;
        $totalServices = Service::where('business_id', $business_id)->count();
        $limit = $this->getEffectiveLimit($business_id, 'service');

        return view('business.service.index', compact('categories', 'totalServices', 'limit'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,in-active',
        ]);

        try {
            DB::beginTransaction();

            $businessId = getBusinessId();
            $service = Service::where('id', $id)
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($service->status === 'in-active' && $request->status === 'active') {
                $limitCheck = $this->checkListingLimit($businessId, Service::class, 'service');

                if ($limitCheck) {
                    return response()->json([
                        'success' => false,
                        'message' => $limitCheck,
                    ], 422);
                }
            }

            $service->status = $request->status;
            $service->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Service status updated successfully.',
                'redirect' => route('business.service.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = getServiceCategory();
        return view('business.service.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = route('business.service.index');

        try {
            DB::beginTransaction();
            $rules = [
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
                'price_type' => 'required',
                'status' => 'required',
            ];

            if ($request->price_type == 'FixPrice') {
                $rules['price'] = 'required|numeric';
            } elseif ($request->price_type == 'PriceInRange') {
                $rules['min_price'] = 'required|numeric';
                $rules['max_price'] = 'required|numeric';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                $business_id = getBusinessId();
                $limitCheck = $this->checkListingLimit($business_id, Service::class, 'service');

                if ($limitCheck) {
                    return response()->json(['success' => false, 'message' => $limitCheck]);
                }

                $image_url = '';
                if ($request->hasFile('image')) {
                    $image_url = fileUploadStorage($request->file('image'), 'services', 600, 600);
                }

                Service::create([
                    'business_id' => Auth::user()->business_id,
                    'category_id' => $request->category_id,
                    'name' => $request->name,
                    'slug' => generateUniqueSlug(Service::class, $request->name, 'slug', Auth::user()->business_id),
                    'image_url' => $image_url,
                    'description' => $request->description,
                    'price_type' => $request->price_type,
                    'price' => $request->price,
                    'min_price' => $request->min_price,
                    'max_price' => $request->max_price,
                    'status' => $request->status,
                ]);

                $success = true;
                $message = 'Service created successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
            if (isset($image_url)) {
                fileRemoveStorage($image_url);
            }
        }

        return response()->json(['success' => $success, 'message' => $message, 'redirect' => $redirect]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $service = Service::where('id', $id)->where('business_id', Auth::user()->business_id)->firstOrFail();
        $categories = getServiceCategory();
        return view('business.service.edit', compact('service', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = route('business.service.index');

        try {
            DB::beginTransaction();
            $business_id = getBusinessId();
            $service = Service::where('id', $id)->where('business_id', $business_id)->lockForUpdate()->firstOrFail();

            $rules = [
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'price_type' => 'required',
                'status' => 'required',
            ];

            if ($request->price_type == 'FixPrice') {
                $rules['price'] = 'required|numeric';
            } elseif ($request->price_type == 'PriceInRange') {
                $rules['min_price'] = 'required|numeric';
                $rules['max_price'] = 'required|numeric';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                if ($request->hasFile('image')) {
                    $oldimg = $service->image_url;
                    $newimg = fileUploadStorage($request->file('image'), 'services', 600, 600);
                    $service->image_url = $newimg;
                }

                $service->name = $request->name;
                $service->category_id = $request->category_id;
                $service->slug = $request->slug ?? Str::slug($request->name);
                $service->description = $request->description;
                $service->price_type = $request->price_type;
                $service->price = $request->price;
                $service->min_price = $request->min_price;
                $service->max_price = $request->max_price;
                if ($service->status == 'in-active' && $request->status == 'active') {
                    $limitCheck = $this->checkListingLimit($business_id, Service::class, 'service');

                    if ($limitCheck) {
                        return response()->json(['success' => false, 'message' => $limitCheck]);
                    }
                    $service->status = $request->status;
                }

                $service->save();

                if (isset($oldimg)) {
                    fileRemoveStorage($oldimg);
                }

                $success = true;
                $message = 'Service updated successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
            if (isset($newimg)) {
                fileRemoveStorage($newimg);
            }
        }

        return response()->json(['success' => $success, 'message' => $message, 'redirect' => $redirect]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $service = Service::where('id', $id)->where('business_id', Auth::user()->business_id)->lockForUpdate()->firstOrFail();
            fileRemoveStorage($service->image_url);
            $service->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Service deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
