@include('emails.header')

<!-- Content -->
<div class="p-10">
  <h1 class="text-center" style="font-size: 26px; margin-bottom: 24px;">
    Good News! 🎉
  </h1>

  <p class="text-center" style="font-size: 16px; margin-bottom: 16px;">
    Hi <strong style="color: #111827;">{{ $user->first_name }}</strong>,
  </p>

  <p class="text-center" style="font-size: 16px; margin-bottom: 24px; color: #4b5563;">
    Your business <strong style="color: #2b6be2;">“{{ $business->name }}”</strong> has been approved and is now active on {{ config('const.site_setting.name') }}.
  </p>

  <div style="background-color: #ecfdf5; border: 1px solid #d1fae5; border-radius: 8px; padding: 16px; margin-bottom: 32px; text-align: center;">
    <p style="margin: 0; color: #065f46; font-size: 15px; font-weight: 600;">🚀 You are now live and visible to customers!</p>
  </div>

  <p class="text-center" style="font-size: 16px; margin-bottom: 32px; color: #4b5563;">
    You can now log in to manage your profile, list your services, and start connecting with potential customers.
  </p>

  <div class="text-center" style="margin-bottom: 32px;">
    <a href="{{ route('business.dashboard') }}" class="btn">Go to Dashboard</a>
  </div>

  <!-- Inline Help Footer -->
  <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #edf2f7;">
    <p style="font-size: 14px; color: #9ca3af; margin: 0;">Need help? Simply reply to this email.</p>
  </div>
</div>

@include('emails.footer')