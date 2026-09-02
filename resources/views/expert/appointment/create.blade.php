@extends('expert.layouts.app')

@section('header')
<a href="{{ route('expert.dashboard') }}" style="color:var(--text-main); text-decoration:none; font-weight:500;">
    <i class="bi bi-chevron-left"></i> Back
</a>
<h2 style="font-size:1.1rem; margin:0;">New Appointment</h2>
<div style="width:50px"></div>
@endsection

@section('content')
<div class="expert-container">
    <div class="card">
        <div class="card-header">
            <span>New Appointment</span>
        </div>
        <form action="{{ route('expert.appointments.store') }}" method="POST" class="formaction" data-action="redirect">
            @csrf

            <div class="mb-3">
                <label class="form-label">Client Name <span class="text-danger">*</span></label>
                <input type="text" name="user_name" class="form-control required">
            </div>

            <div class="mb-3">
                <label class="form-label">Contact Number</label>
                <input type="number" name="user_contact" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" name="booking_date" class="form-control required" value="{{ date('Y-m-d') }}">
            </div>

            @if($expert->is_appointment_book_with_time_slot)
            <div class="mb-3">
                <label class="form-label">Available Slots <span class="text-danger">*</span></label>
                <select name="timeslote" id="timeslote" class="form-select required">
                    <option value="">Select Date First</option>
                </select>
            </div>
            @endif

            <div class="mb-3">
                <label class="form-label">Client Note</label>
                <textarea name="note" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" id="status_selector" class="form-select">
                    <option value="confirmed">Confirmed (Queue)</option>
                    <option value="in_progress">Start Immediately</option>
                    <option value="pending">Pending</option>
                    <option value="completed" selected>Completed</option>
                </select>
            </div>

            @if($settings->is_appointment_price_required)
            <div id="payment_fields" class="p-3 bg-light rounded mb-3" style="display:none;">
                <label class="form-label fw-bold small text-uppercase">Payment Details (Required)</label>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small">Amount</label>
                        <input type="number" name="amount" class="form-control required" step="0.01" placeholder="0.00">
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Payment Type</label>
                        <select name="payment_type" class="form-select required">
                            <option value="Cash">Cash</option>
                            <option value="Online">Online</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label small">Expert Note</label>
                        <textarea name="expert_note" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>
            @endif


            <div class="alert alert-info py-2 px-3 mb-3 small d-flex align-items-center justify-content-between rounded-3 border-0 bg-primary bg-opacity-10 text-primary">
                <span><i class="bi bi-coin me-1"></i> Credit Deduction:</span>
                <span class="fw-bold">{{ number_format($creditDeductionAmount ?? 1, 2) }} Credit(s)</span>
            </div>

            @if($settings->credit > 0)
            <button type="submit" class="btn btn-primary w-100 btn_action">
                <span id="buttonText">Create Appointment</span>
                <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>
            @else
            <button type="button" class="btn btn-primary w-100" disabled>
                <span id="buttonText">Unavailable to Create Appointment</span>
            </button>
            @endif


        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const dateInput = document.querySelector('input[name="booking_date"]');
        const slotSelect = document.getElementById('timeslote');

        if (dateInput && slotSelect) {
            const fetchSlots = async () => {
                const date = dateInput.value;
                if (!date) return;

                slotSelect.innerHTML = '<option value="">Loading...</option>';

                try {
                    const response = await fetch("{{ route('expert.appointments.get.timing') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            booking_date: date
                        })
                    });

                    const slots = await response.json();
                    slotSelect.innerHTML = '<option value="">Select Slot</option>';

                    if (slots.length > 0) {
                        slots.forEach(slot => {
                            const option = document.createElement('option');
                            option.value = slot.time;
                            option.textContent = slot.time;
                            if (!slot.is_available || slot.is_booked) {
                                option.disabled = true;
                                option.textContent += ' (Booked)';
                            }
                            slotSelect.appendChild(option);
                        });
                    } else {
                        slotSelect.innerHTML = '<option value="">No slots available</option>';
                    }
                } catch (error) {
                    console.error('Error fetching slots:', error);
                    slotSelect.innerHTML = '<option value="">Error loading slots</option>';
                }
            };

            dateInput.addEventListener('change', fetchSlots);
            fetchSlots(); // Initial fetch
        }
    });
</script>

@if($settings->is_appointment_price_required)
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const statusSelector = document.getElementById('status_selector');
        const paymentFields = document.getElementById('payment_fields');

        function togglePayment() {
            if (statusSelector.value === 'completed') {
                paymentFields.style.display = 'block';
            } else {
                paymentFields.style.display = 'none';
            }
        }

        statusSelector.addEventListener('change', togglePayment);

        // Run initial check
        togglePayment();
    });
</script>
@endif
@endsection