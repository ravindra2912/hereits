@extends('pos.layouts.main')

@section('title', 'Dashboard')
@section('header_title', 'Dashboard')

@section('content')
<div class="row g-4">
    <!-- Welcome Card -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4" style="background: linear-gradient(to right, #ffffff, #f0fdf4);">
            <h2 class="fw-bold text-dark">Welcome back, {{ Auth::guard('pos')->user()->first_name }}! 👋</h2>
            <p class="text-muted mb-0">Your POS terminal is ready for operations. What would you like to do today?</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="col-md-4">
        <div class="stats-card border-0 shadow-sm rounded-4 p-4 bg-white d-flex align-items-center gap-3">
            <div class="stats-icon bg-primary-subtle text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;"><i class="bi bi-currency-rupee fs-4"></i></div>
            <div>
                <div class="text-muted small fw-bold text-uppercase ls-1" style="font-size: 0.7rem;">Today's Sales</div>
                <div class="fs-4 fw-bold text-dark">₹{{ number_format($today_sales, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card border-0 shadow-sm rounded-4 p-4 bg-white d-flex align-items-center gap-3">
            <div class="stats-icon bg-success-subtle text-success rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;"><i class="bi bi-cart-check fs-4"></i></div>
            <div>
                <div class="text-muted small fw-bold text-uppercase ls-1" style="font-size: 0.7rem;">Total Orders</div>
                <div class="fs-4 fw-bold text-dark">{{ $total_orders }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card border-0 shadow-sm rounded-4 p-4 bg-white d-flex align-items-center gap-3">
            <div class="stats-icon bg-info-subtle text-info rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;"><i class="bi bi-bag-check fs-4"></i></div>
            <div>
                <div class="text-muted small fw-bold text-uppercase ls-1" style="font-size: 0.7rem;">Orders Today</div>
                <div class="fs-4 fw-bold text-dark">{{ $today_order_count }}</div>
            </div>
        </div>
    </div>

    <!-- Recent Orders & Actions -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Recent Operations</h5>
                <a href="{{ route('pos.order.index') }}" class="btn btn-light btn-sm rounded-pill px-3 fw-bold small">View All History</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase ls-1">Invoice</th>
                                <th class="py-3 text-secondary small fw-bold text-uppercase ls-1">Customer</th>
                                <th class="py-3 text-secondary small fw-bold text-uppercase ls-1">Total</th>
                                <th class="pe-4 py-3 text-end text-secondary small fw-bold text-uppercase ls-1">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_orders as $order)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">#{{ $order->invoice_number }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $order->customer_name }}</div>
                                    <div class="small text-muted">{{ $order->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="fw-bold text-primary">₹{{ number_format($order->total, 2) }}</td>
                                <td class="pe-4 text-end">
                                    <span class="badge rounded-pill bg-success px-3 py-1 small">
                                        {{ ucfirst($order->order_status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="opacity-25 py-3">
                                        <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                                        <p class="mb-0">No recent orders found</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 bg-primary text-white p-4 h-100 text-center d-flex flex-column justify-content-center">
            <div class="opacity-75 mb-3">
                <i class="bi bi-pc-display fs-1"></i>
            </div>
            <h4 class="fw-bold mb-2">Ready to Serve?</h4>
            <p class="small opacity-75 mb-4 px-3">Process sales quickly with our streamlined POS interface.</p>
            <a href="{{ route('pos.sale.index') }}" class="btn btn-white bg-white text-primary rounded-pill fw-bold py-2 shadow-sm mx-auto px-4">
                Open POS Terminal <i class="bi bi-chevron-right ms-1"></i>
            </a>
        </div>
    </div>
</div>
</div>
@endsection
