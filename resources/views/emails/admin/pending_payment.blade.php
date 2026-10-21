@include('emails.header')

<!-- Content -->
<div class="p-10">
  <h1 class="text-center" style="font-size: 24px; margin-bottom: 24px; color: #1e293b;">
    Pending Payment Verification Request ⏳
  </h1>

  <p>Hello Admin,</p>

  <p>A business user has submitted a manual UPI payment for verification. Please review the details below:</p>

  <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 24px 0;">
    <h3 style="font-size: 16px; margin-top: 0; margin-bottom: 12px; color: #0f172a; border-bottom: 1px solid #edf2f7; padding-bottom: 8px;">Business & Plan Details</h3>
    <p style="margin-bottom: 8px; font-size: 14px;"><strong>Business:</strong> {{ $business->name }}</p>
    <p style="margin-bottom: 8px; font-size: 14px;"><strong>Plan Type:</strong> {{ ucfirst($purchase->plan_type) }}</p>
    <p style="margin-bottom: 0; font-size: 14px;"><strong>Amount:</strong> ₹{{ number_format($transaction->amount, 2) }}</p>
  </div>

  <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 24px 0;">
    <h3 style="font-size: 16px; margin-top: 0; margin-bottom: 12px; color: #0f172a; border-bottom: 1px solid #edf2f7; padding-bottom: 8px;">Payment Transaction Details</h3>
    <p style="margin-bottom: 8px; font-size: 14px;"><strong>Transaction ID/UTR:</strong> {{ $transaction->payment_id ?? 'N/A' }}</p>
    <p style="margin-bottom: 0; font-size: 14px;"><strong>Submitted On:</strong> {{ $transaction->created_at ? $transaction->created_at->format('M d, Y H:i A') : now()->format('M d, Y H:i A') }}</p>
  </div>

  @if($transaction->payment_screen_shot)
  <div style="margin: 24px 0; text-align: center;">
    <h3 style="font-size: 16px; margin-bottom: 12px; color: #0f172a; text-align: left;">Payment Screenshot</h3>
    <a href="{{ getImage($transaction->payment_screen_shot) }}" target="_blank" style="display: inline-block; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; padding: 6px; background-color: #ffffff;">
      <img src="{{ getImage($transaction->payment_screen_shot) }}" alt="Payment Screenshot" style="max-width: 100%; max-height: 300px; display: block; border-radius: 6px;">
    </a>
  </div>
  @endif

  <p style="margin-bottom: 32px; margin-top: 24px;">Please review the screenshot and transaction ID against your bank statement, then approve or reject the transaction.</p>

  <div class="text-center" style="margin-bottom: 32px;">
    <a href="{{ route('admin.transactions.show', $transaction->id) }}" class="btn">Verify & Process Payment</a>
  </div>

  <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #edf2f7;">
    <p style="font-size: 14px; color: #9ca3af; margin: 0;">Thanks, <br>The {{ config('const.site_setting.name') }} Automated System</p>
  </div>
</div>

@include('emails.footer')
