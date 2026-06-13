@include('emails.header')

<!-- Content -->
<div class="p-10">
  <div style="text-align: center; margin-bottom: 24px;">
    <div style="background-color: #eff6ff; color: #2b6be2; width: 64px; height: 64px; line-height: 64px; border-radius: 50%; font-size: 32px; display: inline-block; margin-bottom: 16px;">
      💼
    </div>
    <h1 style="font-size: 26px; margin-bottom: 8px; color: #111827;">Welcome to the Team!</h1>
  </div>

  <p style="font-size: 16px; margin-bottom: 16px;">
    Hi <strong style="color: #111827;">{{ $user->first_name }}</strong>,
  </p>

  <p style="font-size: 16px; margin-bottom: 24px;">
    You have been assigned the role of <strong style="color: #2b6be2;">{{ $role->name }}</strong> for <strong style="color: #111827;">{{ $business->name }}</strong> on {{ config('const.site_setting.name') }}.
  </p>

  @if($password)
  <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
    <p style="margin: 0; font-size: 14px; color: #374151;">An account was created for you. Your temporary password is:</p>
    <p style="margin: 8px 0 0 0; font-size: 18px; font-weight: bold; color: #111827; letter-spacing: 2px;">{{ $password }}</p>
    <p style="margin: 8px 0 0 0; font-size: 12px; color: #ef4444;">Please change this password after logging in.</p>
  </div>
  @endif

  <p style="font-size: 16px; margin-bottom: 32px;">
    You can log in to your account and access your assigned panels to start collaborating.
  </p>

  <div class="text-center" style="margin-bottom: 32px;">
    @if(isset($role->permissions['business_access']) && $role->permissions['business_access'])
      <a href="{{ route('business.dashboard') }}" class="btn" style="display: inline-block; background-color: #2b6be2; color: #ffffff; padding: 14px 32px; border-radius: 50px; text-decoration: none; font-weight: 700; margin: 5px;">Login to Business Panel</a>
    @endif

    @if(isset($role->permissions['pos_access']) && $role->permissions['pos_access'])
      <a href="{{ route('pos.dashboard') }}" class="btn" style="display: inline-block; background-color: #10b981; color: #ffffff; padding: 14px 32px; border-radius: 50px; text-decoration: none; font-weight: 700; margin: 5px;">Login to POS Panel</a>
    @endif
  </div>

  <div class="divider" style="border-top: 1px solid #edf2f7; margin: 24px 0;"></div>

  <p style="font-size: 14px; color: #6b7280; text-align: center; margin: 0;">
    If you have any questions, please contact the business owner or our support team.
  </p>
</div>

@include('emails.footer')