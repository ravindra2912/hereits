<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessSetting;
use App\Models\CreditHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CreditHistoryController extends Controller
{
    /**
     * Display Business List with Used Credit, Available Credit, and History modal.
     */
    public function index(Request $request)
    {
        if (!$request->has('date_preset')) {
            $request->merge(['date_preset' => 'today']);
        }

        if ($request->ajax()) {
            $usedCreditSub = CreditHistory::selectRaw('COALESCE(SUM(amount), 0)')
                ->whereColumn('credit_histories.business_id', 'businesses.id')
                ->where('transaction_type', 'debit');
            $this->applyDateFilter($usedCreditSub, $request);

            $freeCreditSub = CreditHistory::selectRaw('COALESCE(SUM(amount), 0)')
                ->whereColumn('credit_histories.business_id', 'businesses.id')
                ->where('transaction_type', 'credit')
                ->where('type', 'registration');
            $this->applyDateFilter($freeCreditSub, $request);

            $purchasedCreditSub = CreditHistory::selectRaw('COALESCE(SUM(amount), 0)')
                ->whereColumn('credit_histories.business_id', 'businesses.id')
                ->where('transaction_type', 'credit')
                ->where('type', 'recharge');
            $this->applyDateFilter($purchasedCreditSub, $request);

            $query = Business::with(['owner:id,first_name,last_name', 'businessSetting:id,business_id,credit'])
                ->leftJoin('business_settings', 'businesses.id', '=', 'business_settings.business_id')
                ->select('businesses.id', 'businesses.name', 'businesses.business_logo', 'businesses.owner_id', 'businesses.created_at', 'business_settings.credit')
                ->selectSub($usedCreditSub, 'total_used_credit')
                ->selectSub($freeCreditSub, 'total_free_credit')
                ->selectSub($purchasedCreditSub, 'total_purchased_credit');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('business_info', function ($row) {
                    $logo = getImage($row->business_logo);
                    $ownerName = trim(($row->owner->first_name ?? '') . ' ' . ($row->owner->last_name ?? ''));
                    return '<div class="d-flex align-items-center">
                        <img src="' . $logo . '" class="rounded-circle me-2.5 border shadow-sm" style="width: 40px; height: 40px; object-fit: cover;">
                        <div>
                            <div class="fw-bold text-dark mb-0" style="font-size: 0.92rem;">' . e($row->name) . '</div>
                            ' . ($ownerName ? '<small class="text-muted" style="font-size: 0.76rem;"><i class="bi bi-person me-1"></i>' . e($ownerName) . '</small>' : '') . '
                        </div>
                    </div>';
                })
                ->addColumn('available_credit', function ($row) {
                    $balance = (float)($row->businessSetting->credit ?? $row->credit ?? 0);
                    $badgeClass = $balance > 10 ? 'success' : ($balance > 0 ? 'warning' : 'danger');
                    $statusLabel = $balance > 10 ? 'Healthy' : ($balance > 0 ? 'Low' : 'Empty');
                    return '<div class="d-flex align-items-center gap-2">
                        <span class="fw-bold text-dark fs-6">' . number_format($balance, 2) . ' Cr</span>
                        <span class="badge bg-' . $badgeClass . ' bg-opacity-10 text-' . $badgeClass . ' border border-' . $badgeClass . ' border-opacity-25" style="font-size: 0.68rem;">' . $statusLabel . '</span>
                    </div>';
                })
                ->addColumn('used_credit', function ($row) {
                    $total = (float)($row->total_used_credit ?? 0);
                    return '<span class="fw-bold text-danger" style="font-size: 0.9rem;">-' . number_format($total, 2) . ' Cr</span>';
                })
                ->addColumn('free_credit', function ($row) {
                    $total = (float)($row->total_free_credit ?? 0);
                    return '<span class="fw-bold text-info" style="font-size: 0.9rem;">+' . number_format($total, 2) . ' Cr</span>';
                })
                ->addColumn('purchased_credit', function ($row) {
                    $total = (float)($row->total_purchased_credit ?? 0);
                    return '<span class="fw-bold text-success" style="font-size: 0.9rem;">+' . number_format($total, 2) . ' Cr</span>';
                })
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 open-history-modal-btn" data-business-id="' . $row->id . '" data-business-name="' . e($row->name) . '" title="View Credit History">
                        <i class="bi bi-clock-history me-1"></i> History
                    </button>';
                })
                ->rawColumns(['business_info', 'available_credit', 'used_credit', 'free_credit', 'purchased_credit', 'action'])
                ->make(true);
        }

        // Calculate KPI summary metrics
        $summary = $this->calculateSummaryMetrics($request);

        return view('admin.credit_history.index', compact('summary'));
    }

    /**
     * AJAX action to fetch credit transaction history for a specific business.
     */
    public function getBusinessHistory(Request $request, $businessId)
    {
        $query = CreditHistory::where('business_id', $businessId)->select('credit_histories.*');

        // Filter by Transaction Type (credit / debit)
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Filter by Category Type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Date filtering
        $this->applyDateFilter($query, $request);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('type_badge', function ($row) {
                $badges = [
                    'registration'=> ['bg' => 'success', 'icon' => 'bi-award', 'label' => 'Registration'],
                    'recharge'    => ['bg' => 'success', 'icon' => 'bi-wallet2', 'label' => 'Recharge'],
                    'order'       => ['bg' => 'primary', 'icon' => 'bi-cart-check', 'label' => 'Order'],
                    'appointment' => ['bg' => 'warning', 'icon' => 'bi-calendar-event', 'label' => 'Appointment'],
                    'quotation'   => ['bg' => 'info', 'icon' => 'bi-file-earmark-text', 'label' => 'Quotation'],
                    'lucky_draw'  => ['bg' => 'purple', 'icon' => 'bi-gift', 'label' => 'Lucky Draw'],
                    'manual'      => ['bg' => 'secondary', 'icon' => 'bi-sliders', 'label' => 'Manual'],
                    'other'       => ['bg' => 'dark', 'icon' => 'bi-circle', 'label' => 'Other'],
                ];
                $info = $badges[$row->type] ?? ['bg' => 'secondary', 'icon' => 'bi-tag', 'label' => ucfirst($row->type)];
                return '<span class="badge rounded-pill bg-' . $info['bg'] . ' bg-opacity-10 text-' . $info['bg'] . ' px-2.5 py-1 border border-' . $info['bg'] . ' border-opacity-25" style="font-size: 0.72rem;">
                    <i class="bi ' . $info['icon'] . ' me-1"></i>' . $info['label'] . '
                </span>';
            })
            ->addColumn('transaction_type_badge', function ($row) {
                if ($row->transaction_type === 'credit') {
                    return '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1" style="font-size: 0.72rem;"><i class="bi bi-arrow-down-left me-1"></i>Credit</span>';
                }
                return '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1" style="font-size: 0.72rem;"><i class="bi bi-arrow-up-right me-1"></i>Debit</span>';
            })
            ->addColumn('formatted_amount', function ($row) {
                $isCredit = $row->transaction_type === 'credit';
                $color = $isCredit ? 'text-success' : 'text-danger';
                $prefix = $isCredit ? '+' : '-';
                return '<span class="fw-bold ' . $color . '" style="font-size: 0.88rem;">' . $prefix . number_format($row->amount, 2) . ' Cr</span>';
            })
            ->addColumn('formatted_balance', function ($row) {
                return '<span class="fw-semibold text-dark" style="font-size: 0.85rem;">' . number_format($row->balance_after, 2) . ' Cr</span>';
            })
            ->addColumn('formatted_date', function ($row) {
                return '<div class="small text-dark fw-semibold">' . $row->created_at->format('d M, Y') . '</div>' .
                       '<small class="text-muted" style="font-size: 0.72rem;">' . $row->created_at->format('h:i A') . ' (' . $row->created_at->diffForHumans() . ')</small>';
            })
            ->addColumn('description_info', function ($row) {
                $desc = e($row->description ?? '-');
                $ref = $row->reference_id ? '<span class="badge bg-light text-muted border ms-1" style="font-size: 0.7rem;">Ref: #' . e($row->reference_id) . '</span>' : '';
                return '<div><span class="small text-secondary">' . $desc . '</span>' . $ref . '</div>';
            })
            ->rawColumns(['type_badge', 'transaction_type_badge', 'formatted_amount', 'formatted_balance', 'formatted_date', 'description_info'])
            ->make(true);
    }

    /**
     * Calculate summary metrics.
     */
    public function getSummaryMetrics(Request $request)
    {
        $summary = $this->calculateSummaryMetrics($request);
        return response()->json(['success' => true, 'summary' => $summary]);
    }

    /**
     * Helper to apply date filtering preset or custom dates.
     */
    protected function applyDateFilter($query, Request $request, string $column = 'credit_histories.created_at'): void
    {
        $preset = $request->get('date_preset');

        if ($preset === 'today') {
            $query->whereDate($column, Carbon::today());
        } elseif ($preset === 'this_week') {
            $query->whereBetween($column, [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($preset === 'this_month') {
            $query->whereMonth($column, Carbon::now()->month)
                  ->whereYear($column, Carbon::now()->year);
        } elseif ($preset === 'last_month') {
            $query->whereMonth($column, Carbon::now()->subMonth()->month)
                  ->whereYear($column, Carbon::now()->subMonth()->year);
        } elseif ($preset === 'custom' || ($request->filled('start_date') && $request->filled('end_date'))) {
            if ($request->filled('start_date')) {
                $query->whereDate($column, '>=', Carbon::parse($request->start_date));
            }
            if ($request->filled('end_date')) {
                $query->whereDate($column, '<=', Carbon::parse($request->end_date));
            }
        }
    }

    /**
     * Calculate summary metrics.
     */
    protected function calculateSummaryMetrics(Request $request): array
    {
        if (!$request->has('date_preset')) {
            $request->merge(['date_preset' => 'today']);
        }

        $baseQuery = CreditHistory::query();
        $this->applyDateFilter($baseQuery, $request);

        $totalFreeCredit = (clone $baseQuery)->where('transaction_type', 'credit')->where('type', 'registration')->sum('amount');
        $totalPurchasedCredit = (clone $baseQuery)->where('transaction_type', 'credit')->where('type', 'recharge')->sum('amount');
        $totalRecharged = (clone $baseQuery)->where('transaction_type', 'credit')->sum('amount');
        $totalUsed = (clone $baseQuery)->where('transaction_type', 'debit')->sum('amount');

        $totalAvailableBalance = BusinessSetting::sum('credit');
        $lowBalanceCount = BusinessSetting::where('credit', '<=', 10)->count();

        return [
            'total_free_credit'       => (float)$totalFreeCredit,
            'total_purchased_credit'  => (float)$totalPurchasedCredit,
            'total_recharged'         => (float)$totalRecharged,
            'total_used'              => (float)$totalUsed,
            'total_available_balance' => (float)$totalAvailableBalance,
            'low_balance_count'       => (int)$lowBalanceCount,
        ];
    }
}
