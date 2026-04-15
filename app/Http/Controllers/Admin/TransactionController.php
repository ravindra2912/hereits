<?php

namespace App\Http\Controllers\Admin;

use App\Models\Transactions;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\Business\PurchaseSuccessMail;
use App\Models\Business;
use App\Models\BusinessSetting;
use App\Models\Coupon;
use App\Traits\BusinessTraits;
use Carbon\Carbon;

class TransactionController extends Controller
{
    use BusinessTraits;
    public function pending(Request $request)
    {
        if ($request->ajax()) {
            $data = Transactions::with(['business', 'purchase', 'purchase.plan'])
                ->where('status', 'pending')
                ->where('payment_gateway', 'upi_manual')
                ->select('transactions.*');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('business_info', function ($row) {
                    $logo = $row->business ? getImage($row->business->business_logo) : asset('assets/images/default.png');
                    return '<div class="d-flex align-items-center">
                        <img src="' . $logo . '" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                        <div>
                            <div class="fw-bold mb-0">' . ($row->business->name ?? 'N/A') . '</div>
                            <small class="text-muted">' . ($row->business->owner->first_name ?? '') . '</small>
                        </div>
                    </div>';
                })
                ->addColumn('plan_info', function ($row) {
                    $type = $row->purchase->plan_type ?? 'N/A';
                    return '<div>
                        <div class="fw-bold mb-0">' . ($row->purchase->plan->name ?? ucfirst($type)) . '</div>
                        <small class="text-muted text-uppercase" style="font-size: 0.65rem;">' . $type . '</small>
                    </div>';
                })
                ->addColumn('amount', function ($row) {
                    return '<div class="fw-bold text-dark">' . \currencyFormat($row->amount) . '</div>';
                })
                ->addColumn('transaction_details', function ($row) {
                    return '<div>
                        <div class="small"><span class="text-muted">ID:</span> ' . $row->payment_id . '</div>
                        <div class="small"><span class="text-muted">Date:</span> ' . ($row->transaction_date ? date('d M, Y H:i', strtotime($row->transaction_date)) : 'N/A') . '</div>
                    </div>';
                })
                ->addColumn('action', function ($row) {
                    return '<div class="text-center">
                        <button onclick="viewDetails(' . $row->id . ')" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="bi bi-eye me-1"></i> View Details
                        </button>
                    </div>';
                })
                ->rawColumns(['business_info', 'plan_info', 'amount', 'transaction_details', 'action'])
                ->make(true);
        }
        return view('admin.transactions.pending');
    }

    public function show($id)
    {
        $transaction = Transactions::with(['business', 'purchase', 'purchase.plan', 'business.owner'])->findOrFail($id);
        $html = view('admin.transactions.details_modal', compact('transaction'))->render();
        return response()->json(['success' => true, 'html' => $html]);
    }

    public function approve(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = Transactions::findOrFail($id);

            if ($transaction->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'Transaction already processed']);
            }

            $payment_id = $request->payment_id;

            if (empty($payment_id) && empty($transaction->payment_id)) {
                return response()->json(['success' => false, 'message' => 'Payment ID is required']);
            }

            // Use provided payment_id or keep existing one
            $final_payment_id = !empty($payment_id) ? $payment_id : $transaction->payment_id;

            // Check if this payment_id is already used by ANOTHER transaction
            if (Transactions::where('payment_id', $final_payment_id)->where('id', '!=', $id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Payment ID already used by another transaction']);
            }

            $transaction->payment_id = $final_payment_id;
            $transaction->status = 'paid';
            $transaction->save();

            // Update purchase status
            if ($transaction->purchase) {

                $purchase = $transaction->purchase;

                //deactive current active plan if have
                Purchase::where('business_id', $purchase->business_id)
                    ->where('plan_type', $purchase->plan_type)
                    ->where('status', 'paid')
                    ->where('plan_status', 'active')
                    ->whereDate('end_date', '>=', Carbon::now())
                    ->update(['plan_status' => 'override']);

                $purchase->update(['status' => 'paid', 'plan_status' => 'active']);

                // Increment Coupon Usage if applicable
                if ($purchase->coupon_id) {
                    Coupon::where('id', $purchase->coupon_id)->increment('usage_count');
                }

                $this->updateBusinessSettings($purchase->id);

                // Send Purchase Success Email
                try {
                    $purchase->load(['business', 'business.owner']);
                    if ($purchase->business && $purchase->business->owner && $purchase->business->owner->email) {
                        Mail::to($purchase->business->owner->email)->send(new PurchaseSuccessMail($purchase));
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to send purchase success email: " . $e->getMessage());
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaction approved successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Approve Transaction error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong!']);
        }
    }

    public function reject($id)
    {
        try {
            $transaction = Transactions::findOrFail($id);
            if ($transaction->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'Transaction already processed']);
            }

            $transaction->update(['status' => 'failed']);

            if ($transaction->purchase) {
                $transaction->purchase->update(['status' => 'failed']);
            }

            return response()->json(['success' => true, 'message' => 'Transaction rejected successfully']);
        } catch (\Exception $e) {
            Log::error("Reject Transaction error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong!']);
        }
    }
}
