@include('emails.header')

<!-- Content -->
<div class="p-10">
  <div style="text-align: center; margin-bottom: 24px;">
    <div style="background-color: #ecfdf5; color: #10b981; width: 64px; height: 64px; line-height: 64px; border-radius: 50%; font-size: 32px; display: inline-block; margin-bottom: 16px;">
      ✓
    </div>
    <h1 style="font-size: 26px; margin-bottom: 8px; color: #111827;">Payment Successful!</h1>
    <p style="font-size: 16px; color: #6b7280; margin: 0;">Thank you for your purchase.</p>
  </div>

  <p style="font-size: 16px; margin-bottom: 24px;">
    Hi <strong style="color: #111827;">{{ $purchase->business->owner->first_name ?? 'User' }}</strong>,<br><br>
    We've successfully processed your payment for your business <strong style="color: #2b6be2;">{{ $purchase->business->name ?? '' }}</strong>. Your account has been updated accordingly.
  </p>

  <!-- Order Summary -->
  <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; margin-bottom: 32px;">
    <h3 style="font-size: 16px; margin-top: 0; margin-bottom: 16px; color: #374151; text-transform: uppercase; letter-spacing: 0.5px;">Order Summary</h3>
    
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
      <tr>
        <td style="padding-bottom: 12px; color: #6b7280; font-size: 15px;">Order ID</td>
        <td style="padding-bottom: 12px; color: #111827; font-size: 15px; text-align: right; font-weight: 600;">#{{ $purchase->id }}</td>
      </tr>
      <tr>
        <td style="padding-bottom: 12px; color: #6b7280; font-size: 15px;">Item</td>
        <td style="padding-bottom: 12px; color: #111827; font-size: 15px; text-align: right; font-weight: 600;">{{ $purchase->quantity ? $purchase->quantity . ' Credits' : 'Credits' }}</td>
      </tr>
      <tr>
        <td style="padding-bottom: 12px; color: #6b7280; font-size: 15px;">Date</td>
        <td style="padding-bottom: 12px; color: #111827; font-size: 15px; text-align: right; font-weight: 600;">{{ $purchase->created_at->format('M d, Y') }}</td>
      </tr>
      <tr>
        <td style="padding-bottom: 12px; color: #6b7280; font-size: 15px;">Subtotal</td>
        <td style="padding-bottom: 12px; color: #111827; font-size: 15px; text-align: right; font-weight: 600;">₹{{ number_format($purchase->subtotal, 2) }}</td>
      </tr>
      
      @if($purchase->coupon_discount_amount > 0)
      <tr>
        <td style="padding-bottom: 12px; color: #10b981; font-size: 15px;">Coupon Discount</td>
        <td style="padding-bottom: 12px; color: #10b981; font-size: 15px; text-align: right; font-weight: 600;">-₹{{ number_format($purchase->coupon_discount_amount, 2) }}</td>
      </tr>
      @endif
      
      <tr>
        <td style="padding-top: 16px; border-top: 1px solid #e5e7eb; color: #111827; font-size: 16px; font-weight: 800;">Total Amount</td>
        <td style="padding-top: 16px; border-top: 1px solid #e5e7eb; color: #111827; font-size: 18px; text-align: right; font-weight: 800;">₹{{ number_format($purchase->total_amount, 2) }}</td>
      </tr>
    </table>
  </div>

  <div class="text-center" style="margin-bottom: 16px;">
    <a href="{{ route('business.dashboard') }}" class="btn">Go to Dashboard</a>
  </div>
  
  <!-- We don't link directly to PDF inside email without auth usually, but preserving original logic -->
  @if(Route::has('purchase.invoice'))
  <div class="text-center" style="margin-bottom: 32px;">
    <a href="{{ route('purchase.invoice', $purchase->id) }}" style="color: #6366f1; font-weight: 600; text-decoration: underline; font-size: 14px;">Download Invoice PDF</a>
  </div>
  @endif

  <div class="divider"></div>

  <!-- Inline Help Footer -->
  <div style="text-align: center; margin-top: 20px;">
    <p style="font-size: 14px; color: #9ca3af; margin: 0;">If you have any questions about this receipt, simply reply to this email or contact our support team.</p>
  </div>
</div>

@include('emails.footer')