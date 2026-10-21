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
    <title>Lucky Scratch & Win - {{ $draw->business->name ?? 'Store' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Meta Tags -->
    <meta name="description" content="{{ $draw->title }} - Exclusive Lucky Draw Scratch Campaign by {{ $draw->business->name ?? 'Our Store' }}. Scratch to reveal your prize!">
    <meta name="keywords" content="{{ $draw->title }}, Lucky Draw, Scratch Card, Scratch and Win, {{ $draw->business->name ?? 'Store' }}">
    <meta name="author" content="{{ $draw->business->name ?? 'Store' }}">

    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:title" content="{{ $draw->title }} - {{ $draw->business->name ?? 'Store' }}">
    <meta property="og:description" content="Participate in {{ $draw->title }} by {{ $draw->business->name ?? 'Our Store' }} and scratch to win rewards!">
    <meta property="og:image" content="{{ $bizLogo }}">
    <meta property="og:type" content="website">

    <!-- Business Favicon -->
    <link rel="icon" type="image/png" href="{{ $bizLogo }}">
    <link rel="shortcut icon" href="{{ $bizLogo }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ $bizLogo }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Template 1 Master Assets -->
    <link rel="stylesheet" href="{{ asset('assets/front/template1/lucky-draw.css') }}?v={{ filemtime(public_path('assets/front/template1/lucky-draw.css')) }}">
</head>

<body>
    <!-- Top Header Bar: Coupon Name (Left) & Business Name (Right) -->
    <header class="scratch-top-header w-100">
        <div class="scratch-header-content d-flex justify-content-between align-items-center mx-auto px-3 px-md-4 py-3">
            <div class="d-flex align-items-center me-2 overflow-hidden">
                <span class="badge badge-gold px-3 py-2 rounded-pill fs-6 fw-bold text-truncate">
                    <i class="bi bi-ticket-perforated-fill me-1"></i> {{ $draw->title }}
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

    <div class="container py-4 pb-5 d-flex flex-column align-items-center">
        <!-- Customer Greeting -->
        <div class="text-center mb-4">
            <p class="text-secondary small mb-0">Hello <strong>{{ $entry->customer_name }}</strong>! Scratch ANY card below to reveal your prize!</p>
        </div>

        <!-- Main Cards Container -->
        <div class="glass-card p-3 p-md-5 text-center w-100 scratch-container">

            <!-- Status Banner -->
            <div id="scratchInstruction" class="mb-4">
                @if($entry->status === 'scratched')
                    @if($wonPrize)
                    <div class="alert alert-success bg-success-subtle text-success border-0 rounded-pill py-2 px-4 d-inline-block small fw-bold">
                        <i class="bi bi-check-circle-fill me-1"></i> Scratched on {{ $entry->scratched_at ? $entry->scratched_at->format('M d, Y') : 'Today' }}
                    </div>
                    @else
                    <div class="alert alert-warning bg-warning-subtle text-warning border-0 rounded-pill py-2 px-4 d-inline-block small fw-bold">
                        <i class="bi bi-emoji-frown-fill me-1"></i> No Prize Found
                    </div>
                    @endif
                @else
                <p class="text-secondary small mb-0 fw-bold">
                    <i class="bi bi-hand-index-thumb me-1 text-warning"></i> Pick and scratch ANY card below!
                </p>
                @endif
            </div>

            <!-- Loading Indicator until canvas covers finish drawing -->
            @if($entry->status === 'pending')
            <div id="cardLoader" class="py-5 text-center">
                <div class="spinner-border text-warning mb-3" style="width: 2.8rem; height: 2.8rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h6 class="fw-bold text-white mb-1">Loading Scratch Cards...</h6>
                <p class="text-secondary small mb-0">Preparing your lucky prizes</p>
            </div>
            @endif

            <!-- Cards Grid -->
            <div class="cards-grid mb-3" id="cardsGrid" style="{{ $entry->status === 'pending' ? 'display: none;' : '' }}">
                @php
                $isScratched = ($entry->status === 'scratched');
                $activeWinnerSlot = $scratchedIndex ?? 0;
                @endphp

                @foreach($prizesArray as $index => $prizeItem)
                @php
                $isCardWinner = ($isScratched && $index == $activeWinnerSlot);
                @endphp
                <div class="scratch-card-box" data-card-index="{{ $index }}" data-prize-id="{{ $prizeItem->id }}">
                    <!-- Prize Layer Underneath -->
                    <div class="prize-layer {{ $isScratched && !$isCardWinner ? 'disabled-card' : '' }}" id="prizeLayer_{{ $index }}">
                        <img src="{{ $prizeItem->image_url }}"
                            alt="{{ $prizeItem->name }}"
                            class="prize-img mb-2 {{ $isCardWinner ? 'pop-in' : '' }}"
                            id="prizeImg_{{ $index }}">
                        <h6 class="fw-bold text-dark mb-1 text-truncate w-100" id="prizeTitle_{{ $index }}">
                            {{ $prizeItem->name }}
                        </h6>
                        <span class="badge {{ $isCardWinner ? ($wonPrize ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning') : 'd-none' }} rounded-pill px-3 py-1 mt-1 small" id="prizeBadge_{{ $index }}">
                            @if($isScratched && $isCardWinner)
                                {{ $wonPrize ? '🎉 Winner' : 'No Prize Found' }}
                            @endif
                        </span>
                    </div>

                    <!-- Canvas Overlay (If Pending) -->
                    @if(!$isScratched)
                    <canvas class="scratch-canvas" id="canvas_{{ $index }}" data-card-index="{{ $index }}" width="220" height="240"></canvas>
                    @endif
                </div>
                @endforeach
            </div>

            <!-- Visit Business Store Button -->
            <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 text-center">
                <a href="{{ route('business-details', ['business_slug' => $draw->business->slug]) }}" class="btn btn-outline-light rounded-pill px-4 py-2 text-decoration-none fw-bold shadow-sm">
                    <i class="bi bi-shop me-2 text-warning"></i> Visit {{ $draw->business->name ?? 'Store' }}
                </a>
            </div>

        </div>

        <!-- Footer Branding -->
        <div class="text-center mt-4">
            <small class="text-secondary opacity-75">Powered by {{ config('app.name', 'Shoper') }}</small>
        </div>
    </div>

    <!-- Right Side Floating Scroll Bar for Mobile/Touch & Desktop Navigation -->
    <div id="floatingScrollHelper" class="floating-scroll-helper text-center text-white d-flex flex-column align-items-center justify-content-center" title="Hold & drag to scroll page">
        <i class="bi bi-chevron-up small text-warning mb-1"></i>
        <i class="bi bi-arrows-vertical fs-6 text-white my-1"></i>
        <span class="scroll-text text-uppercase fw-bold opacity-90 text-warning">Scroll</span>
        <i class="bi bi-chevron-down small text-warning mt-1"></i>
    </div>

    <!-- Won Prize Data for JS Injection -->
    <script>
        window.WON_PRIZE_DATA = {
            prize_id: "{{ $wonPrize ? $wonPrize->id : 0 }}",
            name: "{{ $wonPrize ? addslashes($wonPrize->name) : 'Prize Revealed!' }}",
            image: "{{ getImage($wonPrize ? $wonPrize->image_path : '') }}"
        };
    </script>

    <!-- Won Prize Celebration Modal -->
    <div class="modal fade" id="wonPrizeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow text-center p-4 text-dark bg-white">
                <div class="modal-body p-2">
                    <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-trophy-fill fs-1"></i>
                    </div>
                    <h3 class="fw-bold mb-1 text-dark">🎉 Congratulations!</h3>
                    <p class="text-secondary small mb-3">Hello <strong>{{ $entry->customer_name }}</strong>, you won an exclusive prize!</p>
                    
                    <div class="p-3 bg-light rounded-4 mb-4 border text-center">
                        <img id="wonPrizeImg" src="" alt="Won Prize" class="img-fluid mb-2" style="max-height: 120px; object-fit: contain;">
                        <h5 class="fw-bold text-dark mb-0" id="wonPrizeTitle"></h5>
                    </div>

                    <div class="d-grid">
                        <button type="button" class="btn btn-primary btn-lg rounded-pill fw-bold" data-bs-dismiss="modal">
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

    @if($entry->status === 'pending')
    <script>
        window.WON_PRIZE_DATA = {
            prize_id: "{{ $wonPrize ? $wonPrize->id : 0 }}",
            name: @json($wonPrize ? $wonPrize->name : 'No Prize Found'),
            image: @json(getImage($wonPrize ? $wonPrize->image_path : ''))
        };

        let cheerBuffer = null;

        function getAudioContext() {
            if (!audioCtx) {
                const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                if (AudioContextClass) {
                    audioCtx = new AudioContextClass();
                }
            }
            return audioCtx;
        }

        // Load & decode cheers.mp3 using Web Audio API buffer
        function loadCheerSound() {
            const ctx = getAudioContext();
            if (!ctx) return;

            fetch("{{ asset('assets/front/template1/sound/cheers.mp3') }}")
                .then(response => response.arrayBuffer())
                .then(arrayBuffer => ctx.decodeAudioData(arrayBuffer))
                .then(decodedBuffer => {
                    cheerBuffer = decodedBuffer;
                })
                .catch(err => {
                    console.log("Cheer audio buffer load error:", err);
                });
        }

        // Resume AudioContext on any user gesture
        function unlockAudioContext() {
            const ctx = getAudioContext();
            if (ctx && ctx.state === 'suspended') {
                ctx.resume().catch(() => {});
            }
        }

        ['touchstart', 'touchend', 'mousedown', 'pointerdown', 'click'].forEach(evt => {
            window.addEventListener(evt, unlockAudioContext, { capture: true, passive: true });
        });

        // Preload cheer audio buffer
        loadCheerSound();

        function initMultiScratchCards() {
            const cardLoader = document.getElementById("cardLoader");
            const cardsGrid = document.getElementById("cardsGrid");

            if (cardLoader && cardsGrid) {
                cardLoader.style.display = "none";
                cardsGrid.style.display = "grid";
            }

            const canvases = document.querySelectorAll(".scratch-canvas");
            if (!canvases.length) return;

            let globalClaimed = false;
            let activeScratchCardIndex = null;

            canvases.forEach(canvas => {
                const rect = canvas.getBoundingClientRect();
                if (rect.width > 0 && rect.height > 0) {
                    canvas.width = Math.round(rect.width);
                    canvas.height = Math.round(rect.height);
                }

                const ctx = canvas.getContext("2d", { willReadFrequently: true });
                const width = canvas.width;
                const height = canvas.height;
                const cardIndex = parseInt(canvas.getAttribute("data-card-index"));

                let isDrawing = false;
                let lastX = 0;
                let lastY = 0;

                // Draw Gold Overlay Cover
                function drawCover() {
                    const grad = ctx.createLinearGradient(0, 0, width, height);
                    grad.addColorStop(0, '#f59e0b');
                    grad.addColorStop(0.5, '#fbbf24');
                    grad.addColorStop(1, '#d97706');

                    ctx.fillStyle = grad;
                    ctx.fillRect(0, 0, width, height);

                    const titleFont = Math.max(13, Math.floor(width / 11));
                    const subFont = Math.max(10, Math.floor(width / 16));

                    // High contrast drop shadow & solid white text
                    ctx.shadowColor = "rgba(0, 0, 0, 0.5)";
                    ctx.shadowBlur = 6;
                    ctx.shadowOffsetY = 2;
                    ctx.fillStyle = "#ffffff";

                    ctx.font = "bold " + titleFont + "px sans-serif";
                    ctx.textAlign = "center";
                    ctx.fillText("✨ SCRATCH ME ✨", width / 2, height / 2 - 4);

                    ctx.font = "bold " + subFont + "px sans-serif";
                    ctx.fillText("TAP & DRAG", width / 2, height / 2 + titleFont + 4);

                    // Reset shadow for texture dots
                    ctx.shadowColor = "transparent";
                    ctx.shadowBlur = 0;
                    ctx.shadowOffsetY = 0;

                    // Texture dots
                    ctx.fillStyle = "rgba(255, 255, 255, 0.25)";
                    for (let i = 15; i < width; i += 25) {
                        for (let j = 15; j < height; j += 25) {
                            ctx.beginPath();
                            ctx.arc(i, j, 2, 0, Math.PI * 2);
                            ctx.fill();
                        }
                    }
                }

                drawCover();

                function getPos(e) {
                    const rect = canvas.getBoundingClientRect();
                    const scaleX = canvas.width / rect.width;
                    const scaleY = canvas.height / rect.height;

                    let clientX = e.clientX;
                    let clientY = e.clientY;

                    if (e.touches && e.touches.length > 0) {
                        clientX = e.touches[0].clientX;
                        clientY = e.touches[0].clientY;
                    }

                    return {
                        x: (clientX - rect.left) * scaleX,
                        y: (clientY - rect.top) * scaleY
                    };
                }

                function lockOtherCards(chosenIndex) {
                    canvases.forEach(c => {
                        const idx = parseInt(c.getAttribute("data-card-index"));
                        if (idx !== chosenIndex) {
                            c.style.pointerEvents = "none";
                        }
                    });
                }

                // Immediately swap the underlying card content of chosenIndex to show wonPrize BEFORE any scratching is visible!
                function swapWonPrizeToCard(scratchedIndex) {
                    const won = window.WON_PRIZE_DATA || {};
                    if (!won.prize_id || won.prize_id == "0") return;
                    let originalWonIndex = null;

                    document.querySelectorAll(".scratch-card-box").forEach(box => {
                        const idx = parseInt(box.getAttribute("data-card-index"));
                        const prizeId = box.getAttribute("data-prize-id");
                        if (prizeId == won.prize_id) {
                            originalWonIndex = idx;
                        }
                    });

                    if (originalWonIndex !== null && originalWonIndex !== scratchedIndex) {
                        const scratchedImg = document.getElementById("prizeImg_" + scratchedIndex);
                        const scratchedTitle = document.getElementById("prizeTitle_" + scratchedIndex);
                        const scratchedBox = document.querySelector(`.scratch-card-box[data-card-index="${scratchedIndex}"]`);

                        const origImg = document.getElementById("prizeImg_" + originalWonIndex);
                        const origTitle = document.getElementById("prizeTitle_" + originalWonIndex);
                        const origBox = document.querySelector(`.scratch-card-box[data-card-index="${originalWonIndex}"]`);

                        if (scratchedImg && origImg && scratchedTitle && origTitle) {
                            const tempSrc = scratchedImg.src;
                            scratchedImg.src = origImg.src;
                            origImg.src = tempSrc;

                            const tempTitle = scratchedTitle.innerText;
                            scratchedTitle.innerText = origTitle.innerText;
                            origTitle.innerText = tempTitle;

                            const tempPrizeId = scratchedBox.getAttribute("data-prize-id");
                            scratchedBox.setAttribute("data-prize-id", origBox.getAttribute("data-prize-id"));
                            origBox.setAttribute("data-prize-id", tempPrizeId);
                        }
                    }
                }

                function startScratch(e) {
                    if (globalClaimed) return;

                    // Unlock Web Audio API on user touch/click gesture
                    unlockAudioContext();

                    if (activeScratchCardIndex === null) {
                        activeScratchCardIndex = cardIndex;
                        lockOtherCards(cardIndex);
                        swapWonPrizeToCard(cardIndex);
                    } else if (activeScratchCardIndex !== cardIndex) {
                        return;
                    }

                    isDrawing = true;
                    const pos = getPos(e);
                    lastX = pos.x;
                    lastY = pos.y;
                    scratchDot(pos.x, pos.y);
                }

                function moveScratch(e) {
                    if (globalClaimed || !isDrawing || activeScratchCardIndex !== cardIndex) return;
                    const pos = getPos(e);
                    scratchLine(lastX, lastY, pos.x, pos.y);
                    lastX = pos.x;
                    lastY = pos.y;
                }

                function stopScratch() {
                    if (isDrawing) {
                        isDrawing = false;
                        checkCardPercentage(true);
                    }
                }

                function scratchDot(x, y) {
                    ctx.globalCompositeOperation = "destination-out";
                    ctx.beginPath();
                    ctx.arc(x, y, 22, 0, Math.PI * 2);
                    ctx.fill();
                    checkCardPercentage(false);
                }

                function scratchLine(fromX, fromY, toX, toY) {
                    ctx.globalCompositeOperation = "destination-out";
                    ctx.lineWidth = 44;
                    ctx.lineCap = "round";
                    ctx.lineJoin = "round";
                    ctx.beginPath();
                    ctx.moveTo(fromX, fromY);
                    ctx.lineTo(toX, toY);
                    ctx.stroke();
                    checkCardPercentage(false);
                }

                // Event Listeners
                canvas.addEventListener("mousedown", (e) => {
                    startScratch(e);
                });
                canvas.addEventListener("mousemove", (e) => {
                    moveScratch(e);
                });
                window.addEventListener("mouseup", () => {
                    stopScratch();
                });

                canvas.addEventListener("touchstart", (e) => {
                    startScratch(e);
                    e.preventDefault();
                }, {
                    passive: false
                });

                canvas.addEventListener("touchmove", (e) => {
                    moveScratch(e);
                    e.preventDefault();
                }, {
                    passive: false
                });

                window.addEventListener("touchend", () => {
                    stopScratch();
                });

                let checkThrottle = null;

                function checkCardPercentage(isRelease = false) {
                    if (globalClaimed) return;
                    if (checkThrottle && !isRelease) return;

                    const runCheck = () => {
                        if (globalClaimed) return;
                        const imageData = ctx.getImageData(0, 0, width, height);
                        const pixels = imageData.data;
                        let transparentCount = 0;

                        for (let i = 3; i < pixels.length; i += 4) {
                            if (pixels[i] === 0) {
                                transparentCount++;
                            }
                        }

                        const percentage = (transparentCount / (pixels.length / 4)) * 100;
                        const triggerThreshold = isRelease ? 8 : 18;

                        // When user scratches >= triggerThreshold of THIS card
                        if (percentage >= triggerThreshold) {
                            globalClaimed = true;
                            triggerGlobalScratchComplete(cardIndex);
                        }
                    };

                    if (isRelease) {
                        if (checkThrottle) {
                            clearTimeout(checkThrottle);
                            checkThrottle = null;
                        }
                        runCheck();
                    } else {
                        checkThrottle = setTimeout(() => {
                            checkThrottle = null;
                            runCheck();
                        }, 60);
                    }
                }
            });

        function playWinningSound() {
            try {
                const ctx = getAudioContext();
                if (!ctx) return;

                if (ctx.state === 'suspended') {
                    ctx.resume().catch(() => {});
                }

                if (cheerBuffer) {
                    const source = ctx.createBufferSource();
                    source.buffer = cheerBuffer;
                    const gainNode = ctx.createGain();
                    gainNode.gain.value = 0.9;
                    source.connect(gainNode);
                    gainNode.connect(ctx.destination);
                    source.start(0);
                } else {
                    playSynthChime(ctx);
                }
            } catch (e) {
                console.log("Audio playback exception:", e);
                playSynthChime();
            }
        }

        function playSynthChime(ctx) {
            try {
                if (!ctx) ctx = getAudioContext();
                if (!ctx) return;
                if (ctx.state === "suspended") {
                    ctx.resume().catch(() => {});
                }

                const notes = [523.25, 659.25, 783.99, 1046.50, 1318.51];
                const times = [0, 0.09, 0.18, 0.27, 0.42];
                const durations = [0.18, 0.18, 0.18, 0.28, 0.7];

                notes.forEach((freq, i) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();

                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(freq, ctx.currentTime + times[i]);

                    gain.gain.setValueAtTime(0.01, ctx.currentTime + times[i]);
                    gain.gain.linearRampToValueAtTime(0.4, ctx.currentTime + times[i] + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + times[i] + durations[i]);

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start(ctx.currentTime + times[i]);
                    osc.stop(ctx.currentTime + times[i] + durations[i]);
                });
            } catch (e) {
                console.log("Synth audio error:", e);
            }
        }

        function triggerGlobalScratchComplete(scratchedIndex) {
            const won = window.WON_PRIZE_DATA || {};
            const hasWonPrize = (won.prize_id && won.prize_id != "0");

            // Play victory winning chime sound effect if prize won
            if (hasWonPrize) {
                try {
                    playWinningSound();
                } catch (e) {
                    console.log("Audio error:", e);
                }
            }

            // Hide floating scroll helper on scratch completion
            try {
                const scrollHelper = document.getElementById("floatingScrollHelper");
                if (scrollHelper) {
                    scrollHelper.style.opacity = "0";
                    scrollHelper.style.pointerEvents = "none";
                    setTimeout(() => {
                        if (scrollHelper && scrollHelper.parentNode) {
                            scrollHelper.remove();
                        }
                    }, 400);
                }
            } catch (e) {}

            // Clear pixels & fade out ALL canvases completely
            document.querySelectorAll(".scratch-canvas").forEach(c => {
                c.style.pointerEvents = "none";
                c.style.opacity = "0";
                try {
                    const cCtx = c.getContext("2d");
                    if (cCtx) {
                        cCtx.clearRect(0, 0, c.width, c.height);
                    }
                } catch(e) {}
                setTimeout(() => {
                    if (c && c.parentNode) {
                        c.remove();
                    }
                }, 350);
            });

            // Highlight scratched card as winner, lock all other cards as disabled
            document.querySelectorAll(".scratch-card-box").forEach(box => {
                const idx = parseInt(box.getAttribute("data-card-index"));
                const layer = document.getElementById("prizeLayer_" + idx);
                const badge = document.getElementById("prizeBadge_" + idx);

                if (idx === scratchedIndex) {
                    layer.classList.remove("disabled-card");
                    document.getElementById("prizeImg_" + idx).classList.add("pop-in");
                    if (badge) {
                        if (hasWonPrize) {
                            badge.className = "badge bg-success-subtle text-success rounded-pill px-3 py-1 mt-1 small";
                            badge.innerText = "🎉 Winner";
                        } else {
                            badge.className = "badge bg-warning-subtle text-warning rounded-pill px-3 py-1 mt-1 small";
                            badge.innerText = "No Prize Found";
                        }
                    }
                } else {
                    layer.classList.add("disabled-card");
                    if (badge) {
                        badge.className = "d-none";
                        badge.innerText = "";
                    }
                }
            });

            // Update instruction banner
            if (hasWonPrize) {
                $('#scratchInstruction').html(`
                    <div class="alert alert-success bg-success-subtle text-success border-0 rounded-pill py-2 px-4 d-inline-block small fw-bold">
                        <i class="bi bi-check-circle-fill me-1"></i> Scratch Complete!
                    </div>
                `);
            } else {
                $('#scratchInstruction').html(`
                    <div class="alert alert-warning bg-warning-subtle text-warning border-0 rounded-pill py-2 px-4 d-inline-block small fw-bold">
                        <i class="bi bi-emoji-frown-fill me-1"></i> Scratch Complete! No Prize Found.
                    </div>
                `);
            }

            // Send Claim AJAX with scratched_card_index
            $.ajax({
                url: "{{ route('lucky-draw.claim', ['business_slug' => $draw->business->slug ?? 'store', 'token' => $entry->token]) }}",
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    scratched_card_index: scratchedIndex
                },
                success: function(res) {
                    if (res.success && hasWonPrize) {
                        if (typeof confetti === 'function') {
                            confetti({
                                particleCount: 120,
                                spread: 80,
                                origin: {
                                    y: 0.6
                                }
                            });
                        }
                        $('#wonPrizeImg').attr('src', res.prize_image || won.image);
                        $('#wonPrizeTitle').text(res.prize_name || won.name);
                        $('#wonPrizeModal').modal('show');
                    }
                }
            });
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initMultiScratchCards);
    } else {
        initMultiScratchCards();
    }
</script>
@endif

<script>
    function initFloatingScrollHelper() {
        const scrollHelper = document.getElementById("floatingScrollHelper");
        if (!scrollHelper) return;

        let startY = 0;
        let initialScrollY = 0;
        let isDragging = false;

        // Sync vertical position of scroll icon with page scroll position when NOT dragging
        function updateScrollHelperPos() {
            if (isDragging) return;
            const scrollTop = window.scrollY || document.documentElement.scrollTop;
            const totalHeight = document.documentElement.scrollHeight - window.innerHeight;
            if (totalHeight <= 0) return;

            const scrollRatio = Math.min(1, Math.max(0, scrollTop / totalHeight));
            const minTop = 18;
            const maxTop = 82;
            const computedTop = minTop + (scrollRatio * (maxTop - minTop));
            scrollHelper.style.top = computedTop + "%";
        }

        window.addEventListener("scroll", updateScrollHelperPos, {
            passive: true
        });
        updateScrollHelperPos();

        function handleDragStart(clientY) {
            startY = clientY;
            initialScrollY = window.scrollY;
            isDragging = true;
            scrollHelper.classList.add("is-dragging");
        }

        function handleDragMove(clientY) {
            if (!isDragging) return;

            // Lock icon position directly under finger/mouse
            const clampedY = Math.min(window.innerHeight - 50, Math.max(50, clientY));
            scrollHelper.style.top = clampedY + "px";

            // Scroll page smoothly according to touch move distance
            const totalHeight = document.documentElement.scrollHeight - window.innerHeight;
            if (totalHeight > 0) {
                const dragRatio = (clientY - startY) / (window.innerHeight * 0.6);
                window.scrollTo(0, initialScrollY + (dragRatio * totalHeight));
            }
        }

        function handleDragEnd() {
            if (!isDragging) return;
            isDragging = false;
            scrollHelper.classList.remove("is-dragging");
            updateScrollHelperPos();
        }

        // Touch Event Listeners
        scrollHelper.addEventListener("touchstart", (e) => {
            if (e.touches && e.touches.length > 0) {
                handleDragStart(e.touches[0].clientY);
            }
        }, {
            passive: true
        });

        scrollHelper.addEventListener("touchmove", (e) => {
            if (!isDragging) return;
            if (e.touches && e.touches.length > 0) {
                handleDragMove(e.touches[0].clientY);
            }
            if (e.cancelable) e.preventDefault();
        }, {
            passive: false
        });

        window.addEventListener("touchend", handleDragEnd);
        window.addEventListener("touchcancel", handleDragEnd);

        // Mouse Event Listeners (for desktop)
        scrollHelper.addEventListener("mousedown", (e) => {
            handleDragStart(e.clientY);
            e.preventDefault();
        });

        window.addEventListener("mousemove", (e) => {
            if (isDragging) {
                handleDragMove(e.clientY);
                e.preventDefault();
            }
        });

        window.addEventListener("mouseup", handleDragEnd);

        // Click handler (tap to scroll down or back to top)
        scrollHelper.addEventListener("click", () => {
            if (Math.abs(window.scrollY - initialScrollY) > 15) return;
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 80) {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            } else {
                window.scrollBy({
                    top: 350,
                    behavior: 'smooth'
                });
            }
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initFloatingScrollHelper);
    } else {
        initFloatingScrollHelper();
    }
</script>
</body>

</html>