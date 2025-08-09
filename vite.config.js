import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

// Generate timestamp for CSS cache busting
const timestamp = Date.now();

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
        // Vite 5 optimizations
        target: 'es2022', // Modern JavaScript target for better optimization
        chunkSizeWarningLimit: 1600,
        cssCodeSplit: true,
        sourcemap: process.env.NODE_ENV === 'development',
        reportCompressedSize: false, // Faster builds
        rollupOptions: {
            output: {
                // Enhanced manual chunks with better splitting strategy
                manualChunks: (id) => {
                    // Core Vue ecosystem
                    if (id.includes('vue') || id.includes('vue-router')) {
                        return 'vendor-vue';
                    }
                    // Chart and visualization libraries
                    if (id.includes('chart.js')) {
                        return 'vendor-charts';
                    }
                    // UI components and utilities
                    if (id.includes('sweetalert2') || id.includes('flatpickr') || id.includes('@heroicons/vue')) {
                        return 'vendor-ui';
                    }
                    // HTTP and real-time communication
                    if (id.includes('axios') || id.includes('laravel-echo') || id.includes('pusher-js') || id.includes('socket.io-client')) {
                        return 'vendor-http';
                    }
                    // Alpine.js and its ecosystem
                    if (id.includes('alpinejs')) {
                        return 'vendor-alpine';
                    }
                    // Large node_modules packages
                    if (id.includes('node_modules')) {
                        return 'vendor';
                    }
                },
                // Optimize asset naming with content hash and timestamp for CSS
                assetFileNames: (assetInfo) => {
                    const info = assetInfo.name.split('.');
                    const extType = info[info.length - 1];
                    if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(extType)) {
                        return `assets/images/[name]-[hash][extname]`;
                    }
                    if (/css/i.test(extType)) {
                        // Add timestamp to CSS files for cache prevention
                        return `assets/css/[name]-[hash]-${timestamp}[extname]`;
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
        // Enhanced minification for production with Vite 5 compatible options
        minify: process.env.NODE_ENV === 'production' ? 'terser' : false,
        terserOptions: {
            compress: {
                arguments: true, // Vite 5 compatible option
                drop_console: process.env.NODE_ENV === 'production',
                drop_debugger: true,
                pure_funcs: ['console.log', 'console.info', 'console.warn'],
                reduce_vars: true,
                reduce_funcs: true,
                passes: 3, // Increased passes for better compression in Vite 5
                unsafe_arrows: true, // Better arrow function optimization
                unsafe_methods: true, // More aggressive method optimization
            },
            mangle: {
                properties: {
                    regex: /^_/, // Mangle private properties
                },
                safari10: true,
                reserved: ['$', 'jQuery', 'Alpine', 'Chart', 'Vue']
            },
            format: {
                comments: false,
                ecma: 2022, // Modern ECMAScript format
            },
        },
        // Enhanced CSS optimization with lightningcss
        cssMinify: 'lightningcss',
        // Vite 5 specific optimizations
        assetsInlineLimit: 4096, // Inline small assets
        cssTarget: 'es2022', // Modern CSS target
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
        // Force optimization of these dependencies
        include: [
            'vue',
            'vue-router',
            'axios',
            'chart.js',
            'chart.js/auto',
            'chart.js/helpers',
            'sweetalert2',
            'flatpickr',
            'alpinejs',
            '@heroicons/vue/24/outline',
            '@heroicons/vue/24/solid',
            'laravel-echo',
            'pusher-js',
            'socket.io-client',
        ],
        // Vite 5: Enhanced optimization options
        force: process.env.NODE_ENV === 'development',
        holdUntilCrawlEnd: true, // Better handling of dynamic imports
    },
    define: {
        // Vue 3 feature flags
        __VUE_OPTIONS_API__: true,
        __VUE_PROD_DEVTOOLS__: process.env.NODE_ENV === 'development',
        __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: process.env.NODE_ENV === 'development',
        // CSS timestamp for cache busting (available globally)
        __CSS_TIMESTAMP__: JSON.stringify(timestamp),
        // Environment-specific definitions
        'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'development'),
    },
});
