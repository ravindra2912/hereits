@include('emails.header')

<!-- Content -->
<div class="p-10">
  <h1 class="text-center" style="font-size: 24px; margin-bottom: 24px;">
    You've Been Added as Staff! 🎉
  </h1>

  <p>Hi <strong>{{ $user->first_name }} {{ $user->last_name }}</strong>,</p>

  <p>You have been assigned to <strong>{{ $business->name }}</strong> as a <strong>{{ $role->name }}</strong>.</p>

  @if($tempPassword)
  <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 24px 0;">
    <h3 style="font-size: 16px; margin-bottom: 12px; color: #0f172a;">Your Login Credentials</h3>
    <p style="margin-bottom: 8px; font-size: 14px;"><strong>Email:</strong> {{ $user->email }}</p>
    <p style="margin-bottom: 0; font-size: 14px;"><strong>Password:</strong> <code style="background: #e2e8f0; padding: 2px 6px; border-radius: 4px;">{{ $tempPassword }}</code></p>
  </div>
  @else
  <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 24px 0;">
    <p style="margin-bottom: 0; font-size: 14px; color: #4b5563;">You can use your existing <strong>{{ $user->email }}</strong> account credentials to log in.</p>
  </div>
  @endif

  @if(!empty($permissionsList))
  <div style="margin: 24px 0;">
    <h3 style="font-size: 16px; margin-bottom: 12px; color: #0f172a;">Assigned Permissions</h3>
    <ul style="padding-left: 20px; margin: 0; font-size: 14px; color: #4b5563; line-height: 1.8;">
      @foreach($permissionsList as $permissionName)
        <li>{{ $permissionName }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <p style="margin-bottom: 32px;">Please click the button below to access your account dashboard and get started:</p>

  <div class="text-center" style="margin-bottom: 32px;">
    <a href="{{ $loginUrl }}" class="btn">Log In to Panel</a>
  </div>

  <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #edf2f7;">
    <p style="font-size: 14px; color: #9ca3af; margin: 0;">Thanks, <br>The {{ config('const.site_setting.name') }} Team</p>
  </div>
</div>

@include('emails.footer')
