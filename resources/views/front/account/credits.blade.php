@extends('front.layouts.main')

@section('title', 'My Credits')

@section('content')
<div class="bg-light pb-5 pt-3 pt-lg-5">
  <div class="container">
    <div class="row g-4">
      <!-- User Sidebar -->
      @include('front.account.sidebar')

      <!-- Main Content -->
      <div class="col-lg-9">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
          <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Credit History</h5>
            <div class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold fs-6">
              <i class="fas fa-coins me-1"></i> {{ Auth::user()->available_credits }} Available Credits
            </div>
          </div>
          <div class="card-body p-0">
            @if($transactions->count() > 0)
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="bg-light">
                    <tr>
                      <th class="border-0 px-4 py-3">Date</th>
                      <th class="border-0 px-4 py-3">Description</th>
                      <th class="border-0 px-4 py-3 text-center">Type</th>
                      <th class="border-0 px-4 py-3 text-end">Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($transactions as $transaction)
                      <tr>
                        <td class="px-4 py-3 text-muted small">
                          {{ $transaction->created_at->format('M d, Y h:i A') }}
                        </td>
                        <td class="px-4 py-3">
                          @if($transaction->reference_type == \App\Models\UserCreditTransaction::REF_BUSINESS_SUBSCRIPTION)
                            Referral Bonus 
                            @if($transaction->business)
                              - <a href="{{ route('business-details', $transaction->business->slug) }}" class="fw-bold text-primary text-decoration-none hover-underline">{{ $transaction->business->name }}</a>
                            @else
                              (Business Subscription)
                            @endif
                          @elseif($transaction->reference_type == \App\Models\UserCreditTransaction::REF_PAYOUT)
                            Payout Withdrawal
                          @elseif($transaction->reference_type == \App\Models\UserCreditTransaction::REF_ADMIN_ADJUSTMENT)
                            Admin Adjustment
                          @else
                            {{ ucwords(str_replace('_', ' ', $transaction->reference_type)) }}
                          @endif
                          
                          @if($transaction->reference_id)
                            <small class="text-muted d-block">Ref: #{{ $transaction->reference_id }}</small>
                          @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                          @if($transaction->type == \App\Models\UserCreditTransaction::TYPE_CREDIT)
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1">Credit</span>
                          @else
                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1">Debit</span>
                          @endif
                        </td>
                        <td class="px-4 py-3 text-end fw-bold {{ $transaction->type == \App\Models\UserCreditTransaction::TYPE_CREDIT ? 'text-success' : 'text-danger' }}">
                          {{ $transaction->type == \App\Models\UserCreditTransaction::TYPE_CREDIT ? '+' : '-' }}₹{{ number_format($transaction->amount, 2) }}
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              
              @if($transactions->hasPages())
                <div class="p-4 border-top d-flex justify-content-center">
                  {{ $transactions->links() }}
                </div>
              @endif
            @else
              <div class="text-center py-5 text-muted">
                <i class="fas fa-receipt fs-1 mb-3 text-light"></i>
                <h5 class="fw-bold text-dark">No transactions yet</h5>
                <p class="mb-0">You don't have any credit history to show.</p>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
