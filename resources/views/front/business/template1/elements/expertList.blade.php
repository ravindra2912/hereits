<div class="row">
    @forelse($experts as $expert)
    <div class="col-lg-6 mb-4">
        <a href="{{ route('expert', [$expert->business->slug, $expert->slug]) }}" class="text-decoration-none">
            <div class="card-modern p-3 hover-lift h-100">
                <div class="d-flex align-items-center">
                    <!-- Expert Image & Favorite -->
                    <div class="flex-shrink-0 me-3 position-relative">
                        <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($expert->expert_image, 'expert') }}"
                            alt="{{ $expert->expert_name }}"
                            class="rounded-circle shadow-sm object-fit-cover border border-2 border-light expert-thumb"
                            loading="lazy">

                        <!-- Favorite Button (Left Overlay) -->
                        <button type="button" class="favorite-btn position-absolute top-0 start-0 m-0 rounded-circle border-0 d-flex align-items-center justify-content-center shadow-sm toggle-favorite-btn"
                            data-item-id="{{ $expert->id }}"
                            data-business-id="{{ $expert->business_id }}"
                            data-type="expert"
                            style="width: 26px; height: 26px; background: white; z-index: 10; transition: all 0.3s ease; left: 0px !important; top: 0px !important;">
                            <i class="{{ $expert->is_favorited ? 'fas fa-heart text-danger' : 'far fa-heart text-muted' }}" style="font-size: 0.75rem;"></i>
                        </button>

                        <!-- Availability Dot -->
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
                        <p class="text-muted small mb-2 text-truncate">{{ $expert->title }}</p>
                        @endif

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-star text-warning me-1"></i> {{ $expert->rating }}
                            </span>
                            @if(isset($expert->department) && !empty($expert->department->department_name))
                            <span class="badge badge-info text-truncate badge-maxWidth-100">{{ $expert->department->department_name }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Live Token (Right Side) -->
                    @if($expertAvailability['status'] == 'open' && isset($expertAvailability['data']))
                    <div class="ms-3">
                        <div class="live-token-container shadow-sm">
                            <div class="live-token-label">
                                <span class="live-dot-pulsing"></span> LIVE TOKEN
                            </div>
                            <div class="live-token-number">{{ $expertAvailability['data']->token_number }}</div>
                        </div>
                    </div>
                    @else
                    <div class="ms-3 text-end">
                        <i class="fas fa-chevron-right text-muted opacity-25"></i>
                    </div>
                    @endif
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