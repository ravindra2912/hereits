/**
 * Firebase Web Push Notification Handler
 * Manages token registration, permission prompts, foreground notifications, and click redirections.
 * Compatible with Mobile Chrome (Android), Desktop Chrome, Edge, Safari (iOS PWA & macOS), and Firefox.
 */
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-app.js";
import { isSupported, getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-messaging.js";

(async function () {
    const config = window.firebaseConfig;
    if (!config || !config.apiKey || !config.projectId) {
        return;
    }

    // 1. Check if origin is a secure context (HTTPS or localhost)
    const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    const isHttps = window.location.protocol === 'https:';

    if (!isHttps && !isLocalhost) {
        // Automatically redirect http://hereits.test (or .test domains) to HTTPS if local SSL is active
        if (window.location.protocol === 'http:' && (window.location.hostname.endsWith('.test') || window.location.hostname === 'hereits.com')) {
            console.warn('⚠️ [Firebase Messaging] Push notifications require HTTPS. Redirecting to HTTPS...');
            window.location.replace(window.location.href.replace('http:', 'https:'));
            return;
        }

        console.warn(
            '⚠️ [Firebase Messaging] Push Notifications require HTTPS or localhost.\n' +
            'You are currently on: ' + window.location.origin + '\n' +
            'Web browsers automatically disable PushManager and Notification APIs on plain HTTP.\n\n' +
            '👉 For mobile testing:\n' +
            '1. In Laragon: Apache -> SSL -> Enabled\n' +
            '2. Open your site with HTTPS: https://' + window.location.host + '\n' +
            '3. For real mobile phone over USB: Chrome DevTools -> Remote Devices -> Port Forwarding (8000 -> localhost:8000)'
        );
        return;
    }

    // 2. Check iOS standalone status (iOS requires PWA Home Screen install for Web Push)
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    if (isIOS && !isStandalone) {
        console.info('ℹ️ [Firebase Messaging] On iOS Safari/Chrome, Web Push requires adding the site to your Home Screen ("Share" -> "Add to Home Screen").');
    }

    // 3. Check if browser environment supports Web Push and Service Workers
    const hasSW = 'serviceWorker' in navigator;
    const hasNotification = 'Notification' in window;
    const hasPushManager = 'PushManager' in window;

    if (!hasSW || !hasNotification || !hasPushManager) {
        console.warn(
            '⚠️ [Firebase Messaging] Missing browser APIs: ' +
            (!hasSW ? '[serviceWorker] ' : '') +
            (!hasNotification ? '[Notification] ' : '') +
            (!hasPushManager ? '[PushManager]' : '')
        );
        return;
    }

    console.info('🔔 [Firebase Messaging] Initializing... Permission status: "' + Notification.permission + '"');

    try {
        // 4. Verify Firebase SDK browser support
        const supported = await isSupported().catch(() => false);
        if (!supported) {
            console.warn('Firebase Messaging: Browser does not support Firebase messaging APIs in this environment.');
            return;
        }

        const app = initializeApp(config);
        const messaging = getMessaging(app);

        // 5. Register Service Worker
        const swParams = new URLSearchParams({
            apiKey: config.apiKey,
            projectId: config.projectId,
            messagingSenderId: config.messagingSenderId || '',
            appId: config.appId || ''
        });

        const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js?' + swParams.toString())
            .catch(function (err) {
                console.warn('Service Worker registration skipped:', err);
                return null;
            });

        if (registration) {
            // Function to trigger permission request directly from a user gesture
            const triggerPermissionRequest = function () {
                if (Notification.permission === 'granted') {
                    acquireToken(messaging, registration, config.vapidKey);
                    return;
                }

                if (Notification.permission === 'denied') {
                    console.warn('⚠️ [Firebase Messaging] Permission is "denied". Browser will not show prompt until user unblocks it in site settings.');
                    if (window.toastr) {
                        window.toastr.warning('Notifications are blocked in your browser. Please click the lock icon in your address bar to allow notifications.', 'Notifications Blocked');
                    } else {
                        alert('Notifications are blocked in your browser settings. To enable them, click the lock icon next to the address bar and switch Notifications to Allow.');
                    }
                    return;
                }

                // Request permission
                Notification.requestPermission().then(function (permission) {
                    console.info('🔔 [Firebase Messaging] User responded to permission prompt:', permission);
                    if (permission === 'granted') {
                        hidePromptCard();
                        hideFloatingBell();
                        acquireToken(messaging, registration, config.vapidKey);
                        if (window.toastr) {
                            window.toastr.success('Notifications enabled! You will now receive instant updates.', 'Notifications Enabled');
                        }
                    } else if (permission === 'denied') {
                        hidePromptCard();
                        hideFloatingBell();
                        console.warn('⚠️ [Firebase Messaging] User denied notification permission.');
                    }
                }).catch(function (err) {
                    console.warn('Notification permission request error:', err);
                });
            };

            // Expose globally so buttons or developer console can trigger it anytime
            window.requestWebPushPermission = triggerPermissionRequest;

            // Handle permission states
            if (Notification.permission === 'granted') {
                acquireToken(messaging, registration, config.vapidKey);
            } else if (Notification.permission === 'default') {
                // Check if user dismissed in this session
                const isDismissed = sessionStorage.getItem('hereits_fcm_prompt_dismissed');

                if (!isDismissed) {
                    // Show Soft Prompt Card after page stabilizes (700ms)
                    setTimeout(function () {
                        if (Notification.permission === 'default') {
                            showPromptCard(triggerPermissionRequest);
                        }
                    }, 700);
                } else {
                    // Show subtle floating bell widget for easy opt-in
                    showFloatingBell(triggerPermissionRequest);
                }
            } else if (Notification.permission === 'denied') {
                console.warn('⚠️ [Firebase Messaging] Notification permission is "denied" in this browser. To see prompt or test notifications, reset permission via Chrome URL bar lock icon.');
            }
        }

        // 6. Foreground Message Listener (when user is active on the tab)
        onMessage(messaging, function (payload) {
            const title = payload.notification?.title || payload.data?.title || 'Notification';
            const body = payload.notification?.body || payload.data?.body || payload.data?.message || '';
            const targetUrl = payload.fcmOptions?.link || payload.data?.click_url || payload.data?.url || '';

            if (window.toastr) {
                window.toastr.options = window.toastr.options || {};
                window.toastr.options.onclick = function () {
                    if (targetUrl) {
                        window.location.href = targetUrl;
                    }
                };
                window.toastr.info(body + (targetUrl ? '<br><small>Click to view</small>' : ''), title, {
                    timeOut: 10000,
                    extendedTimeOut: 2000,
                    closeButton: true,
                    progressBar: true
                });
            } else if (window.Swal) {
                window.Swal.fire({
                    title: title,
                    text: body,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'View Details',
                    cancelButtonText: 'Close'
                }).then(function (result) {
                    if (result.isConfirmed && targetUrl) {
                        window.location.href = targetUrl;
                    }
                });
            } else {
                if (confirm(title + "\n\n" + body + "\n\nClick OK to view.")) {
                    if (targetUrl) {
                        window.location.href = targetUrl;
                    }
                }
            }
        });

    } catch (e) {
        console.warn('Firebase Messaging init skipped:', e.message || e);
    }

    // =========================================================================
    // UI Prompt Components (Soft Prompt Card & Floating Opt-in Bell)
    // =========================================================================

    function showPromptCard(onAllowCallback) {
        if (document.getElementById('hereits-fcm-prompt') || Notification.permission !== 'default') {
            return;
        }

        const promptCard = document.createElement('div');
        promptCard.id = 'hereits-fcm-prompt';
        promptCard.className = 'fcm-prompt-card';
        promptCard.setAttribute('role', 'dialog');
        promptCard.setAttribute('aria-live', 'polite');

        promptCard.innerHTML =
            '<div class="fcm-prompt-header">' +
                '<div class="fcm-prompt-icon-wrapper">' +
                    '<svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z"/></svg>' +
                '</div>' +
                '<div class="fcm-prompt-text">' +
                    '<div class="fcm-prompt-title">Enable Notifications</div>' +
                    '<div class="fcm-prompt-desc">Allow notifications to receive instant updates for your bookings, orders, and messages.</div>' +
                '</div>' +
            '</div>' +
            '<div class="fcm-prompt-actions">' +
                '<button type="button" class="fcm-btn-dismiss" id="fcm-dismiss-btn">Later</button>' +
                '<button type="button" class="fcm-btn-allow" id="fcm-allow-btn">' +
                    '<svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:currentColor;"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>' +
                    'Allow Notifications' +
                '</button>' +
            '</div>';

        document.body.appendChild(promptCard);

        const allowBtn = document.getElementById('fcm-allow-btn');
        const dismissBtn = document.getElementById('fcm-dismiss-btn');

        if (allowBtn) {
            allowBtn.addEventListener('click', function () {
                onAllowCallback();
            });
        }

        if (dismissBtn) {
            dismissBtn.addEventListener('click', function () {
                sessionStorage.setItem('hereits_fcm_prompt_dismissed', '1');
                hidePromptCard();
                showFloatingBell(onAllowCallback);
            });
        }
    }

    function hidePromptCard() {
        const promptCard = document.getElementById('hereits-fcm-prompt');
        if (promptCard) {
            promptCard.classList.add('fcm-prompt-hiding');
            setTimeout(function () {
                if (promptCard.parentNode) {
                    promptCard.parentNode.removeChild(promptCard);
                }
            }, 300);
        }
    }

    function showFloatingBell(onClickCallback) {
        if (document.getElementById('hereits-fcm-bell') || Notification.permission !== 'default') {
            return;
        }

        const bellBtn = document.createElement('button');
        bellBtn.id = 'hereits-fcm-bell';
        bellBtn.className = 'fcm-floating-bell';
        bellBtn.setAttribute('title', 'Enable Push Notifications');
        bellBtn.setAttribute('aria-label', 'Enable Push Notifications');
        bellBtn.innerHTML =
            '<div class="fcm-bell-pulse"></div>' +
            '<svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z"/></svg>';

        bellBtn.addEventListener('click', function () {
            hideFloatingBell();
            showPromptCard(onClickCallback);
        });

        document.body.appendChild(bellBtn);
    }

    function hideFloatingBell() {
        const bell = document.getElementById('hereits-fcm-bell');
        if (bell && bell.parentNode) {
            bell.parentNode.removeChild(bell);
        }
    }

    // =========================================================================
    // Device ID & Form Auto-fill Management
    // =========================================================================

    // Ensure persistent unique device identifier for this browser
    let deviceId = localStorage.getItem('hereits_device_id');
    if (!deviceId) {
        deviceId = (typeof crypto !== 'undefined' && crypto.randomUUID)
            ? crypto.randomUUID()
            : 'dev_' + Date.now() + '_' + Math.random().toString(36).substring(2, 12);
        localStorage.setItem('hereits_device_id', deviceId);
    }
    const deviceName = navigator.userAgent ? navigator.userAgent.substring(0, 50) : 'Browser';
    localStorage.setItem('hereits_device_name', deviceName);

    function populateFormTokens(token) {
        const currentToken = token || localStorage.getItem('hereits_fcm_token') || '';
        const currentDeviceId = localStorage.getItem('hereits_device_id') || deviceId;
        const currentDeviceName = localStorage.getItem('hereits_device_name') || deviceName;

        if (currentToken) {
            document.querySelectorAll('input[name="fcm_token"]').forEach(function(el) {
                el.value = currentToken;
            });
        }
        if (currentDeviceId) {
            document.querySelectorAll('input[name="device_id"]').forEach(function(el) {
                el.value = currentDeviceId;
            });
        }
        if (currentDeviceName) {
            document.querySelectorAll('input[name="device_name"]').forEach(function(el) {
                el.value = currentDeviceName;
            });
        }
    }

    // Pre-fill any existing form fields on page load
    populateFormTokens(localStorage.getItem('hereits_fcm_token'));

    // Re-fill token and device info whenever any modal opens
    document.addEventListener('show.bs.modal', function() {
        populateFormTokens();
    });

    if (window.jQuery) {
        window.jQuery(document).on('show.bs.modal shown.bs.modal', function () {
            populateFormTokens();
        });
    }

    function acquireToken(messaging, registration, vapidKey) {
        if (!vapidKey) {
            console.warn('Firebase Messaging: FIREBASE_VAPID_KEY is missing in .env. Web push subscription token requires a VAPID key from Firebase Console.');
            return;
        }

        getToken(messaging, {
            serviceWorkerRegistration: registration,
            vapidKey: vapidKey
        }).then(function (currentToken) {
            if (currentToken) {
                localStorage.setItem('hereits_fcm_token', currentToken);
                console.info('🔔 [Firebase Messaging] Token acquired successfully.');

                // Auto-fill hidden input fields across forms (login/register)
                populateFormTokens(currentToken);
            }
        }).catch(function (err) {
            console.warn('Error retrieving FCM registration token:', err.message || err);
        });
    }
})();
