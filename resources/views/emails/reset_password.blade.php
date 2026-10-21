@include('emails.header')

<!-- Content -->
<div class="p-10">
  <h1 class="text-center" style="font-size: 24px; margin-bottom: 20px;">Reset Your Password</h1>
  <p>Hi <strong>{{ $data['username'] }}</strong>,</p>
  <p>We received a request to reset your password for your <strong>{{ config('const.site_setting.name') }}</strong> account.</p>
  <p>To proceed, please click the button below:</p>
  
  <div class="text-center" style="margin: 30px 0;">
    <a href="{{ $data['url'] }}" class="btn">Reset Password</a>
  </div>
  
  <p style="font-size: 14px; color: #6b7280; margin-bottom: 20px; word-break: break-all;">
    If the button above does not work, copy and paste this link into your web browser: <br>
    <a href="{{ $data['url'] }}">{{ $data['url'] }}</a>
  </p>
  
  <p>If you did not request a password reset, please ignore this email. Your password will remain secure.</p>
  
  <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #edf2f7;">
    <p style="font-size: 14px; color: #9ca3af; margin: 0;">Thanks, <br>The {{ config('const.site_setting.name') }} Team</p>
  </div>
</div>

@include('emails.footer')