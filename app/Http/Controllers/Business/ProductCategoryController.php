<?php

namespace App\Http\Controllers\Business;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            if ($request->has('all')) {
                $categories = Category::where('business_id', getBusinessId())
                    ->where('type', 'Products')
                    ->orderBy('sort_order', 'asc')
                    ->get(['id', 'name', 'image_url']);

                foreach ($categories as $cat) {
                    $cat->image_url = getImage($cat->image_url);
                }

                return response()->json(['success' => true, 'data' => $categories]);
            }
            $data = Category::where('business_id', getBusinessId())
                ->where('type', 'Products')
                ->orderBy('sort_order', 'asc')
                ->select('id', 'name', 'status', 'image_url', 'sort_order');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('image', function ($row) {
                    return '<img src="' . getImage($row->image_url) . '" class="rounded-circle border shadow-sm" style="width: 70px; height: 70px; object-fit: cover;">';
                })
                ->addColumn('status', function ($row) {
                    $class = $row->status == 'active' ? 'bg-success' : 'bg-danger';
                    return '<span class="badge rounded-pill ' . $class . ' px-3 py-1 small">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">';
                    if (checkBusinessPermission('product', 'categories', 'update') || checkBusinessPermission('product', 'categories', 'view')) {
                        $html .= '<button onclick="editCategory(' . $row->id . ')" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2">Edit</button>';
                    }
                    if (checkBusinessPermission('product', 'categories', 'delete')) {
                        $url = "'" . route('business.product-category.destroy', $row->id) . "'";
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
        return view('business.product-category.index');
    }

    public function create()
    {
        return view('business.product-category.create');
    }

    public function store(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.product-category.index');
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
                $insert->type = 'Products';
                $insert->status = $request->status;
                $insert->sort_order = Category::where('business_id', getBusinessId())->where('type', 'Products')->max('sort_order') + 1;

                if ($request->hasFile('image')) {
                    $newimg = fileUploadStorage($request->file('image'), 'category_images', 200, 200);
                    $insert->image_url = $newimg;
                }

                $insert->save();

                $success = true;
                $message = 'Product Category added successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($newimg) && $newimg) {
                fileRemoveStorage($newimg);
            }
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function edit($id)
    {
        $category = Category::where('business_id', getBusinessId())->where('type', 'Products')->findOrFail($id);
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
        $redirect = Route('business.product-category.index');
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
                $update = Category::where('business_id', getBusinessId())->where('type', 'Products')->findOrFail($id);
                $update->name = $request->name;
                $update->status = $request->status;

                if ($request->hasFile('image')) {
                    $oldimg = $update->image_url;
                    $newimg = fileUploadStorage($request->file('image'), 'category_images', 200, 200);
                    $update->image_url = $newimg;
                }

                $update->save();

                if (isset($oldimg) && $oldimg) {
                    fileRemoveStorage($oldimg);
                }

                $success = true;
                $message = 'Product Category updated successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($newimg) && $newimg) {
                fileRemoveStorage($newimg);
            }
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function destroy($id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = route('business.product-category.index');
        $data = array();

        try {
            $productCount = Product::where('category_id', $id)->count();
            if ($productCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete this category because ' . $productCount . ' product(s) are linked to it.',
                    'redirect' => $redirect
                ]);
            }

            DB::beginTransaction();
            $delete = Category::where('business_id', getBusinessId())->where('type', 'Products')->find($id);
            if ($delete) {
                $delete->delete();
                $success = true;
                $message = 'Product Category deleted successfully.';
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
                    ->where('type', 'Products')
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
