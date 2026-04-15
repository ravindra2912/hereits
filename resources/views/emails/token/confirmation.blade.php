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
          <p>Your token has been successfully generated.</p>
          <p style="margin-top:20px;"> Details: </p>
          <ul>
            <li>Token Number : {{ $apoinment_details->token_number }}</li>
            <li>Appointment for : {{ $apoinment_details->user_name }}</li>
            <li>Service: {{ $apoinment_details->expert->expert_name }}</li>
            <li>Date: {{ get_date($apoinment_details->booking_date) }}</li>
            <li>Estimated Time: [Estimated Time or Position in Queue]</li>
            <li>Location: {{$apoinment_details->business->address}}</li>
          </ul>

          <p style="margin-top:20px;">You’ll be notified as your turn approaches. Please keep your phone handy and arrive a few minutes early.</p>
          <p>– The Hereits Team</p>
        </td>
      </tr>


    </table>
  </center>
</body>
@include('emails.footer')