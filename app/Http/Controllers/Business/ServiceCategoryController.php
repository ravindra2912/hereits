<?php

namespace App\Http\Controllers\Business;

use App\Models\Category;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Traits\CacheManagement;

class ServiceCategoryController extends Controller
{
    use CacheManagement;
    public function index(Request $request)
    {
        if ($request->ajax()) {
            if ($request->has('all')) {
                $categories = Category::where('business_id', getBusinessId())
                    ->where('type', 'Services')
                    ->orderBy('sort_order', 'asc')
                    ->get(['id', 'name', 'image_url', 'parent_id']);

                foreach ($categories as $cat) {
                    $cat->image_url = getImage($cat->image_url);
                }

                return response()->json(['success' => true, 'data' => $categories]);
            }

            $query = Category::with('parent:id,name')
                ->where('business_id', getBusinessId())
                ->where('type', 'Services');

            if ($request->has('category') && !empty($request->category)) {
                $query->where('parent_id', $request->category);
            }

            $searchValue = trim($request->input('search.value', ''));
            if (!empty($searchValue)) {
                $query->where('name', 'like', '%' . $searchValue . '%');
            }

            if (!$request->has('order')) {
                $query->orderBy('sort_order', 'asc');
            }

            $data = $query->select('id', 'business_id', 'parent_id', 'name', 'status', 'image_url', 'sort_order');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('image', function ($row) {
                    return '<img src="' . getImage($row->image_url) . '" class="rounded-circle border shadow-sm" style="width: 70px; height: 70px; object-fit: cover;">';
                })
                ->editColumn('name', function ($row) use ($request) {
                    $url = route('business.service-category.index', ['category' => $row->id]);
                    return $row->parent ?  e($row->name)  : '<a href="' . $url . '" class="text-primary text-decoration-none fw-bold">' . e($row->name) . '</a>';
                })
                ->addColumn('parent_name', function ($row) {
                    return $row->parent ? '<span class="badge bg-light text-dark border fw-normal px-2 py-1">' . e($row->parent->name) . '</span>' : '<span class="text-muted small">-</span>';
                })
                ->editColumn('status', function ($row) {
                    $canEdit = checkBusinessPermission('edit_service_category');
                    $checked = $row->status == 'active' ? 'checked' : '';
                    $disabled = $canEdit ? '' : 'disabled title="You do not have permission to edit categories"';
                    return '<div class="d-flex justify-content-center m-0">
                                <div class="form-check form-switch text-switch m-0 p-0">
                                    <input class="form-check-input status-toggle m-0" type="checkbox" role="switch" data-id="' . $row->id . '" data-on="Active" data-off="In-Active" ' . $checked . ' ' . $disabled . '>
                                </div>
                            </div>';
                })
                ->addColumn('action', function ($row) {
                    $url = route('business.service-category.destroy', $row->id);
                    $url = "'" . $url . "'";
                    $btn = '<div class="btn-group">';
                    if (checkBusinessPermission('edit_service_category')) {
                        $btn .= '<button onclick="editCategory(' . $row->id . ')" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2">Edit</button>';
                    }
                    if (checkBusinessPermission('delete_service_category')) {
                        $btn .= '<button onclick="destroy(' . $url . ', ' . $row->id . ')" class="btn btn-light btn-sm rounded-pill px-2 border shadow-sm btn_delete-' . $row->id . '" title="Delete">
                            <i id="buttonText" class="bi bi-trash text-danger"></i>
                            <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action', 'status', 'image', 'parent_name', 'name'])
                ->make(true);
        }

        $currentCategory = null;
        if ($request->has('category') && !empty($request->category)) {
            $currentCategory = Category::where('business_id', getBusinessId())
                ->where('type', 'Services')
                ->find($request->category);
        }

        $parentCategories = Category::where('business_id', getBusinessId())
            ->where('type', 'Services')
            ->whereNull('parent_id')
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return view('business.service-category.index', compact('parentCategories', 'currentCategory'));
    }

    public function create()
    {
        if (!checkBusinessPermission('add_service_category')) {
            abort(403, 'Unauthorized. You do not have permission to add service categories.');
        }
        return view('business.service-category.create');
    }

    public function store(Request $request)
    {
        if (!checkBusinessPermission('add_service_category')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to add service categories.'], 403);
        }
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.service-category.index');
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'name' => [
                    'required',
                    Rule::unique('categories')->where(function ($query) {
                        return $query->where('business_id', getBusinessId());
                    })
                ],
                'parent_id' => 'nullable|exists:categories,id',
                'status' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                $insert = new Category();
                $insert->business_id = getBusinessId();
                $insert->parent_id = $request->parent_id ?: null;
                $insert->name = $request->name;
                $insert->slug = Str::slug($request->name);
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
                $this->clearBusinessServiceCache(getBusinessId());
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function edit($id)
    {
        if (!checkBusinessPermission('edit_service_category')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit service categories.'], 403);
        }
        $category = Category::where('business_id', getBusinessId())->where('type', 'Services')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $category,
            'image_url' => getImage($category->image_url)
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!checkBusinessPermission('edit_service_category')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit service categories.'], 403);
        }
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.service-category.index');
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'name' => [
                    'required',
                    Rule::unique('categories')->where(function ($query) {
                        return $query->where('business_id', getBusinessId());
                    })->ignore($id)
                ],
                'parent_id' => 'nullable|exists:categories,id',
                'status' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                $update = Category::where('business_id', getBusinessId())->where('type', 'Services')->findOrFail($id);
                $update->parent_id = ($request->parent_id && $request->parent_id != $id) ? $request->parent_id : null;
                $update->name = $request->name;
                $update->slug = Str::slug($request->name);
                $update->status = $request->status;

                if ($request->hasFile('image')) {
                    if (isset($update->image_url) && $update->image_url) {
                        $check = Category::where('business_id', getBusinessId())
                            ->where('type', 'Services')
                            ->where('id', '!=', $id)
                            ->where('image_url', $update->image_url)
                            ->exists();

                        if (!$check) {
                            fileRemoveStorage($update->image_url);
                        }
                    }
                    $update->image_url = fileUploadStorage($request->file('image'), 'category', 200, 200);
                }

                $update->save();

                $success = true;
                $message = 'Service Category updated successfully.';
                DB::commit();
                $this->clearBusinessServiceCache(getBusinessId());
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function destroy($id)
    {
        if (!checkBusinessPermission('delete_service_category')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to delete service categories.'], 403);
        }
        $success = false;
        $message = 'Something Wrong!';
        $redirect = route('business.service-category.index');
        $data = array();

        try {
            DB::beginTransaction();
            $delete = Category::where('business_id', getBusinessId())->where('type', 'Services')->find($id);
            if ($delete) {
                if ($delete->hasLinkedItems()) {
                    $linkedCount = $delete->linkedItemsCount();
                    $message = "This category cannot be deleted because {$linkedCount} {$delete->linkedItemsLabel()}(s) are linked to it.";
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
                }

                if (isset($delete->image_url) && $delete->image_url) {
                    $check = Category::where('business_id', getBusinessId())
                        ->where('type', 'Services')
                        ->where('id', '!=', $id)
                        ->where('image_url', $delete->image_url)
                        ->exists();

                    if (!$check) {
                        fileRemoveStorage($delete->image_url);
                    }
                }
                $delete->delete();
                $success = true;
                $message = 'Service Category deleted successfully.';
                DB::commit();
                $this->clearBusinessServiceCache(getBusinessId());
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function reorder(Request $request)
    {
        if (!checkBusinessPermission('edit_service_category')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to reorder service categories.'], 403);
        }
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
            $this->clearBusinessServiceCache(getBusinessId());
            return response()->json(['success' => true, 'message' => 'Categories reordered successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => checkResponse($e)], 500);
        }
    }
    public function statusToggle(Request $request)
    {
        if (!checkBusinessPermission('edit_service_category')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit service categories.'], 403);
        }
        try {
            $category = Category::where('id', $request->id)
                ->where('business_id', getBusinessId())
                ->where('type', 'Services')
                ->firstOrFail();

            $category->status = $category->status == 'active' ? 'in-active' : 'active';
            $category->save();
            $this->clearBusinessServiceCache(getBusinessId());

            return response()->json(['success' => true, 'message' => 'Status updated successfully.', 'status' => $category->status]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update status.']);
        }
    }
}
