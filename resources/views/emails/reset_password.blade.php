@include('emails.header')

<!-- Body -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td class="p-10" style="padding: 40px;">
      <h2 style="font-size: 24px; margin-bottom: 16px;">Hi {{ $data['username'] ?? 'User' }},</h2>
      <p style="font-size: 16px;">We received a request to reset your password for your Hereits account.</p>
      
      <div class="text-center" style="margin: 32px 0;">
        <a href="{{ $data['url'] }}" class="btn" style="display: inline-block; background-color: #2b6be2; color: #ffffff; padding: 14px 32px; border-radius: 50px; text-decoration: none; font-weight: 700;">Reset Password</a>
      </div>
      
      <p style="font-size: 14px; color: #6b7280; margin-bottom: 24px;">If the button doesn't work, copy and paste this link into your browser:<br>
      <a href="{{ $data['url'] }}" style="color: #2b6be2; word-break: break-all;">{{ $data['url'] }}</a></p>

      <div class="divider" style="border-top: 1px solid #edf2f7; margin: 24px 0;"></div>
      
      <p style="font-size: 16px; margin-bottom: 0;">Thanks,<br><strong>The Hereits Team</strong></p>
    </td>
  </tr>
</table>

@include('emails.footer')