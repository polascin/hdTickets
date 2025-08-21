import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/welcome.js',
                'resources/css/welcome.css'
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
            '@css': resolve(__dirname, 'resources/css'),
            '@images': resolve(__dirname, 'resources/images'),
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['axios', 'alpinejs'],
                    charts: ['chart.js'],
                    realtime: ['laravel-echo', 'pusher-js'],
                },
            },
        },
        sourcemap: true,
    },
    server: {
        hmr: {
            host: 'localhost',
        },
    },
});
