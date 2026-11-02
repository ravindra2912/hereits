/**
 * Firebase Web Push Notification Handler
 * Manages token registration, foreground notifications, and click redirections
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
        console.warn(
            '⚠️ [Firebase Messaging] Push Notifications require HTTPS or localhost.\n' +
            'You are currently on: ' + window.location.origin + '\n' +
            'Web browsers automatically disable PushManager and Notification APIs on plain HTTP.\n\n' +
            '👉 How to fix for Laragon:\n' +
            '1. In Laragon: Right-click Laragon tray icon -> Apache -> SSL -> Check "Enabled"\n' +
            '2. Open your site with HTTPS: https://' + window.location.host + '\n' +
            '   (OR test on http://localhost:8000)'
        );
        return;
    }

    // 2. Check if browser environment supports Web Push and Service Workers
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

    try {
        // 3. Verify Firebase SDK browser support before calling getMessaging
        const supported = await isSupported().catch(() => false);
        if (!supported) {
            console.warn('Firebase Messaging: Browser does not support Firebase messaging APIs in this environment.');
            return;
        }

        const app = initializeApp(config);
        const messaging = getMessaging(app);

        // 4. Register Service Worker and retrieve FCM Token
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
            if (Notification.permission === 'granted') {
                acquireToken(messaging, registration, config.vapidKey);
            } else if (Notification.permission !== 'denied') {
                Notification.requestPermission().then(function (permission) {
                    if (permission === 'granted') {
                        acquireToken(messaging, registration, config.vapidKey);
                    }
                });
            }
        }

        // 5. Foreground Message Listener (when user is active on the tab)
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
                const storedToken = localStorage.getItem('hereits_fcm_token');
                if (storedToken !== currentToken) {
                    sendTokenToServer(currentToken);
                }
            }
        }).catch(function (err) {
            console.warn('Error retrieving FCM registration token:', err.message || err);
        });
    }

    function sendTokenToServer(token) {
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

        fetch('/save-fcm-token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                fcm_token: token,
                platform: 'web',
                device_name: navigator.userAgent.substring(0, 50)
            })
        }).then(function (res) {
            return res.json();
        }).then(function (data) {
            if (data.status) {
                localStorage.setItem('hereits_fcm_token', token);
            }
        }).catch(function (err) {
            console.error('Failed to save FCM token on server:', err);
        });
    }
})();
