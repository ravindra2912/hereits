<div class="row">
    @forelse($experts as $expert)
    @php
        $bizSlug = $expert->business->slug ?? ($business->slug ?? '');
        $expertAvailability = $expert->timing ?? isExpertAvailable($expert->id, $expert->business_id);
        $inProgressBooking = $expert->in_progress_booking ?? ($expertAvailability['data'] ?? null);
        $isFavExpert = isFavorite('expert', $expert->id);
    @endphp
    <div class="col-lg-6 mb-4">
        <div class="expert-card p-3 p-md-4 h-100 d-flex flex-column position-relative">
            <!-- Favorite Toggle Button -->
            <button type="button"
                class="btn-toggle-favorite card-fav-btn {{ $isFavExpert ? 'active' : '' }}"
                data-type="expert"
                data-id="{{ $expert->id }}"
                data-business-id="{{ $expert->business_id ?? $expert->business?->id }}"
                title="{{ $isFavExpert ? 'Remove from favorites' : 'Add to favorites' }}">
                <i class="{{ $isFavExpert ? 'fas fa-heart text-danger' : 'far fa-heart text-muted' }}"></i>
            </button>

            <!-- Top Profile Row -->
            <div class="d-flex align-items-start gap-3 mb-3 pe-4">
                <!-- Expert Avatar with Live Status Dot -->
                <div class="position-relative flex-shrink-0">
                    <a href="{{ route('expert', [$bizSlug, $expert->slug]) }}">
                        <img src="{{ getImage($expert->user?->profile, 'expert') }}"
                            alt="{{ $expert->user?->name }}"
                            class="expert-avatar-lg shadow-sm border"
                            loading="lazy">
                    </a>

                    @if($expertAvailability['status'] == 'open')
                        <span class="expert-status-indicator bg-success" data-bs-toggle="tooltip" title="Available"></span>
                    @elseif($expertAvailability['status'] == 'break')
                        <span class="expert-status-indicator bg-warning" data-bs-toggle="tooltip" title="On Break"></span>
                    @else
                        <span class="expert-status-indicator bg-danger" data-bs-toggle="tooltip" title="Closed"></span>
                    @endif
                </div>

                <!-- Info: Name, Title, Badges -->
                <div class="flex-grow-1 overflow-hidden">
                    <h5 class="fw-bold mb-1 text-truncate">
                        <a href="{{ route('expert', [$bizSlug, $expert->slug]) }}" class="text-dark text-decoration-none hover-text-primary">
                            {{ $expert->user?->name }}
                        </a>
                    </h5>

                    @if(!empty($expert->title))
                        <p class="text-muted small mb-2 text-truncate">{{ $expert->title }}</p>
                    @endif

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="badge bg-warning bg-opacity-10 text-dark border border-warning-subtle rounded-pill px-2 py-1 small">
                            <i class="fas fa-star text-warning me-1"></i>{{ number_format((float)($expert->rating ?? 0), 1) }}
                        </span>

                        @if(isset($expert->department) && !empty($expert->department->department_name))
                            <span class="badge bg-light text-primary border rounded-pill px-2 py-1 small text-truncate">
                                <i class="fas fa-layer-group me-1"></i>{{ $expert->department->department_name }}
                            </span>
                        @endif

                        @if($expert->is_appointment_book_with_time_slot)
                            <span class="badge bg-light text-muted border rounded-pill px-2 py-1 small">
                                <i class="far fa-clock me-1"></i>Slot Booking
                            </span>
                        @else
                            <span class="badge bg-light text-muted border rounded-pill px-2 py-1 small">
                                <i class="fas fa-ticket-alt me-1"></i>Token System
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Current Token & Live Queue Status Section -->
            <div class="mb-3">
                @if($expertAvailability['status'] == 'open')
                    @if($inProgressBooking && isset($inProgressBooking->token_number))
                        <div class="expert-token-badge-box d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="token-icon-badge bg-success text-white shadow-sm">
                                    <i class="fas fa-ticket-alt fa-lg"></i>
                                </div>
                                <div>
                                    <div class="token-label-micro text-success">Current Token</div>
                                    <div class="token-value-display text-dark">#{{ $inProgressBooking->token_number }}</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge-live-serving">
                                    <span class="token-pulse-dot"></span> Serving Now
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="expert-token-badge-box is-waiting d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="token-icon-badge bg-light text-primary border shadow-sm">
                                    <i class="fas fa-ticket-alt"></i>
                                </div>
                                <div>
                                    <div class="token-label-micro text-muted">Current Token</div>
                                    <div class="fw-bold text-dark">No Active Token</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge-status-available">
                                    <i class="fas fa-check-circle me-1 text-primary"></i> Available
                                </span>
                            </div>
                        </div>
                    @endif
                @elseif($expertAvailability['status'] == 'break')
                    <div class="expert-token-badge-box is-break d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="token-icon-badge bg-warning text-dark shadow-sm">
                                <i class="fas fa-coffee"></i>
                            </div>
                            <div>
                                <div class="token-label-micro text-warning-emphasis">Status</div>
                                <div class="fw-bold text-dark">On Break</div>
                            </div>
                        </div>
                        <div class="text-end">
                            @if(isset($expertAvailability['data']) && !empty($expertAvailability['data']->start_time))
                                <span class="badge-status-break">
                                    <i class="fas fa-clock me-1"></i> Back at {{ get_time($expertAvailability['data']->start_time) }}
                                </span>
                            @else
                                <span class="badge-status-break">
                                    Resumes Soon
                                </span>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="expert-token-badge-box is-closed d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="token-icon-badge bg-light text-muted border shadow-sm">
                                <i class="fas fa-moon"></i>
                            </div>
                            <div>
                                <div class="token-label-micro text-muted">Status</div>
                                <div class="fw-bold text-muted">Closed Now</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge-status-closed">
                                Closed Today
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Action Buttons Footer -->
            <div class="d-flex align-items-center gap-2 mt-auto pt-2 border-top">
                <a href="{{ route('expert.board', [$bizSlug, $expert->slug]) }}"
                    class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-medium d-flex align-items-center gap-1"
                    title="View Live Queue Board">
                    <i class="fas fa-tv small text-primary"></i>
                    <span>Live Board</span>
                </a>
                <a href="{{ route('expert', [$bizSlug, $expert->slug]) }}"
                    class="btn btn-sm btn-primary rounded-pill px-4 fw-bold ms-auto d-flex align-items-center gap-1 shadow-sm">
                    <span>Book Appointment</span>
                    <i class="fas fa-arrow-right small"></i>
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <div class="text-muted">
            <i class="far fa-calendar-times fa-3x mb-3 opacity-50"></i>
            <h5 class="fw-bold">No specialists found</h5>
            <p class="small">Try clearing search filters or check back later.</p>
        </div>
    </div>
    @endforelse
</div>