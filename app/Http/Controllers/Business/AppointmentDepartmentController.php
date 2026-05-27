<?php

namespace App\Http\Controllers\Business;

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
use App\Models\AppointmentBooking;
use App\Models\AppointmentDepartment;
use App\Models\Expert;
use App\Models\User;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AppointmentDepartmentController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index(Request $request)
    {
        $businessSetting = getBusinessSettings();
        if ($request->ajax()) {
            $data = AppointmentDepartment::where('business_id', getBusinessId())->select('id', 'department_name');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<div class="text-center">';
                    if (checkBusinessPermission('appointments', 'department', 'update') || checkBusinessPermission('appointments', 'department', 'view')) {
                        $html .= '<a href="' . route('business.appointment.department.edit', $row->id) . '" class="btn btn-outline-primary btn-sm" title="Edit"><i class="bi bi-pencil"></i></a>';
                    }
                    if (checkBusinessPermission('appointments', 'department', 'delete')) {
                        $url = "'" . route('business.appointment.department.destroy', $row->id) . "'";
                        $html .= '<button onclick="destroy(' . $url . ', ' . $row->id . ')" class="btn btn-outline-danger btn-sm ms-1 btn_delete-' . $row->id . '" title="Delete">
                                    <i id="buttonText" class="bi bi-trash"></i>
                                    <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                  </button>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('business.appointment.department.index', compact('businessSetting'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('business.appointment.department.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.appointment.department.index');
        $data = array();

        try {
            $rules = [
                'department_name' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
            } else {
                $insert = new AppointmentDepartment();
                $insert->business_id  = Auth::user()->business_id;
                $insert->department_name = $request->department_name;
                $insert->save();

                $success = true;
                $message = 'Department Create successfully.';
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
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
        $department = AppointmentDepartment::find($id);
        return view('business.appointment.department.edit', compact('department'));
    }

    public function update(Request $request, $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.appointment.department.index');
        $data = array();

        try {
            $rules = [
                'department_name' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
            } else {
                $insert = AppointmentDepartment::find($id);
                $insert->department_name = $request->department_name;
                $insert->save();

                $success = true;
                $message = 'Department Update successfully.';
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
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
        $redirect = Route('business.appointment.department.index');
        $data = array();

        try {
            $checkExpert = Expert::where('department_id', $id)->where('business_id', getBusinessId())->get();
            if ($checkExpert && count($checkExpert) > 0) {
                $message = 'Please first delete experts which are in this department.';
            } else {
                $delete = AppointmentDepartment::find($id);
                if ($delete) {
                    // fileRemoveStorage($delete->profile);
                    $delete->delete();

                    $success = true;
                    $message = 'Department deleted successfully.';
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }
}
