<div class="timeline-with-icons ms-2 my-2">
    @forelse($activities as $activity)
    <div class="timeline-item mb-3 position-relative ps-4 {{ $loop->last ? '' : 'border-start' }}" style="{{ $loop->last ? '' : 'border-left: 2px solid #dee2e6 !important;' }}">
        <span class="position-absolute translate-middle p-1 bg-primary border border-light rounded-circle" style="left: -1px; top: 0; width: 10px; height: 10px;"></span>
        <div class="fw-bold text-dark text-uppercase small" style="font-size: 0.8rem;">
            {{ $activity->title }}
            <span class="badge bg-light text-dark border px-2 py-0.5 ms-1 text-lowercase" style="font-size: 0.7rem;">{{ $activity->type }}</span>
        </div>
        <div class="text-muted small mb-1" style="font-size: 0.75rem;">{{ $activity->created_at->format('d M Y, h:i A') }}</div>
        <div class="text-dark small" style="font-size: 0.8rem;">{{ $activity->remark }}</div>
    </div>
    @empty
    <div class="text-center text-muted py-4">
        <i class="bi bi-clock-history fs-3 d-block mb-2 text-secondary"></i>
        No activity history found for this staff member.
    </div>
    @endforelse
</div>