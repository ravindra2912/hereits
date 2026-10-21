@extends('pos.layouts.main')
@section('title', 'Lucky Draw Scratch Links')
@section('header_title', 'Lucky Draw Links')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-ticket-perforated me-2 text-primary"></i>Customer Scratch Links</h5>
                    <p class="text-muted small mb-0">Generate and share exclusive scratch card links for customers.</p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @if(checkPosPermission('generate_lucky_draw'))
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#generateEntryModal">
                        <i class="bi bi-plus-lg me-1"></i> Generate Scratch Link
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header py-3 bg-white border-0 ps-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <h6 class="m-0 fw-bold text-dark">Generated Link History</h6>
        <div class="d-flex align-items-center gap-2">
            @if($activeDraws->count() > 1)
            <select class="form-select form-select-sm border-0 bg-light rounded-pill px-3" id="campaign_filter" style="width: 200px;">
                <option value="">All Campaigns</option>
                @foreach($activeDraws as $draw)
                <option value="{{ $draw->id }}">{{ $draw->title }}</option>
                @endforeach
            </select>
            @endif
            <button type="button" class="btn btn-sm btn-light rounded-pill px-3" id="reload-table-btn" title="Refresh Table">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="pos-lucky-draw-table" width="100%">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold border-0" width="50">#</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Customer</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Contact</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Campaign</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Status</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Prize Result</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Created</th>
                        <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold border-0 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Generate Scratch Link -->
@if(checkPosPermission('generate_lucky_draw'))
<div class="modal fade" id="generateEntryModal" tabindex="-1" aria-labelledby="generateEntryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="generateEntryModalLabel">
                    <i class="bi bi-ticket-perforated me-2 text-primary"></i>Generate Customer Scratch Link
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="generateEntryForm">
                @csrf
                <div class="modal-body p-4">
                    @if($activeDraws->isEmpty())
                    <div class="alert alert-warning rounded-3 mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i> No active Lucky Draw campaigns available at the moment.
                    </div>
                    @else
                    <div class="alert alert-info rounded-3 py-2 px-3 mb-3 small">
                        <i class="bi bi-info-circle me-1"></i> Generating a scratch link deducts <strong>{{ $costPerScratch }} credit(s)</strong>. Winning prize will be randomly selected.
                    </div>

                    <div class="mb-3">
                        <label for="lucky_draw_id" class="form-label fw-bold small">Select Campaign <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" name="lucky_draw_id" id="lucky_draw_id" required>
                            @foreach($activeDraws as $draw)
                            <option value="{{ $draw->id }}">{{ $draw->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="customer_name" class="form-label fw-bold small">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" name="customer_name" id="customer_name" placeholder="e.g. Rahul Sharma" required>
                    </div>

                    <div class="mb-3">
                        <label for="customer_contact" class="form-label fw-bold small">Customer Contact Number (10 Digits) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start-3">+91</span>
                            <input type="tel" class="form-control border-start-0 rounded-end-3" name="customer_contact" id="customer_contact" maxlength="10" placeholder="9876543210" required>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    @if($activeDraws->isNotEmpty())
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="generate-submit-btn">
                        <i class="bi bi-magic me-1"></i> Generate Link
                    </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Modal: Edit Customer Scratch Link -->
@if(checkPosPermission('edit_lucky_draw'))
<div class="modal fade" id="editEntryModal" tabindex="-1" aria-labelledby="editEntryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="editEntryModalLabel">
                    <i class="bi bi-pencil-square me-2 text-primary"></i>Edit Customer Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editEntryForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="entry_id" id="edit_entry_id">
                <input type="hidden" name="lucky_draw_id" id="edit_lucky_draw_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_customer_name" class="form-label fw-bold small">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" name="customer_name" id="edit_customer_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_customer_contact" class="form-label fw-bold small">Customer Contact Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start-3">+91</span>
                            <input type="tel" class="form-control border-start-0 rounded-end-3" name="customer_contact" id="edit_customer_contact" maxlength="10" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="edit-submit-btn">
                        <i class="bi bi-check-lg me-1"></i> Update Details
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Modal: Share Generated Link via WhatsApp -->
<div class="modal fade" id="linkShareModal" tabindex="-1" aria-labelledby="linkShareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 text-center p-4">
            <div class="modal-body">
                <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-check-lg fs-1"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">Scratch Link Ready!</h4>
                <p class="text-muted small mb-4">Share this link with <strong id="share-customer-name"></strong> via WhatsApp.</p>

                <div class="input-group mb-4">
                    <input type="text" class="form-control bg-light border-end-0 rounded-start-3" id="share-scratch-url" readonly>
                    <button class="btn btn-outline-secondary rounded-end-3 px-3" type="button" id="modal-copy-btn">
                        <i class="bi bi-copy me-1"></i> Copy
                    </button>
                </div>

                <div class="d-grid gap-2">
                    <a href="#" id="modal-wa-btn" target="_blank" class="btn btn-success btn-lg rounded-pill fw-bold">
                        <i class="bi bi-whatsapp me-2"></i> Send via WhatsApp
                    </a>
                    <button type="button" class="btn btn-light rounded-pill mt-1" data-bs-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/pos/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/pos/css/datatables-combined.min.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('assets/pos/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/pos/js/datatables-combined.min.js')) }}"></script>

<script>
    $(function() {
        var table = $('#pos-lucky-draw-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('pos.lucky-draw.index') }}",
                data: function(d) {
                    d.lucky_draw_id = $('#campaign_filter').val();
                }
            },
            lengthChange: false,
            pageLength: 15,
            language: {
                search: "",
                searchPlaceholder: "🔍 Search customer links...",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                zeroRecords: "No matching scratch links found",
                emptyTable: "No scratch links generated yet"
            },
            responsive: true,
            autoWidth: false,
            order: [
                [0, 'desc']
            ],
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'id',
                    className: "ps-4 py-3 fw-bold text-dark",
                    width: "40px"
                },
                {
                    data: 'customer_name',
                    name: 'customer_name',
                    className: "py-3"
                },
                {
                    data: 'contact',
                    name: 'customer_contact',
                    className: "py-3"
                },
                {
                    data: 'campaign',
                    name: 'luckyDraw.title',
                    className: "py-3"
                },
                {
                    data: 'status_badge',
                    name: 'status',
                    className: "py-3"
                },
                {
                    data: 'prize_result',
                    name: 'wonPrize.name',
                    className: "py-3"
                },
                {
                    data: 'created_info',
                    name: 'created_at',
                    className: "py-3"
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: "text-end pe-4 py-3"
                }
            ]
        });

        $('#campaign_filter, #reload-table-btn').on('change click', function() {
            table.draw();
        });

        // Submit Generate Scratch Link
        $('#generateEntryForm').on('submit', function(e) {
            e.preventDefault();
            var btn = $('#generate-submit-btn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Generating...');

            $.ajax({
                url: "{{ route('pos.lucky-draw.generate') }}",
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#generateEntryModal').modal('hide');
                        $('#generateEntryForm')[0].reset();
                        
                        // Update remaining credit balance
                        if (response.remaining_credits !== undefined) {
                            $('#pos-credit-balance').text(response.remaining_credits);
                        }

                        // Show share modal
                        $('#share-customer-name').text(response.customer_name);
                        $('#share-scratch-url').val(response.scratch_url);
                        $('#modal-wa-btn').attr('href', response.whatsapp_url);
                        $('#linkShareModal').modal('show');

                        table.ajax.reload(null, false);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || "Failed to generate scratch card URL.");
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="bi bi-magic me-1"></i> Generate Link');
                }
            });
        });

        // Open Edit Entry Modal
        $(document).on('click', '.edit-entry-btn', function() {
            var id = $(this).data('id');
            var drawId = $(this).data('draw-id');
            var name = $(this).data('name');
            var contact = $(this).data('contact');

            $('#edit_entry_id').val(id);
            $('#edit_lucky_draw_id').val(drawId);
            $('#edit_customer_name').val(name);
            $('#edit_customer_contact').val(contact);
            $('#editEntryModal').modal('show');
        });

        // Submit Edit Entry Form
        $('#editEntryForm').on('submit', function(e) {
            e.preventDefault();
            var id = $('#edit_entry_id').val();
            var btn = $('#edit-submit-btn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...');

            var url = "{{ route('pos.lucky-draw.update', ':id') }}".replace(':id', id);

            $.ajax({
                url: url,
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#editEntryModal').modal('hide');
                        table.ajax.reload(null, false);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || "Failed to update entry.");
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Update Details');
                }
            });
        });

        // Copy URL functionality
        $(document).on('click', '.copy-url-btn, #modal-copy-btn', function() {
            var url = $(this).data('url') || $('#share-scratch-url').val();
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(function() {
                    toastr.success("Scratch card URL copied to clipboard!");
                }).catch(function() {
                    fallbackCopyUrl(url);
                });
            } else {
                fallbackCopyUrl(url);
            }
        });

        function fallbackCopyUrl(url) {
            var textArea = document.createElement("textarea");
            textArea.value = url;
            textArea.style.position = "fixed";
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.width = "2em";
            textArea.style.height = "2em";
            textArea.style.padding = "0";
            textArea.style.border = "none";
            textArea.style.outline = "none";
            textArea.style.boxShadow = "none";
            textArea.style.background = "transparent";

            var modalOpen = document.querySelector('.modal.show .modal-body');
            var container = modalOpen || document.body;
            container.appendChild(textArea);
            textArea.focus();
            textArea.select();
            textArea.setSelectionRange(0, 99999);

            try {
                var successful = document.execCommand('copy');
                if (successful) {
                    toastr.success("Scratch card URL copied to clipboard!");
                } else {
                    toastr.info("Please copy the URL manually.");
                }
            } catch (err) {
                toastr.info("Please copy the URL manually.");
            }
            container.removeChild(textArea);
        }
    });
</script>
@endpush
