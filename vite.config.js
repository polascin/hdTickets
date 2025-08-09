import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: [
                'resources/views/**',
                'resources/js/**',
                'app/Http/Controllers/**',
                'routes/**',
            ],
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
                compilerOptions: {
                    // Enable Vue 3 features
                    isCustomElement: tag => tag.startsWith('x-')
                }
            },
            // Enable reactive transform (experimental)
            reactivityTransform: true,
        }),
    ],
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
            '@components': resolve(__dirname, 'resources/js/components'),
            '@modules': resolve(__dirname, 'resources/js/modules'),
            '@utils': resolve(__dirname, 'resources/js/utils'),
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    build: {
        chunkSizeWarningLimit: 1600,
        cssCodeSplit: true,
        sourcemap: process.env.NODE_ENV === 'development',
        reportCompressedSize: false, // Faster builds
        rollupOptions: {
            output: {
                manualChunks: {
                    // Core Vue ecosystem
                    'vendor-vue': ['vue', 'vue-router'],
                    // Chart and visualization libraries
                    'vendor-charts': ['chart.js', 'chart.js/auto'],
                    // UI components and utilities
                    'vendor-ui': ['sweetalert2', 'flatpickr', '@heroicons/vue'],
                    // HTTP and real-time communication
                    'vendor-http': ['axios', 'laravel-echo', 'pusher-js', 'socket.io-client'],
                    // Alpine.js and its ecosystem
                    'vendor-alpine': ['alpinejs'],
                    // Performance utilities
                    'vendor-performance': ['chart.js/helpers'],
                    // Third-party analytics
                    'vendor-analytics': [],
                },
                // Optimize asset naming with content hash
                assetFileNames: (assetInfo) => {
                    const info = assetInfo.name.split('.');
                    const extType = info[info.length - 1];
                    if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(extType)) {
                        return `assets/images/[name]-[hash][extname]`;
                    }
                    if (/css/i.test(extType)) {
                        return `assets/css/[name]-[hash][extname]`;
                    }
                    if (/woff2?|ttf|otf/i.test(extType)) {
                        return `assets/fonts/[name]-[hash][extname]`;
                    }
                    return `assets/[name]-[hash][extname]`;
                },
                chunkFileNames: 'assets/js/[name]-[hash].js',
                entryFileNames: 'assets/js/[name]-[hash].js',
            },
        },
        // Enhanced minification for production
        minify: process.env.NODE_ENV === 'production' ? 'terser' : false,
        terserOptions: {
            compress: {
                drop_console: process.env.NODE_ENV === 'production',
                drop_debugger: true,
                pure_funcs: ['console.log', 'console.info'],
                reduce_vars: true,
                reduce_funcs: true,
                passes: 2, // Multiple passes for better compression
            },
            mangle: {
                safari10: true,
                reserved: ['$', 'jQuery', 'Alpine', 'Chart']
            },
            format: {
                comments: false,
            },
        },
        // CSS optimization
        cssMinify: 'lightningcss',
    },
    server: {
        hmr: {
            host: 'localhost',
            overlay: true,
        },
        host: true,
        port: 5173,
        strictPort: true,
    },
    optimizeDeps: {
        include: [
            'vue',
            'vue-router',
            'axios',
            'chart.js',
            'sweetalert2',
            'flatpickr',
            'alpinejs',
            '@heroicons/vue/24/outline',
            '@heroicons/vue/24/solid',
            'laravel-echo',
            'pusher-js',
        ],
    },
    define: {
        __VUE_OPTIONS_API__: true,
        __VUE_PROD_DEVTOOLS__: false,
    },
});
