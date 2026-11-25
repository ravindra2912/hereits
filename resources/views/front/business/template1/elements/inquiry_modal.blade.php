<!-- Inquiry Popup Modal -->
<div class="modal fade" id="inquiryModal" tabindex="-1" aria-labelledby="inquiryModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-light py-3 px-4">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-chat-dots-fill text-primary"></i>
                    </div>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="inquiryModalTitle">Send Inquiry</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="inquiryModalForm">
                @csrf
                <input type="hidden" name="inquiry_type" id="inquiryType" value="contact_us">
                <input type="hidden" name="source_id" id="inquirySourceId" value="">

                <div class="modal-body p-4">
                    <div id="inquiryModalAlert" class="alert alert-danger d-none py-2 px-3 small rounded-3 mb-3"></div>

                    <!-- Target Item Context Card (Populated dynamically via JS) -->
                    <div id="inquiryItemInfo" class="d-none bg-light border border-light-subtle rounded-3 p-3 mb-3 d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 text-primary fs-3" id="inquiryItemIconContainer">
                            <i class="bi bi-box-seam" id="inquiryItemIcon"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0-5 small mb-1" id="inquiryItemTypeLabel">Product</span>
                            <div class="fw-bold text-dark text-truncate" id="inquiryItemName"></div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="inquiryName" class="form-label small fw-bold text-muted text-uppercase ls-1">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3" id="inquiryName" name="name" required
                                value="{{ auth()->check() ? trim(auth()->user()->first_name . ' ' . auth()->user()->last_name) : '' }}"
                                placeholder="Your Name">
                        </div>

                        <div class="col-md-6">
                            <label for="inquiryContact" class="form-label small fw-bold text-muted text-uppercase ls-1">Contact Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control rounded-3" id="inquiryContact" name="contact" required
                                value="{{ auth()->check() ? auth()->user()->contact : '' }}"
                                placeholder="10-digit mobile number">
                        </div>

                        <div class="col-12">
                            <label for="inquiryEmail" class="form-label small fw-bold text-muted text-uppercase ls-1">Email Address</label>
                            <input type="email" class="form-control rounded-3" id="inquiryEmail" name="email"
                                value="{{ auth()->check() ? auth()->user()->email : '' }}"
                                placeholder="name@example.com (optional)">
                        </div>

                        <div class="col-12" id="inquirySubjectGroup">
                            <label for="inquirySubject" class="form-label small fw-bold text-muted text-uppercase ls-1">Subject</label>
                            <input type="text" class="form-control rounded-3" id="inquirySubject" name="subject"
                                placeholder="What is this inquiry about?">
                        </div>

                        <div class="col-12">
                            <label for="inquiryMessage" class="form-label small fw-bold text-muted text-uppercase ls-1">Message</label>
                            <textarea class="form-control rounded-3" id="inquiryMessage" name="message" rows="3"
                                placeholder="Add any details, questions, or requirements..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 bg-light py-3 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="inquiryModalSubmitBtn">
                        <i class="bi bi-send me-1"></i> Submit Inquiry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

