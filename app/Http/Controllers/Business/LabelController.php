<?php

namespace App\Http\Controllers\Business;

use App\Models\Label;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Traits\CacheManagement;

class LabelController extends Controller
{
    use CacheManagement;
    public function index(Request $request)
    {
        if ($request->ajax()) {
            if ($request->has('all')) {
                $labels = Label::where('business_id', getBusinessId())
                    ->orderBy('short_order', 'asc')
                    ->get(['id', 'name', 'image']);

                foreach ($labels as $label) {
                    $label->image = getImage($label->image);
                }

                return response()->json(['success' => true, 'data' => $labels]);
            }
            $data = Label::where('business_id', getBusinessId())
                ->orderBy('short_order', 'asc')
                ->select('id', 'name', 'status', 'image', 'short_order');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('image', function ($row) {
                    return '<img src="' . getImage($row->image) . '" class="rounded-circle border shadow-sm" style="width: 70px; height: 70px; object-fit: cover;">';
                })
                ->addColumn('status', function ($row) {
                    $canEdit = checkBusinessPermission('edit_labels');
                    $checked = $row->status == 'active' ? 'checked' : '';
                    $disabled = $canEdit ? '' : 'disabled title="You do not have permission to edit labels"';
                    return '<div class="d-flex justify-content-center m-0">
                                <div class="form-check form-switch text-switch m-0 p-0">
                                    <input class="form-check-input status-toggle m-0" type="checkbox" role="switch" data-id="' . $row->id . '" data-on="Active" data-off="In-Active" ' . $checked . ' ' . $disabled . '>
                                </div>
                            </div>';
                })
                ->addColumn('action', function ($row) {
                    $url = route('business.label.destroy', $row->id);
                    $url = "'" . $url . "'";
                    $btn = '<div class="btn-group">';
                    if (checkBusinessPermission('edit_labels')) {
                        $btn .= '<button onclick="editLabel(' . $row->id . ')" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2">Edit</button>';
                    }
                    if (checkBusinessPermission('delete_labels')) {
                        $btn .= '<button onclick="destroy(' . $url . ', ' . $row->id . ')" class="btn btn-light btn-sm rounded-pill px-2 border shadow-sm btn_delete-' . $row->id . '" title="Delete">
                            <i id="buttonText" class="bi bi-trash text-danger"></i>
                            <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action', 'status', 'image'])
                ->make(true);
        }
        return view('business.label.index');
    }

    public function store(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.label.index');
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'name' => [
                    'required',
                    Rule::unique('labels')->where(function ($query) {
                        return $query->where('business_id', getBusinessId());
                    })
                ],
                'status' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                $insert = new Label();
                $insert->business_id = getBusinessId();
                $insert->name = $request->name;
                $insert->slug = Str::slug($request->name);
                $insert->status = $request->status;
                $insert->short_order = Label::where('business_id', getBusinessId())->max('short_order') + 1;

                if ($request->hasFile('image')) {
                    $newimg = fileUploadStorage($request->file('image'), 'label_images', 200, 200);
                    $insert->image = $newimg;
                }

                $insert->save();

                $success = true;
                $message = 'Label added successfully.';
                DB::commit();
                $this->clearBusinessProductCache(getBusinessId());
            }
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($newimg) && $newimg) {
                fileRemoveStorage($newimg);
            }
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function edit($id)
    {
        $label = Label::where('business_id', getBusinessId())->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $label,
            'image' => getImage($label->image)
        ]);
    }

    public function update(Request $request, $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.label.index');
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'name' => [
                    'required',
                    Rule::unique('labels')->where(function ($query) {
                        return $query->where('business_id', getBusinessId());
                    })->ignore($id)
                ],
                'status' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                $update = Label::where('business_id', getBusinessId())->findOrFail($id);
                $update->name = $request->name;
                $update->slug = Str::slug($request->name);
                $update->status = $request->status;

                if ($request->hasFile('image')) {
                    $oldimg = $update->image;
                    $newimg = fileUploadStorage($request->file('image'), 'label_images', 200, 200);
                    $update->image = $newimg;
                }

                $update->save();

                if (isset($oldimg) && $oldimg) {
                    $check = Label::where('id', '!=', $id)
                        ->where('image', $oldimg)
                        ->exists();

                    if (!$check) {
                        fileRemoveStorage($oldimg);
                    }
                }

                $success = true;
                $message = 'Label updated successfully.';
                DB::commit();
                $this->clearBusinessProductCache(getBusinessId());
            }
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($newimg) && $newimg) {
                fileRemoveStorage($newimg);
            }
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function destroy($id)
    {
        if (!checkBusinessPermission('delete_labels')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to delete labels.'], 403);
        }

        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.label.index');
        $data = array();

        try {
            DB::beginTransaction();
            $delete = Label::where('business_id', getBusinessId())->find($id);
            if ($delete) {
                if ($delete->hasLinkedProducts()) {
                    $linkedCount = $delete->linkedProductsCount();
                    $message = "This label cannot be deleted because {$linkedCount} product(s) are linked to it.";
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
                }

                if (isset($delete->image) && $delete->image) {
                    $check = Label::where('id', '!=', $id)
                        ->where('image', $delete->image)
                        ->exists();

                    if (!$check) {
                        fileRemoveStorage($delete->image);
                    }
                }
                $delete->delete();
                $success = true;
                $message = 'Label deleted successfully.';
                DB::commit();
                $this->clearBusinessProductCache(getBusinessId());
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function reorder(Request $request)
    {
        if (!checkBusinessPermission('edit_labels')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit labels.'], 403);
        }

        $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:labels,id',
        ]);

        try {
            DB::beginTransaction();
            foreach ($request->order as $index => $id) {
                Label::where('id', $id)
                    ->where('business_id', getBusinessId())
                    ->update(['short_order' => $index + 1]);
            }
            DB::commit();
            $this->clearBusinessProductCache(getBusinessId());
            return response()->json(['success' => true, 'message' => 'Labels reordered successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => checkResponse($e)], 500);
        }
    }
    public function statusToggle(Request $request)
    {
        if (!checkBusinessPermission('edit_labels')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit labels.'], 403);
        }

        try {
            $label = Label::where('id', $request->id)
                ->where('business_id', getBusinessId())
                ->firstOrFail();

            $label->status = $label->status == 'active' ? 'in-active' : 'active';
            $label->save();
            $this->clearBusinessProductCache(getBusinessId());

            return response()->json(['success' => true, 'message' => 'Status updated successfully.', 'status' => $label->status]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update status.']);
        }
    }
}
