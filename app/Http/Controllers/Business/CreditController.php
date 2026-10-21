<?php

namespace App\Http\Controllers\Business;

use App\Models\Purchase;
use App\Models\Business;
use App\Models\SiteSetting;
use App\Models\CreditHistory;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    /**
     * Display credit page
     */
    public function index()
    {
        $business_id = getBusinessId();
        $businessSettings = getBusinessSettings($business_id);

        $site_setting = SiteSetting::first(['per_credit_price']);
        $price = $site_setting->per_credit_price ?? 1;

        return view('business.credit.index', compact('site_setting', 'businessSettings', 'price'));
    }

    /**
     * Get credit history datatable response
     */
    public function history(Request $request)
    {
        $business_id = getBusinessId();

        if ($request->ajax()) {
            $query = CreditHistory::where('business_id', $business_id);

            if ($request->filled('transaction_type')) {
                $query->where('transaction_type', $request->transaction_type);
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('d M Y, h:i A') : '-';
                })
                ->editColumn('transaction_type', function ($row) {
                    if ($row->transaction_type === 'credit') {
                        return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold"><i class="bi bi-arrow-down-left me-1"></i> Credit</span>';
                    }
                    return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-bold"><i class="bi bi-arrow-up-right me-1"></i> Debit</span>';
                })
                ->editColumn('type', function ($row) {
                    $badges = [
                        'registration'=> '<span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 text-capitalize">Registration</span>',
                        'order'       => '<span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 text-capitalize">Order</span>',
                        'appointment' => '<span class="badge bg-info-subtle text-info rounded-pill px-3 py-1 text-capitalize">Appointment</span>',
                        'quotation'   => '<span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-1 text-capitalize">Quotation</span>',
                        'lucky_draw'  => '<span class="badge bg-secondary-subtle text-dark rounded-pill px-3 py-1 text-capitalize">Lucky Draw</span>',
                        'recharge'    => '<span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 text-capitalize">Recharge</span>',
                    ];
                    return $badges[$row->type] ?? '<span class="badge bg-light text-dark rounded-pill px-3 py-1 text-capitalize">' . e($row->type) . '</span>';
                })
                ->editColumn('amount', function ($row) {
                    $prefix = $row->transaction_type === 'credit' ? '+' : '-';
                    $class = $row->transaction_type === 'credit' ? 'text-success' : 'text-danger';
                    return '<strong class="' . $class . '">' . $prefix . ' ' . number_format($row->amount, 2) . '</strong>';
                })
                ->editColumn('balance_after', function ($row) {
                    return number_format($row->balance_after, 2);
                })
                ->editColumn('description', function ($row) {
                    return e($row->description ?? '-');
                })
                ->rawColumns(['transaction_type', 'type', 'amount', 'description'])
                ->make(true);
        }

        return redirect()->route('business.credits');
    }

    /**
     * Purchase credit details page
     */
    public function show(Request $request)
    {
        $business_id = getBusinessId();
        $businessSettings = getBusinessSettings($business_id);
        $site_setting = SiteSetting::first(['per_credit_price']);
        $price = $site_setting->per_credit_price ?? 1;
        $quantity = $request->quantity ?? 50;

        return view('business.credit.show', compact('price', 'quantity', 'site_setting', 'businessSettings'));
    }

    /**
     * AJAX validation for coupons
     */
    public function validateCouponAjax(Request $request)
    {
        $site_setting = SiteSetting::first(['per_credit_price']);
        $unit_price = $site_setting->per_credit_price ?? 1;
        $quantity = $request->quantity ?? 1;
        $total_amount = $quantity * $unit_price;

        $result = validateCoupon($request->coupon_code, 'credit', $total_amount);

        return response()->json($result);
    }

    /**
     * Purchase credit
     */
    public function buy(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = '';
        $data = array();

        try {
            DB::beginTransaction();
            $business = Business::select('id')->find(getBusinessId());
            $quantity = $request->quantity;

            if (!$business) {
                $message = 'Business not found.';
            } elseif (!$quantity || $quantity < 1) {
                $message = 'Please enter a valid quantity.';
            } else {
                $site_setting = SiteSetting::first(['per_credit_price']);
                $unit_price = $site_setting->per_credit_price ?? 1;
                $originalPrice = $quantity * $unit_price;

                $discountAmount = 0;
                $couponId = null;

                // Coupon Validation
                if ($request->has('coupon_code') && !empty($request->coupon_code)) {
                    $validation = validateCoupon($request->coupon_code, 'credit', $originalPrice);
                    if (!$validation['success']) {
                        throw new \Exception($validation['message']);
                    }
                    $discountAmount = $validation['discount_amount'];
                    $couponId = $validation['coupon']->id;
                }

                $totalAmount = max(0, $originalPrice - $discountAmount);

                $binsert = new Purchase();
                $binsert->business_id = getBusinessId();
                $binsert->plan_id = null; // No fixed plan for credits
                $binsert->plan_type = 'credit';
                $binsert->subtotal = $originalPrice;
                $binsert->quantity = $quantity;
                $binsert->coupon_id = $couponId;
                $binsert->coupon_discount_amount = $discountAmount;
                $binsert->total_amount = $totalAmount;
                $binsert->start_date = Carbon::now();
                $binsert->status = 'pending';

                if (!$binsert->save()) {
                    throw new \Exception('Failed to create purchase record.');
                }

                $redirect = route('business.Payment', ['type' => 'credit', 'id' => $binsert->id]);
                $success = true;
                $message = 'Credit purchase initiated.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }
}
