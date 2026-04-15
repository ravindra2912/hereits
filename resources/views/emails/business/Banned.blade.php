@include('emails.header')

<!-- Content -->
<div class="p-10">
  <h1 class="text-center" style="font-size: 26px; margin-bottom: 24px; color: #dc2626;">
    Account Suspended ⚠️
  </h1>

  <p class="text-center" style="font-size: 16px; margin-bottom: 16px;">
    Hi <strong style="color: #111827;">{{ $user->first_name }}</strong>,
  </p>

  <p class="text-center" style="font-size: 16px; margin-bottom: 24px; color: #4b5563;">
    We regret to inform you that your business account <strong style="color: #111827;">“{{ $business->name }}”</strong> on Hereits has been suspended.
  </p>

  <div style="background-color: #fef2f2; border: 1px solid #fee2e2; border-radius: 8px; padding: 20px; margin-bottom: 32px; text-align: left;">
    <h4 style="margin: 0 0 8px; color: #991b1b; font-size: 15px;">Why was this done?</h4>
    <p style="margin: 0; color: #7f1d1d; font-size: 14px;">This action is typically taken due to a violation of our terms of service or activity that goes against our platform policies.</p>
  </div>

  <p class="text-center" style="font-size: 16px; margin-bottom: 32px; color: #4b5563;">
    If you believe this was a mistake or would like to appeal the decision, please contact our support team. We value all our users and are happy to work with you to resolve any misunderstandings.
  </p>

  <div class="text-center" style="margin-bottom: 32px;">
    <a href="mailto:{{ config('const.contact_info.email') }}" class="btn" style="background-color: #6b7280; box-shadow: none;">Contact Support</a>
  </div>

  <!-- Inline Help Footer -->
  <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #edf2f7;">
    <p style="font-size: 14px; color: #9ca3af; margin: 0;">Thank you for your understanding.</p>
  </div>
</div>

@include('emails.footer')