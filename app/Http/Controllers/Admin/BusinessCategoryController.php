<?php

namespace App\Http\Controllers\Admin;

use App\Models\Faq;
use Illuminate\Http\Request;
use App\Models\BusinessCategory;

use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class BusinessCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = BusinessCategory::query();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('img', function ($row) {
                    return '<div class="text-center"><img src="' . getImage($row->image) . '" class="avatar-img rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" /></div>';
                })
                ->addColumn('action', function ($row) {
                    $url = route('admin.businesscategory.destroy', $row->id);
                    $url = "'" . $url . "'";
                    return ' <div class="text-center">
                    <a href="' . route('admin.businesscategory.edit', $row->id) . '" class="btn btn-outline-primary btn-sm" title="Edit"><i class="bi bi-pencil-square"></i></a>
                    <button onclick="destroy(' . $url . ', ' . $row->id . ')" class="btn btn-outline-danger btn-sm btn_delete-' . $row->id . '" title="Delete">
                        <i id="buttonText" class="bi bi-trash"></i>
                        <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                    </div>';
                })
                ->rawColumns(['action', 'img'])
                ->make(true);
        }
        return view('admin.businesscategory.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.businesscategory.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('admin.businesscategory.index');
        $data = array();

        try {
            $rules = [
                'image' => 'nullable|mimes:jpg,jpeg,png,webp',
                'name' => 'required',
                'deduct_credit_per_customer_appointment' => 'required|numeric|min:0',
                'deduct_credit_per_self_appointment' => 'required|numeric|min:0',
                'deduct_credit_per_customer_order' => 'required|numeric|min:0',
                'deduct_credit_per_self_order' => 'required|numeric|min:0',
                'deduct_credit_per_chat' => 'required|numeric|min:0',
                'deduct_credit_per_quotation' => 'required|numeric|min:0',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
            } else {

                $insert = new BusinessCategory();

                if ($request->hasFile('image')) {
                    $image_name = fileUploadStorage($request->file('image'), 'business_category_images', 500, 500);
                    $insert->image = $image_name;
                }

                $insert->name = $request->name;
                $insert->slug = generateUniqueSlug(BusinessCategory::class, $request->name);
                $insert->deduct_credit_per_customer_appointment = $request->deduct_credit_per_customer_appointment;
                $insert->deduct_credit_per_self_appointment = $request->deduct_credit_per_self_appointment;
                $insert->deduct_credit_per_customer_order = $request->deduct_credit_per_customer_order;
                $insert->deduct_credit_per_self_order = $request->deduct_credit_per_self_order;
                $insert->deduct_credit_per_chat = $request->deduct_credit_per_chat;
                $insert->deduct_credit_per_quotation = $request->deduct_credit_per_quotation;
                $insert->save();

                Cache::forget('BusinessCategory');

                $success = true;
                $message = 'Business category add successfully.';
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if (isset($image_name) && !empty($image_name)) {
                fileRemoveStorage($image_name);
            }
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function edit(Request $request, $id)
    {
        $cat = BusinessCategory::find($id);
        return view('admin.businesscategory.edit', compact('cat'));
    }

    public function update(Request $request, $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('admin.businesscategory.index');
        $data = array();

        try {
            $rules = [
                'image' => 'nullable|mimes:jpg,jpeg,png,webp|',
                'name' => 'required',
                'deduct_credit_per_customer_appointment' => 'required|numeric|min:0',
                'deduct_credit_per_self_appointment' => 'required|numeric|min:0',
                'deduct_credit_per_customer_order' => 'required|numeric|min:0',
                'deduct_credit_per_self_order' => 'required|numeric|min:0',
                'deduct_credit_per_chat' => 'required|numeric|min:0',
                'deduct_credit_per_quotation' => 'required|numeric|min:0',
                'status' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
            } else {

                $update = BusinessCategory::find($id);

                if ($request->hasFile('image')) {
                    $oldimage = $update->image;
                    $image_name = fileUploadStorage($request->file('image'), 'business_category_images', 500, 500);
                    $update->image = $image_name;
                }

                $update->name = $request->name;
                $update->deduct_credit_per_customer_appointment = $request->deduct_credit_per_customer_appointment;
                $update->deduct_credit_per_self_appointment = $request->deduct_credit_per_self_appointment;
                $update->deduct_credit_per_customer_order = $request->deduct_credit_per_customer_order;
                $update->deduct_credit_per_self_order = $request->deduct_credit_per_self_order;
                $update->deduct_credit_per_chat = $request->deduct_credit_per_chat;
                $update->deduct_credit_per_quotation = $request->deduct_credit_per_quotation;
                $update->status = $request->status;
                $update->save();

                // Remove old uploaded image if exist
                if (isset($oldimage)) {
                    fileRemoveStorage($oldimage);
                }

                Cache::forget('BusinessCategory');

                $success = true;
                $message = 'Business category updated successfully.';
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if (isset($image_name) && !empty($image_name)) {
                fileRemoveStorage($image_name);
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
        $redirect = route('admin.businesscategory.index');
        $data = array();

        try {
            $delete = BusinessCategory::find($id);
            if ($delete) {
                if ($delete->businesses()->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This category cannot be deleted because there are businesses listed under it.',
                        'data' => $data,
                        'redirect' => $redirect
                    ]);
                }

                fileRemoveStorage($delete->image);
                $delete->delete();

                Cache::forget('BusinessCategory');

                $success = true;
                $message = 'Business category deleted successfully.';
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }
}
