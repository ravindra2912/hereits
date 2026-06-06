@extends('front.layouts.main')

@section('title', 'My Bookings')

@section('content')
<div class="bg-light pb-5 pt-3 pt-lg-5">
    <div class="container">
        <div class="row g-4">
            <!-- User Sidebar -->
            @include('front.account.sidebar')

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                    <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-0">My Bookings</h4>
                            <p class="text-muted small mb-0">Track and manage your upcoming and past appointments</p>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div id="booking-data" class="row g-3">
                            <!-- Data will be loaded here via AJAX -->
                        </div>

                        <div id="data-loader" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mt-2">Loading your bookings...</p>
                        </div>

                        <div id="no-data" class="text-center py-5 d-none">
                            <div class="mb-4">
                                <i class="fas fa-calendar-times text-muted opacity-25" style="font-size: 5rem;"></i>
                            </div>
                            <h5 class="fw-bold">No Bookings Found</h5>
                            <p class="text-muted">You haven't made any appointments yet.</p>
                            <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4 mt-2">Book Now</a>
                        </div>

                        <div id="list-obj" style="height: 10px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    var limit = 10;
    var offset = 0;
    var is_data = true;
    var listAjax = '';

    function getList() {
        if (listAjax != '' || !is_data) return true;

        listAjax = $.ajax({
            type: "get",
            url: "{{ route('account.get.booking') }}",
            data: {
                offset: offset,
                limit: limit
            },
            dataType: "json",
            beforeSend: function() {
                $('#data-loader').removeClass('d-none');
            },
            success: function(res) {
                $('#data-loader').addClass('d-none');

                if (res.counts < limit) {
                    is_data = false;
                }

                if (res.counts == 0 && offset == 0) {
                    $('#no-data').removeClass('d-none');
                } else {
                    $('#booking-data').append(res.list);
                }

                offset += limit;
                listAjax = '';
            },
            error: function(xhr, status, error) {
                $('#data-loader').addClass('d-none');
                toastr.error("Failed to load bookings. Please try again.");
            }
        });
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                getList();
            }
        });
    }, {
        threshold: 1.0
    });

    observer.observe(document.querySelector('#list-obj'));
</script>
<style>
    .badge {
        padding: 0.5em 1em;
        font-weight: 600;
        border-radius: 6px;
    }

    .bg-soft-warning {
        background-color: #fff9e6;
        color: #d4a017;
    }

    .bg-soft-info {
        background-color: #e6f7ff;
        color: #17a2b8;
    }

    .bg-soft-success {
        background-color: #e6ffed;
        color: #28a745;
    }

    .bg-soft-danger {
        background-color: #ffe6e6;
        color: #dc3545;
    }

    .bg-soft-primary {
        background-color: #e6f0ff;
        color: #007bff;
    }
</style>
@endpush
@endsection