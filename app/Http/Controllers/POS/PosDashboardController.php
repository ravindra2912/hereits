<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PosDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('pos')->user();
        $business_id = getPosBusinessId();
        $today = Carbon::today();

        $query = Order::where('business_id', $business_id);
        
        // If they can't view all orders, restrict to their own
        if (!checkPosPermission('view_all_orders')) {
            $query->where('created_user_id', $user->id);
        }

        $today_sales = (clone $query)->whereDate('created_at', $today)->sum('total');
        $total_orders = (clone $query)->count();
        $today_order_count = (clone $query)->whereDate('created_at', $today)->count();
        $recent_orders = (clone $query)->latest()->limit(5)->get();

        return view('pos.dashboard', compact('today_sales', 'total_orders', 'today_order_count', 'recent_orders'));
    }
}
