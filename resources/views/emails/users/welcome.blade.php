@include('emails.header')




<!-- Content -->
<div class="p-10">
  <h1 class="text-center" style="font-size: 26px; margin-bottom: 24px;">
    Welcome to {{ config('const.site_setting.name') }}! <span style="font-size: 30px;">👋</span>
  </h1>

  <p class="text-center" style="font-size: 16px; margin-bottom: 16px;">
    Hi <strong style="color: #111827;">{{ $user->first_name }}</strong>,
  </p>

  <p class="text-center" style="font-size: 16px; margin-bottom: 32px;">
    Thanks for joining us! we are thrilled to have you as part of our community.
    <strong style="color: #2b6be2;">{{ config('const.site_setting.name') }}</strong> connects you with the best businesses and experts in town.
  </p>

  <div class="text-center" style="margin-bottom: 32px;">
    <a href="{{ route('home') }}" class="btn">Start Exploring</a>
  </div>

  <!-- <div class="divider"></div> -->

  <!-- <p class="text-center font-bold" style="color: #111827; margin-bottom: 20px;">Unlock the full potential:</p>

        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 24px;">
          <tr>
            <td valign="top" width="50%" style="padding: 0 10px 20px;">
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td align="center" style="padding-bottom: 12px;">
                    <div style="background-color: #eff6ff; border-radius: 12px; width: 48px; height: 48px; line-height: 48px; font-size: 24px;">🔍</div>
                  </td>
                </tr>
                <tr>
                  <td align="center">
                    <h3 style="font-size: 15px; margin-bottom: 4px;">Discover</h3>
                    <p style="font-size: 14px; margin: 0; color: #6b7280;">Find top-rated local pros.</p>
                  </td>
                </tr>
              </table>
            </td>
            <td valign="top" width="50%" style="padding: 0 10px 20px;">
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td align="center" style="padding-bottom: 12px;">
                    <div style="background-color: #ecfdf5; border-radius: 12px; width: 48px; height: 48px; line-height: 48px; font-size: 24px;">📅</div>
                  </td>
                </tr>
                <tr>
                  <td align="center">
                    <h3 style="font-size: 15px; margin-bottom: 4px;">Book Instantly</h3>
                    <p style="font-size: 14px; margin: 0; color: #6b7280;">Schedule appointments 24/7.</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table> -->

  <!-- Inline Help Footer -->
  <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #edf2f7;">
    <p style="font-size: 14px; color: #9ca3af; margin: 0;">Need help? Simply reply to this email.</p>
  </div>
</div>


@include('emails.footer')