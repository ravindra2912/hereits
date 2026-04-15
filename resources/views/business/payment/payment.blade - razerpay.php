@extends('business.layouts.main')
@section('content')
@section('title', 'Payment')
<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">Dashboard</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->


<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class=" text-center">
        <input type="hidden" name="sitename" value="{{ config('const.site_setting.name') }}" />
        <input type="hidden" name="sitelogo" value="{{ config('const.site_setting.logo') }}" />
        <input type="hidden" name="KEY_ID" value="{{ env('RAZORPAY_ENV') == 'live'? env('RAZORPAY_LIVE_KEY'):env('RAZORPAY_TEST_KEY') }}" />
        <input type="hidden" name="name" value="{{$data->name}}" />
        <input type="hidden" name="email" value="{{$data->email}}" />
        <input type="hidden" name="contacts" value="{{$data->contact}}" />
        <input type="hidden" name="amount" value="{{ number_format((float)$data->total, 2, '.', '')}}" />
        <form id="paymentResponceForm" action="{{ route('business.payment.responce') }}" method="post" enctype="multipart/form-data" class="formaction" data-action="redirect" data-tost="false"> @csrf
          <input type="hidden" name="redirectUrl" value="{{$data->redirectUrl}}" />
          <input type="hidden" name="order" value="{{$data->orderid}}" />
          <input type="hidden" name="type" value="{{$data->type}}" />
          <input type="hidden" id="razorpay_payment_id" name="razorpay_payment_id" value="" />
        </form>
        <p>Payment...</p>
        <button class="btn btn-success mr-3 rezorpay-btn">Payment</button>
        <a href="{{$data->redirectUrl}}" class="btn btn-danger">Cancel</a>

      </div>


    </div>

  </div>
</section>

@push('js')
</script>

<script src="{{ asset('rezorpay/jquery.min.js') }}"></script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script src="{{ asset('rezorpay/rezorpay.js') }}"></script>

@endpush



@endsection