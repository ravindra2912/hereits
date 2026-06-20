<?php

namespace App\Http\Controllers\Business;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ServiceCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            if ($request->has('all')) {
                $categories = Category::where('business_id', getBusinessId())
                    ->where('type', 'Services')
                    ->orderBy('sort_order', 'asc')
                    ->get(['id', 'name', 'image_url']);

                foreach ($categories as $cat) {
                    $cat->image_url = getImage($cat->image_url);
                }

                return response()->json(['success' => true, 'data' => $categories]);
            }
            $data = Category::where('business_id', getBusinessId())
                ->where('type', 'Services')
                ->orderBy('sort_order', 'asc')
                ->select('id', 'name', 'status', 'image_url', 'sort_order');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('image', function ($row) {
                    return '<img src="' . getImage($row->image_url) . '" class="rounded-circle border shadow-sm" style="width: 70px; height: 70px; object-fit: cover;">';
                })
                ->addColumn('status', function ($row) {
                    return renderStatusControl(
                        route('business.service-category.status.update', $row->id),
                        $row->status,
                        $row->id,
                        checkBusinessPermission('service', 'categories', 'update'),
                        [
                            'active_label' => 'Active',
                            'inactive_label' => 'Inactive',
                        ]
                    );
                })
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">';
                    if (checkBusinessPermission('service', 'categories', 'update') || checkBusinessPermission('service', 'categories', 'view')) {
                        $html .= '<button onclick="editCategory(' . $row->id . ')" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2">Edit</button>';
                    }
                    if (checkBusinessPermission('service', 'categories', 'delete')) {
                        $url = "'" . route('business.service-category.destroy', $row->id) . "'";
                        $html .= '<button onclick="destroy(' . $url . ', ' . $row->id . ')" class="btn btn-light btn-sm rounded-pill px-2 border shadow-sm btn_delete-' . $row->id . '" title="Delete">
                                    <i id="buttonText" class="bi bi-trash text-danger"></i>
                                    <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                  </button>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['action', 'status', 'image'])
                ->make(true);
        }
        return view('business.service-category.index');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,in-active',
        ]);

        try {
            DB::beginTransaction();

            $category = Category::where('business_id', getBusinessId())
                ->where('type', 'Services')
                ->lockForUpdate()
                ->findOrFail($id);

            $category->status = $request->status;
            $category->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Service category status updated successfully.',
                'redirect' => route('business.service-category.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
        return view('business.service-category.create');
    }

    public function store(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.service-category.index');
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'name' => 'required',
                'status' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                $insert = new Category();
                $insert->business_id = getBusinessId();
                $insert->name = $request->name;
                $insert->type = 'Services';
                $insert->status = $request->status;
                $insert->sort_order = Category::where('business_id', getBusinessId())->where('type', 'Services')->max('sort_order') + 1;

                if ($request->hasFile('image')) {
                    $insert->image_url = fileUploadStorage($request->file('image'), 'category', 200, 200);
                }

                $insert->save();

                $success = true;
                $message = 'Service Category added successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function edit($id)
    {
        $category = Category::where('business_id', getBusinessId())->where('type', 'Services')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $category,
            'image_url' => getImage($category->image_url)
        ]);
    }

    public function update(Request $request, $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.service-category.index');
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'name' => 'required',
                'status' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                $update = Category::where('business_id', getBusinessId())->where('type', 'Services')->findOrFail($id);
                $update->name = $request->name;
                $update->status = $request->status;

                if ($request->hasFile('image')) {
                    if ($update->image_url) {
                        fileRemoveStorage($update->image_url);
                    }
                    $update->image_url = fileUploadStorage($request->file('image'), 'category', 200, 200);
                }

                $update->save();

                $success = true;
                $message = 'Service Category updated successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function destroy($id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = route('business.service-category.index');
        $data = array();

        try {
            $serviceCount = Service::where('category_id', $id)->count();
            if ($serviceCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete this category because ' . $serviceCount . ' service(s) are linked to it.',
                    'redirect' => $redirect
                ]);
            }

            DB::beginTransaction();
            $delete = Category::where('business_id', getBusinessId())->where('type', 'Services')->find($id);
            if ($delete) {
                $delete->delete();
                $success = true;
                $message = 'Service Category deleted successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:categories,id',
        ]);

        try {
            DB::beginTransaction();
            foreach ($request->order as $index => $id) {
                Category::where('id', $id)
                    ->where('business_id', getBusinessId())
                    ->where('type', 'Services')
                    ->update(['sort_order' => $index + 1]);
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Categories reordered successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
