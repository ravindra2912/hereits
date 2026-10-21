@extends('front.layouts.main')

@section('title', 'My Quotations')

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
            <h5 class="m-0 fw-bold"><i class="fas fa-file-invoice me-2 text-primary"></i>My Quotations</h5>
          </div>
          <div class="card-body p-4">
            @if($quotations->count() > 0)
              <div class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Quotation No</th>
                      <th>Business</th>
                      <th>Created Date</th>
                      <th>Valid Until</th>
                      <th>Total</th>
                      <th>Status</th>
                      <th class="text-end">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($quotations as $quotation)
                      <tr>
                        <td>
                          <span class="fw-bold text-primary">{{ $quotation->quotation_no }}</span>
                        </td>
                        <td>{{ $quotation->business->name ?? 'N/A' }}</td>
                        <td>{{ $quotation->created_at ? $quotation->created_at->format('d M, Y') : 'N/A' }}</td>
                        <td>
                          @if($quotation->valid_until)
                            <span class="text-muted small">{{ $quotation->valid_until->format('d M, Y') }}</span>
                          @else
                            <span class="text-muted small">-</span>
                          @endif
                        </td>
                        <td class="fw-bold">₹{{ number_format($quotation->total, 2) }}</td>
                        <td>
                          @php
                            $status = strtolower($quotation->status ?? 'pending');
                          @endphp
                          @if($status === 'ordered')
                            <span class="badge bg-success px-3 py-2 rounded-pill">Ordered</span>
                          @elseif($status === 'inprogress')
                            <span class="badge bg-info px-3 py-2 rounded-pill">In Progress</span>
                          @elseif($status === 'pending')
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">Pending</span>
                          @elseif($status === 'cancel')
                            <span class="badge bg-danger px-3 py-2 rounded-pill">Cancelled</span>
                          @elseif($status === 'expired')
                            <span class="badge bg-secondary px-3 py-2 rounded-pill">Expired</span>
                          @else
                            <span class="badge bg-light text-dark px-3 py-2 rounded-pill">{{ ucfirst($status) }}</span>
                          @endif
                        </td>
                        <td class="text-end">
                          <a href="{{ route('account.quotation.details', $quotation->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                            View Details
                          </a>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              <div class="mt-4 d-flex justify-content-center">
                {{ $quotations->links('pagination::bootstrap-5') }}
              </div>
            @else
              <div class="text-center py-5">
                <div class="mb-4">
                  <i class="fas fa-file-invoice text-muted fs-1"></i>
                </div>
                <h5 class="fw-bold mb-2">No Quotations Found</h5>
                <p class="text-muted mb-4">You have not requested or received any quotations yet.</p>
                <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4">Explore Businesses</a>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
