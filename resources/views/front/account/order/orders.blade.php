@extends('front.layouts.main')

@section('title', 'My Orders')

@section('content')
<div class="bg-light py-5">
  <div class="container">
    <div class="row g-4">
      <!-- User Sidebar -->
      @include('front.account.sidebar')

      <!-- Main Dashboard Content -->
      <div class="col-lg-9">

<div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
    <div class="card-header py-3 bg-white border-bottom d-flex align-items-center justify-content-between">
        <h5 class="m-0 fw-bold"><i class="fas fa-shopping-bag me-2 text-primary"></i>My Orders</h5>
    </div>
    <div class="card-body p-4">
        @if($orders->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Order ID</th>
                        <th>Business</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td>
                            <span class="fw-bold">{{ $order->invoice_number }}</span>
                        </td>
                        <td>{{ $order->business->name ?? 'N/A' }}</td>
                        <td>{{ $order->created_at->format('d M, Y h:i A') }}</td>
                        <td class="fw-bold">₹{{ number_format($order->total, 2) }}</td>
                        <td>
                            @if($order->order_status == 'pending')
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">Pending</span>
                            @elseif($order->order_status == 'confirmed')
                            <span class="badge bg-info px-3 py-2 rounded-pill">Confirmed</span>
                            @elseif($order->order_status == 'delivered')
                            <span class="badge bg-success px-3 py-2 rounded-pill">Delivered</span>
                            @elseif($order->order_status == 'canceled' || $order->order_status == 'canceled_by_user')
                            <span class="badge bg-danger px-3 py-2 rounded-pill">Canceled</span>
                            @else
                            <span class="badge bg-secondary px-3 py-2 rounded-pill">{{ ucfirst($order->order_status) }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('account.order.details', $order->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 d-flex justify-content-center">
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
        @else
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-shopping-bag fa-4x text-muted opacity-25"></i>
            </div>
            <h4 class="text-muted fw-bold">No Orders Found</h4>
            <p class="text-muted mb-4">You haven't placed any orders yet.</p>
        </div>
        @endif
    </div>
</div>

      </div>
    </div>
  </div>
</div>

@endsection
