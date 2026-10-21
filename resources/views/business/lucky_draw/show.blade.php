@extends('business.layouts.main')
@section('title', 'Manage ' . $draw->title)

@push('style')
<link rel="stylesheet" href="{{ asset('assets/business/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/business/css/datatables-combined.min.css')) }}" />
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 border-bottom pb-3">
        <div>
            <a href="{{ route('business.lucky-draw.index') }}" class="text-decoration-none text-muted small me-2">
                <i class="bi bi-arrow-left me-1"></i> Back to Campaigns
            </a>
            <h3 class="fw-bold mb-0 mt-1">
                <i class="bi bi-ticket-perforated text-primary me-2"></i>{{ $draw->title }}
                @if($draw->type === 'spinner')
                <span class="badge bg-primary-subtle text-primary ms-2 fs-6 rounded-pill"><i class="bi bi-disc me-1"></i> Spinner</span>
                @else
                <span class="badge bg-warning-subtle text-warning ms-2 fs-6 rounded-pill"><i class="bi bi-ticket-perforated me-1"></i> Scratch Card</span>
                @endif
            </h3>
            <p class="text-muted mb-0 small">{{ $draw->description ?: 'Campaign settings, prize probabilities, and customer scratch card generator.' }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(checkBusinessPermission('edit_lucky_draw'))
            <button class="btn btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#editCampaignModal">
                <i class="bi bi-pencil me-1"></i> Edit Campaign
            </button>
            @endif
            @if(checkBusinessPermission('add_lucky_draw'))
            <button class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#generateEntryModal">
                <i class="bi bi-send me-1"></i> Send Scratch Link
            </button>
            @endif
        </div>
    </div>

    <!-- Stats & Probability Notice -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 me-3">
                        <i class="bi bi-gift fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">Total Prizes</h6>
                        <h4 class="fw-bold mb-0" id="stat-total-prizes">{{ $draw->prizes->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success-subtle text-success p-3 me-3">
                        <i class="bi bi-percent fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">Total Probability Sum</h6>
                        <h4 class="fw-bold mb-0 {{ $totalProbability == 100 ? 'text-success' : 'text-warning' }}" id="stat-total-probability">
                            {{ number_format($totalProbability, 2) }}%
                        </h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info-subtle text-info p-3 me-3">
                        <i class="bi bi-send-check fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">Generated Scratch Links</h6>
                        <h4 class="fw-bold mb-0">{{ $draw->entries->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-warning rounded-4 shadow-sm mb-4 d-flex align-items-center gap-3" id="probability-notice-alert" style="{{ $totalProbability != 100 ? '' : 'display: none;' }}">
        <i class="bi bi-exclamation-triangle-fill fs-3 text-warning"></i>
        <div>
            <strong>Notice on Win Probability:</strong> Current total win percentage sum is <strong id="notice-probability-value">{{ number_format($totalProbability, 2) }}%</strong>.
            For optimal balance, ensure all prize win percentages total 100%.
        </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs border-bottom mb-4" id="drawTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="prizes-tab" data-bs-toggle="tab" data-bs-target="#prizes" type="button" role="tab">
                <i class="bi bi-gift me-1"></i> Prizes List <span id="tab-prizes-count">({{ $draw->prizes->count() }})</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="entries-tab" data-bs-toggle="tab" data-bs-target="#entries-pane" type="button" role="tab">
                <i class="bi bi-people me-1"></i> Customer Links ({{ $draw->entries->count() }})
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="drawTabContent">
        <!-- Prizes Tab -->
        <div class="tab-pane fade show active" id="prizes" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="fw-bold mb-0">Campaign Prizes</h5>
                @if(checkBusinessPermission('add_lucky_draw'))
                <div class="d-flex align-items-center gap-2">
                    @if(isset($availableCampaigns) && $availableCampaigns->count() > 0)
                    <button class="btn btn-outline-secondary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#importPrizesModal">
                        <i class="bi bi-box-arrow-in-down me-1"></i> Import Prizes
                    </button>
                    @endif
                    <button class="btn btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addPrizeModal" onclick="resetPrizeForm()">
                        <i class="bi bi-plus-lg me-1"></i> Add Prize Item
                    </button>
                </div>
                @endif
            </div>

            <!-- Empty State Prompt when Prizes List is Empty -->
            <div id="emptyPrizesState" class="card border-0 shadow-sm rounded-4 text-center p-4 p-md-5 mb-4 bg-white" style="{{ $draw->prizes->count() == 0 ? '' : 'display: none;' }}">
                <div class="py-2">
                    <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center p-3 mb-3" style="width: 72px; height: 72px;">
                        <i class="bi bi-gift fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-2">No Prize Items Added Yet</h5>
                    <p class="text-muted mx-auto mb-4" style="max-width: 480px;">
                        This campaign does not have any prize items yet. You can quickly copy prizes from an existing campaign or add custom items.
                    </p>
                    @if(checkBusinessPermission('add_lucky_draw'))
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        @if(isset($availableCampaigns) && $availableCampaigns->count() > 0)
                        <button class="btn btn-outline-secondary rounded-pill px-4 py-2" data-bs-toggle="modal" data-bs-target="#importPrizesModal">
                            <i class="bi bi-box-arrow-in-down me-1"></i> Import From Another Campaign
                        </button>
                        @endif
                        <button class="btn btn-primary rounded-pill px-4 py-2" data-bs-toggle="modal" data-bs-target="#addPrizeModal" onclick="resetPrizeForm()">
                            <i class="bi bi-plus-lg me-1"></i> Add Prize Item
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="prizes-data-table" width="100%">
                            <thead class="bg-light text-muted small uppercase">
                                <tr>
                                    <th class="ps-4 py-3" width="60">S.No</th>
                                    <th class="py-3">Prize Item</th>
                                    <th class="py-3">Win Percentage</th>
                                    <th class="py-3">Times Claimed</th>
                                    <th class="pe-4 py-3 text-end" width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Generated Links History Tab -->
        <div class="tab-pane fade" id="entries-pane" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Generated Customer Scratch Cards</h5>
                @if(checkBusinessPermission('add_lucky_draw'))
                <button class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#generateEntryModal">
                    <i class="bi bi-send me-1"></i> Send Scratch Link
                </button>
                @endif
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="entries-data-table" width="100%">
                            <thead class="bg-light text-muted small uppercase">
                                <tr>
                                    <th class="ps-4 py-3" width="60">S.No</th>
                                    <th class="py-3">Customer Name</th>
                                    <th class="py-3">Contact</th>
                                    <th class="py-3">Status</th>
                                    <th class="py-3">Prize Result</th>
                                    <th class="py-3">Scratched At</th>
                                    <th class="pe-4 py-3 text-end" width="220">Share Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Prize Modal -->
<div class="modal fade" id="addPrizeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="prizeModalTitle"><i class="bi bi-gift text-primary me-2"></i>Add Prize Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="prizeForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="prize_id" id="prize_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Prize Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="prize_name" class="form-control rounded-3" placeholder="e.g. iPhone 15, Car, Phone Cover, Better Luck Next Time" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Win Probability (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" name="win_percentage" id="prize_percentage" class="form-control rounded-3" placeholder="e.g. 5.00" required>
                        <small class="text-muted">Luck percentage chance.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Prize Image</label>
                        <input type="file" name="image" id="prize_image_input" class="form-control rounded-3" accept="image/*">
                        <div id="prizeImagePreviewContainer" class="mt-3 text-center p-2 border rounded-3 bg-light" style="display: none;">
                            <p class="text-muted small mb-1 fw-bold">Selected Image Preview:</p>
                            <img id="prizeImagePreview" src="" alt="Prize Preview" class="rounded-3 shadow-sm" style="max-height: 120px; max-width: 100%; object-fit: contain;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Save Prize</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Generate Customer Link Modal -->
<div class="modal fade" id="generateEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="bi bi-send text-success me-2"></i>Generate Customer Scratch Link</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="generateEntryForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info rounded-3 small mb-3">
                        <i class="bi bi-info-circle me-1"></i> Generating a unique link deducts <strong>{{ $costPerScratch }} Credit(s)</strong> from your Credits.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control rounded-3" placeholder="e.g. Rahul Sharma" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Customer Phone / WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" name="customer_contact" id="customer_contact_input" class="form-control rounded-3" placeholder="e.g. 9876543210" maxlength="10" pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" required>
                        <small class="text-muted">Must be a valid 10-digit mobile number.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Winning Prize Item</label>
                        <select name="won_prize_id" class="form-select rounded-3">
                            <option value="">-- Random (Auto-selected by probability) --</option>
                            @foreach($draw->prizes->where('win_percentage', '>', 0) as $prize)
                            <option value="{{ $prize->id }}">{{ $prize->name }} ({{ number_format($prize->win_percentage, 2) }}%)</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pre-select a specific prize for this customer, or leave blank to auto-roll.</small>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">Generate & Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Link Success / Edit Entry Modal -->
<div class="modal fade" id="linkSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow text-center p-4">
            <form id="editEntryForm">
                @csrf
                <input type="hidden" id="resEntryId" name="entry_id">
                <div class="mb-3">
                    <div class="rounded-circle bg-success-subtle text-success d-inline-flex p-3 mb-2" id="modalStatusIconContainer">
                        <i class="bi bi-check-circle-fill fs-1" id="modalStatusIcon"></i>
                    </div>
                    <h4 class="fw-bold mb-1" id="modalHeaderTitle">Scratch Link Ready!</h4>
                    <span class="badge bg-warning-subtle text-warning px-3 py-1 rounded-pill" id="modalStatusBadge">Pending Scratch</span>
                </div>

                <div class="text-start mb-3">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-1">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" id="resCustomerNameInput" name="customer_name" class="form-control rounded-3" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-1">Customer Phone / WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" id="resCustomerContactInput" name="customer_contact" class="form-control rounded-3" maxlength="10" pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-1">Winning Prize Item <span class="text-danger">*</span></label>
                        <select id="resWonPrizeSelect" name="won_prize_id" class="form-select rounded-3">
                            <option value="">-- No Specific Prize --</option>
                            @foreach($draw->prizes->where('win_percentage', '>', 0) as $prize)
                            <option value="{{ $prize->id }}">{{ $prize->name }} ({{ number_format($prize->win_percentage, 2) }}%)</option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1" id="prizeEditNote">Pre-assigned winning prize for this customer scratch card.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-1">Generated Scratch Card URL</label>
                        <div class="input-group">
                            <input type="text" id="resScratchUrl" class="form-control rounded-start-3 text-muted small" readonly>
                            <button type="button" class="btn btn-primary rounded-end-3 px-3 copy-result-url">
                                <i class="bi bi-copy me-1"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column gap-2 mt-2">
                    <a href="#" id="resWaBtn" target="_blank" class="btn btn-success rounded-pill px-4 w-100 py-2">
                        <i class="bi bi-whatsapp me-2"></i> Send via WhatsApp
                    </a>
                    <button type="submit" id="saveEntryBtn" class="btn btn-primary rounded-pill px-4 w-100 py-2 fw-bold">
                        <i class="bi bi-check-circle me-1"></i> Save Changes
                    </button>
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-link text-decoration-none text-muted" data-bs-dismiss="modal">Done</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Prizes Modal -->
<div class="modal fade" id="importPrizesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-box-arrow-in-down text-primary me-2"></i>Import Prizes from Campaign
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importPrizesForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info rounded-3 small mb-3">
                        <i class="bi bi-info-circle me-1"></i> Select an existing campaign below to preview its prizes, select items to copy, and import them directly into <strong>{{ $draw->title }}</strong>.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Campaign <span class="text-danger">*</span></label>
                        <select name="source_campaign_id" id="importSourceCampaignSelect" class="form-select rounded-3" required>
                            <option value="">-- Choose a Campaign to Import From --</option>
                            @if(isset($availableCampaigns) && $availableCampaigns->count() > 0)
                                @foreach($availableCampaigns as $camp)
                                    <option value="{{ $camp->id }}">
                                        {{ $camp->title }} ({{ $camp->prizes_count }} Prize{{ $camp->prizes_count > 1 ? 's' : '' }} • {{ ucfirst($camp->type) }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @if(!isset($availableCampaigns) || $availableCampaigns->count() === 0)
                            <small class="text-danger d-block mt-1">No other campaigns with prize items found to copy from.</small>
                        @endif
                    </div>

                    <!-- Loading Spinner -->
                    <div id="importLoadingContainer" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted small mt-2 mb-0">Loading prizes from selected campaign...</p>
                    </div>

                    <!-- Prizes Selection List -->
                    <div id="importPrizesContainer" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold mb-0">Select Prize Items to Copy</label>
                            <span class="text-muted small fw-bold" id="importSelectionCount">0 item(s) selected</span>
                        </div>

                        <div class="border rounded-3 overflow-hidden mb-2">
                            <div class="table-responsive" style="max-height: 280px;">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-muted small uppercase sticky-top">
                                        <tr>
                                            <th class="ps-3 py-2" width="45">
                                                <input class="form-check-input" type="checkbox" id="selectAllImportPrizes" checked>
                                            </th>
                                            <th class="py-2">Prize Item</th>
                                            <th class="py-2 text-end pe-3">Win Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody id="importPrizesTableBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="submitImportPrizesBtn" class="btn btn-primary rounded-pill px-4" disabled>
                        <i class="bi bi-copy me-1"></i> Import Selected Prizes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/business/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/business/js/datatables-combined.min.js')) }}"></script>
<script>
    function resetPrizeForm() {
        $('#prizeForm')[0].reset();
        $('#prize_id').val('');
        $('#prizeImagePreviewContainer').hide();
        $('#prizeImagePreview').attr('src', '');
        $('#prizeModalTitle').html('<i class="bi bi-gift text-primary me-2"></i>Add Prize Item');
    }

    $(document).ready(function() {
        function updateSummaryCards(totalPrizes, totalProbability) {
            if (totalPrizes !== undefined) {
                $('#stat-total-prizes').text(totalPrizes);
                $('#tab-prizes-count').text('(' + totalPrizes + ')');
            }
            if (totalProbability !== undefined) {
                let probVal = parseFloat(totalProbability);
                let probFormatted = probVal.toFixed(2) + '%';
                let probEl = $('#stat-total-probability');
                probEl.text(probFormatted);
                if (probVal === 100) {
                    probEl.removeClass('text-warning').addClass('text-success');
                    $('#probability-notice-alert').slideUp();
                } else {
                    probEl.removeClass('text-success').addClass('text-warning');
                    $('#notice-probability-value').text(probFormatted);
                    $('#probability-notice-alert').slideDown();
                }
            }
        }

        // Initialize Prizes DataTable
        let prizesTable = $('#prizes-data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('business.lucky-draw.show', $draw->id) }}",
                data: {
                    type: 'prizes'
                }
            },
            pageLength: 10,
            drawCallback: function(settings) {
                let api = this.api();
                let recordsTotal = api.page.info().recordsTotal;
                if (recordsTotal === 0) {
                    $('#emptyPrizesState').slideDown();
                } else {
                    $('#emptyPrizesState').slideUp();
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'ps-4 py-3 fw-bold text-dark'
                },
                {
                    data: 'prize_item',
                    name: 'name',
                    orderable: true,
                    searchable: true,
                    className: 'py-3'
                },
                {
                    data: 'win_percentage_badge',
                    name: 'win_percentage',
                    orderable: true,
                    searchable: false,
                    className: 'py-3'
                },
                {
                    data: 'times_claimed',
                    name: 'total_won',
                    orderable: true,
                    searchable: false,
                    className: 'py-3'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'pe-4 py-3 text-end'
                }
            ]
        });

        // Initialize Entries DataTable
        let entriesTable = $('#entries-data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('business.lucky-draw.show', $draw->id) }}",
                data: {
                    type: 'entries'
                }
            },
            pageLength: 10,
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'ps-4 py-3 fw-bold text-dark'
                },
                {
                    data: 'customer_name',
                    name: 'customer_name',
                    orderable: true,
                    searchable: true,
                    className: 'py-3'
                },
                {
                    data: 'contact',
                    name: 'customer_contact',
                    orderable: true,
                    searchable: true,
                    className: 'py-3'
                },
                {
                    data: 'status_badge',
                    name: 'status',
                    orderable: true,
                    searchable: true,
                    className: 'py-3'
                },
                {
                    data: 'prize_result',
                    name: 'wonPrize.name',
                    orderable: false,
                    searchable: true,
                    className: 'py-3'
                },
                {
                    data: 'scratched_at_formatted',
                    name: 'scratched_at',
                    orderable: true,
                    searchable: false,
                    className: 'py-3'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'pe-4 py-3 text-end'
                }
            ]
        });

        // Handle tab change to adjust column widths for DataTables
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            if (e.target.id === 'prizes-tab') {
                prizesTable.columns.adjust().draw();
            } else if (e.target.id === 'entries-tab') {
                entriesTable.columns.adjust().draw();
            }
        });

        // Image Selection Live Preview
        $('#prize_image_input').on('change', function() {
            let file = this.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#prizeImagePreview').attr('src', e.target.result);
                    $('#prizeImagePreviewContainer').show();
                };
                reader.readAsDataURL(file);
            }
        });

        // Edit Prize Button
        $(document).on('click', '.edit-prize-btn', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            let percentage = $(this).data('percentage');
            let image = $(this).data('image');

            $('#prize_id').val(id);
            $('#prize_name').val(name);
            $('#prize_percentage').val(percentage);

            if (image) {
                $('#prizeImagePreview').attr('src', image);
                $('#prizeImagePreviewContainer').show();
            } else {
                $('#prizeImagePreviewContainer').hide();
                $('#prizeImagePreview').attr('src', '');
            }

            $('#prizeModalTitle').html('<i class="bi bi-pencil text-primary me-2"></i>Edit Prize Item');
            $('#addPrizeModal').modal('show');
        });

        // Store Prize AJAX
        $('#prizeForm').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            let btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

            $.ajax({
                url: "{{ route('business.lucky-draw.prize.store', $draw->id) }}",
                method: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    btn.prop('disabled', false).text('Save Prize');
                    if (res.success) {
                        toastr.success(res.message);
                        $('#addPrizeModal').modal('hide');
                        prizesTable.ajax.reload(null, false);
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Save Prize');
                    toastr.error('Failed to save prize. Please check inputs.');
                }
            });
        });

        // Delete Prize AJAX
        $(document).on('click', '.delete-prize-btn', function() {
            let id = $(this).data('id');
            if (confirm('Are you sure you want to remove this prize?')) {
                let deleteUrl = "{{ route('business.lucky-draw.prize.delete', ['id' => $draw->id, 'prize_id' => ':prize_id']) }}".replace(':prize_id', id);
                $.ajax({
                    url: deleteUrl,
                    method: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            prizesTable.ajax.reload(null, false);
                        } else {
                            toastr.error(res.message);
                        }
                    }
                });
            }
        });

        // ================= IMPORT PRIZES FROM CAMPAIGN =================
        function updateImportSelectionCount() {
            let total = $('.import-prize-checkbox').length;
            let checked = $('.import-prize-checkbox:checked').length;
            $('#importSelectionCount').text(`${checked} of ${total} item(s) selected`);
            $('#selectAllImportPrizes').prop('checked', total > 0 && total === checked);
            $('#submitImportPrizesBtn').prop('disabled', checked === 0);
        }

        // Reset Import Modal on open
        $('#importPrizesModal').on('show.bs.modal', function() {
            $('#importSourceCampaignSelect').val('');
            $('#importPrizesTableBody').empty();
            $('#importPrizesContainer').hide();
            $('#importLoadingContainer').hide();
            $('#submitImportPrizesBtn').prop('disabled', true).html('<i class="bi bi-copy me-1"></i> Import Selected Prizes');
            $('#importSelectionCount').text('0 item(s) selected');
        });

        // Load prizes when a source campaign is selected
        $('#importSourceCampaignSelect').on('change', function() {
            let sourceId = $(this).val();
            let container = $('#importPrizesContainer');
            let loading = $('#importLoadingContainer');
            let tbody = $('#importPrizesTableBody');
            let submitBtn = $('#submitImportPrizesBtn');

            tbody.empty();
            if (!sourceId) {
                container.hide();
                loading.hide();
                submitBtn.prop('disabled', true);
                $('#importSelectionCount').text('0 item(s) selected');
                return;
            }

            loading.show();
            container.hide();
            submitBtn.prop('disabled', true);

            let fetchUrl = "{{ route('business.lucky-draw.source-prizes', ['id' => $draw->id, 'source_id' => ':source_id']) }}".replace(':source_id', sourceId);

            $.ajax({
                url: fetchUrl,
                method: "GET",
                success: function(res) {
                    loading.hide();
                    if (res.success && res.prizes && res.prizes.length > 0) {
                        let html = '';
                        res.prizes.forEach(function(prize) {
                            let imgHtml = prize.image_url 
                                ? `<img src="${prize.image_url}" alt="${prize.name}" class="rounded-3 me-2" style="width: 38px; height: 38px; object-fit: cover;">`
                                : `<div class="rounded-3 me-2 bg-light d-flex align-items-center justify-content-center text-muted" style="width: 38px; height: 38px;"><i class="bi bi-gift"></i></div>`;
                            
                            html += `
                            <tr>
                                <td class="ps-3 py-2">
                                    <input class="form-check-input import-prize-checkbox" type="checkbox" name="prize_ids[]" value="${prize.id}" checked>
                                </td>
                                <td class="py-2">
                                    <div class="d-flex align-items-center">
                                        ${imgHtml}
                                        <div>
                                            <span class="fw-bold text-dark d-block">${prize.name}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2 text-end pe-3">
                                    <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1 rounded-pill">
                                        ${parseFloat(prize.win_percentage).toFixed(2)}%
                                    </span>
                                </td>
                            </tr>`;
                        });
                        tbody.html(html);
                        $('#selectAllImportPrizes').prop('checked', true);
                        updateImportSelectionCount();
                        container.show();
                    } else {
                        tbody.html('<tr><td colspan="3" class="text-center text-muted py-3">No prizes available in this campaign.</td></tr>');
                        container.show();
                        submitBtn.prop('disabled', true);
                        $('#importSelectionCount').text('0 item(s) selected');
                    }
                },
                error: function(xhr) {
                    loading.hide();
                    toastr.error('Failed to load campaign prizes.');
                }
            });
        });

        // Toggle all checkboxes
        $('#selectAllImportPrizes').on('change', function() {
            let isChecked = $(this).is(':checked');
            $('.import-prize-checkbox').prop('checked', isChecked);
            updateImportSelectionCount();
        });

        // Individual checkbox change
        $(document).on('change', '.import-prize-checkbox', function() {
            updateImportSelectionCount();
        });

        // Submit Import Prizes Form
        $('#importPrizesForm').on('submit', function(e) {
            e.preventDefault();
            let selectedCount = $('.import-prize-checkbox:checked').length;
            if (selectedCount === 0) {
                toastr.error('Please select at least one prize item to import.');
                return false;
            }

            let btn = $('#submitImportPrizesBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Importing...');

            $.ajax({
                url: "{{ route('business.lucky-draw.import-prizes', $draw->id) }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="bi bi-copy me-1"></i> Import Selected Prizes');
                    if (res.success) {
                        toastr.success(res.message);
                        $('#importPrizesModal').modal('hide');
                        prizesTable.ajax.reload(null, false);
                        if (res.total_prizes !== undefined && res.total_probability !== undefined) {
                            updateSummaryCards(res.total_prizes, res.total_probability);
                        }
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-copy me-1"></i> Import Selected Prizes');
                    let errMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to import prizes.';
                    toastr.error(errMsg);
                }
            });
        });

        // Generate Entry Link AJAX
        $('#generateEntryForm').on('submit', function(e) {
            e.preventDefault();
            let phone = $('#customer_contact_input').val().trim();
            if (!/^[0-9]{10}$/.test(phone)) {
                toastr.error('Customer phone number must be exactly 10 digits.');
                return false;
            }

            let form = $(this);
            let btn = form.find('button[type="submit"]');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Generating...');

            $.ajax({
                url: "{{ route('business.lucky-draw.generate-entry', $draw->id) }}",
                method: "POST",
                data: form.serialize(),
                success: function(res) {
                    btn.prop('disabled', false).text('Generate & Send');
                    if (res.success) {
                        toastr.success(res.message);
                        $('#generateEntryModal').modal('hide');

                        $('#modalHeaderTitle').text('Scratch Link Ready!');
                        $('#resEntryId').val(res.entry_id);
                        $('#resCustomerNameInput').val(res.customer_name).prop('disabled', false);
                        $('#resCustomerContactInput').val(res.customer_contact).prop('disabled', false);
                        $('#resWonPrizeSelect').val(res.won_prize_id || '').prop('disabled', false);
                        $('#resScratchUrl').val(res.scratch_url);
                        $('#resWaBtn').attr('href', res.whatsapp_url);

                        $('#modalStatusBadge')
                            .removeClass('bg-success-subtle text-success')
                            .addClass('bg-warning-subtle text-warning')
                            .html('<i class="bi bi-clock me-1"></i> Pending Scratch');
                        $('#prizeEditNote').text('Pre-assigned winning prize for this customer scratch card. You can change this item before customer scratches.').removeClass('text-danger').addClass('text-muted');
                        $('#saveEntryBtn').show();

                        $('#linkSuccessModal').modal('show');
                        entriesTable.ajax.reload(null, false);
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Generate & Send');
                    toastr.error('Failed to generate scratch link.');
                }
            });
        });

        // Edit Entry Modal Trigger
        $(document).on('click', '.edit-entry-btn', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            let contact = $(this).data('contact');
            let status = $(this).data('status');
            let wonPrizeId = $(this).data('won-prize-id');
            let wonPrizeName = $(this).data('won-prize-name');
            let url = $(this).data('url');
            let waUrl = $(this).data('wa-url');

            $('#modalHeaderTitle').text('Edit Customer Scratch Link');
            $('#resEntryId').val(id);
            $('#resCustomerNameInput').val(name);
            $('#resCustomerContactInput').val(contact);
            $('#resWonPrizeSelect').val(wonPrizeId || '');
            $('#resScratchUrl').val(url);
            $('#resWaBtn').attr('href', waUrl);

            if (status === 'scratched' || status === 'claimed') {
                $('#resCustomerNameInput').prop('disabled', true);
                $('#resCustomerContactInput').prop('disabled', true);
                $('#resWonPrizeSelect').prop('disabled', true);
                $('#modalStatusBadge')
                    .removeClass('bg-warning-subtle text-warning')
                    .addClass('bg-success-subtle text-success')
                    .html('<i class="bi bi-check-circle me-1"></i> Scratched (' + wonPrizeName + ')');
                $('#prizeEditNote').text('This card has already been scratched by the customer and cannot be edited.').removeClass('text-muted').addClass('text-danger');
                $('#saveEntryBtn').hide();
            } else {
                $('#resCustomerNameInput').prop('disabled', false);
                $('#resCustomerContactInput').prop('disabled', false);
                $('#resWonPrizeSelect').prop('disabled', false);
                $('#modalStatusBadge')
                    .removeClass('bg-success-subtle text-success')
                    .addClass('bg-warning-subtle text-warning')
                    .html('<i class="bi bi-clock me-1"></i> Pending Scratch');
                $('#prizeEditNote').text('Pre-assigned winning prize for this customer scratch card. You can change this item before customer scratches.').removeClass('text-danger').addClass('text-muted');
                $('#saveEntryBtn').show();
            }

            $('#linkSuccessModal').modal('show');
        });

        // Edit Entry AJAX Submit
        $('#editEntryForm').on('submit', function(e) {
            e.preventDefault();
            let entryId = $('#resEntryId').val();
            if (!entryId) return;

            let phone = $('#resCustomerContactInput').val().trim();
            if (!/^[0-9]{10}$/.test(phone)) {
                toastr.error('Customer phone number must be exactly 10 digits.');
                return false;
            }

            let btn = $('#saveEntryBtn');
            let originalHtml = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

            let updateUrl = "{{ route('business.lucky-draw.entry.update', ['id' => $draw->id, 'entry_id' => ':entry_id']) }}".replace(':entry_id', entryId);

            $.ajax({
                url: updateUrl,
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html(originalHtml);
                    if (res.success) {
                        toastr.success(res.message);
                        if (res.entry && res.entry.whatsapp_url) {
                            $('#resWaBtn').attr('href', res.entry.whatsapp_url);
                        }
                        entriesTable.ajax.reload(null, false);
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(originalHtml);
                    toastr.error('Failed to update entry.');
                }
            });
        });

        function copyTextToClipboard(text, targetInputElement) {
            let success = false;

            if (targetInputElement) {
                targetInputElement.focus();
                targetInputElement.select();
                targetInputElement.setSelectionRange(0, 99999);
                try {
                    success = document.execCommand('copy');
                } catch (e) {
                    success = false;
                }
            } else {
                let tempInput = document.createElement("input");
                tempInput.type = "text";
                tempInput.value = text;
                tempInput.style.position = "absolute";
                tempInput.style.left = "-9999px";
                document.body.appendChild(tempInput);
                tempInput.focus();
                tempInput.select();
                tempInput.setSelectionRange(0, 99999);
                try {
                    success = document.execCommand('copy');
                } catch (e) {
                    success = false;
                }
                document.body.removeChild(tempInput);
            }

            if (!success && navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text);
                success = true;
            }

            return success;
        }

        // Copy URL Buttons
        $(document).on('click', '.copy-url-btn', function() {
            let btn = $(this);
            let originalHtml = btn.html();
            let url = btn.data('url');

            let copied = copyTextToClipboard(url, null);
            if (copied) {
                toastr.success('Scratch link copied to clipboard!');
                btn.html('<i class="bi bi-check2 me-1"></i> Copied!');
                setTimeout(function() {
                    btn.html(originalHtml);
                }, 1500);
            } else {
                toastr.error('Failed to copy link.');
            }
        });

        $(document).on('click', '.copy-result-url', function() {
            let btn = $(this);
            let originalHtml = btn.html();
            let inputEl = document.getElementById('resScratchUrl');
            let url = inputEl ? inputEl.value : '';

            let copied = copyTextToClipboard(url, inputEl);
            if (copied) {
                toastr.success('Scratch link copied to clipboard!');
                btn.html('<i class="bi bi-check2 me-1"></i> Copied!');
                setTimeout(function() {
                    btn.html(originalHtml);
                }, 1500);
            } else {
                toastr.error('Failed to copy link.');
            }
        });

        $('#editCampaignForm').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = form.find('button[type="submit"]');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

            $.ajax({
                url: "{{ route('business.lucky-draw.update', $draw->id) }}",
                method: "POST",
                data: form.serialize(),
                success: function(res) {
                    btn.prop('disabled', false).text('Update Campaign');
                    if (res.success) {
                        toastr.success(res.message);
                        setTimeout(function() {
                            location.reload();
                        }, 800);
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Update Campaign');
                    let err = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to update campaign.';
                    toastr.error(err);
                }
            });
        });
    });
</script>

<!-- Edit Campaign Details Modal -->
<div class="modal fade" id="editCampaignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Campaign Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCampaignForm">
                @csrf
                <div class="modal-body p-4 text-start">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Campaign Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3" value="{{ $draw->title }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Campaign Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select rounded-3" required>
                            <option value="scratch" {{ $draw->type === 'scratch' ? 'selected' : '' }}>🎫 Scratch Cards</option>
                            <option value="spinner" {{ $draw->type === 'spinner' ? 'selected' : '' }}>🎡 Spinning Wheel (Spinner)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control rounded-3" rows="2">{{ $draw->description }}</textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Start Date</label>
                            <input type="date" name="start_date" class="form-control rounded-3" value="{{ $draw->start_date ? $draw->start_date->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">End Date</label>
                            <input type="date" name="end_date" class="form-control rounded-3" value="{{ $draw->end_date ? $draw->end_date->format('Y-m-d') : '' }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select rounded-3" required>
                            <option value="active" {{ $draw->status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="in-active" {{ $draw->status === 'in-active' ? 'selected' : '' }}>In-Active</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Update Campaign</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush