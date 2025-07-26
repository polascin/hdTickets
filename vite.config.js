import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    build: {
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor-vue': ['vue'],
                    'vendor-ui': ['sweetalert2', 'chart.js', 'flatpickr'],
                    'vendor-http': ['axios', 'laravel-echo', 'pusher-js'],
                    'vendor-alpine': ['alpinejs'],
                },
            },
        },
    },
    server: {
        hmr: {
            host: 'localhost',
        },
    },
});
