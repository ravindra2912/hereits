import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Enable pusher-js console logging for full WebSocket debug logs
Pusher.logToConsole = true;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

// Bind connection events for explicit console feedback
if (window.Echo.connector && window.Echo.connector.pusher) {
    const pusherConn = window.Echo.connector.pusher.connection;

    pusherConn.bind('state_change', (states) => {
        console.log('[Echo Connection State Change]:', states.previous, '->', states.current);
    });

    pusherConn.bind('connected', () => {
        console.log('[Echo]: Successfully connected to Reverb server!');
    });

    pusherConn.bind('disconnected', () => {
        console.warn('[Echo]: Disconnected from Reverb.');
    });

    pusherConn.bind('failed', () => {
        console.error('[Echo]: Connection to Reverb failed.');
    });
}

const userIdMeta = document.querySelector('meta[name="user-id"]');
if (userIdMeta && userIdMeta.content) {
    const userId = userIdMeta.content;
    window.Echo.private(`App.Models.User.${userId}`)
        .notification((notification) => {
            if (window.toastr) {
                window.toastr.info(notification.message || 'New notification received.');
            }
        });
}
