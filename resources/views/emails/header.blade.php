<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>{{ config('const.site_setting.name') }}</title>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    /* Base Resets */
    body,
    html {
      margin: 0;
      padding: 0;
      height: 100%;
      width: 100%;
    }

    body {
      background-color: #f3f4f6;
      font-family: 'Nunito', sans-serif;
      color: #4b5563;
      -webkit-text-size-adjust: 100%;
      text-size-adjust: 100%;
      line-height: 1.6;
    }

    /* Container */
    .email-wrapper {
      width: 100%;
      background-color: #f3f4f6;
      padding: 40px 0;
    }

    .email-container {
      max-width: 600px;
      margin: 0 auto;
      background-color: #ffffff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    /* Typography */
    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
      color: #111827;
      margin-top: 0;
      font-weight: 800;
    }

    p {
      margin-top: 0;
      margin-bottom: 1rem;
    }

    a {
      color: #2b6be2;
      text-decoration: none;
      font-weight: 600;
    }

    /* Elements */
    .btn {
      display: inline-block;
      background-color: #2b6be2;
      color: #ffffff !important;
      padding: 12px 32px;
      border-radius: 50px;
      text-decoration: none;
      font-weight: 700;
      box-shadow: 0 4px 6px -1px rgba(43, 107, 226, 0.4);
    }

    .divider {
      border-top: 1px solid #edf2f7;
      margin: 24px 0;
    }

    /* Layout Helpers */
    .text-center {
      text-align: center;
    }

    .p-10 {
      padding: 40px;
    }

    .mb-4 {
      margin-bottom: 16px;
    }

    /* Responsive */
    @media only screen and (max-width: 600px) {
      .email-container {
        width: 100% !important;
        border-radius: 0 !important;
      }

      .p-10 {
        padding: 20px !important;
      }
    }
  </style>
</head>

<body>
  <div class="email-wrapper">
    <div class="email-container">
      <!-- Header -->
      <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td align="center" style="padding: 30px 40px; border-bottom: 1px solid #edf2f7; background-color: #ffffff;">
            <a href="{{ route('home') }}">
              <img src="{{ config('const.site_setting.logo') }}" alt="{{ config('const.site_setting.name') }}" style="height: 48px; width: auto; border: 0; display: block;">
            </a>
          </td>
        </tr>
      </table>