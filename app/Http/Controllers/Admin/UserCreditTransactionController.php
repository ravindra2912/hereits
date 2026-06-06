<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserCreditTransaction;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class UserCreditTransactionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = UserCreditTransaction::with('user')
                ->select('user_credit_transactions.*');

            // Filter by type
            if ($request->filled('type')) {
                $data->where('type', $request->type);
            }

            // Filter by reference_type
            if ($request->filled('reference_type')) {
                $data->where('reference_type', $request->reference_type);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('user_info', function ($row) {
                    $user = $row->user;
                    return '<div>
                        <div class="fw-bold mb-0">' . ($user ? $user->first_name . ' ' . $user->last_name : 'N/A') . '</div>
                        <small class="text-muted">' . ($user->email ?? '') . '</small>
                    </div>';
                })
                ->addColumn('type_badge', function ($row) {
                    $color = $row->type === 'credit' ? 'success' : 'danger';
                    $icon  = $row->type === 'credit' ? 'arrow-down-circle' : 'arrow-up-circle';
                    return '<span class="badge bg-' . $color . ' bg-opacity-10 text-' . $color . ' px-2 py-1">
                        <i class="bi bi-' . $icon . ' me-1"></i>' . ucfirst($row->type) . '
                    </span>';
                })
                ->addColumn('credit_col', function ($row) {
                    $color = $row->type === 'credit' ? 'success' : 'danger';
                    $sign  = $row->type === 'credit' ? '+' : '-';
                    return '<span class="fw-bold text-' . $color . '">' . $sign . '₹' . number_format($row->amount, 2) . '</span>';
                })
                ->addColumn('reference_info', function ($row) {
                    $badge = match ($row->reference_type) {
                        'business_subscription' => 'primary',
                        'payout'                => 'warning',
                        'admin_adjustment'      => 'secondary',
                        default                 => 'secondary',
                    };
                    $label = ucwords(str_replace('_', ' ', $row->reference_type));
                    return '<div>
                        <span class="badge bg-' . $badge . ' bg-opacity-10 text-' . $badge . ' mb-1">' . $label . '</span>
                        ' . ($row->reference_id ? '<small class="text-muted d-block">Ref ID: ' . $row->reference_id . '</small>' : '') . '
                        ' . ($row->transaction_id ? '<small class="text-muted d-block">TXN: ' . $row->transaction_id . '</small>' : '') . '
                    </div>';
                })
                ->addColumn('date', function ($row) {
                    return '<small class="text-muted">' . $row->created_at ?? $row->created_at->format('d M Y, h:i A') . '</small>';
                })
                ->rawColumns(['user_info', 'type_badge', 'credit_col', 'reference_info', 'date'])
                ->make(true);
        }

        return view('admin.user_credit_transactions.index');
    }
}
