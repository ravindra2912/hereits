<?php

namespace App\Http\Controllers\Admin;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;

class PurchaseHistoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Purchase::with(['business', 'plan', 'transaction', 'coupon'])
                ->select('purchases.*');

            if ($request->has('status') && !empty($request->status)) {
                $data->where('purchases.status', $request->status);
            }

            if ($request->has('plan_type') && !empty($request->plan_type)) {
                $data->where('purchases.plan_type', $request->plan_type);
            }

            if ($request->has('plan_status') && !empty($request->plan_status)) {
                $data->where('purchases.plan_status', $request->plan_status);
            }

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
                    $typeIcons = [
                        'subscription' => ['icon' => 'bi-star', 'color' => 'primary'],
                        'credit' => ['icon' => 'bi-wallet2', 'color' => 'success'],
                    ];
                    $type = $row->plan_type ?? 'subscription';
                    $info = $typeIcons[$type] ?? ['icon' => 'bi-credit-card', 'color' => 'secondary'];

                    return '<div class="d-flex align-items-center">
                        <div class="bg-' . $info['color'] . ' bg-opacity-10 text-' . $info['color'] . ' rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                            <i class="bi ' . $info['icon'] . ' small"></i>
                        </div>
                        <div>
                            <div class="fw-bold mb-0">' . ($row->plan->name ?? ucfirst($type)) . '</div>
                            <small class="text-muted text-uppercase" style="font-size: 0.65rem;">' . $type . '</small>
                        </div>
                    </div>';
                })
                ->addColumn('amount', function ($row) {
                    $discounts = '';
                    if ($row->coupon_discount_amount > 0 || $row->activated_plan_discount > 0) {
                        $discounts .= '<small class="text-success" style="font-size: 0.7rem;">- ' . \currencyFormat($row->coupon_discount_amount + $row->activated_plan_discount) . ' (saved)</small>';
                    }
                    return '<div class="fw-bold text-dark">' . \currencyFormat($row->total_amount) . '</div>' . $discounts;
                })
                ->addColumn('dates', function ($row) {
                    if (!$row->start_date && !$row->end_date) return '<span class="text-muted">-</span>';
                    $start = $row->start_date ? date('d M, Y', strtotime($row->start_date)) : 'N/A';
                    $end = $row->end_date ? date('d M, Y', strtotime($row->end_date)) : 'N/A';
                    return '<div class="small">
                        <span class="text-muted">Start:</span> ' . $start . '<br>
                        <span class="text-muted">End:</span> ' . $end . '
                    </div>';
                })
                ->addColumn('status', function ($row) {
                    $status = $row->status ?? 'pending';
                    $badgeClass = ($status == 'success' || $status == 'paid') ? 'success' : ($status == 'pending' ? 'info' : 'danger');
                    return '<span class="badge rounded-pill bg-' . $badgeClass . ' bg-opacity-10 text-' . $badgeClass . ' px-2 border border-' . $badgeClass . ' border-opacity-25" style="font-size: 0.65rem;">' . ucfirst($status) . '</span>';
                })
                ->addColumn('plan_status', function ($row) {

                    $planStatus = $row->plan_status ?? 'pending';
                    $psBadgeClass = match ($planStatus) {
                        'active' => 'success',
                        'expired' => 'danger',
                        'override' => 'warning',
                        default => 'info'
                    };
                    return '<span class="badge rounded-pill bg-' . $psBadgeClass . ' bg-opacity-10 text-' . $psBadgeClass . ' px-2 border border-' . $psBadgeClass . ' border-opacity-25" style="font-size: 0.65rem;">' . ucfirst($planStatus) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    return '<div class="text-center">
                        <button onclick="viewDetails(' . $row->id . ')" class="btn btn-outline-primary btn_action btn-sm rounded-pill px-3 me-2" title="View Details">
                            <i class="bi bi-eye me-1"></i> Details
                        </button>
                        <a href="' . route('purchase.invoice', $row->id) . '" class="btn btn-outline-danger btn-sm rounded-pill px-3" title="Download Invoice">
                            <i class="bi bi-download me-1"></i>
                        </a>
                    </div>';
                })
                ->rawColumns(['business_info', 'plan_info', 'amount', 'dates', 'status', 'plan_status', 'action'])
                ->make(true);
        }
        return view('admin.purchase_history.index');
    }

    public function show($id)
    {
        $purchase = Purchase::with(['business', 'plan', 'transaction', 'business.owner', 'coupon'])->find($id);
        if (!$purchase) {
            return response()->json(['success' => false, 'message' => 'Purchase not found']);
        }

        $html = view('admin.purchase_history.details_modal', compact('purchase'))->render();
        return response()->json(['success' => true, 'html' => $html]);
    }

    public function downloadInvoice($id)
    {
        $purchase = Purchase::with(['transaction', 'plan', 'business', 'business.owner'])->findOrFail($id);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('business.purchase.invoice', compact('purchase'));
        return $pdf->download('invoice-' . $purchase->id . '.pdf');
    }
}
