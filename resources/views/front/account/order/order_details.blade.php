@extends('front.layouts.main')

@section('title', 'Order Details')

@section('content')
<div class="bg-light py-5">
  <div class="container">
    <div class="row g-4">
      <!-- User Sidebar -->
      @include('front.account.sidebar')

      <!-- Main Dashboard Content -->
      <div class="col-lg-9">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Order Details</h4>
    <a href="{{ route('account.orders') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fas fa-arrow-left me-1"></i> Back to Orders
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Items in Order</h5>
                <span class="badge bg-primary px-3 py-2 rounded-pill">{{ $order->invoice_number }}</span>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr class="border-bottom">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h6 class="mb-0 fw-bold">{{ $item->item_name }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">₹{{ number_format($item->price, 2) }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end fw-bold">₹{{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="fw-bold mb-0">Order Summary</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Subtotal</span>
                    <span class="fw-bold">₹{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if(!empty($order->is_pf) || ($order->packing_charge ?? 0) > 0)
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Packing & Forwarding</span>
                    <span class="fw-bold">₹{{ number_format($order->packing_charge, 2) }}</span>
                </div>
                @else
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Shipping</span>
                    <span class="fw-bold">₹{{ number_format($order->shipping_charge, 2) }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Discount</span>
                    <span class="fw-bold text-success">-₹{{ number_format($order->discount, 2) }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-4">
                    <h5 class="fw-bold mb-0">Total</h5>
                    <h5 class="fw-bold mb-0 text-primary">₹{{ number_format($order->total, 2) }}</h5>
                </div>
                
                <h6 class="fw-bold mb-3">Shipping Details</h6>
                <div class="bg-light p-3 rounded-3 mb-4 text-sm">
                    <p class="mb-1 fw-bold">{{ $order->customer_name }}</p>
                    <p class="mb-1 text-muted">{{ $order->address }}</p>
                    <p class="mb-1 text-muted">{{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}</p>
                    <p class="mb-0 text-muted"><i class="fas fa-phone-alt me-2"></i>{{ $order->customer_contact }}</p>
                </div>
                
                <h6 class="fw-bold mb-3">Order Status</h6>
                <div>
                    @if($order->order_status == 'pending')
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill w-100 fs-6">Pending</span>
                    @elseif($order->order_status == 'confirmed')
                    <span class="badge bg-info px-3 py-2 rounded-pill w-100 fs-6">Confirmed</span>
                    @elseif($order->order_status == 'delivered')
                    <span class="badge bg-success px-3 py-2 rounded-pill w-100 fs-6">Delivered</span>
                    @elseif($order->order_status == 'canceled' || $order->order_status == 'canceled_by_user')
                    <span class="badge bg-danger px-3 py-2 rounded-pill w-100 fs-6">Canceled</span>
                    @else
                    <span class="badge bg-secondary px-3 py-2 rounded-pill w-100 fs-6">{{ ucfirst($order->order_status) }}</span>
                    @endif
                </div>

                @if($order->payment_status == 'pending' && !$order->transaction_id)
                <div class="mt-3">
                    <a href="{{ route('payment.show', ['type' => 'order', 'source_id' => encryptId($order->id)]) }}" target="_blank" class="btn btn-primary rounded-pill px-3 py-2 w-100 fw-semibold text-white">
                        <i class="fas fa-credit-card me-2"></i> Make Payment
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

      </div>
    </div>
  </div>
</div>

@endsection
