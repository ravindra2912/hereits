@extends('expert.layouts.app')

@section('header')
<a href="{{ route('expert.dashboard') }}" style="color:var(--text-main); text-decoration:none; font-weight:500;">
    <i class="bi bi-chevron-left"></i> Back
</a>
<h2 style="font-size:1.1rem; margin:0;">History</h2>
<div style="width:50px"></div>
@endsection

@section('content')
<div class="expert-container">
    <div class="card">
        <div class="card-header" style="flex-direction: column; align-items: flex-start; gap: 15px;">
            <span>Appointment History</span>

            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ route('expert.history', ['filter' => 'today']) }}" class="btn btn-sm" style="background:{{ request('filter', 'today') == 'today' ? 'var(--primary)' : '#e5e7eb' }}; color:{{ request('filter', 'today') == 'today' ? 'white' : 'var(--text-main)' }}; text-decoration:none;">Today</a>
                <!-- <a href="{{ route('expert.history', ['filter' => 'last_7']) }}" class="btn btn-sm" style="background:{{ request('filter') == 'last_7' ? 'var(--primary)' : '#e5e7eb' }}; color:{{ request('filter') == 'last_7' ? 'white' : 'var(--text-main)' }}; text-decoration:none;">Last 7 Days</a>
                <a href="{{ route('expert.history', ['filter' => 'month']) }}" class="btn btn-sm" style="background:{{ request('filter') == 'month' ? 'var(--primary)' : '#e5e7eb' }}; color:{{ request('filter') == 'month' ? 'white' : 'var(--text-main)' }}; text-decoration:none;">This Month</a> -->
                <button onclick="document.getElementById('custom_filter').style.display = 'flex'" class="btn btn-sm" style="background:{{ request('filter') == 'custom' ? 'var(--primary)' : '#e5e7eb' }}; color:{{ request('filter') == 'custom' ? 'white' : 'var(--text-main)' }};">Custom</button>
            </div>

            <form id="custom_filter" method="GET" action="{{ route('expert.history') }}" class="row" style="display:{{ request('filter') == 'custom' ? 'flex' : 'none' }}; gap:10px; width:100%;">
                <input type="hidden" name="filter" value="custom">
                <input type="date" name="start_date" class="form-input" style="padding:6px; font-size:0.9rem;" value="{{ request('start_date') }}" required>
                <input type="date" name="end_date" class="form-input" style="padding:6px; font-size:0.9rem;" value="{{ request('end_date') }}">
                <button type="submit" class="btn btn-primary btn-sm">Go</button>
            </form>
        </div>

        @if(isset($settings) && $settings->is_appointment_price_required)
        <div style="padding: 15px; background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
            <div style="display: flex; justify-content: space-between; font-size: 0.9rem; color: var(--text-main);">
                <div>
                    <span style="color: var(--success); font-weight: 600;">Cash:</span> {{ number_format($metrics['total_cash'], 2) }}
                </div>
                <div>
                    <span style="color: var(--primary); font-weight: 600;">Online:</span> {{ number_format($metrics['total_online'], 2) }}
                </div>
                <div>
                    <span style="font-weight: 700;">Total:</span> {{ number_format($metrics['total_all'], 2) }}
                </div>
            </div>
        </div>
        @endif

        @if($history->count() > 0)
        @foreach($history as $item)
        <div class="queue-item">
            <div class="queue-time" style="width: auto; margin-right: 15px; min-width: 60px;">
                {{ \Carbon\Carbon::parse($item->booking_date)->format('M d') }}
            </div>
            <div class="queue-info">
                <div class="queue-name">{{ $item->user_name }}</div>
                <div class="queue-service" style="font-size:0.8rem;">
                    {{ str_replace('_', ' ', ucfirst($item->status)) }}
                    @if($item->amount > 0)
                    <span style="display:block; color:#10b981; margin-top:2px; font-weight:500;">
                        {{ number_format($item->amount, 2) }} ({{ $item->payment_type }})
                    </span>
                    @endif
                </div>
            </div>
            <div class="queue-action">
                @if($item->status == 'completed')
                <span style="background:var(--success); padding: 4px 8px; border-radius:4px; color:white; font-size:0.7rem">Done</span>
                @elseif($item->status == 'cancel' || $item->status == 'cancel_by_user')
                <span style="background:var(--danger); padding: 4px 8px; border-radius:4px; color:white; font-size:0.7rem">Cancelled</span>
                @else
                <span style="background:var(--text-sub); padding: 4px 8px; border-radius:4px; color:white; font-size:0.7rem">{{ $item->status }}</span>
                @endif
            </div>
        </div>
        @endforeach

        <div style="padding: 20px; text-align:center;">
            <!-- Pagination links usually require Tailwind/Bootstrap, we might just get unstyled HTML. 
                     Keeping it simple or we should wrap it. -->
            {{ $history->links('pagination::simple-default') }}
        </div>
        @else
        <div class="empty-state">No history found.</div>
        @endif
    </div>
</div>
@endsection