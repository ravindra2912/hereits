@extends('expert.layouts.app')



@section('content')

<div class="expert-container">
    <!-- <div style="display:flex; gap:10px; align-items:center;">
        <a href="{{ route('expert.appointments.create') }}" class="btn btn-primary btn-sm" style="text-decoration:none;">+ New</a>
    </div> -->

    <!-- Active Appointment -->
    @if($current)
    <div class="card current-appt">
        <div class="card-header">
            <span>In Progress</span>
            <span style="color:var(--primary)">#{{ $current->token_number }}</span>
        </div>

        <h1 class="customer-name">{{ $current->user_name }}</h1>
        <div class="service-detail">
            <span>{{ $current->department ? $current->department->department_name : 'General' }}</span>
            @if($current->slot_start_time)
            <span>• {{ \Carbon\Carbon::parse($current->slot_start_time)->format('h:i A') }}</span>
            @endif
            <!-- @if($current->user_contact)
            <span>• {{ $current->user_contact }}</span>
            @endif -->
        </div>

        <div style="margin-bottom:10px;">
            @if($current->note)
            <div style="background:#f9fafb; padding:10px; border-radius:8px; width:100%; margin-bottom:15px; border:1px solid #e5e7eb;">
                <span style="font-size:0.8rem; color:var(--text-sub); display:block; margin-bottom:4px;">Client Note:</span>
                <p style="margin:0; font-size:0.95rem;">{{ $current->note }}</p>
            </div>
            @endif

            <div style="width:100%; margin-bottom:15px;">
                <label style="font-size:0.85rem; color:var(--text-sub); display:block; margin-bottom:6px;">Expert Note:</label>
                <textarea id="expert_note_{{ $current->id }}" class="form-input" rows="2" placeholder="Add your notes here...">{{ $current->expert_note }}</textarea>
            </div>

            <!-- Payment Fields (Hidden by default, or shown if setting enabled) -->
            @if($settings->is_appointment_price_required)
            <div style="background:#f3f4f6; padding:15px; border-radius:8px; margin-bottom:15px;">
                <label style="font-size:0.9rem; font-weight:600; margin-bottom:10px; display:block;">Payment Details (Required)</label>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <div>
                        <label style="font-size:0.8rem; display:block; margin-bottom:4px;">Amount</label>
                        <input type="number" id="amount_{{ $current->id }}" class="form-input" step="0.01" placeholder="0.00">
                    </div>
                    <div>
                        <label style="font-size:0.8rem; display:block; margin-bottom:4px;">Payment Type</label>
                        <select id="payment_type_{{ $current->id }}" class="form-input" style="background:white">
                            <option value="Cash">Cash</option>
                            <option value="Online">Online</option>
                        </select>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="actions" style="grid-template-columns: 1fr 1fr 1fr; gap:8px;">
            <button class="btn btn-primary action-btn" data-id="{{ $current->id }}"
                data-status="complete_and_next"
                data-req-price="{{ $settings->is_appointment_price_required ? 1 : 0 }}"
                style="font-size:0.9rem; padding:12px 4px;">
                Complete & Next
            </button>
            <button class="btn btn-success action-btn" data-id="{{ $current->id }}"
                data-status="completed"
                data-req-price="{{ $settings->is_appointment_price_required ? 1 : 0 }}"
                style="font-size:0.9rem; padding:12px 4px;">
                Complete Only
            </button>
            <button class="btn btn-danger action-btn" data-id="{{ $current->id }}" data-status="cancel" style="font-size:0.9rem; padding:12px 4px;">
                Cancel
            </button>
        </div>
    </div>
    @else
    <div class="card" style="text-align:center; padding:40px 20px;">
        <h3 style="color:var(--text-sub); margin:0;">No Active Appointment</h3>
        <p style="color:var(--text-sub); opacity:0.7; font-size:0.9rem;">Select a client from the queue to start</p>
    </div>
    @endif

    <!-- Queue -->
    <div class="card">
        <div class="card-header">
            <span>Queue ({{ $queue->count() }})</span>
            <span>Today</span>
        </div>

        @if($queue->count() > 0)
        @foreach($queue as $item)
        <div class="queue-item">
            <div class="queue-time">
                @if($item->slot_start_time)
                {{ \Carbon\Carbon::parse($item->slot_start_time)->format('h:i A') }}
                @else
                #{{ $item->token_number }}
                @endif
            </div>
            <div class="queue-info">
                <div class="queue-name">{{ $item->user_name }}</div>
                <div class="queue-service">{{ $item->department ? $item->department->department_name : 'General' }}</div>
            </div>
            <div class="queue-action">
                @if(!$current)
                <button class="btn btn-primary btn-sm action-btn" data-id="{{ $item->id }}" data-status="in_progress">
                    Start
                </button>
                @else
                <!-- Optional: Allow switching? No, user rules say "Minimal" -->
                <span style="font-size:0.8rem; color:var(--text-sub); background:#f3f4f6; padding:4px 8px; border-radius:4px;">Waiting</span>
                @endif
            </div>
        </div>
        @endforeach
        @else
        <div class="empty-state">
            No appointments in queue.
        </div>
        @endif
    </div>

    <!-- Stats/Links -->
    <div style="text-align:center; margin-top:30px; margin-bottom: 40px;">
        <span style="color:var(--text-sub); font-size:0.9rem">Completed Today: <b>{{ $completedCount }}</b></span>
        <div style="margin-top:10px;">
            <a href="{{ route('expert.history') }}" style="color:var(--primary); text-decoration:none; font-size:0.9rem;">View History</a>
        </div>
    </div>

    @if(!$current && $queue->count() == 0)
    <script>
        setTimeout(function() {
            window.location.reload();
        }, 60000); // 60 seconds
    </script>
    @endif
</div>
@endsection