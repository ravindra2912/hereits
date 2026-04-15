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
          <p>Your appointment is successfully booked.</p>
          <p style="margin-top:20px;"> Details: </p>
          <ul>
            <li>Appointment for : {{ $apoinment_details->user_name }}</li>
            <li>Provider: {{ $apoinment_details->expert->expert_name }}</li>
            <li>Date: {{ get_date($apoinment_details->booking_date) }}</li>
            <li>Time: {{ get_time($apoinment_details->slot_start_time) }}</li>
            <li>Location: {{$apoinment_details->business->address}}</li>
          </ul>

          <!-- <p>Need to reschedule?:<a href="https://brandbatao.com">click here</a></p> -->
          <p style="margin-top:20px;">Thank you for choosing brandbatao!</p>
          <p>– The brandbatao Team</p>
        </td>
      </tr>


    </table>
  </center>
</body>
@include('emails.footer')