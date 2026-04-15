<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="shareModalLabel">Share this <span id="shareItemType">Item</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4 small">Copy the link or share it directly on your favorite platforms.</p>

                <!-- Copy Link Input -->
                <div class="input-group mb-4 bg-light p-2 rounded-3 border">
                    <input type="text" id="shareLinkInput" class="form-control border-0 bg-transparent shadow-none" readonly>
                    <button class="btn btn-primary rounded-3 px-3" type="button" onclick="copyShareLink()" id="copyBtn">
                        <i class="far fa-copy me-1"></i> Copy
                    </button>
                </div>

                <!-- Social Icons Grid -->
                <div class="row g-3 text-center">
                    <div class="col-3">
                        <a href="#" id="shareWhatsapp" target="_blank" class="text-decoration-none">
                            <div class="bg-success bg-opacity-10 text-success p-3 rounded-4 mb-2 hover-lift">
                                <i class="bi bi-whatsapp fs-2"></i>
                            </div>
                            <span class="small text-dark fw-medium">WhatsApp</span>
                        </a>
                    </div>
                    <div class="col-3">
                        <a href="#" id="shareFacebook" target="_blank" class="text-decoration-none">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-4 mb-2 hover-lift">
                                <i class="bi bi-facebook fs-2 text-white"></i>
                            </div>
                            <span class="small text-dark fw-medium">Facebook</span>
                        </a>
                    </div>
                    <div class="col-3">
                        <a href="#" id="shareTwitter" target="_blank" class="text-decoration-none">
                            <div class="bg-dark bg-opacity-10 text-dark p-3 rounded-4 mb-2 hover-lift">
                                <i class="bi bi-twitter-x fs-2"></i>
                            </div>
                            <span class="small text-dark fw-medium">X (Twitter)</span>
                        </a>
                    </div>
                    <div class="col-3">
                        <a href="https://www.instagram.com" target="_blank" class="text-decoration-none">
                            <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-4 mb-2 hover-lift">
                                <i class="bi bi-instagram fs-2"></i>
                            </div>
                            <span class="small text-dark fw-medium">Instagram</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    function openShareModal(url, type, title) {
        // Set values in modal
        $('#shareLinkInput').val(url);
        $('#shareItemType').text(type || 'Item');

        let shareMessage = "";
        const businessName = "{{ $business->name ?? '' }}";

        if (type === 'Product') {
            shareMessage = `🛒 Check out this amazing product: ${title}! \nGet it now at ${businessName}.`;
        } else if (type === 'Service') {
            shareMessage = `🛠️ Highly recommended service: ${title} \nOffered by ${businessName}. Check details here:`;
        } else if (type === 'Expert') {
            shareMessage = `👤 Book an appointment with ${title} \nExpert at ${businessName}. View profile:`;
        } else {
            shareMessage = `✨ Check this out: ${title} at ${businessName}`;
        }

        // Encode for URLs
        const encodedUrl = encodeURIComponent(url);
        const encodedTitle = encodeURIComponent(shareMessage);

        // Update Share Links
        $('#shareWhatsapp').attr('href', `https://wa.me/?text=${encodedTitle}%0A${encodedUrl}`);
        $('#shareFacebook').attr('href', `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`);
        $('#shareTwitter').attr('href', `https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodedTitle}`);

        // Show Modal
        const modal = new bootstrap.Modal(document.getElementById('shareModal'));
        modal.show();
    }

    function copyShareLink() {
        const copyText = document.getElementById("shareLinkInput");
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile
        navigator.clipboard.writeText(copyText.value).then(() => {
            const copyBtn = document.getElementById('copyBtn');
            const originalText = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied';
            copyBtn.classList.replace('btn-primary', 'btn-success');

            setTimeout(() => {
                copyBtn.innerHTML = originalText;
                copyBtn.classList.replace('btn-success', 'btn-primary');
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }
</script>
@endpush