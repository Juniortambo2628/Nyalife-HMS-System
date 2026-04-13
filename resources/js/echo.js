import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Only initialize Echo if Reverb config is present and we're not in a broken state
const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost = import.meta.env.VITE_REVERB_HOST;
const reverbPort = import.meta.env.VITE_REVERB_PORT;

if (reverbKey && reverbHost && reverbPort) {
    try {
        // Only connect if we are on localhost OR if the host isn't the default 127.0.0.1
        const isLocal = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
        const isDefaultHost = reverbHost === '127.0.0.1' || reverbHost === 'localhost';

        if (isLocal || !isDefaultHost) {
            // Suppress Pusher connection warnings in console
            Pusher.logToConsole = false;

            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: reverbKey,
                wsHost: reverbHost,
                wsPort: reverbPort ?? 80,
                wssPort: reverbPort ?? 443,
                forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
                enabledTransports: ['ws', 'wss'],
                enableStats: false,
            });
        }
    } catch (e) {
        console.warn('[Echo] Failed to initialize WebSocket connection:', e.message);
    }
}
