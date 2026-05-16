@extends('business.layouts.main')
@section('title', 'Business Timing')
@section('content')

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold">Business Timing</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active">Business Timing</li>
            </ol>
        </nav>
    </div>

    <div class="row g-4">
        @foreach ($timing as $day)
        @php $day = (object)$day; @endphp
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
            <div class="card border-0 shadow-sm rounded-4 h-100 transition-all hover-shadow">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">{{ $day->day }}</h5>
                    <button type="button" class="btn btn-primary btn-sm rounded-circle shadow-sm" onclick="addTiming('{{ $day->day }}')" title="Add Time Slot">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="timing-list mt-2">
                        @forelse ($day->timing as $time)
                        <div class="d-flex justify-content-between align-items-center bg-light rounded-3 p-3 mb-2 border">
                            <div>
                                <span class="fw-bold text-dark d-block" style="font-size: 0.9rem;">
                                    {{ get_time($time->start_time) }} - {{ get_time($time->end_time) }}
                                </span>
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary border-0 rounded-circle me-1" 
                                    onclick="editTiming({{ json_encode($time) }})" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger border-0 rounded-circle btn_delete-{{ $time->id }}" 
                                    onclick="destroy({{ $time->id }})" title="Delete">
                                    <i class="bi bi-trash" id="buttonText"></i>
                                    <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 rounded-3 border-dashed" style="border: 2px dashed #dee2e6;">
                            <i class="bi bi-calendar-x text-muted d-block fs-4 mb-2"></i>
                            <span class="text-muted small fw-medium">Closed</span>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Modal for add/edit timing -->
<div class="modal fade" id="modal-timing" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('business.setting.business.timing.add') }}" id="timing-form" data-action="reload" class="modal-content formaction border-0 shadow-lg rounded-4" method="POST">
            @csrf
            <input type="hidden" name="id" id="timing_id">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="modalLabel text-dark">Add Time Slot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted text-uppercase">Day <span class="text-danger">*</span></label>
                    <select class="form-select required shadow-sm border-0 bg-light" name="day" id="week_day">
                        <option value="">Select Day</option>
                        @foreach (config('const.week_day_name') as $day)
                        <option value="{{ $day }}">{{ $day }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <label class="form-label fw-bold small text-muted text-uppercase">Start Time <span class="text-danger">*</span></label>
                        <input type="time" class="form-control required shadow-sm border-0 bg-light" name="start_time" id="start_time" />
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold small text-muted text-uppercase">End Time <span class="text-danger">*</span></label>
                        <input type="time" class="form-control required shadow-sm border-0 bg-light" name="end_time" id="end_time" />
                    </div>
                </div>

                <div class="mb-2" id="apply_all_wrapper">
                    <div class="form-check form-switch p-3 bg-light rounded-3 shadow-sm border">
                        <input class="form-check-input ms-0 me-3" type="checkbox" name="apply_to_all" id="apply_to_all" value="1">
                        <label class="form-check-label fw-bold text-dark" for="apply_to_all">Apply to all days</label>
                        <small class="text-muted d-block mt-1 ps-5">This will add this time slot to every day of the week.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 btn_action shadow-sm">
                    <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    <span id="buttonText">Save Timing</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
    .form-switch .form-check-input {
        width: 3em;
        height: 1.5em;
    }
    .border-dashed {
        border-style: dashed !important;
    }
</style>

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const timingModalElement = document.getElementById('modal-timing');
    const timingModal = new bootstrap.Modal(timingModalElement);

    function addTiming(day) {
        $('#timing-form')[0].reset();
        $('#timing_id').val('');
        $('#week_day').val(day).prop('readonly', false);
        $('#modalLabel').text('Add Time Slot');
        $('#apply_all_wrapper').removeClass('d-none');
        timingModal.show();
    }

    function editTiming(time) {
        $('#timing-form')[0].reset();
        $('#timing_id').val(time.id);
        $('#week_day').val(time.day);
        $('#start_time').val(time.start_time);
        $('#end_time').val(time.end_time);
        $('#modalLabel').text('Edit Time Slot');
        $('#apply_all_wrapper').addClass('d-none'); // Hide apply to all when editing
        timingModal.show();
    }

    function destroy(id) {
        Swal.fire({
            title: 'Delete Time Slot?',
            text: "Are you sure you want to remove this timing?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel',
            borderRadius: '1rem'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('business.setting.business.timing.remove') }}",
                    type: "POST",
                    data: { id: id },
                    headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
                    beforeSend: function() {
                        $('.btn_delete-' + id + ' #buttonText').addClass('d-none');
                        $('.btn_delete-' + id + ' #loader').removeClass('d-none');
                        $('.btn_delete-' + id).prop('disabled', true);
                    },
                    success: function(result) {
                        if (result.success) {
                            toastr.success(result.message);
                            location.reload();
                        } else {
                            toastr.error(result.message);
                        }
                    },
                    error: function() {
                        toastr.error('Something went wrong');
                        $('.btn_delete-' + id + ' #buttonText').removeClass('d-none');
                        $('.btn_delete-' + id + ' #loader').addClass('d-none');
                        $('.btn_delete-' + id).prop('disabled', false);
                    }
                });
            }
        });
    }
</script>
@endpush