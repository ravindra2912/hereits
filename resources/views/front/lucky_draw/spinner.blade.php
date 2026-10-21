<!DOCTYPE html>
<html lang="en">

@php
    $bizLogo = ($draw->business && ($draw->business->business_logo || $draw->business->business_image))
        ? getImage($draw->business->business_logo ?: $draw->business->business_image)
        : asset('favicon.ico');
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spin & Win - {{ $draw->business->name ?? 'Store' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Meta Tags -->
    <meta name="description" content="{{ $draw->title }} - Exclusive Lucky Draw Campaign by {{ $draw->business->name ?? 'Our Store' }}. Participate to win exciting prizes!">
    <meta name="keywords" content="{{ $draw->title }}, Lucky Draw, Spin and Win, Scratch Card, {{ $draw->business->name ?? 'Store' }}">
    <meta name="author" content="{{ $draw->business->name ?? 'Store' }}">

    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:title" content="{{ $draw->title }} - {{ $draw->business->name ?? 'Store' }}">
    <meta property="og:description" content="Participate in {{ $draw->title }} by {{ $draw->business->name ?? 'Our Store' }} and win exciting rewards!">
    <meta property="og:image" content="{{ $bizLogo }}">
    <meta property="og:type" content="website">

    <!-- Business Favicon -->
    <link rel="icon" type="image/png" href="{{ $bizLogo }}">
    <link rel="shortcut icon" href="{{ $bizLogo }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ $bizLogo }}">

    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Template 1 Master Assets -->
    <link rel="stylesheet" href="{{ asset('assets/front/template1/lucky-draw.css') }}?v={{ filemtime(public_path('assets/front/template1/lucky-draw.css')) }}">
</head>

<body>
    <!-- Background Light Orbs -->
    <div class="bg-glow-orb-1"></div>
    <div class="bg-glow-orb-2"></div>

    <!-- Top Header Bar: Coupon Name (Left) & Business Name (Right) -->
    <header class="scratch-top-header w-100">
        <div class="scratch-header-content d-flex justify-content-between align-items-center mx-auto px-3 px-md-4 py-3">
            <div class="d-flex align-items-center me-2 overflow-hidden">
                <span class="badge badge-gold px-3 py-2 rounded-pill fs-6 fw-bold text-truncate">
                    <i class="bi bi-disc me-1"></i> {{ $draw->title }}
                </span>
            </div>
            <div class="d-flex align-items-center ms-2 text-end overflow-hidden">
                @if($draw->business && ($draw->business->business_logo || $draw->business->business_image))
                    <img src="{{ getImage($draw->business->business_logo ?: $draw->business->business_image) }}" alt="{{ $draw->business->name }}" class="rounded-circle me-2" style="width: 28px; height: 28px; object-fit: cover;">
                @else
                    <i class="bi bi-shop text-warning me-1"></i>
                @endif
                <h5 class="fw-bold text-white mb-0 fs-5 text-truncate">
                    {{ $draw->business->name ?? 'Our Store' }}
                </h5>
            </div>
        </div>
    </header>

    <div class="container py-4 pb-5 d-flex flex-column align-items-center style="position: relative; z-index: 2;">
        <!-- Customer Greeting Pill -->
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill glass-pill shadow-sm">
                <span class="pulse-dot"></span>
                <span class="small fw-medium text-white-50">
                    Hello <strong class="text-white">{{ $entry->customer_name }}</strong>! Spin the Lucky Wheel below to reveal your prize!
                </span>
            </div>
        </div>

        <!-- Main Cards Container -->
        <div class="glass-card p-4 p-md-5 text-center w-100 scratch-container">

            <!-- Status Banner -->
            <div id="scratchInstruction" class="mb-4">
                @if($entry->status === 'scratched')
                    @if($wonPrize)
                    <div class="alert alert-success bg-success-subtle text-success border-0 rounded-pill py-2 px-4 d-inline-block small fw-bold shadow-sm">
                        <i class="bi bi-check-circle-fill me-1"></i> Spinned on {{ $entry->scratched_at ? $entry->scratched_at->format('M d, Y') : 'Today' }}
                    </div>
                    @else
                    <div class="alert alert-warning bg-warning-subtle text-warning border-0 rounded-pill py-2 px-4 d-inline-block small fw-bold shadow-sm">
                        <i class="bi bi-emoji-frown-fill me-1"></i> No Prize Found
                    </div>
                    @endif
                @else
                <p class="text-white-50 small mb-0 fw-semibold">
                    <i class="bi bi-hand-index-thumb me-1 text-warning"></i> Tap <strong class="text-warning">SPIN NOW</strong> to spin the wheel!
                </p>
                @endif
            </div>

            <!-- Spinning Wheel Container -->
            <div class="spinner-wrapper mb-4">
                <svg class="spinner-pointer" id="spinnerPointer" viewBox="0 0 36 44">
                    <polygon points="18,44 0,0 36,0" />
                </svg>

                <!-- 12 Casino LED Ring Bulbs -->
                @for($l = 0; $l < 12; $l++)
                    <div class="led-dot led-dot-{{ $l }}"></div>
                @endfor

                <div class="spinner-outer-ring" id="spinnerRing">
                    <canvas id="wheelCanvas" width="800" height="800"></canvas>
                </div>
                <button class="spinner-center-btn" id="spinWheelBtn" {{ $entry->status === 'scratched' ? 'disabled' : '' }}>
                    @if($entry->status === 'scratched')
                        <span>SPINNED</span>
                    @else
                        <span>SPIN<br>NOW</span>
                    @endif
                </button>
            </div>

            @if($entry->status === 'scratched' && $wonPrize)
            <div class="alert alert-success bg-success-subtle text-success border-0 rounded-4 p-3 d-inline-block mb-3 shadow-sm">
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <img src="{{ getImage($wonPrize->image_path) }}" alt="{{ $wonPrize->name }}" style="width: 50px; height: 50px; object-fit: contain;">
                    <div class="text-start">
                        <small class="text-uppercase fw-bold text-muted d-block" style="font-size: 10px;">Your Won Prize</small>
                        <h6 class="fw-bold mb-0 text-dark">{{ $wonPrize->name }}</h6>
                    </div>
                </div>
            </div>
            @endif

            <!-- Visit Business Store Button -->
            <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 text-center">
                <a href="{{ route('business-details', ['business_slug' => $draw->business->slug]) }}" class="btn btn-outline-light rounded-pill px-4 py-2 text-decoration-none fw-bold shadow-sm glass-pill">
                    <i class="bi bi-shop me-2 text-warning"></i> Visit {{ $draw->business->name ?? 'Store' }}
                </a>
            </div>

        </div>

        <!-- Footer Branding -->
        <div class="text-center mt-4">
            <small class="text-white-50 opacity-75">Powered by {{ config('app.name', 'Shoper') }}</small>
        </div>
    </div>

    <!-- Won Prize Celebration Modal -->
    <div class="modal fade" id="wonPrizeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg text-center p-4 text-dark bg-white">
                <div class="modal-body p-2">
                    <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 84px; height: 84px;">
                        <i class="bi bi-trophy-fill fs-1"></i>
                    </div>
                    <h3 class="fw-extrabold mb-1 text-dark">🎉 Congratulations!</h3>
                    <p class="text-secondary small mb-3">Hello <strong>{{ $entry->customer_name }}</strong>, you won an exclusive prize!</p>
                    
                    <div class="p-3 bg-light rounded-4 mb-4 border text-center shadow-sm">
                        <img id="wonPrizeImg" src="" alt="Won Prize" class="img-fluid mb-2" style="max-height: 120px; object-fit: contain;">
                        <h5 class="fw-bold text-dark mb-0" id="wonPrizeTitle"></h5>
                    </div>

                    <div class="d-grid">
                        <button type="button" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm" data-bs-dismiss="modal">
                            Awesome! Claim Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
    <script src="{{ asset('assets/front/template1/lucky-draw.js') }}?v={{ filemtime(public_path('assets/front/template1/lucky-draw.js')) }}"></script>

    <script>
        $(document).ready(function() {
            let prizes = @json($prizesArray);
            let isScratched = {{ $entry->status === 'scratched' ? 'true' : 'false' }};
            let wonPrizeId = {{ $wonPrize ? $wonPrize->id : 'null' }};
            let scratchedIndex = {{ !is_null($scratchedIndex) ? $scratchedIndex : 'null' }};
            let isSpinning = false;

            let winIndex = 0;
            if (wonPrizeId) {
                let foundIdx = prizes.findIndex(p => p.id == wonPrizeId);
                if (foundIdx !== -1) {
                    winIndex = foundIdx;
                }
            }

            let canvas = document.getElementById('wheelCanvas');
            if (canvas) {
                let ctx = canvas.getContext('2d');
                let numSlices = prizes.length;
                let sliceAngle = (2 * Math.PI) / numSlices;
                
                // Modern vibrant color palette
                let colors = ['#6366f1', '#ec4899', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#f43f5e', '#3b82f6', '#14b8a6', '#d97706'];

                let loadedImages = {};
                prizes.forEach((prize, index) => {
                    let imgUrl = prize.image_url || prize.image_path;
                    if (imgUrl) {
                        let img = new Image();
                        img.crossOrigin = "anonymous";
                        img.onload = function() {
                            loadedImages[index] = img;
                            drawWheel();
                        };
                        img.src = imgUrl;
                    }
                });

                function drawWheel() {
                    let centerX = canvas.width / 2;
                    let centerY = canvas.height / 2;
                    let radius = canvas.width / 2 - 8;

                    ctx.clearRect(0, 0, canvas.width, canvas.height);

                    for (let i = 0; i < numSlices; i++) {
                        let startAngle = i * sliceAngle;
                        let endAngle = startAngle + sliceAngle;

                        // Draw Slice Sector
                        ctx.beginPath();
                        ctx.moveTo(centerX, centerY);
                        ctx.arc(centerX, centerY, radius, startAngle, endAngle);
                        ctx.closePath();
                        ctx.fillStyle = colors[i % colors.length];
                        ctx.fill();
                        ctx.lineWidth = 4;
                        ctx.strokeStyle = '#ffffff';
                        ctx.stroke();

                        ctx.save();
                        ctx.translate(centerX, centerY);
                        ctx.rotate(startAngle + sliceAngle / 2);

                        let img = loadedImages[i];
                        let imgDist = 285;
                        let badgeRadius = 38;

                        // Draw White Circular Badge for Prize Image
                        ctx.save();
                        ctx.shadowColor = 'rgba(0, 0, 0, 0.4)';
                        ctx.shadowBlur = 8;
                        ctx.beginPath();
                        ctx.arc(imgDist, 0, badgeRadius, 0, 2 * Math.PI);
                        ctx.fillStyle = '#ffffff';
                        ctx.fill();
                        ctx.lineWidth = 3;
                        ctx.strokeStyle = 'rgba(255, 255, 255, 0.9)';
                        ctx.stroke();
                        ctx.restore();

                        // Draw Prize Image inside Badge
                        if (img && img.complete && img.naturalWidth !== 0) {
                            try {
                                ctx.save();
                                ctx.beginPath();
                                ctx.arc(imgDist, 0, badgeRadius - 2, 0, 2 * Math.PI);
                                ctx.clip();
                                ctx.drawImage(img, imgDist - 26, -26, 52, 52);
                                ctx.restore();
                            } catch(e) {}
                        } else {
                            ctx.font = '26px Segoe UI, sans-serif';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.fillText('🎁', imgDist, 0);
                        }

                        // Draw Prize Name Text with High-Contrast Shadow
                        ctx.save();
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.font = 'bold 24px Segoe UI, sans-serif';
                        ctx.fillStyle = '#ffffff';
                        ctx.shadowColor = 'rgba(0, 0, 0, 0.85)';
                        ctx.shadowBlur = 8;
                        ctx.shadowOffsetX = 1;
                        ctx.shadowOffsetY = 2;

                        let prizeName = prizes[i].name;
                        if (prizeName.length > 13) {
                            prizeName = prizeName.substring(0, 11) + '..';
                        }
                        ctx.fillText(prizeName, 180, 0);
                        ctx.restore();

                        ctx.restore();
                    }

                    // Outer Gold Border
                    ctx.beginPath();
                    ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
                    ctx.lineWidth = 12;
                    ctx.strokeStyle = '#f59e0b';
                    ctx.stroke();
                }

                drawWheel();

                let sliceDeg = 360 / numSlices;
                let targetSliceCenter = (winIndex + 0.5) * sliceDeg;
                let initialDeg = (270 - targetSliceCenter + 360) % 360;
                let currentDeg = isScratched ? initialDeg : 0;

                if (isScratched) {
                    canvas.style.transform = `rotate(${initialDeg}deg)`;
                }

                $('#spinWheelBtn').on('click', function() {
                    if (isSpinning || isScratched) return;
                    isSpinning = true;
                    let btn = $(this);
                    btn.prop('disabled', true).html('<span>SPINNING...</span>');
                    $('#spinnerRing, .spinner-wrapper').addClass('is-spinning');

                    let startDeg = (currentDeg % 360 + 360) % 360;
                    let targetSliceAngle = (270 - targetSliceCenter + 360) % 360;
                    let totalLaps = 7;
                    let targetDeg = startDeg + (360 * totalLaps) + ((targetSliceAngle - startDeg + 360) % 360);

                    let startTime = null;
                    let duration = 5500;

                    let lastSliceIdx = Math.floor(((270 - (startDeg % 360) + 360) % 360) / sliceDeg);

                    function animateSpin(timestamp) {
                        if (!startTime) startTime = timestamp;
                        let elapsed = timestamp - startTime;
                        let progress = Math.min(elapsed / duration, 1);

                        // Smooth quintic deceleration curve
                        let ease = 1 - Math.pow(1 - progress, 4.5);
                        let nowDeg = startDeg + (targetDeg - startDeg) * ease;
                        canvas.style.transform = `rotate(${nowDeg}deg)`;
                        currentDeg = nowDeg;

                        // Calculate current slice under top pointer (270 deg)
                        let currentSliceIdx = Math.floor(((270 - (nowDeg % 360) + 360) % 360) / sliceDeg);

                        if (currentSliceIdx !== lastSliceIdx) {
                            let speedRatio = 1 - progress;
                            playClickSound(speedRatio);
                            triggerPointerBounce();
                            lastSliceIdx = currentSliceIdx;
                        }

                        if (progress < 1) {
                            requestAnimationFrame(animateSpin);
                        } else {
                            $('#spinnerRing, .spinner-wrapper').removeClass('is-spinning');
                            playWinSound();
                            if (typeof confetti === 'function') {
                                confetti({
                                    particleCount: 150,
                                    spread: 90,
                                    origin: { y: 0.6 }
                                });
                            }

                            $.ajax({
                                url: "{{ route('lucky-draw.claim', ['business_slug' => $draw->business->slug, 'token' => $entry->token]) }}",
                                method: "POST",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    scratched_card_index: winIndex
                                },
                                success: function(res) {
                                    btn.html('<span>SPINNED</span>');
                                    isScratched = true;
                                    if (res.success) {
                                        $('#wonPrizeImg').attr('src', res.prize_image);
                                        $('#wonPrizeTitle').text(res.prize_name);
                                        $('#wonPrizeModal').modal('show');

                                        $('#scratchInstruction').html(`
                                            <div class="alert alert-success bg-success-subtle text-success border-0 rounded-pill py-2 px-4 d-inline-block small fw-bold">
                                                <i class="bi bi-check-circle-fill me-1"></i> Spinned on ${res.scratched_at}
                                            </div>
                                        `);
                                    }
                                },
                                error: function() {
                                    btn.html('<span>SPINNED</span>');
                                }
                            });
                        }
                    }

                    requestAnimationFrame(animateSpin);
                });
            }
        });
    </script>
</body>

</html>
