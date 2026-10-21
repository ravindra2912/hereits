<div class="modal-header border-bottom-0 pt-4 px-4 bg-light">
    <h5 class="modal-title fw-bold" id="transactionModalLabel">
        <i class="bi bi-wallet2 text-primary me-2"></i> Transaction Details
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body p-4">
    <div class="row g-4">
        <!-- Details Column -->
        <div class="col-md-6">
            <h6 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-info-circle me-1 text-primary"></i> Basic Info</h6>
            <table class="table table-sm table-borderless mb-4">
                <tr>
                    <td class="text-secondary ps-0" width="140">Transaction ID (UTR)</td>
                    <td class="fw-bold text-dark">: 
                        @if($transaction->status == 'pending' && checkPosPermission('edit_transactions'))
                            <span class="badge bg-light text-dark border">Editable Below</span>
                        @else
                            {{ $transaction->transaction_id ?? 'N/A' }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="text-secondary ps-0">Amount</td>
                    <td class="fw-bold text-success">: ₹{{ number_format($transaction->amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-secondary ps-0">Gateway / Method</td>
                    <td class="fw-medium text-dark">: {{ ucfirst(str_replace('_', ' ', $transaction->payment_gateway ?? 'manual_upi')) }}</td>
                </tr>
                <tr>
                    <td class="text-secondary ps-0">Date & Time</td>
                    <td class="text-dark">: {{ $transaction->transaction_date ? date('d M Y, h:i A', strtotime($transaction->transaction_date)) : $transaction->created_at->format('d M Y, h:i A') }}</td>
                </tr>
                <tr>
                    <td class="text-secondary ps-0">Status</td>
                    <td>: 
                        @php
                            $colors = [
                                'pending' => 'bg-warning',
                                'paid' => 'bg-success',
                                'failed' => 'bg-danger',
                            ];
                            $class = $colors[$transaction->status] ?? 'bg-secondary';
                        @endphp
                        <span class="badge rounded-pill {{ $class }} px-3 py-1 small">{{ ucfirst($transaction->status) }}</span>
                    </td>
                </tr>
            </table>

            @if($transaction->type == 'order' && $transaction->order)
            <h6 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-cart3 me-1 text-primary"></i> Order Info</h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <td class="text-secondary ps-0" width="140">Invoice Number</td>
                    <td class="fw-bold text-dark">: #{{ $transaction->order->invoice_number }}</td>
                </tr>
                <tr>
                    <td class="text-secondary ps-0">Customer Name</td>
                    <td class="fw-medium text-dark">: {{ $transaction->order->customer_name }}</td>
                </tr>
                <tr>
                    <td class="text-secondary ps-0">Customer Contact</td>
                    <td class="text-dark">: {{ $transaction->order->customer_contact ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="text-secondary ps-0">Order Total</td>
                    <td class="fw-bold text-primary">: ₹{{ number_format($transaction->order->total, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-secondary ps-0">Payment Status</td>
                    <td>: 
                        @php
                            $order_colors = [
                                'pending' => 'badge bg-warning',
                                'paid' => 'badge bg-success',
                                'failed' => 'badge bg-danger',
                            ];
                            $order_class = $order_colors[$transaction->order->payment_status] ?? 'badge bg-secondary';
                        @endphp
                        <span class="{{ $order_class }} px-2 py-1 small">{{ ucfirst($transaction->order->payment_status) }}</span>
                    </td>
                </tr>
            </table>
            @endif

            @if($transaction->gateway_response)
            <h6 class="fw-bold mt-4 mb-2 text-dark"><i class="bi bi-chat-text me-1 text-primary"></i> Verification History</h6>
            <div class="p-3 bg-light rounded-3 small">
                @if(isset($transaction->gateway_response['verified_at']))
                <div class="mb-1"><strong>Verified At:</strong> {{ date('d M Y, h:i A', strtotime($transaction->gateway_response['verified_at'])) }}</div>
                @endif
                @if(isset($transaction->gateway_response['remarks']) && !empty($transaction->gateway_response['remarks']))
                <div><strong>Remarks/Notes:</strong> {{ $transaction->gateway_response['remarks'] }}</div>
                @endif
            </div>
            @endif
        </div>

        <!-- Screenshot Column -->
        <div class="col-md-6 text-center border-start">
            <h6 class="fw-bold mb-3 text-dark text-start"><i class="bi bi-image me-1 text-primary"></i> Payment Screenshot</h6>
            @if($transaction->transaction_screenshot)
            <div class="p-2 border rounded-3 bg-light">
                <a href="{{ getImage($transaction->transaction_screenshot) }}" target="_blank" title="Click to view full image">
                    <img src="{{ getImage($transaction->transaction_screenshot) }}" class="img-fluid rounded shadow-sm" style="max-height: 350px; object-fit: contain; width: 100%;">
                </a>
                <div class="mt-2 small text-muted"><i class="bi bi-zoom-in"></i> Click image to open full size</div>
            </div>
            @else
            <div class="d-flex flex-column align-items-center justify-content-center border rounded-3 p-5 bg-light h-100" style="min-height: 250px;">
                <i class="bi bi-image-fill text-muted" style="font-size: 3.5rem;"></i>
                <span class="text-muted mt-2 small">No screenshot was uploaded for this transaction.</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Verification Actions Form -->
    @if($transaction->status == 'pending' && checkPosPermission('edit_transactions'))
    <div class="row mt-4 pt-3 border-top">
        <div class="col-12">
            <form id="verificationForm" action="{{ route('pos.transaction.verify', $transaction->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="transaction_id" class="form-label fw-bold text-dark">Transaction ID (UTR) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-3" id="transaction_id" name="transaction_id" value="{{ $transaction->transaction_id }}" placeholder="Enter UTR / Transaction ID">
                </div>
                
                <div class="mb-3">
                    <label for="remarks" class="form-label fw-bold text-dark">Verification Remarks <span class="text-muted small fw-normal">(Optional)</span></label>
                    <textarea class="form-control rounded-3" id="remarks" name="remarks" rows="2" placeholder="Add any confirmation details, reason for rejection, etc."></textarea>
                </div>
                
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="status" value="failed" class="btn btn-danger rounded-pill px-4 fw-semibold">
                        <i class="bi bi-x-circle me-1"></i> Reject & Fail
                    </button>
                    <button type="submit" name="status" value="paid" class="btn btn-success rounded-pill px-4 fw-semibold text-white">
                        <i class="bi bi-check-circle me-1"></i> Approve & Pay
                    </button>
                </div>
            </form>
        </div>
    </div>
    @else
    <div class="modal-footer border-top-0 px-0 pb-0 mt-4">
        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
    </div>
    @endif
</div>
