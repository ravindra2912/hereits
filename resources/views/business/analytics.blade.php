@extends('business.layouts.main')
@section('title', 'Analytics')
@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-12">
                <x-business-alert :businessDetails="$businessDetails" />
            </div>
            <div class="col-sm-6">
                <h1 class="m-0">Analytics</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Analytics</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->


<section class="content">
    <div class="row">


        @if ($businessSettings->is_ecommerce_system)
        <!-- Products Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('business.product.index') }}" class="text-decoration-none">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row g-0 align-items-center">
                            <div class="col me-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Products</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $businessDetails->allProducts }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-box-seam dash-icon text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endif

        @if ($businessSettings->is_service_system)
        <!-- Services Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('business.service.index') }}" class="text-decoration-none">
                <div class="card border-left-secondary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row g-0 align-items-center">
                            <div class="col me-2">
                                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Services</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $businessDetails->allServices }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-tools dash-icon text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endif

        @if($businessSettings->is_appointment_system)
        <!-- Credit Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('business.credits') }}" class="text-decoration-none">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row g-0 align-items-center">
                            <div class="col me-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Credit</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $businessDetails->businessSetting->credit }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-credit-card dash-icon text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <!-- Experts Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('business.appointment.expert.index') }}" class="text-decoration-none">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row g-0 align-items-center">
                            <div class="col me-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Experts</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $businessDetails->allExperts }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-person-badge dash-icon text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        @if ($businessSettings->is_appointment_with_department)
        <!-- Departments Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('business.appointment.department.index') }}" class="text-decoration-none">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row g-0 align-items-center">
                            <div class="col me-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Departments</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $businessDetails->allDepartments }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-diagram-3 dash-icon text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endif

        <!-- Completed Appointments Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('business.appointment.bookings.index') }}" class="text-decoration-none">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row g-0 align-items-center">
                            <div class="col me-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Completed Appointments</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $businessDetails->complited_count }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-calendar-check dash-icon text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- All Appointments Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('business.appointment.bookings.index') }}" class="text-decoration-none">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row g-0 align-items-center">
                            <div class="col me-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">All Appointments</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $businessDetails->all_count }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-calendar-event dash-icon text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endif

        @if (!$businessSettings->is_ecommerce_system && !$businessSettings->is_service_system && !$businessSettings->is_appointment_system)
        <div class="col-12">
            <div class="alert alert-warning text-center p-5 shadow-sm rounded-4 bg-white border-0">
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10 p-4">
                        <i class="bi bi-exclamation-triangle-fill text-warning display-4"></i>
                    </div>
                </div>
                <h2 class="alert-heading fw-bold mb-3 text-dark">No Active Modules Found</h2>
                <p class="lead text-muted mb-4" style="max-width: 600px; margin: 0 auto;">It looks like your business account doesn't have any active modules (Products, Services, or Appointments) enabled yet. Please contact the administrator to activate these features.</p>
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <a href="{{ route('business.setting.business') }}" class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm hover-scale">
                        <i class="bi bi-gear-fill me-2"></i> Check Settings
                    </a>
                    <a href="mailto:{{ config('const.contact_info.email') }}" class="btn btn-outline-primary px-4 py-2 rounded-pill fw-bold shadow-sm hover-scale">
                        <i class="bi bi-headset me-2"></i> Contact Support
                    </a>
                </div>
            </div>
        </div>
        @endif

    </div>

    @if($businessSettings->is_appointment_system)
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Appointment Analytics</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</section>

@push('js')
<!-- ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(function() {
        /* ChartJS
         * -------
         * Here we will create a few charts using ChartJS
         */

        function charAjax() {
            $.ajax({
                url: "{{ route('business.dashboard.analytics') }}",
                type: "POST",
                data: {
                    '_method': 'post',
                    'id': ''
                },
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                beforeSend: function() {
                    // loader(true)
                },
                success: function(result) {
                    if (result.success) {
                        appointmrntChart(result.data);
                    } else {
                        toastr.error(result.message);
                    }
                    // loader(true)
                },
                error: function(e) {
                    toastr.error('Somthing Wrong');
                    console.log(e);
                    // loader(true)
                }
            });
        }

        charAjax();



        //-------------
        //- BAR CHART -
        //-------------
        function appointmrntChart(data) {

            var areaChartData = {
                labels: data.appointmrntChart.lable,
                datasets: [{
                        label: 'Complited',
                        backgroundColor: 'rgba(60,141,188,0.9)',
                        data: data.appointmrntChart.Complited
                    },
                    {
                        label: 'All',
                        backgroundColor: 'rgba(210, 214, 222, 1)',
                        data: data.appointmrntChart.All
                    },
                ]
            }

            var barChartCanvas = $('#barChart').get(0).getContext('2d')
            var barChartData = $.extend(true, {}, areaChartData)
            var temp0 = areaChartData.datasets[0]
            var temp1 = areaChartData.datasets[1]
            barChartData.datasets[0] = temp1
            barChartData.datasets[1] = temp0

            var barChartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                datasetFill: false,
            }

            new Chart(barChartCanvas, {
                type: 'bar',
                data: barChartData,
                options: barChartOptions
            })
        }

    })
</script>
@endpush


@endsection