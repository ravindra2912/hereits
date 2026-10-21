<?php

namespace App\Http\Controllers\Business;

use App\Models\State;
use App\Models\StateViseDeliveryCharge;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class StateDeliveryChargeController extends Controller
{
    /**
     * Display a listing of the state-wise delivery charges.
     */
    public function index(Request $request)
    {
        $businessId = getBusinessId();

        // Optimize query by selecting only required columns
        $charges = StateViseDeliveryCharge::where('business_id', $businessId)
            ->select('id', 'state_id', 'delivery_charge_per_kg')
            ->orderBy('id', 'desc')
            ->get();

        // Optimize: Fetch all states in a single query and key them by ID to prevent N+1 queries when mapping names
        $states = State::where('country_id', 101)
            ->select('id', 'name')
            ->get()
            ->keyBy('id');

        // Map state IDs to names
        foreach ($charges as $charge) {
            $stateIds = explode(',', $charge->state_id);
            $names = [];
            foreach ($stateIds as $id) {
                if (isset($states[$id])) {
                    $names[] = $states[$id]->name;
                }
            }
            $charge->state_names = implode(', ', $names);
        }

        // Fetch all states for the dropdown modal
        $allStates = State::where('country_id', 101)
            ->select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

        return view('business.setting.state_charges', compact('charges', 'allStates'));
    }

    /**
     * Store a newly created state-wise delivery charge in storage.
     */
    public function store(Request $request)
    {
        $success = false;
        $message = 'Something went wrong!';
        $redirect = route('business.setting.business.state-charges');
        $data = [];

        try {
            DB::beginTransaction();

            $rules = [
                'state_ids' => 'required|array',
                'state_ids.*' => 'exists:states,id',
                'delivery_charge_per_kg' => 'required|numeric|min:0',
            ];

            $validator = Validator::make($request->all(), $rules, [
                'state_ids.required' => 'Please select at least one state.',
                'delivery_charge_per_kg.required' => 'Please enter the delivery charge amount per kg.',
            ]);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                // Check if any of the selected states are already configured for this business
                $businessId = getBusinessId();
                $existingCharges = StateViseDeliveryCharge::where('business_id', $businessId)->get(['id', 'state_id']);
                
                $conflictingStates = [];
                $selectedStates = $request->state_ids;

                foreach ($existingCharges as $existing) {
                    $existingIds = explode(',', $existing->state_id);
                    $intersection = array_intersect($selectedStates, $existingIds);
                    if (!empty($intersection)) {
                        $conflictingStates = array_merge($conflictingStates, $intersection);
                    }
                }

                if (!empty($conflictingStates)) {
                    $stateNames = State::whereIn('id', array_unique($conflictingStates))->pluck('name')->toArray();
                    $message = [
                        'state_ids' => ['The following states already have configured delivery charges: ' . implode(', ', $stateNames)]
                    ];
                } else {
                    $charge = new StateViseDeliveryCharge();
                    $charge->business_id = $businessId;
                    // Store state IDs comma-separated as requested: state_id (store like 1,2,4 ids)
                    $charge->state_id = implode(',', $request->state_ids);
                    $charge->delivery_charge_per_kg = $request->delivery_charge_per_kg;
                    $charge->save();

                    $success = true;
                    $message = 'State-wise delivery charge added successfully.';
                    DB::commit();
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = checkResponse($e);
        }

        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    /**
     * Show the form for editing the specified state-wise delivery charge.
     */
    public function edit($id)
    {
        try {
            $charge = StateViseDeliveryCharge::where('business_id', getBusinessId())
                ->select('id', 'state_id', 'delivery_charge_per_kg')
                ->findOrFail($id);

            // Parse comma-separated string to array for Select2 pre-selection
            $charge->state_ids = explode(',', $charge->state_id);

            return response()->json([
                'success' => true,
                'data' => $charge
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found.'
            ], 404);
        }
    }

    /**
     * Update the specified state-wise delivery charge in storage.
     */
    public function update(Request $request, $id)
    {
        $success = false;
        $message = 'Something went wrong!';
        $redirect = route('business.setting.business.state-charges');
        $data = [];

        try {
            DB::beginTransaction();

            $charge = StateViseDeliveryCharge::where('business_id', getBusinessId())->findOrFail($id);

            $rules = [
                'state_ids' => 'required|array',
                'state_ids.*' => 'exists:states,id',
                'delivery_charge_per_kg' => 'required|numeric|min:0',
            ];

            $validator = Validator::make($request->all(), $rules, [
                'state_ids.required' => 'Please select at least one state.',
                'delivery_charge_per_kg.required' => 'Please enter the delivery charge amount per kg.',
            ]);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                // Check if any of the selected states are already configured in *other* records for this business
                $businessId = getBusinessId();
                $existingCharges = StateViseDeliveryCharge::where('business_id', $businessId)
                    ->where('id', '!=', $id)
                    ->get(['id', 'state_id']);
                
                $conflictingStates = [];
                $selectedStates = $request->state_ids;

                foreach ($existingCharges as $existing) {
                    $existingIds = explode(',', $existing->state_id);
                    $intersection = array_intersect($selectedStates, $existingIds);
                    if (!empty($intersection)) {
                        $conflictingStates = array_merge($conflictingStates, $intersection);
                    }
                }

                if (!empty($conflictingStates)) {
                    $stateNames = State::whereIn('id', array_unique($conflictingStates))->pluck('name')->toArray();
                    $message = [
                        'state_ids' => ['The following states already have configured delivery charges in other rules: ' . implode(', ', $stateNames)]
                    ];
                } else {
                    $charge->state_id = implode(',', $request->state_ids);
                    $charge->delivery_charge_per_kg = $request->delivery_charge_per_kg;
                    $charge->save();

                    $success = true;
                    $message = 'State-wise delivery charge updated successfully.';
                    DB::commit();
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = checkResponse($e);
        }

        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    /**
     * Remove the specified state-wise delivery charge from storage.
     */
    public function destroy($id)
    {
        $success = false;
        $message = 'Something went wrong!';
        $redirect = route('business.setting.business.state-charges');
        $data = [];

        try {
            DB::beginTransaction();

            $charge = StateViseDeliveryCharge::where('business_id', getBusinessId())->findOrFail($id);
            $charge->delete();

            $success = true;
            $message = 'State-wise delivery charge deleted successfully.';
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $message = checkResponse($e);
        }

        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }
}
