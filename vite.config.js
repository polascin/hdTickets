import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';
import { visualizer } from 'rollup-plugin-visualizer';

// Generate timestamp for CSS cache busting - ensures fresh CSS delivery
const timestamp = Date.now();

// Helper function to determine if we're in development mode
const isDev = process.env.NODE_ENV === 'development';
const isProd = process.env.NODE_ENV === 'production';
const isAnalyze = process.env.ANALYZE === 'true';
const isReport = process.env.REPORT === 'true';

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
                'config/**',
            ],
            // Enhanced Vite 7.x Laravel plugin options
            buildDirectory: 'build',
            hotFile: 'public/hot',
            // Enhanced refresh with glob patterns for better performance
            refreshGlob: ['resources/**', 'app/Http/**', 'routes/**'],
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
                compilerOptions: {
                    // Enable Vue 3 features
                    isCustomElement: tag => tag.startsWith('x-'),
                    // Enhanced Vue 3.5+ compatibility
                    compatConfig: {
                        MODE: 3
                    }
                }
            },
            // Enhanced Vue plugin options for Vite 7.x
            script: {
                defineModel: true,
                propsDestructure: true
            },
            // Better development experience
            reactivityTransform: true
        }),
        
        // Bundle analyzer plugin - only when ANALYZE=true
        ...(isAnalyze ? [
            visualizer({
                filename: 'public/build/bundle-analysis.html',
                open: true,
                gzipSize: true,
                brotliSize: true,
                template: 'treemap', // 'sunburst', 'treemap', 'network'
                title: 'HD Tickets - Bundle Analysis Report',
                sourcemap: true
            })
        ] : []),
        
        // Report plugin for detailed build statistics
        ...(isReport ? [
            visualizer({
                filename: 'public/build/build-report.html',
                open: false,
                gzipSize: true,
                brotliSize: true,
                template: 'list',
                title: 'HD Tickets - Build Report'
            })
        ] : []),
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
        // Vite 7.x optimizations with modern ES2024 target
        target: ['es2022', 'edge88', 'firefox78', 'chrome87', 'safari14'],
        chunkSizeWarningLimit: 1600,
        cssCodeSplit: true,
        // Enhanced source maps configuration
        sourcemap: isDev ? 'inline' : (isProd ? 'hidden' : false),
        reportCompressedSize: isProd && !isAnalyze, // Only in production, skip during analysis
        // Enhanced module preload polyfill for better browser support
        modulePreload: {
            polyfill: true,
            resolveDependencies: (filename, deps, { hostId, hostType }) => {
                return deps.filter(dep => !dep.includes('chunk'))
            }
        },
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
                // Enhanced asset naming with content hash and timestamp for CSS cache prevention
                assetFileNames: (assetInfo) => {
                    const info = assetInfo.name.split('.');
                    const extType = info[info.length - 1];
                    if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(extType)) {
                        return `assets/images/[name]-[hash][extname]`;
                    }
                    if (/css/i.test(extType)) {
                        // CRITICAL: Add timestamp to CSS files for cache prevention as per requirements
                        // Use file modification time for production builds for better caching
                        const buildTimestamp = isProd ? timestamp : Date.now();
                        return `assets/css/[name]-[hash]-${buildTimestamp}[extname]`;
                    }
                    if (/woff2?|ttf|otf|eot/i.test(extType)) {
                        return `assets/fonts/[name]-[hash][extname]`;
                    }
                    if (/webp|avif/i.test(extType)) {
                        return `assets/images/[name]-[hash][extname]`;
                    }
                    return `assets/[name]-[hash][extname]`;
                },
                chunkFileNames: 'assets/js/[name]-[hash].js',
                entryFileNames: 'assets/js/[name]-[hash].js',
            },
        },
        // Enhanced minification for production with Vite 7.x compatible options
        minify: isProd ? 'terser' : false,
        terserOptions: {
            compress: {
                arguments: true,
                drop_console: isProd,
                drop_debugger: isProd,
                pure_funcs: ['console.log', 'console.info', 'console.warn', 'console.debug'],
                reduce_vars: true,
                reduce_funcs: true,
                passes: isProd ? 3 : 1,
                unsafe_arrows: true,
                unsafe_methods: true,
                // Enhanced Vite 7.x compression options
                keep_fargs: false,
                toplevel: true,
            },
            mangle: {
                properties: {
                    regex: /^_/,
                },
                safari10: true,
                reserved: ['$', 'jQuery', 'Alpine', 'Chart', 'Vue', 'Inertia']
            },
            format: {
                comments: false,
                ecma: 2022,
                ascii_only: true, // Better compatibility
            },
        },
        // Enhanced CSS optimization - use esbuild for Tailwind CSS compatibility
        cssMinify: 'esbuild',
        // Vite 7.x optimizations
        assetsInlineLimit: 4096,
        cssTarget: ['chrome87', 'firefox78', 'safari14', 'edge88'],
    },
    server: {
        hmr: {
            host: 'localhost',
            overlay: true,
            // Enhanced HMR configuration for better development experience
            clientPort: 5173,
        },
        host: true,
        port: 5173,
        strictPort: true,
        // Enhanced Vite 7.x server options
        cors: true,
        // Better development server performance
        middlewareMode: false,
        fs: {
            // Allow serving files from one level up to the project root
            allow: ['..'],
            strict: true
        },
        // Enhanced watch options for better file system performance
        watch: {
            usePolling: false,
            interval: 100,
            ignored: ['**/node_modules/**', '**/.git/**']
        }
    },
    optimizeDeps: {
        // Enhanced dependency optimization for Vite 7.x
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
            '@alpinejs/collapse',
            '@alpinejs/focus',
            '@alpinejs/intersect',
            '@alpinejs/persist',
            '@heroicons/vue/24/outline',
            '@heroicons/vue/24/solid',
            '@heroicons/vue/20/solid',
            'laravel-echo',
            'pusher-js',
            'socket.io-client',
            '@inertiajs/vue3',
            '@vueuse/core',
            '@vueuse/components',
            'zxcvbn',
            'date-fns',
            'chartjs-adapter-date-fns'
        ],
        // Enhanced Vite 7.x optimization options
        force: isDev,
        holdUntilCrawlEnd: true,
        // Better handling of ESM dependencies
        esbuildOptions: {
            target: 'es2022',
            supported: {
                'top-level-await': true
            }
        }
    },
    define: {
        // Enhanced Vue 3.5+ feature flags for better compatibility
        __VUE_OPTIONS_API__: true,
        __VUE_PROD_DEVTOOLS__: isDev,
        __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: isDev,
        __VUE_FEATURE_SUSPENSE__: true,
        __VUE_FEATURE_TELEPORT__: true,
        // CRITICAL: CSS timestamp for cache busting - ensures fresh CSS delivery
        __CSS_TIMESTAMP__: JSON.stringify(timestamp),
        // Environment and build-specific definitions
        'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'development'),
        'process.env.VITE_APP_NAME': JSON.stringify(process.env.VITE_APP_NAME || 'HD Tickets'),
        // Laravel integration helpers
        __LARAVEL_VITE_TIMESTAMP__: JSON.stringify(timestamp),
    },
    
    // Enhanced CSS processing configuration
    css: {
        postcss: {
            plugins: [
                // Ensure PostCSS plugins are properly loaded
            ]
        },
        // Better CSS dev source maps
        devSourcemap: isDev,
        // Enhanced CSS preprocessing options
        preprocessorOptions: {
            scss: {
                additionalData: `$css-timestamp: ${timestamp};`,
                sourceMap: isDev
            },
            sass: {
                additionalData: `$css-timestamp: ${timestamp}`,
                sourceMap: isDev
            }
        }
    },
    
    // Enhanced Vite 7.x experimental features
    experimental: {
        // Enable render built-in optimizations
        renderBuiltUrl(filename, { hostType, type, ssr }) {
            if (type === 'asset' && /\.css$/i.test(filename)) {
                // Ensure CSS files include timestamp for cache busting
                return { relative: true }
            }
            return { relative: true }
        }
    },
});
