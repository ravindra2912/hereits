<link rel="stylesheet" href="{{ asset('assets/common/css/cookie-consent.css') }}?v={{ file_exists(public_path('assets/common/css/cookie-consent.css')) ? filemtime(public_path('assets/common/css/cookie-consent.css')) : time() }}">

<div id="hereits-cookie-banner" class="cookie-consent-banner" role="dialog" aria-live="polite" style="display: none;">
    <div class="cookie-consent-header">
        <div class="cookie-consent-icon-wrapper">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10c0-.55-.05-1.09-.13-1.62-.22-.05-.44-.09-.67-.09-1.66 0-3-1.34-3-3 0-.72.26-1.38.69-1.9-.38-.06-.77-.1-1.17-.1-2.21 0-4-1.79-4-4 0-.8.24-1.55.65-2.18-.45-.07-.91-.11-1.37-.11zm-1.5 5c.83 0 1.5.67 1.5 1.5S11.33 10 10.5 10 9 9.33 9 8.5 9.67 7 10.5 7zm-3 4c.83 0 1.5.67 1.5 1.5S8.33 14 7.5 14 6 13.33 6 12.5 6.67 11 7.5 11zm7.5 3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5-1.5-.67-1.5-1.5.67-1.5 1.5-1.5zm-4 2c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5-1.5-.67-1.5-1.5.67-1.5 1.5-1.5z"/></svg>
        </div>
        <div class="cookie-consent-body">
            <div class="cookie-consent-title">Cookie &amp; Privacy Preferences</div>
            <p class="cookie-consent-desc">
                We use cookies and similar technologies to enhance your experience, analyze traffic, and support marketing. Review our
                <a href="{{ Route::has('privacyPolicy') ? route('privacyPolicy') : '/privacy-policy' }}" class="cookie-consent-policy-link" target="_blank">Privacy Policy</a>.
            </p>
        </div>
    </div>
    <div class="cookie-consent-actions">
        <button type="button" class="cookie-btn-decline" id="cookie-essential-only">Essential Only</button>
        <button type="button" class="cookie-btn-accept" id="cookie-accept-all">Accept All</button>
    </div>
</div>

<script data-cfasync="false" src="{{ asset('assets/common/js/cookie-consent.js') }}?v={{ file_exists(public_path('assets/common/js/cookie-consent.js')) ? filemtime(public_path('assets/common/js/cookie-consent.js')) : time() }}"></script>
