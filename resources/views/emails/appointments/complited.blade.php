<!DOCTYPE html>
@include('emails.header')

<body style="background:#f5f5f5;margin:0;">
  <center>
    <table role="presentation" class="email-container">
      <!-- Header -->
      <tr>
        <td class="header">
          <a href="#"><img src="{{ asset(config('const.site_setting.logo-bg-black')) }}" alt="brandbatao"></a>
        </td>
      </tr>

      <!-- Body -->
      <tr>
        <td class="content">
          <h5>Hi {{ $user_name }},</h5>
          <p>We hope your recent appointment with {{ $apoinment_details->expert->expert_name }} went well!</p>
          <p>We’d love to hear your thoughts to help us improve.</p>
          <p><a href="{{ route('account.booking.details', $apoinment_details->id) }}">Leave a Review</a></p>
          <p style="margin-top:20px;">Thanks for using brandbatao!</p>
          <p>– The brandbatao Team</p>
        </td>
      </tr>


    </table>
  </center>
</body>
@include('emails.footer')