import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';
window.axios.defaults.headers.common['Content-Type'] = 'application/json';

// Get CSRF token from meta tag
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Configure axios interceptors for better error handling
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 419) {
            // CSRF token mismatch - reload page
            window.location.reload();
        } else if (error.response?.status === 401) {
            // Unauthorized - redirect to login
            window.location.href = '/login';
        } else if (error.response?.status === 429) {
            // Rate limited
            Swal.fire({
                icon: 'warning',
                title: 'Rate Limited',
                text: 'Too many requests. Please wait a moment and try again.',
                timer: 3000
            });
        }
        return Promise.reject(error);
    }
);

// SweetAlert2 for notifications
import Swal from 'sweetalert2';
window.Swal = Swal;

// Flatpickr for date/time pickers
import flatpickr from 'flatpickr';
window.flatpickr = flatpickr;

// Chart.js for dashboards
import Chart from 'chart.js/auto';
window.Chart = Chart;

// Echo for real-time features - ENABLED
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY || 'hd-tickets-key',
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || '',
    wsHost: import.meta.env.VITE_PUSHER_HOST || '127.0.0.1',
    wsPort: import.meta.env.VITE_PUSHER_PORT || 6001,
    wssPort: import.meta.env.VITE_PUSHER_PORT || 6001,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME || 'http') === 'https',
    encrypted: false,
    enabledTransports: ['ws', 'wss'],
    auth: {
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('api_token') || ''}`,
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    }
});
