/**
 * Cookie Consent Manager with Google Consent Mode v2 Integration
 * Supports GA4, Meta Pixel, and Google AdSense
 */

(function () {
    // 1. Google Consent Mode v2 Default State
    window.dataLayer = window.dataLayer || [];
    function gtag() {
        window.dataLayer.push(arguments);
    }
    window.gtag = window.gtag || gtag;

    const savedConsent = localStorage.getItem('hereits_cookie_consent');

    if (!savedConsent) {
        // Default to denied until user gives explicit consent (GDPR / Google Consent Mode v2 compliance)
        gtag('consent', 'default', {
            'analytics_storage': 'denied',
            'ad_storage': 'denied',
            'ad_user_data': 'denied',
            'ad_personalization': 'denied',
            'wait_for_update': 500
        });
    } else if (savedConsent === 'accepted') {
        gtag('consent', 'default', {
            'analytics_storage': 'granted',
            'ad_storage': 'granted',
            'ad_user_data': 'granted',
            'ad_personalization': 'granted'
        });
    } else {
        gtag('consent', 'default', {
            'analytics_storage': 'denied',
            'ad_storage': 'denied',
            'ad_user_data': 'denied',
            'ad_personalization': 'denied'
        });
    }

    // 2. Render Banner if Consent is not yet decided
    function initCookieBanner() {
        if (savedConsent) {
            return;
        }

        const banner = document.getElementById('hereits-cookie-banner');
        if (!banner) {
            return;
        }

        // Show after 1 second delay
        setTimeout(function () {
            banner.style.display = 'flex';
        }, 1000);

        const acceptBtn = document.getElementById('cookie-accept-all');
        const declineBtn = document.getElementById('cookie-essential-only');

        if (acceptBtn) {
            acceptBtn.addEventListener('click', function () {
                updateConsent('accepted');
            });
        }

        if (declineBtn) {
            declineBtn.addEventListener('click', function () {
                updateConsent('declined');
            });
        }
    }

    function updateConsent(choice) {
        localStorage.setItem('hereits_cookie_consent', choice);

        const isGranted = choice === 'accepted';
        gtag('consent', 'update', {
            'analytics_storage': isGranted ? 'granted' : 'denied',
            'ad_storage': isGranted ? 'granted' : 'denied',
            'ad_user_data': isGranted ? 'granted' : 'denied',
            'ad_personalization': isGranted ? 'granted' : 'denied'
        });

        window.dispatchEvent(new CustomEvent('hereitsCookieConsent', {
            detail: { status: choice, isGranted: isGranted }
        }));

        const banner = document.getElementById('hereits-cookie-banner');
        if (banner) {
            banner.classList.add('cookie-consent-hiding');
            setTimeout(function () {
                banner.style.display = 'none';
            }, 300);
        }
    }

    // Expose reset function for testing or settings page
    window.resetCookieConsent = function () {
        localStorage.removeItem('hereits_cookie_consent');
        const banner = document.getElementById('hereits-cookie-banner');
        if (banner) {
            banner.classList.remove('cookie-consent-hiding');
            banner.style.display = 'flex';
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCookieBanner);
    } else {
        initCookieBanner();
    }
})();
