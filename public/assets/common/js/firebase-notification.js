/**
 * Firebase Web Push Notification Handler
 * Manages token registration, foreground notifications, and click redirections
 */
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-app.js";
import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-messaging.js";

(function () {
    const config = window.firebaseConfig;
    if (!config || !config.apiKey || !config.projectId) {
        return;
    }

    try {
        const app = initializeApp(config);
        const messaging = getMessaging(app);

        // 1. Register Service Worker and retrieve FCM Token
        if ('serviceWorker' in navigator && 'Notification' in window) {
            const swParams = new URLSearchParams({
                apiKey: config.apiKey,
                projectId: config.projectId,
                messagingSenderId: config.messagingSenderId || '',
                appId: config.appId || ''
            });

            navigator.serviceWorker.register('/firebase-messaging-sw.js?' + swParams.toString())
                .then(function (registration) {
                    // Check or request notification permission
                    if (Notification.permission === 'granted') {
                        acquireToken(messaging, registration, config.vapidKey);
                    } else if (Notification.permission !== 'denied') {
                        Notification.requestPermission().then(function (permission) {
                            if (permission === 'granted') {
                                acquireToken(messaging, registration, config.vapidKey);
                            }
                        });
                    }
                })
                .catch(function (err) {
                    console.warn('Service Worker registration failed:', err);
                });
        }

        // 2. Foreground Message Listener (when user is active on page)
        onMessage(messaging, function (payload) {
            const title = payload.notification?.title || payload.data?.title || 'Notification';
            const body = payload.notification?.body || payload.data?.body || payload.data?.message || '';
            const targetUrl = payload.fcmOptions?.link || payload.data?.click_url || payload.data?.url || '';

            // Use toastr if available
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
                // Use SweetAlert2 if toastr is not loaded
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
                // Native browser fallback
                if (confirm(title + "\n\n" + body + "\n\nClick OK to view.")) {
                    if (targetUrl) {
                        window.location.href = targetUrl;
                    }
                }
            }
        });

    } catch (e) {
        console.warn('Firebase Messaging init error:', e);
    }

    function acquireToken(messaging, registration, vapidKey) {
        const tokenOptions = { serviceWorkerRegistration: registration };
        if (vapidKey) {
            tokenOptions.vapidKey = vapidKey;
        }

        getToken(messaging, tokenOptions).then(function (currentToken) {
            if (currentToken) {
                const storedToken = localStorage.getItem('hereits_fcm_token');
                if (storedToken !== currentToken) {
                    sendTokenToServer(currentToken);
                }
            }
        }).catch(function (err) {
            console.warn('Error retrieving FCM registration token:', err);
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
