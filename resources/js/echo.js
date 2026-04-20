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
        const isLocal = window.location.hostname === 'localhost' || 
                        window.location.hostname === '127.0.0.1' || 
                        window.location.hostname === '[::1]';
        const isDefaultHost = reverbHost === '127.0.0.1' || reverbHost === 'localhost';

        if (isLocal || !isDefaultHost) {
            // Suppress Pusher connection warnings in console
            Pusher.logToConsole = false;

            const wsPort = parseInt(reverbPort) || 8080;
            const wssPort = parseInt(reverbPort) || 443;
            const protocol = import.meta.env.VITE_REVERB_SCHEME ?? 'http';

            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: reverbKey,
                wsHost: reverbHost,
                wsPort: wsPort,
                wssPort: wssPort,
                forceTLS: protocol === 'https',
                enabledTransports: ['ws', 'wss'],
                enableStats: false,
                // Add heartbeat/retry limits to prevent console flooding
                activityTimeout: 30000,
                unavailableTimeout: 10000,
            });

            // Handle connection failures silently to prevent console noise
            window.Echo.connector.pusher.connection.bind('error', function(err) {
                if (err && err.error && err.error.data && err.error.data.code === 4004) {
                    // Silently ignore key not found errors
                }
            });

            window.Echo.connector.pusher.connection.bind('state_change', function(states) {
                // You can debug states here if needed
                if (states.current === 'unavailable') {
                    // Fail silently when server is down
                }
            });
        }
    } catch (e) {
        console.warn('[Echo] Failed to initialize WebSocket connection:', e.message);
    }
}
