@extends('front.layouts.main')

@section('title', 'Quotation Details')

@section('content')
<div class="bg-light py-5">
  <div class="container">
    <div class="row g-4">
      <!-- User Sidebar -->
      @include('front.account.sidebar')

      <!-- Main Dashboard Content -->
      <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="fw-bold mb-0">Quotation Details</h4>
          <a href="{{ route('account.quotations') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> Back to Quotations
          </a>
        </div>

        <div class="row g-4">
          <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 mb-4">
              <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Items in Quotation</h5>
                <span class="badge bg-primary px-3 py-2 rounded-pill">{{ $quotation->quotation_no }}</span>
              </div>
              <div class="card-body p-4">
                <div class="table-responsive">
                  <table class="table table-borderless align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Item Description</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($quotation->items as $item)
                        <tr class="border-bottom">
                          <td>
                            <h6 class="mb-0 fw-bold">{{ $item->item_name }}</h6>
                            @if($item->product && $item->product->sku)
                              <span class="text-muted small">SKU: {{ $item->product->sku }}</span>
                            @endif
                          </td>
                          <td class="text-center">₹{{ number_format($item->price, 2) }}</td>
                          <td class="text-center">{{ $item->qty }}</td>
                          <td class="text-end fw-bold">₹{{ number_format($item->price * $item->qty, 2) }}</td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="4" class="text-center py-3 text-muted">No items in this quotation.</td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>

                @if(!empty($quotation->notes))
                  <div class="mt-4 p-3 bg-light rounded-3">
                    <h6 class="fw-bold mb-1"><i class="fas fa-sticky-note me-2 text-warning"></i>Notes</h6>
                    <p class="text-muted small mb-0">{{ $quotation->notes }}</p>
                  </div>
                @endif
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <!-- Business Info Card -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
              <div class="card-body p-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2">Business Information</h6>
                @if($quotation->business)
                  <h6 class="fw-bold text-dark mb-1">{{ $quotation->business->name }}</h6>
                  @if($quotation->business->address)
                    <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1 text-danger"></i>{{ $quotation->business->address }}</p>
                  @endif
                  @if($quotation->business->contact)
                    <p class="text-muted small mb-3"><i class="fas fa-phone me-1 text-success"></i>{{ $quotation->business->contact }}</p>
                    @php
                      $cleanPhone = preg_replace('/[^0-9]/', '', (string)$quotation->business->contact);
                      if (strlen($cleanPhone) === 10) {
                        $cleanPhone = '91' . $cleanPhone;
                      }
                      $waMsg = urlencode("Hello, I am inquiring about quotation #{$quotation->quotation_no} totaling Rs. " . number_format($quotation->total, 2));
                    @endphp
                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waMsg }}" target="_blank" class="btn btn-success btn-sm w-100 rounded-pill mb-2">
                      <i class="fab fa-whatsapp me-1"></i> Inquire on WhatsApp
                    </a>
                  @endif
                  @if($quotation->business->slug)
                    <a href="{{ route('business-details', $quotation->business->slug) }}" class="btn btn-outline-primary btn-sm w-100 rounded-pill">
                      <i class="fas fa-store me-1"></i> Visit Business Page
                    </a>
                  @endif
                @else
                  <p class="text-muted small mb-0">Business details unavailable.</p>
                @endif
              </div>
            </div>

            <!-- Quotation Summary -->
            <div class="card shadow-sm border-0 rounded-4">
              <div class="card-body p-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2">Quotation Summary</h6>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Status</span>
                  @php
                    $status = strtolower($quotation->status ?? 'pending');
                  @endphp
                  @if($status === 'ordered')
                    <span class="badge bg-success rounded-pill px-3 py-1">Ordered</span>
                  @elseif($status === 'inprogress')
                    <span class="badge bg-info rounded-pill px-3 py-1">In Progress</span>
                  @elseif($status === 'pending')
                    <span class="badge bg-warning text-dark rounded-pill px-3 py-1">Pending</span>
                  @elseif($status === 'cancel')
                    <span class="badge bg-danger rounded-pill px-3 py-1">Cancelled</span>
                  @elseif($status === 'expired')
                    <span class="badge bg-secondary rounded-pill px-3 py-1">Expired</span>
                  @else
                    <span class="badge bg-light text-dark rounded-pill px-3 py-1">{{ ucfirst($status) }}</span>
                  @endif
                </div>

                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Created Date</span>
                  <span class="fw-bold">{{ $quotation->created_at ? $quotation->created_at->format('d M, Y') : 'N/A' }}</span>
                </div>

                @if($quotation->valid_until)
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Valid Until</span>
                    <span class="fw-bold text-danger">{{ $quotation->valid_until->format('d M, Y') }}</span>
                  </div>
                @endif

                <hr class="my-3">

                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Subtotal</span>
                  <span class="fw-bold">₹{{ number_format($quotation->subtotal, 2) }}</span>
                </div>

                @if($quotation->discount > 0)
                  <div class="d-flex justify-content-between mb-2 text-success">
                    <span>Discount</span>
                    <span>-₹{{ number_format($quotation->discount, 2) }}</span>
                  </div>
                @endif

                @if($quotation->tax > 0)
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tax</span>
                    <span>₹{{ number_format($quotation->tax, 2) }}</span>
                  </div>
                @endif

                @if($quotation->shipping_charge > 0)
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Shipping</span>
                    <span>₹{{ number_format($quotation->shipping_charge, 2) }}</span>
                  </div>
                @endif

                @if($quotation->packing_charge > 0)
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Packing</span>
                    <span>₹{{ number_format($quotation->packing_charge, 2) }}</span>
                  </div>
                @endif

                <hr class="my-3">

                <div class="d-flex justify-content-between mb-0">
                  <span class="fw-bold fs-5">Total</span>
                  <span class="fw-bold fs-5 text-primary">₹{{ number_format($quotation->total, 2) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
