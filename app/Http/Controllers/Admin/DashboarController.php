<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Business;
use App\Models\Purchase;
use App\Models\Transactions;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\ProfileUpdateRequest;
use Carbon\Carbon;

class DashboarController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index(Request $request): View
    {
        $usersByRole = User::select('role', DB::raw('count(*) as total'))
            ->whereIn('role', ['User', 'Business'])
            ->groupBy('role')
            ->pluck('total', 'role');

        $userCount = $usersByRole['User'] ?? 0;
        $sellerCount = $usersByRole['Business'] ?? 0;

        $businessByStatus = Business::select('status', DB::raw('count(*) as total'))
            ->whereIn('status', ['active', 'pending'])
            ->groupBy('status')
            ->pluck('total', 'status');

        $activeBusinessCount = $businessByStatus['active'] ?? 0;
        $pendingBusinessCount = $businessByStatus['pending'] ?? 0;

        // Base date (call now() ONCE)
        $now = Carbon::now();

        // Date range (last 12 months)
        $startDate = $now->copy()->subMonths(11)->startOfMonth();
        $endDate   = $now->copy()->endOfMonth();

        // Prepare months list (Y-m as key, M Y as label)
        $months = collect(range(0, 11))->mapWithKeys(function ($i) use ($now) {
            $date = $now->copy()->subMonths(11 - $i);
            return [
                $date->format('Y-m') => $date->format('M Y')
            ];
        });

        // Chart labels
        $chartLabels = $months->values()->toArray();

        // Monthly revenue
        $revenueData = Transactions::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        // Monthly purchases
        $purchaseData = Purchase::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        // Map data to 12 months (fill missing with 0)
        $revenueChartData = $months->keys()
            ->map(fn($month) => (float) ($revenueData[$month] ?? 0))
            ->toArray();

        $purchaseChartData = $months->keys()
            ->map(fn($month) => (int) ($purchaseData[$month] ?? 0))
            ->toArray();

        return view('admin.dashboard', compact(
            'userCount',
            'sellerCount',
            'activeBusinessCount',
            'pendingBusinessCount',
            'chartLabels',
            'revenueChartData',
            'purchaseChartData'
        ));
    }
}
