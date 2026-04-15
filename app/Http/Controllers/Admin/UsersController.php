<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Controller;
use App\Models\LegalPage;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = User::select('id', 'first_name', 'last_name', 'email', 'contact', 'role', 'status', 'profile');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('img', function ($row) {

                    return '<div class="text-center"><img src="' . getImage($row->profile) . '" class="avatar-img rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" /></div>';
                })
                ->addColumn('action', function ($row) {
                    $url = route('admin.user.destroy', $row->id);
                    $url = "'" . $url . "'";
                    return ' <div class="text-center">
                    <a href="' . route('admin.user.edit', $row->id) . '" class="btn btn-outline-primary btn-sm" title="Edit"><i class="bi bi-pencil-square"></i></a>
                    <button onclick="destroy(' . $url . ', ' . $row->id . ')" class="btn btn-outline-danger btn-sm btn_delete-' . $row->id . '" title="Delete">
                        <i id="buttonText" class="bi bi-trash"></i>
                        <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                    </div>';
                })
                ->addColumn('status', function ($row) {
                    $status = $row->status == 'active' ? 'success' : 'danger';
                    return '<span class="badge bg-' . $status . '">' . ucfirst($row->status) . '</span>';
                })
                ->rawColumns(['action', 'img', 'status'])
                ->make(true);
        }
        return view('admin.user.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('admin.user.index');
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'profile' => 'required|mimes:jpg,jpeg,png,webp|',
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:users,email',
                'contact' => 'required|numeric|digits:10|unique:users,contact',
                'dob' => 'nullable|date',
                'gender' => 'nullable',
                'role' => 'required',
                'status' => 'required',
                'password' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
            } else {

                $insert = new User();

                $image_name = fileUploadStorage($request->file('profile'), 'user_images', 500, 500);
                $insert->profile = $image_name;

                $insert->first_name = $request->first_name;
                $insert->last_name = $request->last_name;
                $insert->email = $request->email;
                $insert->contact = $request->contact;
                $insert->dob = $request->dob;
                $insert->gender = $request->gender;
                $insert->role = $request->role;
                $insert->status = $request->status;
                $insert->password = Hash::make($request->password);
                $insert->save();

                $success = true;
                $message = 'User add successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
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
        $user = User::find($id);
        return view('admin.user.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('admin.user.index');
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'profile' => 'nullable|mimes:jpg,jpeg,png,webp|',
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:users,email,' . $id,
                'contact' => 'required|numeric|digits:10|unique:users,contact,' . $id,
                'dob' => 'nullable|date',
                'gender' => 'nullable',
                'role' => 'required',
                'status' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
            } else {

                $update = User::find($id);

                if ($request->hasFile('profile')) {
                    $oldimage = $update->profile;
                    $image_name = fileUploadStorage($request->file('profile'), 'user_images', 500, 500);
                    $update->profile = $image_name;
                }

                $update->first_name = $request->first_name;
                $update->last_name = $request->last_name;
                $update->email = $request->email;
                $update->contact = $request->contact;
                $update->dob = $request->dob;
                $update->gender = $request->gender;
                $update->role = $request->role;
                $update->status = $request->status;
                $update->save();

                // Remove old uploaded image if exist
                if (isset($oldimage)) {
                    fileRemoveStorage($oldimage);
                }

                $success = true;
                $message = 'User updated successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
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
        $redirect = route('admin.user.index');
        $data = array();

        try {
            DB::beginTransaction();
            $delete = User::find($id);
            if ($delete) {
                fileRemoveStorage($delete->profile);
                $delete->delete();

                $success = true;
                $message = 'User deleted successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }
    public function changePassword(Request $request, $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $data = array();
        $redirect = '';

        try {
            DB::beginTransaction();
            $rules = [
                'password' => 'required|min:6',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors()->first();
            } else {
                $user = User::find($id);
                if ($user) {
                    $user->password = Hash::make($request->password);
                    $user->save();
                    $success = true;
                    $message = 'Password changed successfully.';
                } else {
                    $message = 'User not found.';
                }
                
                if ($success) {
                    DB::commit();
                } else {
                    DB::rollBack();
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }
}
