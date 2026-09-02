<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessCreditTransaction;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class BusinessCreditHistoryController extends Controller
{
    /**
     * Display Business Credit History main page & handle main DataTables AJAX request.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            [$startDate, $endDate] = $this->parseDateFilter($request);

            $query = Business::with(['owner', 'businessCategory', 'businessSetting'])
                ->select('businesses.id', 'businesses.name', 'businesses.business_logo', 'businesses.owner_id', 'businesses.business_category_id', 'businesses.contact', 'businesses.created_at');

            // Add sum for credit transactions (debits) in period
            $query->withSum(['creditTransactions as period_used_credits' => function ($q) use ($startDate, $endDate) {
                $q->where('type', 'debit');
                if ($startDate) {
                    $q->where('created_at', '>=', $startDate);
                }
                if ($endDate) {
                    $q->where('created_at', '<=', $endDate);
                }
            }], 'amount');

            // Add sum for credit transactions (credits) in period
            $query->withSum(['creditTransactions as period_purchased_credits' => function ($q) use ($startDate, $endDate) {
                $q->where('type', 'credit');
                if ($startDate) {
                    $q->where('created_at', '>=', $startDate);
                }
                if ($endDate) {
                    $q->where('created_at', '<=', $endDate);
                }
            }], 'amount');

            // Add sum for paid credit purchases in period as fallback
            $query->withSum(['purchases as period_purchases_sum' => function ($q) use ($startDate, $endDate) {
                $q->where('plan_type', 'credit')->where('status', 'paid');
                if ($startDate) {
                    $q->where('created_at', '>=', $startDate);
                }
                if ($endDate) {
                    $q->where('created_at', '<=', $endDate);
                }
            }], 'quantity');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('business_info', function ($row) {
                    $logo = getImage($row->business_logo);
                    $category = $row->businessCategory->name ?? 'Uncategorized';
                    return '
                    <div class="d-flex align-items-center">
                        <img src="' . $logo . '" class="rounded-circle me-2 border shadow-sm" style="width: 40px; height: 40px; object-fit: cover;">
                        <div>
                            <div class="fw-bold text-dark mb-0">' . e($row->name) . '</div>
                            <small class="text-muted"><i class="bi bi-tag me-1"></i>' . e($category) . '</small>
                        </div>
                    </div>';
                })
                ->addColumn('owner_info', function ($row) {
                    $owner = $row->owner;
                    if (!$owner) {
                        return '<span class="text-muted">N/A</span>';
                    }
                    $name = trim(($owner->first_name ?? '') . ' ' . ($owner->last_name ?? ''));
                    return '
                    <div>
                        <div class="fw-semibold text-dark mb-0">' . e($name ?: 'N/A') . '</div>
                        <small class="text-muted">' . e($owner->contact ?? $row->contact ?? 'N/A') . '</small>
                    </div>';
                })
                ->addColumn('balance_credit', function ($row) {
                    $balance = (float)($row->businessSetting->credit ?? 0);
                    $badgeClass = $balance > 20 ? 'bg-success' : ($balance > 0 ? 'bg-warning text-dark' : 'bg-danger');
                    return '<span class="badge ' . $badgeClass . ' rounded-pill px-3 py-2 fs-6">
                        <i class="bi bi-wallet2 me-1"></i>' . number_format($balance, 2) . '
                    </span>';
                })
                ->addColumn('used_credit', function ($row) {
                    $used = (float)($row->period_used_credits ?? 0);
                    return '<span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2 fs-6">
                        <i class="bi bi-arrow-up-right me-1"></i>' . number_format($used, 2) . '
                    </span>';
                })
                ->addColumn('purchased_credit', function ($row) {
                    $purchased = max((float)($row->period_purchased_credits ?? 0), (float)($row->period_purchases_sum ?? 0));
                    return '<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fs-6">
                        <i class="bi bi-arrow-down-left me-1"></i>' . number_format($purchased, 2) . '
                    </span>';
                })
                ->addColumn('action', function ($row) {
                    return '
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 view-history-btn" 
                        data-id="' . $row->id . '" 
                        data-name="' . e($row->name) . '"
                        data-balance="' . number_format((float)($row->businessSetting->credit ?? 0), 2) . '">
                        <i class="bi bi-clock-history me-1"></i> History
                    </button>';
                })
                ->rawColumns(['business_info', 'owner_info', 'balance_credit', 'used_credit', 'purchased_credit', 'action'])
                ->make(true);
        }

        // Summary metrics for page stats cards
        $totalBusinesses = Business::count();
        $totalAvailableCredits = \App\Models\BusinessSetting::sum('credit');
        $totalUsedCredits = BusinessCreditTransaction::where('type', 'debit')->sum('amount');
        $totalPurchasedCredits = Purchase::where('plan_type', 'credit')->where('status', 'paid')->sum('quantity');

        return view('admin.business_credit_history.index', compact(
            'totalBusinesses',
            'totalAvailableCredits',
            'totalUsedCredits',
            'totalPurchasedCredits'
        ));
    }

    /**
     * Display credit history modal details for a specific business.
     */
    public function historyDetails(Request $request, $businessId)
    {
        $business = Business::findOrFail($businessId);
        [$startDate, $endDate] = $this->parseDateFilter($request);

        // Fetch recorded credit transactions for this business
        $query = BusinessCreditTransaction::where('business_id', $businessId);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // If no records in business_credit_transactions yet, auto-sync legacy paid purchases as credit transactions
        if ($query->count() === 0 && !BusinessCreditTransaction::where('business_id', $businessId)->exists()) {
            $this->syncLegacyPurchases($businessId);
            $query = BusinessCreditTransaction::where('business_id', $businessId);
            if ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->where('created_at', '<=', $endDate);
            }
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
            ->addColumn('reference_info', function ($row) {
                $refType = ucwords(str_replace('_', ' ', $row->reference_type));
                $badgeColor = match ($row->reference_type) {
                    'purchase'    => 'success',
                    'appointment' => 'info',
                    'order'       => 'primary',
                    'chat'        => 'warning',
                    'pos'         => 'secondary',
                    'quotation'   => 'dark',
                    default       => 'secondary',
                };
                return '
                <div>
                    <span class="badge bg-' . $badgeColor . ' bg-opacity-10 text-' . $badgeColor . ' mb-1">' . $refType . '</span>
                    ' . ($row->reference_id ? '<small class="text-muted d-block">Ref ID: #' . $row->reference_id . '</small>' : '') . '
                </div>';
            })
            ->addColumn('description', function ($row) {
                return '<span class="text-dark small">' . e($row->description ?: 'N/A') . '</span>';
            })
            ->rawColumns(['date', 'type_badge', 'amount_col', 'reference_info', 'description'])
            ->make(true);
    }

    /**
     * Helper to parse date filter parameter into Carbon start/end dates.
     */
    private function parseDateFilter(Request $request): array
    {
        $startDate = null;
        $endDate   = null;
        $rangeType = $request->get('date_range', 'all');

        if ($rangeType === 'today') {
            $startDate = Carbon::today()->startOfDay();
            $endDate   = Carbon::today()->endOfDay();
        } elseif ($rangeType === 'week') {
            $startDate = Carbon::now()->startOfWeek();
            $endDate   = Carbon::now()->endOfWeek();
        } elseif ($rangeType === 'month') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate   = Carbon::now()->endOfMonth();
        } elseif ($rangeType === 'custom') {
            if ($request->filled('start_date')) {
                $startDate = Carbon::parse($request->start_date)->startOfDay();
            }
            if ($request->filled('end_date')) {
                $endDate = Carbon::parse($request->end_date)->endOfDay();
            }
        }

        return [$startDate, $endDate];
    }

    /**
     * Sync legacy purchases into business_credit_transactions for historical data display.
     */
    private function syncLegacyPurchases($businessId): void
    {
        $purchases = Purchase::where('business_id', $businessId)
            ->where('plan_type', 'credit')
            ->where('status', 'paid')
            ->get();

        foreach ($purchases as $purchase) {
            BusinessCreditTransaction::firstOrCreate(
                [
                    'business_id'    => $businessId,
                    'reference_type' => 'purchase',
                    'reference_id'   => $purchase->id,
                ],
                [
                    'type'        => 'credit',
                    'amount'      => $purchase->quantity ?? 0,
                    'description' => 'Credit Purchase (' . ($purchase->quantity ?? 0) . ' Credits)',
                    'created_at'  => $purchase->created_at,
                    'updated_at'  => $purchase->updated_at,
                ]
            );
        }
    }
}
