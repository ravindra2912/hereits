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
          <h5>Hi {{ $data['username'] }},</h5>
          <p>We received a request to reset your password.</p>
          <p style="margin-top:20px;"> To proceed, click the link below: </p>
          <a href="{{ $data['url'] }}">{{ $data['url'] }}</a>
          <p style="margin-top:20px;">Thanks for joining us!</p>
          <p>– The Hereits Team</p>
        </td>
      </tr>


    </table>
  </center>
</body>
@include('emails.footer')