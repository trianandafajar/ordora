import Echo from 'laravel-echo';
import { Reverb } from 'laravel-echo/reverb';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wsPath: import.meta.env.VITE_REVERB_PATH,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'Accept': 'application/json',
        },
    },
});