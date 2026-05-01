<div class="row">
    @forelse($experts as $expert)
    <div class="col-lg-6 mb-4">
        <a href="{{ route('expert', [$expert->business->slug, $expert->slug]) }}" class="text-decoration-none">
            <div class="card-modern p-3 hover-lift h-100">
                <div class="d-flex align-items-center">
                    <!-- Expert Image -->
                    <div class="flex-shrink-0 me-3 position-relative">
                        <img src="{{ getImage($expert->expert_image, 'expert') }}"
                            alt="{{ $expert->expert_name }}"
                            class="rounded-circle shadow-sm object-fit-cover border border-2 border-light expert-thumb"
                            loading="lazy">

                        <!-- Simple Online/Status Dot -->
                        <!-- Simple Online/Status Dot -->
                        @php
                        $expertAvailability = isExpertAvailable($expert->id, $expert->business_id);
                        @endphp
                        @if($expertAvailability['status'] == 'open')
                        <div class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle" data-bs-toggle="tooltip" title="Available"></div>
                        @elseif($expertAvailability['status'] == 'break')
                        <div class="position-absolute bottom-0 end-0 p-1 bg-warning border border-white rounded-circle" data-bs-toggle="tooltip" title="On Break"></div>
                        @else
                        <div class="position-absolute bottom-0 end-0 p-1 bg-danger border border-white rounded-circle" data-bs-toggle="tooltip" title="Closed"></div>
                        @endif

                    </div>

                    <!-- Expert Info -->
                    <div class="flex-grow-1 overflow-hidden">
                        <h5 class="fw-bold text-dark mb-1 text-truncate">{{ $expert->expert_name }}</h5>

                        @if(isset($expert->title) && !empty($expert->title))
                        <p class="text-muted small mb-1 text-truncate">{{ $expert->title }}</p>
                        @endif

                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-star text-warning me-1"></i> {{ $expert->rating }}
                            </span>
                            @if(isset($expert->department) && !empty($expert->department->department_name))
                            <span class="badge badge-info text-truncate badge-maxWidth-100">{{ $expert->department->department_name }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Availability/Action (Compact) -->
                    <div class="ms-3 text-end d-flex flex-column align-items-end gap-2">
                        <button type="button" class="favorite-btn rounded-circle border-0 d-flex align-items-center justify-content-center toggle-favorite-btn"
                            data-item-id="{{ $expert->id }}"
                            data-business-id="{{ $expert->business_id }}"
                            data-type="expert"
                            style="width: 32px; height: 32px; background: rgba(0,0,0,0.05); z-index: 10; transition: all 0.3s ease;">
                            <i class="{{ $expert->is_favorited ? 'fas fa-heart text-danger' : 'far fa-heart text-muted' }} fs-6"></i>
                        </button>
                        <span class="btn btn-outline-primary btn-sm rounded-pill fw-bold d-none d-sm-block">
                            Book
                        </span>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <div class="text-muted">
            <i class="far fa-calendar-times fa-3x mb-3 opacity-50"></i>
            <p>No experts available at the moment.</p>
        </div>
    </div>
    @endforelse
</div>