<?php

namespace App\Http\Controllers\Business;

use App\Models\Purchase;
use App\Models\Business;
use App\Models\SiteSetting;
use App\Models\BusinessCreditTransaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

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
     * Get credit history DataTables JSON for business
     */
    public function historyData(Request $request)
    {
        $businessId = getBusinessId();

        $query = BusinessCreditTransaction::where('business_id', $businessId);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {
                return '<small class="text-muted fw-medium">' . $row->created_at->format('d M Y, h:i A') . '</small>';
            })
            ->addColumn('type_badge', function ($row) {
                if ($row->type === 'credit') {
                    return '<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1"><i class="bi bi-plus-circle me-1"></i>Credit</span>';
                }
                return '<span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1"><i class="bi bi-dash-circle me-1"></i>Debit</span>';
            })
            ->addColumn('amount_col', function ($row) {
                $isCredit = $row->type === 'credit';
                $class = $isCredit ? 'text-success' : 'text-danger';
                $sign  = $isCredit ? '+' : '-';
                return '<span class="fw-bold ' . $class . '">' . $sign . number_format($row->amount, 2) . '</span>';
            })
            ->addColumn('reference_badge', function ($row) {
                $map = [
                    'order'       => ['bg' => 'bg-info bg-opacity-10 text-info', 'icon' => 'bi-cart-check', 'label' => 'Order'],
                    'pos'         => ['bg' => 'bg-primary bg-opacity-10 text-primary', 'icon' => 'bi-receipt', 'label' => 'POS Sale'],
                    'appointment' => ['bg' => 'bg-secondary bg-opacity-10 text-secondary', 'icon' => 'bi-calendar-event', 'label' => 'Appointment'],
                    'chat'        => ['bg' => 'bg-warning bg-opacity-10 text-warning', 'icon' => 'bi-chat-dots', 'label' => 'Chat Unlock'],
                    'quotation'   => ['bg' => 'bg-dark bg-opacity-10 text-dark', 'icon' => 'bi-file-earmark-text', 'label' => 'Quotation'],
                    'purchase'    => ['bg' => 'bg-success bg-opacity-10 text-success', 'icon' => 'bi-bag-check', 'label' => 'Purchase'],
                    'free'        => ['bg' => 'bg-success bg-opacity-10 text-success', 'icon' => 'bi-gift', 'label' => 'Free Bonus'],
                ];
                $info = $map[$row->reference_type] ?? ['bg' => 'bg-secondary bg-opacity-10 text-secondary', 'icon' => 'bi-dash', 'label' => ucfirst($row->reference_type)];
                return '<span class="badge ' . $info['bg'] . ' rounded-pill px-2 py-1"><i class="bi ' . $info['icon'] . ' me-1"></i>' . $info['label'] . '</span>';
            })
            ->addColumn('desc', function ($row) {
                return '<span class="small text-dark">' . e($row->description ?: '-') . '</span>';
            })
            ->rawColumns(['date', 'type_badge', 'amount_col', 'reference_badge', 'desc'])
            ->make(true);
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
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }
}
