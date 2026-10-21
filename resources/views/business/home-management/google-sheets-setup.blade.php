@extends('business.layouts.main')

@section('title', 'Google Sheets Integration Guide')

@push('style')
<style>
    .step-badge {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: var(--bs-primary);
        color: #fff;
        font-weight: bold;
    }
    .code-container {
        position: relative;
        background-color: #1e1e1e;
        border-radius: 12px;
        padding: 1.5rem;
    }
    .code-container pre {
        margin: 0;
        color: #d4d4d4;
        font-family: 'Courier New', Courier, monospace;
        font-size: 0.9rem;
        overflow-x: auto;
    }
    .copy-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: rgba(255, 255, 255, 0.15);
        color: #fff;
        border: none;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 0.8rem;
        transition: all 0.2s ease;
    }
    .copy-btn:hover {
        background-color: rgba(255, 255, 255, 0.3);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>Google Sheets Webhook Setup Guide
            </h4>
            <p class="text-muted mb-0">Follow this guide to automatically push campaign leads directly to your Google Spreadsheet in real-time.</p>
        </div>
        <a href="{{ route('business.home-management.settings') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
            <i class="bi bi-arrow-left me-1"></i> Back to Settings
        </a>
    </div>

    <div class="row g-4">
        <!-- Steps Column -->
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">
                    <i class="bi bi-list-ol me-2 text-primary"></i>Step-by-Step Instructions
                </h5>

                <div class="d-flex flex-column gap-4">
                    <!-- Step 1 -->
                    <div class="d-flex gap-3">
                        <span class="step-badge mt-0.5">1</span>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Create a Google Spreadsheet</h6>
                            <p class="text-secondary small mb-0">Open <a href="https://sheets.google.com" target="_blank" class="fw-semibold">Google Sheets</a>, create a new spreadsheet, and ensure the active sheet is named <strong>"Sheet1"</strong>.</p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="d-flex gap-3">
                        <span class="step-badge mt-0.5">2</span>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Open Apps Script Editor</h6>
                            <p class="text-secondary small mb-0">From the top menu, go to <strong>Extensions</strong> &gt; <strong>Apps Script</strong>. This will open the Google Apps Script code editor in a new tab.</p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="d-flex gap-3">
                        <span class="step-badge mt-0.5">3</span>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Paste the Webhook Code</h6>
                            <p class="text-secondary small mb-2">Delete any default code in the script editor and paste the code from the right panel. Save the project by clicking the save disk icon <i class="bi bi-floppy"></i> or pressing Ctrl + S.</p>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="d-flex gap-3">
                        <span class="step-badge mt-0.5">4</span>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Configure Web App Deployment</h6>
                            <p class="text-secondary small mb-2">Click the <strong>Deploy</strong> button in the top-right corner, then select <strong>New deployment</strong>.</p>
                            <ul class="text-secondary small ps-3 mb-0">
                                <li>Click the gear icon next to "Select type" and choose <strong>Web app</strong>.</li>
                                <li><strong>Execute as:</strong> Set this to <strong>Me (your email address)</strong>.</li>
                                <li><strong>Who has access:</strong> Set this to <strong>Anyone</strong>. *(Crucial for accepting posts from your website)*</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Step 5 -->
                    <div class="d-flex gap-3">
                        <span class="step-badge mt-0.5">5</span>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Deploy and Authorize</h6>
                            <p class="text-secondary small mb-0">Click <strong>Deploy</strong>. If prompted, authorize access by signing in and clicking "Advanced" &gt; "Go to Untitled project (unsafe)" to grant write permissions to your sheet.</p>
                        </div>
                    </div>

                    <!-- Step 6 -->
                    <div class="d-flex gap-3">
                        <span class="step-badge mt-0.5">6</span>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Copy and Save URL</h6>
                            <p class="text-secondary small mb-0">Copy the generated <strong>Web app URL</strong> (it will end in <code>/exec</code>). Paste this URL in your Campaign Popup Settings under the Lead Form options and enable the switch.</p>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info border-0 rounded-3 mt-4 mb-0 d-flex gap-2">
                    <i class="bi bi-info-circle-fill text-info fs-5 mt-0.5"></i>
                    <div class="small">
                        <strong>Pro Tip:</strong> This code automatically detects new custom fields. If you add a new input to your popup lead form, Google Sheets will automatically append a new column header for it!
                    </div>
                </div>
            </div>
        </div>

        <!-- Code Column -->
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-code-slash me-2 text-danger"></i>Apps Script Code
                    </h5>
                </div>

                <div class="code-container">
                    <button class="copy-btn" id="btnCopyCode"><i class="bi bi-clipboard me-1"></i>Copy Code</button>
                    <pre><code id="webhookCode"></code></pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    const base64Code = "ZnVuY3Rpb24gZG9Qb3N0KGUpIHsKICB0cnkgewogICAgY29uc3Qgc2hlZXQgPSBTcHJlYWRzaGVldEFwcC5nZXRBY3RpdmVTcHJlYWRzaGVldCgpLmdldFNoZWV0QnlOYW1lKCJTaGVldDEiKTsKICAgIGNvbnN0IGRhdGEgPSBKU09OLnBhcnNlKGUucG9zdERhdGEuY29udGVudHMpOwogICAgCiAgICAvLyAxLiBHZXQgZXhpc3RpbmcgaGVhZGVycyBmcm9tIGZpcnN0IHJvdwogICAgbGV0IGxhc3RDb2x1bW4gPSBzaGVldC5nZXRMYXN0Q29sdW1uKCk7CiAgICBsZXQgaGVhZGVycyA9IFtdOwogICAgaWYgKGxhc3RDb2x1bW4gPiAwKSB7CiAgICAgIGhlYWRlcnMgPSBzaGVldC5nZXRSYW5nZSgxLCAxLCAxLCBsYXN0Q29sdW1uKS5nZXRWYWx1ZXMoKVswXTsKICAgIH0KICAgIAogICAgLy8gMi4gU2NhbiBpbmNvbWluZyBrZXlzIGFuZCBhcHBlbmQgYW55IG1pc3NpbmcgaGVhZGVycwogICAgbGV0IGhlYWRlcnNDaGFuZ2VkID0gZmFsc2U7CiAgICBPYmplY3Qua2V5cyhkYXRhKS5mb3JFYWNoKGtleSA9PiB7CiAgICAgIGlmIChoZWFkZXJzLmluZGV4T2Yoa2V5KSA9PT0gLTEpIHsKICAgICAgICBoZWFkZXJzLnB1c2goa2V5KTsKICAgICAgICBoZWFkZXJzQ2hhbmdlZCA9IHRydWU7CiAgICAgIH0KICAgIH0pOwogICAgCiAgICAvLyAzLiBXcml0ZSBoZWFkZXJzIGJhY2sgdG8gdGhlIGZpcnN0IHJvdyBpZiBuZXcgb25lcyB3ZXJlIGFkZGVkCiAgICBpZiAoaGVhZGVyc0NoYW5nZWQpIHsKICAgICAgc2hlZXQuZ2V0UmFuZ2UoMSwgMSwgMSwgaGVhZGVycy5sZW5ndGgpLnNldFZhbHVlcyhbaGVhZGVyc10pOwogICAgfQogICAgCiAgICAvLyA0LiBDcmVhdGUgcm93IG1hdGNoaW5nIGhlYWRlciBvcmRlcgogICAgY29uc3Qgcm93ID0gaGVhZGVycy5tYXAoaGVhZGVyID0+IGRhdGFbaGVhZGVyXSAhPT0gdW5kZWZpbmVkID8gZGF0YVtoZWFkZXJdIDogJycpOwogICAgc2hlZXQuYXBwZW5kUm93KHJvdyk7CiAgICAKICAgIHJldHVybiBDb250ZW50U2VydmljZQogICAgICAuY3JlYXRlVGV4dE91dHB1dChKU09OLnN0cmluZ2lmeSh7IHN1Y2Nlc3M6IHRydWUgfSkpCiAgICAgIC5zZXRNaW1lVHlwZShDb250ZW50U2VydmljZS5NaW1lVHlwZS5KU09OKTsKICAgICAgCiAgfSBjYXRjaCAoZXJyb3IpIHsKICAgIHJldHVybiBDb250ZW50U2VydmljZQogICAgICAuY3JlYXRlVGV4dE91dHB1dChKU09OLnN0cmluZ2lmeSh7IHN1Y2Nlc3M6IGZhbHNlLCBlcnJvcjogZXJyb3IudG9TdHJpbmcoKSB9KSkKICAgICAgLnNldE1pbWVUeXBlKENvbnRlbnRTZXJ2aWNlLk1pbWVUeXBlLkpTT04pOwogIH0KfQ==";
    
    // Decode and inject code text
    $('#webhookCode').text(atob(base64Code));

    $('#btnCopyCode').on('click', function() {
        const codeText = $('#webhookCode').text();
        navigator.clipboard.writeText(codeText).then(() => {
            const originalText = $(this).html();
            $(this).html('<i class="bi bi-check2 me-1"></i>Copied!');
            $(this).addClass('bg-success');
            setTimeout(() => {
                $(this).html(originalText);
                $(this).removeClass('bg-success');
            }, 2000);
        }).catch(err => {
            alert('Failed to copy code: ' + err);
        });
    });
});
</script>
@endpush
