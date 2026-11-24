@extends('business.layouts.main')

@section('title', 'Business Inquiries')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/business/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/business/css/datatables-combined.min.css')) }}" />
<link rel="stylesheet" href="{{ asset('assets/business/css/inquiries.css') }}?v={{ filemtime(public_path('assets/business/css/inquiries.css')) }}" />
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- Header Section with Credit Status & Title -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-chat-left-dots text-primary me-2"></i>Inquiry Management
            </h4>
            <div class="text-muted small">Manage customer inquiries, unlock leads with credits, and track sales pipeline.</div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="credit-balance-pill rounded-pill px-3 py-1.5 d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-coin text-warning fs-5"></i>
                <span class="small fw-semibold">Credits:</span>
                <span class="fw-bold text-dark available-credit-amount">{{ number_format($availableCredit, 2) }}</span>
            </div>
            <a href="{{ route('business.credits') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
                <i class="bi bi-plus-circle me-1"></i>Recharge
            </a>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 d-flex flex-row align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary">
                    <i class="bi bi-chat-left-text-fill fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Inquiries</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($stats['total']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 d-flex flex-row align-items-center">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3 text-warning">
                    <i class="bi bi-hourglass-split fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Pending (Awaiting Action)</div>
                    <div class="fs-4 fw-bold text-warning-emphasis">{{ number_format($stats['pending']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 d-flex flex-row align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3 text-success">
                    <i class="bi bi-check2-circle fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Accepted (Active Pipeline)</div>
                    <div class="fs-4 fw-bold text-success">{{ number_format($stats['accepted']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 d-flex flex-row align-items-center">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3 text-info">
                    <i class="bi bi-trophy-fill fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Converted Deals</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($stats['converted']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card with Tabs & DataTable -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">

            <!-- System Status Nav Pills (Quick Filter) -->
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-3 mb-3 border-bottom">
                <ul class="nav nav-pills gap-2 m-0" id="inquiryStatusTabs">
                    <li class="nav-item">
                        <button type="button" class="nav-link filter-status-tab rounded-pill px-3 py-1.5 fw-semibold active" data-status="">
                            All Inquiries
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link filter-status-tab rounded-pill px-3 py-1.5 fw-semibold" data-status="pending">
                            <i class="bi bi-hourglass-split me-1 text-warning"></i>Pending Acceptance
                            @if($stats['pending'] > 0)
                                <span class="badge bg-danger rounded-pill ms-1">{{ $stats['pending'] }}</span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link filter-status-tab rounded-pill px-3 py-1.5 fw-semibold" data-status="accepted">
                            <i class="bi bi-check-circle me-1 text-success"></i>Accepted (Sales Workflow)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link filter-status-tab rounded-pill px-3 py-1.5 fw-semibold" data-status="rejected">
                            <i class="bi bi-x-circle me-1 text-danger"></i>Rejected
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link filter-status-tab rounded-pill px-3 py-1.5 fw-semibold" data-status="not_fulfilled">
                            <i class="bi bi-slash-circle me-1 text-secondary"></i>Not Fulfilled
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Advanced Filters Row -->
            <div class="row g-2 mb-3 align-items-center">
                <div class="col-md-3">
                    <label class="form-label small text-muted fw-semibold mb-1">Sales Status (Accepted)</label>
                    <select class="form-select form-select-sm rounded-3" id="filter_business_status">
                        <option value="">All Sales Statuses</option>
                        <option value="new">New</option>
                        <option value="contacted">Contacted</option>
                        <option value="follow_up">Follow Up</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="converted">Converted</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted fw-semibold mb-1">Inquiry Type</label>
                    <select class="form-select form-select-sm rounded-3" id="filter_inquiry_type">
                        <option value="">All Types</option>
                        <option value="contact_us">Contact Us</option>
                        <option value="product">Product</option>
                        <option value="service">Service</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted fw-semibold mb-1">Assigned Staff</label>
                    <select class="form-select form-select-sm rounded-3" id="filter_assigned_to">
                        <option value="">All Staff</option>
                        <option value="unassigned">Unassigned</option>
                        @foreach($staffUsers as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end pt-3">
                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 w-100" id="btn_reset_filters">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filters
                    </button>
                </div>
            </div>

            <!-- Inquiries DataTable -->
            <div class="table-responsive mt-3">
                <table class="table align-middle table-hover mb-0 w-100" id="inquiries-table">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3 py-3 text-secondary text-uppercase small fw-bold border-0">ID & Date</th>
                            <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Type & Items</th>
                            <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Customer Info</th>
                            <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">System Status</th>
                            <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Sales Status</th>
                            <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Assigned Staff</th>
                            <th class="pe-3 py-3 text-secondary text-uppercase small fw-bold border-0 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal 1: View Inquiry Details -->
<!-- ========================================================================= -->
<div class="modal fade" id="viewInquiryModal" tabindex="-1" aria-labelledby="viewInquiryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="modal-title fw-bold text-dark" id="viewInquiryModalLabel">
                        Inquiry Details <span class="text-primary" id="view_inquiry_id_text"></span>
                    </h5>
                    <span class="badge bg-light text-muted border small" id="view_inquiry_created_at"></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">

                <!-- Privacy Notice for Pending/Locked inquiries -->
                <div id="view_customer_locked_notice" class="privacy-notice-banner mb-3 d-none">
                    <div class="d-flex align-items-start gap-3">
                        <div class="text-warning fs-3">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark mb-1">Customer Information is Protected</div>
                            <div class="small text-muted mb-2">
                                Customer phone number and email are hidden until you accept this inquiry. Accepting will deduct <strong id="view_locked_charge_text">1.00 credit(s)</strong> and unlock complete contact details.
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary text-white px-2 py-1"><i class="bi bi-person me-1"></i>Name: <span id="view_locked_masked_name">Protected</span></span>
                                <span class="badge bg-secondary text-white px-2 py-1"><i class="bi bi-telephone me-1"></i>Phone: <span id="view_locked_masked_contact">Protected</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unlocked Customer Contact Info -->
                <div id="view_customer_unlocked_section" class="card border border-success-subtle bg-success bg-opacity-10 rounded-3 p-3 mb-3 d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-success small text-uppercase">
                            <i class="bi bi-check-circle-fill me-1"></i>Customer Contact Details (Unlocked)
                        </span>
                        <span class="badge bg-white text-success border border-success-subtle rounded-pill small" id="view_accepted_at_badge"></span>
                    </div>
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <div class="small text-muted">Customer Name:</div>
                            <div class="fw-bold text-dark fs-6" id="view_customer_name">N/A</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-muted">Phone Number:</div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-dark" id="view_customer_contact">N/A</span>
                                <a href="#" id="view_customer_contact_call" class="btn btn-xs btn-outline-primary rounded-circle" title="Call Customer"><i class="bi bi-telephone"></i></a>
                                <a href="#" id="view_customer_contact_wa" target="_blank" class="btn btn-xs btn-outline-success rounded-circle" title="WhatsApp Chat"><i class="bi bi-whatsapp"></i></a>
                            </div>
                        </div>
                        <div class="col-sm-12" id="view_customer_email_row">
                            <div class="small text-muted">Email Address:</div>
                            <div>
                                <a href="#" id="view_customer_email_link" class="text-decoration-none text-dark fw-medium" id="view_customer_email"></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Overview Strip -->
                <div class="row g-2 mb-3">
                    <div class="col-sm-4">
                        <div class="p-2.5 rounded-3 bg-light border text-center">
                            <div class="small text-muted">System Status</div>
                            <div class="fw-bold text-dark mt-0.5" id="view_system_status">-</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-2.5 rounded-3 bg-light border text-center">
                            <div class="small text-muted">Sales Status</div>
                            <div class="fw-bold text-primary mt-0.5" id="view_business_status">-</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-2.5 rounded-3 bg-light border text-center">
                            <div class="small text-muted">Assigned Staff</div>
                            <div class="fw-bold text-dark mt-0.5" id="view_assigned_staff">Unassigned</div>
                        </div>
                    </div>
                    <div class="col-12 d-none" id="view_follow_up_row">
                        <div class="p-2 rounded-3 bg-warning bg-opacity-10 border border-warning-subtle text-warning-emphasis small d-flex align-items-center gap-2">
                            <i class="bi bi-alarm-fill fs-6"></i>
                            <span>Next Follow Up Scheduled:</span>
                            <span class="fw-bold" id="view_follow_up_text"></span>
                        </div>
                    </div>
                </div>

                <!-- Customer Message / Description -->
                <div class="mb-3">
                    <h6 class="fw-bold text-secondary small text-uppercase mb-2">
                        <i class="bi bi-envelope-open me-1 text-primary"></i>Customer Requirement / Message
                    </h6>
                    <div class="p-3 bg-light rounded-3 text-dark small" id="view_inquiry_message" style="white-space: pre-line;"></div>
                </div>

                <!-- Associated Products/Services -->
                <div class="mb-2">
                    <h6 class="fw-bold text-secondary small text-uppercase mb-2">
                        <i class="bi bi-boxes me-1 text-primary"></i>Inquired Items / References
                    </h6>
                    <div id="view_inquiry_sources_list"></div>
                </div>

            </div>
            <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                <!-- Actions when pending -->
                <div id="view_modal_pending_actions" class="d-flex align-items-center justify-content-between w-100 gap-2 flex-wrap">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-danger rounded-pill px-3 btn-sm" id="btn_modal_reject_inquiry">
                            <i class="bi bi-x-circle me-1"></i>Reject
                        </button>
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3 btn-sm" id="btn_modal_not_fulfilled_inquiry">
                            <i class="bi bi-slash-circle me-1"></i>Not Fulfilled
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" id="btn_modal_accept_inquiry">
                            <i class="bi bi-unlock-fill me-1"></i>Accept & Unlock Details
                        </button>
                    </div>
                </div>

                <!-- Actions when already accepted -->
                <div id="view_modal_accepted_actions" class="d-flex align-items-center justify-content-between w-100 gap-2 flex-wrap d-none">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3 btn-sm" id="btn_modal_quick_logs">
                        <i class="bi bi-journal-text me-1"></i>View Activity Timeline
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-info rounded-pill px-3 btn-sm" id="btn_modal_quick_assign">
                            <i class="bi bi-person-check me-1"></i>Assign Staff
                        </button>
                        <button type="button" class="btn btn-primary rounded-pill px-3 btn-sm fw-semibold" id="btn_modal_quick_status">
                            <i class="bi bi-arrow-repeat me-1"></i>Update Sales Status
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal 2: Accept Inquiry Confirmation & Credit Deduction -->
<!-- ========================================================================= -->
<div class="modal fade" id="acceptInquiryModal" tabindex="-1" aria-labelledby="acceptInquiryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="acceptInquiryModalLabel">
                    <i class="bi bi-unlock-fill text-success me-2"></i>Accept Inquiry & Unlock Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form_accept_inquiry">
                @csrf
                <input type="hidden" name="inquiry_id" id="accept_inquiry_id">
                <div class="modal-body p-4">

                    <!-- Credit Cost Box -->
                    <div class="p-3 rounded-3 bg-light border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted fw-semibold">Required Charge:</span>
                            <span class="badge bg-primary text-white rounded-pill px-2.5 py-1">
                                <span id="accept_required_credits">1.00</span> Credits
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted fw-semibold">Available Balance:</span>
                            <span class="fw-bold text-dark">
                                <span id="accept_available_credits">{{ number_format($availableCredit, 2) }}</span> Credits
                            </span>
                        </div>
                    </div>

                    <!-- Insufficient Balance Warning -->
                    <div id="accept_insufficient_warning" class="alert alert-danger rounded-3 py-2.5 px-3 small d-none mb-3">
                        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>Insufficient Credit Balance!</div>
                        <div>You do not have enough credits to accept this inquiry. Please recharge your wallet to proceed.</div>
                        <a href="{{ route('business.credits') }}" class="btn btn-danger btn-sm rounded-pill mt-2">
                            <i class="bi bi-plus-circle me-1"></i>Recharge Credits Now
                        </a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold mb-1">Remarks / Note (Optional)</label>
                        <textarea class="form-control rounded-3" name="remarks" id="accept_inquiry_remarks" rows="2" placeholder="e.g. Assigned to sales rep for immediate follow up"></textarea>
                    </div>

                    <div class="small text-muted fst-italic">
                        <i class="bi bi-info-circle me-1"></i>Once accepted, customer phone number and email will be unlocked. Credit deduction is non-refundable.
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" id="btn_confirm_accept">
                        <i class="bi bi-unlock-fill me-1"></i>Confirm & Deduct Credit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal 3: Reject Inquiry -->
<!-- ========================================================================= -->
<div class="modal fade" id="rejectInquiryModal" tabindex="-1" aria-labelledby="rejectInquiryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold text-danger" id="rejectInquiryModalLabel">
                    <i class="bi bi-x-circle-fill me-2"></i>Reject Inquiry
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form_reject_inquiry">
                @csrf
                <input type="hidden" name="inquiry_id" id="reject_inquiry_id">
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Are you sure you want to reject this inquiry? No credits will be deducted.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold mb-1">Reason for Rejection (Optional)</label>
                        <textarea class="form-control rounded-3" name="remarks" id="reject_inquiry_remarks" rows="3" placeholder="e.g. Out of stock, not servicing in customer area, duplicate inquiry..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm" id="btn_confirm_reject">
                        <i class="bi bi-x-circle me-1"></i>Reject Inquiry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal 4: Mark Not Fulfilled -->
<!-- ========================================================================= -->
<div class="modal fade" id="notFulfilledInquiryModal" tabindex="-1" aria-labelledby="notFulfilledInquiryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="notFulfilledInquiryModalLabel">
                    <i class="bi bi-slash-circle text-secondary me-2"></i>Mark as Not Fulfilled
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form_not_fulfilled_inquiry">
                @csrf
                <input type="hidden" name="inquiry_id" id="not_fulfilled_inquiry_id">
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Mark this inquiry as cannot be fulfilled. No credits will be deducted.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold mb-1">Reason (Optional)</label>
                        <textarea class="form-control rounded-3" name="remarks" id="not_fulfilled_inquiry_remarks" rows="3" placeholder="e.g. Specialized requirement not supported, location unreachable..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary rounded-pill px-4 fw-bold shadow-sm" id="btn_confirm_not_fulfilled">
                        <i class="bi bi-slash-circle me-1"></i>Mark Not Fulfilled
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal 5: Update Business Sales Status -->
<!-- ========================================================================= -->
<div class="modal fade" id="statusInquiryModal" tabindex="-1" aria-labelledby="statusInquiryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="statusInquiryModalLabel">
                    <i class="bi bi-arrow-repeat text-primary me-2"></i>Update Sales Workflow Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form_status_inquiry">
                @csrf
                <input type="hidden" name="inquiry_id" id="status_inquiry_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold mb-1">Sales Status <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" name="business_status" id="status_inquiry_select" required>
                            <option value="new">New (Newly Accepted)</option>
                            <option value="contacted">Contacted (Customer reached)</option>
                            <option value="follow_up">Follow Up (Scheduled reminder)</option>
                            <option value="scheduled">Scheduled (Meeting / Appointment)</option>
                            <option value="converted">Converted (Deal won / Success)</option>
                            <option value="closed">Closed (Inquiry ended without deal)</option>
                        </select>
                    </div>

                    <div class="mb-3 d-none" id="status_follow_up_container">
                        <label class="form-label small text-muted fw-semibold mb-1">Next Follow-up Date & Time</label>
                        <input type="datetime-local" class="form-control rounded-3" name="next_follow_up_at" id="status_inquiry_next_follow_up">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold mb-1">Remarks / Activity Notes</label>
                        <textarea class="form-control rounded-3" name="remarks" id="status_inquiry_remarks" rows="3" placeholder="e.g. Called customer, requested price quote, meeting set for tomorrow..."></textarea>
                    </div>

                    <div class="small text-muted fst-italic">
                        <i class="bi bi-shield-check me-1 text-success"></i>Business sales status changes never deduct additional credits.
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="btn_confirm_status">
                        <i class="bi bi-check-lg me-1"></i>Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal 6: Assign Staff -->
<!-- ========================================================================= -->
<div class="modal fade" id="assignInquiryModal" tabindex="-1" aria-labelledby="assignInquiryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="assignInquiryModalLabel">
                    <i class="bi bi-person-check text-info me-2"></i>Assign Staff to Inquiry
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form_assign_inquiry">
                @csrf
                <input type="hidden" name="inquiry_id" id="assign_inquiry_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold mb-1">Select Employee / Staff</label>
                        <select class="form-select rounded-3" name="assigned_to" id="assign_inquiry_staff_select">
                            <option value="">-- Unassigned --</option>
                            @foreach($staffUsers as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold mb-1">Remarks (Optional)</label>
                        <textarea class="form-control rounded-3" name="remarks" id="assign_inquiry_remarks" rows="2" placeholder="e.g. Assigning to senior consultant"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info text-white rounded-pill px-4 fw-bold shadow-sm" id="btn_confirm_assign">
                        <i class="bi bi-person-check me-1"></i>Save Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal 7: Activity History Timeline & Notes -->
<!-- ========================================================================= -->
<div class="modal fade" id="timelineInquiryModal" tabindex="-1" aria-labelledby="timelineInquiryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="timelineInquiryModalLabel">
                    <i class="bi bi-journal-text text-secondary me-2"></i>Inquiry Activity Timeline & Notes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">

                <!-- Add Note Form -->
                <form id="form_add_note" class="mb-4">
                    @csrf
                    <input type="hidden" name="inquiry_id" id="timeline_inquiry_id">
                    <div class="p-3 bg-light rounded-3 border">
                        <label class="form-label small fw-bold text-dark mb-1">
                            <i class="bi bi-pencil-square me-1 text-primary"></i>Add New Internal Note
                        </label>
                        <textarea class="form-control rounded-3 mb-2" name="remarks" id="note_inquiry_remarks" rows="2" placeholder="Write internal remarks or conversation updates..." required></textarea>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold" id="btn_save_note">
                                <i class="bi bi-send me-1"></i>Add Note
                            </button>
                        </div>
                    </div>
                </form>

                <h6 class="fw-bold text-secondary small text-uppercase mb-3">
                    <i class="bi bi-clock-history me-1 text-primary"></i>History & Logs
                </h6>

                <!-- Timeline Container -->
                <div class="inquiry-timeline" id="timeline_logs_container">
                    <div class="text-center py-4 text-muted">
                        <div class="spinner-border spinner-border-sm me-2"></div>Loading history...
                    </div>
                </div>

            </div>
            <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script src="{{ asset('assets/business/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/business/js/datatables-combined.min.js')) }}"></script>
<script>
    window.InquiriesConfig = {
        availableCredit: {{ (float)$availableCredit }},
        inquiryCharges: @json($inquiryCharges),
        routes: {
            index: "{{ route('business.inquiries.index') }}",
            show: "{{ route('business.inquiries.show', ':id') }}",
            accept: "{{ route('business.inquiries.accept', ':id') }}",
            reject: "{{ route('business.inquiries.reject', ':id') }}",
            notFulfilled: "{{ route('business.inquiries.not-fulfilled', ':id') }}",
            businessStatus: "{{ route('business.inquiries.business-status', ':id') }}",
            assign: "{{ route('business.inquiries.assign', ':id') }}",
            addNote: "{{ route('business.inquiries.notes.add', ':id') }}",
            logs: "{{ route('business.inquiries.logs', ':id') }}"
        }
    };
</script>
<script src="{{ asset('assets/business/js/inquiries.js') }}?v={{ filemtime(public_path('assets/business/js/inquiries.js')) }}"></script>
@endpush

