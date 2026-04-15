<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Processing Payment | {{ config('const.site_setting.name') }}</title>

  <!-- Google Fonts - Nunito -->
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 CSS -->
  <link href="{{ asset('assets/common/css/bootstrap.min.css') }}" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="{{ asset('assets/common/css/bootstrap-icons.min.css') }}" rel="stylesheet">
  <!-- Toastr -->
  <link href="{{ asset('assets/common/css/toastr.min.css') }}" rel="stylesheet" />

  <!-- Favicon -->
  <link rel="shortcut icon" href="{{ config('const.site_setting.fevicon') }}" type="image/x-icon">

  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="robots" content="noindex, nofollow">

  <style>
    body {
      font-family: 'Nunito', sans-serif;
      background-color: #f8fbff;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
    }

    .payment-card {
      background: #ffffff;
      border-radius: 30px;
      box-shadow: 0 40px 100px rgba(0, 0, 0, 0.08);
      max-width: 90%;
      width: 95%;
      overflow: hidden;
      border: none;
    }

    @media (max-width: 767px) {
      body {
        height: auto;
        padding-bottom: 40px;
      }

      .payment-card {
        margin-top: 10px;
      }
    }

    @media (min-width: 768px) {
      .border-end-md {
        border-right: 1px solid #edf2f7 !important;
      }
    }

    .qr-container {
      background: #fdfdfd;
      padding: 20px;
      border-radius: 20px;
      display: inline-block;
    }

    .qr-container img {
      width: 200px;
      height: 200px;
    }

    .amount-display {
      border: 1px solid #e2e8f0;
    }

    .form-control-lg {
      border-radius: 12px;
      padding: 12px 20px;
      font-size: 1rem;
      border: 2px solid #edf2f7;
      transition: all 0.3s;
    }

    .form-control-lg:focus {
      border-color: #0d6efd;
      box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
    }

    .btn-primary {
      font-weight: 700;
      letter-spacing: 0.5px;
    }

    .status-text {
      font-weight: 800;
      color: #1a202c;
      letter-spacing: -0.5px;
    }

    .warning-text {
      color: #718096;
    }

    .pulse {
      animation: pulse-animation 2s infinite;
    }

    @keyframes pulse-animation {
      0% {
        opacity: 1;
      }

      50% {
        opacity: 0.6;
      }

      100% {
        opacity: 1;
      }
    }

    /* Custom Screenshot Upload Styles */
    .screenshot-upload-container {
      position: relative;
      margin-bottom: 20px;
    }

    .screenshot-box {
      border: 2px dashed #cbd5e1;
      border-radius: 16px;
      padding: 30px 20px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
      background: #f8fafc;
      overflow: hidden;
      min-height: 160px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .screenshot-box:hover {
      border-color: #0d6efd;
      background: #f0f7ff;
    }

    .screenshot-box i {
      font-size: 2.5rem;
      color: #0d6efd;
      margin-bottom: 10px;
    }

    .screenshot-box p {
      margin: 0;
      font-weight: 600;
      color: #475569;
    }

    .screenshot-box small {
      color: #94a3b8;
    }

    .preview-wrap {
      display: none;
      position: relative;
      width: 100%;
      border-radius: 12px;
      overflow: hidden;
      border: 2px solid #e2e8f0;
    }

    .preview-wrap img {
      width: 100%;
      max-height: 300px;
      object-fit: contain;
      background: #fff;
    }

    .btn-remove-preview {
      position: absolute;
      top: 10px;
      right: 10px;
      background: #ef4444;
      color: white;
      border: none;
      border-radius: 50%;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
      cursor: pointer;
      z-index: 5;
    }

    .btn-remove-preview:hover {
      background: #dc2626;
    }
  </style>
</head>

<body>
  <div class="payment-card shadow-lg">
    <div class="row g-0">
      <!-- Left Panel: QR / Payment Brand -->
      <div class="col-md-5 border-end-md bg-light p-4 p-lg-5 d-flex flex-column align-items-center justify-content-center" style="border-radius: 30px 0 0 30px;">
        <div class="text-center">
          <img src="{{ config('const.site_setting.logo') }}" alt="Logo" style="height: 45px;" class="mb-3">
          <h4 class="status-text mb-0">UPI Payment</h4>
          <p class="warning-text small mt-2">Scan or Click to Pay</p>
        </div>

        <!-- Desktop QR -->
        <div class="d-md-block d-none text-center">
          <div class="qr-container bg-white shadow-sm border mb-3">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($data->upi_url) }}" alt="UPI QR Code" class="img-fluid rounded">
          </div>
          <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill small">
            <i class="bi bi-person-check me-1"></i> {{ config('const.upi_info.payee_name') }}
          </span>
        </div>

        <!-- Mobile Button (Only visible on small screens inside this column or we move it) -->
        <div class="d-md-none w-100 text-center">
          <a href="{{ $data->upi_url }}" class="btn btn-primary btn-lg w-100 py-3 rounded-pill shadow-sm mb-3 pulse">
            <i class="bi bi-wallet2 me-2"></i> Make Payment
          </a>
          <div class="d-flex align-items-center justify-content-center gap-3">
            <img src="{{ asset('assets/images/gpay.png') }}" alt="GPay" style="height: 15px;">
            <img src="{{ asset('assets/images/phonepe.png') }}" alt="PhonePe" style="height: 15px;">
            <img src="{{ asset('assets/images/paytm.png') }}" alt="Paytm" style="height: 15px;">
          </div>
        </div>
      </div>

      <!-- Right Panel: Form & Amount -->
      <div class="col-md-7 p-4 p-lg-5 text-start">
        <div class="amount-display mb-4 p-3 bg-primary bg-opacity-10 rounded-4 border-0">
          <span class="text-primary d-block small fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px;">Amount to Pay</span>
          <h2 class="mb-0 text-dark fw-bolder">₹{{ number_format($data->total, 2) }}</h2>
        </div>

        <form id="upiSubmitForm" action="{{ route('business.payment.upi.submit') }}" method="post" class="formaction" data-action="redirect" data-tost="true" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="order" value="{{ $data->orderid }}">
          <input type="hidden" name="type" value="{{ $data->type }}">
          <input type="hidden" name="redirectUrl" value="{{ $data->redirectUrl }}">

          <div class="mb-4">
            <label for="transaction_id" class="form-label fw-bold small text-secondary">Transaction ID / UTR</label>
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0 border-2 pe-0" style="border-radius: 12px 0 0 12px; border-color: #edf2f7;">
                <i class="bi bi-hash text-muted"></i>
              </span>
              <input type="text" name="transaction_id" id="transaction_id" class="form-control form-control-lg border-start-0 border-2 ps-2" placeholder="Enter 12-digit UTR No." minlength="8" style="border-radius: 0 12px 12px 0; border-color: #edf2f7;">
            </div>

            <div class="text-center my-4 position-relative">
              <hr class="m-0" style="color: #e2e8f0;">
              <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 fw-bold text-muted small" style="letter-spacing: 1px;">OR</span>
            </div>

            <label class="form-label fw-bold small text-secondary">Verify via Screenshot</label>
            <div class="screenshot-upload-container">
              <input type="file" name="payment_screen_shot" id="payment_screen_shot" class="d-none" accept="image/*">

              <div class="screenshot-box shadow-sm" id="uploadTrigger">
                <i class="bi bi-cloud-arrow-up-fill"></i>
                <p>Click to Upload Screenshot</p>
                <small>Image formats: JPG, PNG, WEBP (Max 4MB)</small>
              </div>

              <div class="preview-wrap shadow-sm" id="previewContainer">
                <button type="button" class="btn-remove-preview" id="removePreview" title="Remove image">
                  <i class="bi bi-x"></i>
                </button>
                <img src="" id="imagePreview" alt="Payment Screenshot">
              </div>
            </div>

            <div class="mt-3 p-3 rounded-3 bg-light border-start border-4 border-warning">
              <small class="text-muted d-block" style="line-height: 1.4;">
                <strong>Required:</strong> Please provide either the 12-digit Transaction ID (UTR) or upload a screenshot of your successful payment for verification.
              </small>
            </div>
          </div>

          <button type="submit" class="btn btn-dark btn-lg w-100 py-3 rounded-pill shadow-lg border-0 mb-3 btn_action">
            <i class="bi bi-shield-check me-2" id="buttonText"> Confirm & Submit </i>
            <i class="bi bi-spinner me-2 d-none" id="loader">Submitting...</i>
          </button>
        </form>

        <div class="d-flex align-items-center justify-content-center text-muted small mt-2">
          <i class="bi bi-lock-fill me-2 text-success"></i>
          <span class="fw-bold">100% Secure Transaction</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="{{ asset('assets/common/js/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/common/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/common/js/toastr.min.js') }}"></script>
  <script src="{{ asset('assets/common/js/ajax.js') }}"></script>

  <script>
    $(document).ready(function() {
      // Optional: Add some client-side validation or formatting for transaction ID
      $('#transaction_id').on('input', function() {
        this.value = this.value.replace(/[^0-9a-zA-Z]/g, '');
      });

      // Handle custom upload trigger
      $('#uploadTrigger').on('click', function() {
        $('#payment_screen_shot').click();
      });

      // File change event for preview
      $('#payment_screen_shot').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          // Check file size (optional but good)
          // if (file.size > 4 * 1024 * 1024) {
          //   toastr.error('File size too large. Max limit is 4MB.');
          //   $(this).val('');
          //   return;
          // }

          const reader = new FileReader();
          reader.onload = function(event) {
            $('#imagePreview').attr('src', event.target.result);
            $('#uploadTrigger').hide();
            $('#previewContainer').fadeIn();
          };
          reader.readAsDataURL(file);
        }
      });

      // Remove preview logic
      $('#removePreview').on('click', function() {
        $('#payment_screen_shot').val('');
        $('#previewContainer').hide();
        $('#uploadTrigger').fadeIn();
      });

      // Form validation before submission

    });
  </script>
</body>

</html>