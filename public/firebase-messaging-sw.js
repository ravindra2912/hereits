/**
 * Firebase Cloud Messaging Service Worker for background push notifications and click redirections
 */
importScripts('https://www.gstatic.com/firebasejs/10.9.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.9.0/firebase-messaging-compat.js');

// Parse query parameters passed when registering the service worker (e.g. ?apiKey=...&projectId=...)
const swUrl = new URL(location);
const apiKey = swUrl.searchParams.get('apiKey') || '';
const projectId = swUrl.searchParams.get('projectId') || '';
const messagingSenderId = swUrl.searchParams.get('messagingSenderId') || '';
const appId = swUrl.searchParams.get('appId') || '';

if (projectId) {
    try {
        firebase.initializeApp({
            apiKey: apiKey,
            projectId: projectId,
            messagingSenderId: messagingSenderId,
            appId: appId
        });

        const messaging = firebase.messaging();

        // Catch background messages
        messaging.onBackgroundMessage(function(payload) {
            const title = payload.notification?.title || payload.data?.title || 'Notification';
            const body = payload.notification?.body || payload.data?.body || payload.data?.message || '';
            const targetUrl = payload.fcmOptions?.link || payload.data?.click_url || payload.data?.url || '/';

            const notificationOptions = {
                body: body,
                icon: payload.notification?.icon || '/assets/images/logo.png',
                badge: payload.notification?.badge || '/assets/images/badge.png',
                data: {
                    click_url: targetUrl
                }
            };

            return self.registration.showNotification(title, notificationOptions);
        });
    } catch (e) {
        console.warn('Firebase Service Worker init skipped:', e);
    }
}

// Global click event listener for background notifications: opens/focuses and navigates to event URL
self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    const targetUrl = event.notification.data?.click_url || event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if ('focus' in client) {
                    client.focus();
                    if ('navigate' in client) {
                        return client.navigate(targetUrl);
                    }
                    return;
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
