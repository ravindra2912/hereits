<!DOCTYPE html>
@include('emails.header')

<body style="background:#f5f5f5;margin:0;">
  <center>
    <table role="presentation" class="email-container">
      <!-- Header -->
      <tr>
        <td class="header">
          <a href="#"><img src="{{ asset(config('const.site_setting.logo-bg-black')) }}" alt="Hereits"></a>
        </td>
      </tr>

      <!-- Body -->
      <tr>
        <td class="content">
          <h5>Hi {{ $user_name }},</h5>
          <p>Your token #{{ $apoinment_details->token_number }} for {{ $apoinment_details->expert->expert_name }} on {{ get_date($apoinment_details->booking_date) }} has been cancelled.</p>
          <p>If you didn’t request this cancellation, please contact support or book again:</p>
          <p><a href="{{ route('business-details', $apoinment_details->business->slug) }}">Book New Token</a></p>
          <p style="margin-top:20px;">We're sorry for the inconvenience.</p>
          <p>– The Hereits Team</p>
        </td>
      </tr>


    </table>
  </center>
</body>
@include('emails.footer')