<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Business;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BusinessController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Business::with(['owner', 'businessCategory'])
                ->select('id', 'owner_id', 'business_category_id', 'slug', 'name', 'business_image', 'business_logo', 'address', 'contact', 'status');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('owner', function ($row) {
                    return isset($row->owner) && !empty($row->owner->first_name) ? $row->owner->first_name : '';
                })
                ->addColumn('category', function ($row) {
                    return isset($row->businessCategory) && !empty($row->businessCategory->name) ? $row->businessCategory->name : '';
                    return $row->businessCategory->name;
                })
                ->addColumn('img', function ($row) {
                    return '<div class="text-center d-flex gap-2 justify-content-center">
                        <img src="' . getImage($row->business_logo) . '" class="avatar-img rounded-circle border" style="width: 40px; height: 40px; object-fit: cover;" title="Logo" />
                        <img src="' . getImage($row->business_image) . '" class="avatar-img rounded border" style="width: 40px; height: 40px; object-fit: cover;" title="Image" />
                    </div>';
                })
                ->addColumn('status', function ($row) {
                    return '<span class="badge bg-' . ($row->status == 'active' ? 'success' : 'warning') . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $url = route('admin.business.destroy', $row->id);
                    $url = "'" . $url . "'";
                    return ' <div class="text-center">
                    <a href="javascript:void(0)" class="btn btn-outline-info btn-sm show-business-info" data-id="' . $row->id . '" title="View Info"><i class="bi bi-eye"></i></a>
                    <a href="' . route('admin.business.edit', $row->id) . '" class="btn btn-outline-primary btn-sm" title="Edit"><i class="bi bi-pencil-square"></i></a>
                    <a href="' . route('business-details', ['business_slug' => $row->slug]) . '" target="_blank" class="btn btn-outline-success btn-sm" title="Redirect to business"><i class="bi bi-box-arrow-in-right"></i></a>
                    <!--button onclick="destroy(' . $url . ', ' . $row->id . ')" class="btn btn-outline-danger btn-sm btn_delete-' . $row->id . '" title="Delete">
                        <i id="buttonText" class="bi bi-trash"></i>
                        <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button -->
                    </div>';
                })
                ->rawColumns(['action', 'owner', 'img', 'status'])
                ->make(true);
        }
        return view('admin.business.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $businessCat = getBusinessCategory();
        return view('admin.business.create', compact('businessCat'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('admin.business.index');
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'business_image' => 'required|mimes:jpg,jpeg,png,webp|',
                'business_logo' => 'required|mimes:jpg,jpeg,png,webp|',
                'owner_id' => 'nullable|numeric|exists:users,id',
                'name' => 'required',
                'business_category_id' => 'required',
                'business_type' => 'required',
                'address' => 'required',
                'contact' => 'required|numeric|unique:businesses,contact',
                'state_id' => 'required|exists:states,id',
                'city_id' => 'required|exists:cities,id',
                'area' => 'required|string|max:30',
                'pincode' => 'required',
                'status' => 'required',
                'rating' => 'nullable|numeric|min:0|max:5',
                'user_referral_code' => 'nullable|exists:users,referral_code',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
            } else {
                $insert = new Business();

                $image_name = fileUploadStorage($request->file('business_image'), 'business_images', 800, 600);
                $insert->business_image = $image_name;

                $logo_name = fileUploadStorage($request->file('business_logo'), 'business_logos', 200, 200);
                $insert->business_logo = $logo_name;

                $insert->owner_id = $request->owner_id;
                $insert->name = $request->name;
                $insert->slug = generateUniqueSlug(Business::class, $request->name);
                $insert->business_category_id = $request->business_category_id;
                $insert->business_type = $request->business_type;
                $insert->address = $request->address;
                $insert->latitude = $request->latitude;
                $insert->longitude = $request->longitude;
                $insert->contact = $request->contact;
                $insert->state_id = $request->state_id;
                $insert->city_id = $request->city_id;
                $insert->area = $request->area;
                $insert->pincode = $request->pincode;
                $insert->status = $request->status;
                $insert->rating = $request->rating ?? 0;
                $insert->user_referral_code = $request->user_referral_code;
                $insert->save();

                //change user role to seller
                $user = User::select('id', 'role', 'business_id')->find($request->owner_id);
                if ($user && ($user->role != 2 || $user->business_id == null)) {
                    $user->business_id =  $insert->id;
                    $user->role = 2;
                    $user->save();
                }

                $success = true;
                $message = 'Business add successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
            if (isset($image_name) && !empty($image_name)) {
                fileRemoveStorage($image_name);
            }
            if (isset($logo_name) && !empty($logo_name)) {
                fileRemoveStorage($logo_name);
            }
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $business = Business::select(
            'id',
            'owner_id',
            'business_category_id',
            'name',
            'business_logo',
            'business_image',
            'business_type',
            'status',
            'contact',
            'address',
            'city_id',
            'state_id',
            'pincode',
            'rating',
            'user_referral_code'
        )->with([
            'owner' => function ($q) {
                $q->select('id', 'first_name', 'last_name', 'contact');
            },
            'businessCategory' => function ($q) {
                $q->select('id', 'name');
            },
            'businessSetting' => function ($q) {
                $q->select(
                    'id',
                    'business_id',
                    'credit',
                    'is_appointment_system',
                    'is_ecommerce_system',
                    'is_service_system'
                );
            },
            'state' => function ($q) {
                $q->select('id', 'name');
            },
            'city' => function ($q) {
                $q->select('id', 'name');
            }
        ])
            ->withCount(['products', 'services', 'bookings'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $business
        ]);
    }

    public function edit(Request $request, $id)
    {
        $business = Business::with([
            'purchases' => function ($q) {
                $q->with('transaction')->orderBy('id', 'desc');
            }
        ])->find($id);
        $businessCat = getBusinessCategory();
        $setting = getBusinessSettings($id);
        return view('admin.business.edit', compact('business', 'businessCat', 'setting'));
    }

    public function update(Request $request, $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('admin.business.index');
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'business_image' => 'nullable|mimes:jpg,jpeg,png,webp|',
                'business_logo' => 'nullable|mimes:jpg,jpeg,png,webp|',
                'owner_id' => 'nullable|numeric|exists:users,id',
                'name' => 'required',
                'business_category_id' => 'required',
                'business_type' => 'required',
                'address' => 'required',
                'contact' => 'required|numeric|unique:businesses,contact,' . $id,
                'state_id' => 'required|exists:states,id',
                'city_id' => 'required|exists:cities,id',
                'area' => 'required|string|max:30',
                'pincode' => 'required',
                'status' => 'required',
                'rating' => 'nullable|numeric|min:0|max:5',
                'user_referral_code' => 'nullable|exists:users,referral_code',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
            } else {

                $update = Business::find($id);

                if ($request->hasFile('business_image')) {
                    $oldimage = $update->business_image;
                    $image_name = fileUploadStorage($request->file('business_image'), 'business_images', 800, 600);
                    $update->business_image = $image_name;
                    if (isset($oldimage)) {
                        fileRemoveStorage($oldimage);
                    }
                }

                if ($request->hasFile('business_logo')) {
                    $oldlogo = $update->business_logo;
                    $logo_name = fileUploadStorage($request->file('business_logo'), 'business_logos', 200, 200);
                    $update->business_logo = $logo_name;
                    if (isset($oldlogo)) {
                        fileRemoveStorage($oldlogo);
                    }
                }

                if ($update->owner_id != $request->owner_id) {
                    //change user role to seller
                    $user = User::select('id', 'role', 'business_id')->find($request->owner_id);
                    if ($user && ($user->role != 2 || $user->business_id == null)) {
                        $user->business_id = $id;
                        $user->role = 2;
                        $user->save();
                    }
                }
                $update->owner_id = $request->owner_id;
                $update->name = $request->name;
                $update->business_category_id = $request->business_category_id;
                $update->business_type = $request->business_type;
                $update->address = $request->address;
                $update->latitude = $request->latitude;
                $update->longitude = $request->longitude;
                $update->contact = $request->contact;
                $update->state_id = $request->state_id;
                $update->city_id = $request->city_id;
                $update->area = $request->area;
                $update->pincode = $request->pincode;
                $update->status = $request->status;
                $update->rating = $request->rating ?? 0;
                $update->user_referral_code = $request->user_referral_code;
                $update->save();

                $success = true;
                $message = 'Business update successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
            if (isset($image_name) && !empty($image_name)) {
                fileRemoveStorage($image_name);
            }
            if (isset($logo_name) && !empty($logo_name)) {
                fileRemoveStorage($logo_name);
            }
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = route('admin.business.index');
        $data = array();

        try {
            DB::beginTransaction();
            $delete = Business::find($id);
            if ($delete) {
                fileRemoveStorage($delete->business_image);
                fileRemoveStorage($delete->business_logo);
                $delete->delete();

                $success = true;
                $message = 'Business deleted successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function systemSettingUpdate(Request $request, $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = '';
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                // 'business_image' => 'nullable|mimes:jpg,jpeg,png,webp|',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
            } else {

                $update = BusinessSetting::where('business_id', $id)->first();
                if (!$update) {
                    $update = new BusinessSetting();
                    $update->business_id = $id;
                }
                $update->is_appointment_system = $request->has('is_appointment_system') ? 1 : 0;
                $update->is_appointment_with_department = $request->has('is_appointment_with_department') ? 1 : 0;
                $update->is_appointment_price_required = $request->has('is_appointment_price_required') ? 1 : 0;
                $update->is_ecommerce_system = $request->has('is_ecommerce_system') ? 1 : 0;
                $update->is_product_import_export = $request->has('is_product_import_export') ? 1 : 0;
                $update->is_share_products_to_business = $request->has('is_share_products_to_business') ? 1 : 0;
                $update->is_service_system = $request->has('is_service_system') ? 1 : 0;
                $update->is_verified = $request->has('is_verified') ? 1 : 0;
                $update->is_pos_access = $request->has('is_pos_access') ? 1 : 0;
                $update->is_appointment_creadit_diduct_manual = $request->has('is_appointment_creadit_diduct_manual') ? 1 : 0;
                $update->is_order_creadit_diduct_manual = $request->has('is_order_creadit_diduct_manual') ? 1 : 0;
                $update->is_chat_creadit_diduct_manual = $request->has('is_chat_creadit_diduct_manual') ? 1 : 0;
                $update->is_quotation_creadit_diduct_manual = $request->has('is_quotation_creadit_diduct_manual') ? 1 : 0;

                $update->credit = $request->credit ?? 0;
                $update->deduct_credit_per_customer_appointment = $request->deduct_credit_per_customer_appointment ?? 0;
                $update->deduct_credit_per_self_appointment = $request->deduct_credit_per_self_appointment ?? 0;
                $update->deduct_credit_per_customer_order = $request->deduct_credit_per_customer_order ?? 0;
                $update->deduct_credit_per_self_order = $request->deduct_credit_per_self_order ?? 0;
                $update->deduct_credit_per_chat = $request->deduct_credit_per_chat ?? 0;
                $update->deduct_credit_per_quotation = $request->deduct_credit_per_quotation ?? 0;

                $update->visibility = $request->visibility ?? 'public';
                $update->save();

                $success = true;
                $message = 'Setting update successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function pendingBusinesses(Request $request)
    {
        if ($request->ajax()) {

            $data = Business::with(['owner', 'businessCategory'])
                ->select('id', 'owner_id', 'business_category_id', 'name', 'business_image', 'address', 'contact', 'status')
                ->where('status', 'pending');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('owner', function ($row) {
                    return isset($row->owner) && !empty($row->owner->first_name) ? $row->owner->first_name : '';
                })
                ->addColumn('category', function ($row) {
                    return isset($row->businessCategory) && !empty($row->businessCategory->name) ? $row->businessCategory->name : '';
                    return $row->businessCategory->name;
                })
                ->addColumn('img', function ($row) {
                    return '<div class="text-center"><img src="' . getImage($row->business_image) . '" class="avatar-img rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" /></div>';
                })
                ->addColumn('action', function ($row) {
                    $url = route('admin.business.destroy', $row->id);
                    return ' <div class="text-center d-flex gap-2 justify-content-center">
                    <a href="javascript:void(0)" class="btn btn-outline-info btn-sm show-business-info" data-id="' . $row->id . '" title="View Info"><i class="bi bi-eye"></i></a>
                    <button onclick="changeStatus(' . $row->id . ')" class="btn btn-success btn-sm btn_action-' . $row->id . '" title="Approve">
                        <p id="buttonText" class="m-0">Approve</p>
                        <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                    </div>';
                })
                ->rawColumns(['action', 'owner', 'img'])
                ->make(true);
        }
        return view('admin.business.pendingList');
    }

    public function changeBusinessStatus(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('admin.business.index');
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'business_id' => 'required|numeric|exists:businesses,id',
                'status' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                // $message = $validator->errors();
                $message = $validator->errors()->first();
            } else {

                $update = Business::find($request->business_id);
                $update->status = $request->status;
                $update->save();

                $success = true;
                $message = 'Business update successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }
}
