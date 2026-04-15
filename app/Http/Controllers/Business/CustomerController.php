<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Models\User;

class CustomerController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index(Request $request)
    {
        $businessSetting = getBusinessSettings();
        $businessId = getBusinessId();
        if ($request->ajax()) {
            $data = User::where('referrer_business_id', $businessId);

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('img', function ($row) {
                    return '<div class="text-center"><img src="' . getImage($row->profile) . '" class="rounded" style="width: 40px; height: 40px; object-fit: cover;" /></div>';
                })
                ->addColumn('action', function ($row) {
                    return '<div class="text-center">
                        <button onclick="getUserDetails(' . $row->id . ')" class="btn btn-outline-primary btn-sm rounded-circle shadow-sm" title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>';
                })
                ->rawColumns(['img', 'action'])
                ->make(true);
        }

        return view('business.appointment.customer.index', compact('businessSetting'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $businessId = getBusinessId();
        $user = User::withCount([
            'appointments as completed_appointments' => function ($query) use ($businessId) {
                $query->where('business_id', $businessId)->where('status', 'completed');
            },
            'appointments as uncompleted_appointments' => function ($query) use ($businessId) {
                $query->where('business_id', $businessId)->whereNotIn('status', ['completed', 'auto_cancelled', 'cancel']);
            }
        ])->findOrFail($id);

        $businessSetting = getBusinessSettings();

        $html = view('business.appointment.customer.details_modal', compact('user', 'businessSetting'))->render();
        return response()->json(['success' => true, 'html' => $html]);
    }

    public function edit(Request $request, $id) {}

    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {}
}
