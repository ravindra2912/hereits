@extends('business.layouts.main')
@section('title', 'Edit Appointment')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Appointment</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('business.appointment.bookings.index') }}" class="text-decoration-none">Appointments</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 bg-white">
        <h5 class="m-0 font-weight-bold text-primary">Appointment Details</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('business.appointment.bookings.update', $appontment->id) }}" data-action="redirect" class="formaction" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <!-- Booking Info Section -->
                <div class="col-12 mt-0">
                    <h6 class="text-muted border-bottom pb-2 mb-3">Scheduling Information</h6>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Booking Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control required" min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" name="booking_date" id="booking_date" value="{{ $appontment->booking_date }}" />
                    </div>
                </div>

                @if ($businessSetting->is_appointment_with_department)
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Department <span class="text-danger">*</span></label>
                        <select class="form-select required" name="department_id" id="department_id">
                            <option value="">Select Department</option>
                            @foreach ($departments as $department)
                            <option value="{{ $department->id }}" {{ $appontment->department_id == $department->id ? 'selected' : '' }}>{{ $department->department_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Expert <span class="text-danger">*</span></label>
                        <select class="form-select required" name="expert_id" id="expert_id">
                            <option value="">Select Expert</option>
                            @foreach ($experts as $expert)
                            <option value="{{ $expert->id }}" data-withtime="{{ $expert->is_appointment_book_with_time_slot }}" {{ $appontment->expert_id == $expert->id ? 'selected' : '' }}>{{ $expert->expert_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-4 {{ empty($timeSlots) ? 'd-none' : '' }}" id="time-slot-container">
                    <div class="mb-3">
                        <label class="form-label">Timing <span class="text-danger">*</span></label>
                        <select class="form-select required" name="timeslote" id="timeslote">
                            <option value="">Select Timing</option>
                            @if(!empty($timeSlots))
                            @foreach($timeSlots as $slot)
                            @php
                            // Check if this slot matches the appointment's start time
                            // Format might vary, but usually just comparing strings works if format is consistent
                            $selected = '';
                            if(isset($slot['time']) && \Carbon\Carbon::parse($appontment->slot_start_time)->format('h:i A') == \Carbon\Carbon::parse(explode("-", $slot['time'])[0])->format('h:i A')) {
                            $selected = 'selected';
                            }
                            // Also need to handle "is_booked" but allow if it is the current appointment's slot
                            $disabled = $slot['is_booked'] && $selected == '' ? 'disabled' : '';
                            @endphp
                            <option value="{{ $slot['time'] }}" {{ $disabled }} {{ $selected }}>{{ $slot['time'] }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select required" name="status" id="status">
                            @foreach (config('const.appointment_status') as $status)
                            <option value="{{ $status }}" {{ $appontment->status == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Price Section (Conditional) -->
                <div class="col-12 appointment_price_section {{ $appontment->status == 'completed' && $businessSetting->is_appointment_price_required ? '' : 'd-none' }}">
                    <div class="row g-3 p-3 bg-light rounded border">
                        <div class="col-12 mt-0">
                            <h6 class="text-primary mb-2 small fw-bold">Payment Details</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ currencySymbol() }}</span>
                                <input type="number" step="0.01" class="form-control" name="amount" id="amount" placeholder="0.00" value="{{ $appontment->amount ?? '' }}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="payment_type" id="payment_type">
                                <option value="Cash" {{ ($appontment->payment_type ?? '') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                <option value="Online" {{ ($appontment->payment_type ?? '') == 'Online' ? 'selected' : '' }}>Online</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- User Info Section -->
                <div class="col-12 mt-4">
                    <h6 class="text-muted border-bottom pb-2 mb-3">Customer Information</h6>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">User Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control required" name="user_name" placeholder="Full Name" value="{{ $appontment->user_name }}" />
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">User Contact <span class="text-danger">*</span></label>
                        <input type="text" class="form-control required" name="user_contact" placeholder="Phone Number" value="{{ $appontment->user_contact }}" />
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Customer Note</label>
                        <textarea class="form-control" name="note" rows="3" placeholder="Any special requests or instructions...">{{ $appontment->note }}</textarea>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Expert Note</label>
                        <textarea class="form-control" name="expert_note" rows="3" placeholder="Internal notes for the expert...">{{ $appontment->expert_note }}</textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 border-top pt-3">
                <button class="btn btn-light border" type="button" onclick="history.back()">Cancel</button>
                <button class="btn btn-primary btn_action px-4" type="submit">
                    <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    <span id="buttonText">Update Appointment</span>
                </button>
            </div>

        </form>
    </div>
</div>

@endsection

@push('js')
<script>
    const isPriceRequired = "{{ $businessSetting->is_appointment_price_required ? 'true' : 'false'}}";

    function togglePriceFields() {
        if ($('#status').val() === 'completed' && isPriceRequired) {
            $('.appointment_price_section').removeClass('d-none');
            $('#amount, #payment_type').addClass('required');
        } else {
            $('.appointment_price_section').addClass('d-none');
            $('#amount, #payment_type').removeClass('required');
        }
    }

    $('#status').on('change', togglePriceFields);

    // get professionals on department id
    $('#department_id').on('change', function() {
        $.ajax({
            type: "POST",
            url: "{{ route('business.appointment.bookings.get.expert') }}",
            data: {
                department_id: $(this).val()
            },
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('#expert_id').html('<option value="">Loading ...</option>');
                $('#timeslote').html('<option value="">Select Timing</option>');
            },
            success: function(experts) {
                $('#expert_id').html('<option value="">Select Expert</option>');
                $.each(experts, function(index, item) {
                    $('#expert_id').append('<option value="' + item.id + '" data-withtime="' + item.is_appointment_book_with_time_slot + '">' + item.expert_name + '</option>');
                });
            }
        });
    });

    // get professional time slots
    $('#expert_id, #booking_date').on('change', function() {
        $('#timeslote').html('<option value="">Select Timing</option>');
        let expertOption = $('#expert_id option:selected');
        let withTime = expertOption.data('withtime');

        // Check local value first if undefined from trigger event (though usually it works from DOM)
        if (typeof withTime === 'undefined' && $('#expert_id').val() != '') {
            // Fallback if needed, but 'data-withtime' should be there.
        }

        if (!withTime && withTime !== 0 && withTime !== '0') {
            // If undefined or false-ish but not explicit 0, maybe hide? 
            // Actually checking if it is false (0).
        }

        // Logic from create:
        if (!withTime) {
            $('#time-slot-container').addClass('d-none');
        } else {
            $('#time-slot-container').removeClass('d-none');
        }

        if ($('#booking_date').val() == '' || $('#expert_id').val() == '' || withTime == 0) {
            return;
        }

        $.ajax({
            type: "POST",
            url: "{{ route('business.appointment.bookings.get.expert.timing') }}",
            data: {
                expert_id: $('#expert_id').val(),
                date: $('#booking_date').val(),
                appointment_id: "{{ $appontment->id }}" // Pass ID to exclude current booking from 'booked' check if needed (though API might not use it yet, controller helper does)
            },
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('#timeslote').html('<option value="">Loading ...</option>');
            },
            success: function(slots) {
                $('#timeslote').html('<option value="">Select Timing</option>');
                $.each(slots, function(index, item) {
                    var disable = item.is_booked ? 'disabled' : '';
                    // If this is the current slot we are editing, we might want to select it or ensure it's enabled.
                    // The API helper should handle 'is_booked' logic involving the current ID if passed.
                    // Let's assume standard logic:
                    $('#timeslote').append('<option value="' + item.time + '" ' + disable + '>' + item.time + '</option>');
                });
            }
        });
    });

    $(document).ready(function() {
        togglePriceFields();
        // Trigger logic to handle initial view state (like showing/hiding timeslot container based on initial expert selection)
        let expertOption = $('#expert_id option:selected');
        let withTime = expertOption.data('withtime');
        if (!withTime) {
            $('#time-slot-container').addClass('d-none');
        } else {
            $('#time-slot-container').removeClass('d-none');
        }
    });
</script>
@endpush