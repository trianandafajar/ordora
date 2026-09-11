import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

if (!window.Echo) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        Pusher,
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT,
        wsPath: import.meta.env.VITE_REVERB_PATH,
        forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'Accept': 'application/json',
            },
        },
    });
}

window.dispatchEvent(new CustomEvent('ordora-echo-ready'));
