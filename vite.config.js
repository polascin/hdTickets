import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';
import { defineConfig } from 'vite';

export default defineConfig({
  plugins: [
    react(),
    vue(),
    laravel({
      input: [
        // Tailwind CSS - Main utility-first CSS framework
        'resources/css/tailwind.css',
        // Core application assets
        'resources/css/app.css',
        'resources/js/app.js',
        // Dashboard dedicated entry (code-split customer dashboard)
        'resources/js/dashboard/index.js',
        'resources/js/dashboard/modern-customer-dashboard.js',

        // Ticket system assets
        'resources/js/tickets/index.js',
        'resources/js/tickets/TicketFilters.js',
        'resources/js/tickets/PriceMonitor.js',
        'resources/css/tickets.css',

        // Welcome page assets
        'resources/js/welcome.js',
        'resources/css/welcome.css',
      ],
      // Enable hot reload for Blade files
      refresh: [
        'resources/routes/**',
        'resources/views/**',
        'app/Http/Controllers/**',
        'app/View/**',
      ],
    }),
  ],

  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources/js'),
      '@css': resolve(__dirname, 'resources/css'),
      '@images': resolve(__dirname, 'resources/images'),
      '@components': resolve(__dirname, 'resources/js/components'),
      '@utils': resolve(__dirname, 'resources/js/utils'),
      '@styles': resolve(__dirname, 'resources/css'),
      '@tickets': resolve(__dirname, 'resources/js/tickets'),
      '@frameworks': resolve(__dirname, 'resources/js/frameworks'),
      '@react': resolve(__dirname, 'resources/js/frameworks/react'),
      '@vue': resolve(__dirname, 'resources/js/frameworks/vue'),
      '@angular': resolve(__dirname, 'resources/js/frameworks/angular'),
      '@shared': resolve(__dirname, 'resources/js/frameworks/shared'),
    },
  },

  build: {
    // Target modern browsers for better performance
    target: 'es2022',

    // Optimize chunk size
    chunkSizeWarningLimit: 1000,

    rollupOptions: {
      output: {
        // Advanced chunk splitting strategy
        manualChunks: (id) => {
          // Core vendor libraries
          if (id.includes('node_modules')) {
            if (id.includes('alpinejs')) {
              return 'alpine';
            }
            if (id.includes('react') || id.includes('react-dom')) {
              return 'react-framework';
            }
            if (id.includes('vue') || id.includes('@vue')) {
              return 'vue-framework';
            }
            if (id.includes('@angular') || id.includes('rxjs') || id.includes('zone.js')) {
              return 'angular-framework';
            }
            if (id.includes('axios')) {
              return 'http';
            }
            if (id.includes('chart.js') || id.includes('chartjs')) {
              return 'charts';
            }
            if (id.includes('laravel-echo') || id.includes('pusher-js')) {
              return 'realtime';
            }
            if (id.includes('lodash') || id.includes('moment') || id.includes('dayjs')) {
              return 'utils';
            }
            // Other vendor libraries
            return 'vendor';
          }

          // Application chunks
          if (id.includes('/resources/js/components/')) {
            return 'components';
          }
          if (id.includes('/resources/js/utils/')) {
            return 'app-utils';
          }
          if (id.includes('/resources/js/tickets/')) {
            return 'tickets';
          }
          if (id.includes('/resources/js/frameworks/react/')) {
            return 'react-components';
          }
          if (id.includes('/resources/js/frameworks/vue/')) {
            return 'vue-components';
          }
          if (id.includes('/resources/js/frameworks/angular/')) {
            return 'angular-components';
          }
          if (id.includes('/resources/js/frameworks/shared/')) {
            return 'shared-components';
          }
          if (id.includes('/resources/css/components/')) {
            return 'component-styles';
          }
        },

        // Optimize asset naming
        assetFileNames: (assetInfo) => {
          const info = assetInfo.name.split('.');
          const ext = info[info.length - 1];

          if (/\.(png|jpe?g|gif|svg|webp|avif)$/i.test(assetInfo.name)) {
            return `assets/images/[name].[hash][extname]`;
          }
          if (/\.(woff2?|eot|ttf|otf)$/i.test(assetInfo.name)) {
            return `assets/fonts/[name].[hash][extname]`;
          }
          if (ext === 'css') {
            return `assets/css/[name].[hash][extname]`;
          }
          return `assets/[name].[hash][extname]`;
        },

        chunkFileNames: 'assets/js/[name].[hash].js',
        entryFileNames: 'assets/js/[name].[hash].js',

        // Use ES modules format for better tree shaking
        format: 'es',
        compact: true,
      },

      // External dependencies that should not be bundled
      external: [],
    },

    // Enable source maps in development
    sourcemap: process.env.NODE_ENV === 'development' ? 'inline' : false,

    // Use esbuild for faster minification
    minify: 'esbuild',

    // Enable CSS minification
    cssMinify: 'esbuild',

    // Optimize CSS code splitting
    cssCodeSplit: true,

    // Preload assets
    modulePreload: {
      polyfill: true,
    },

    // Report bundle analyzer results
    reportCompressedSize: false,
  },

  // Development server configuration
  server: {
    hmr: {
      host: 'localhost',
      port: 5173,
    },
    // Enable CORS for development
    cors: true,
    // Open browser on start
    open: false,
    // Host configuration for Docker/WSL
    host: '0.0.0.0',
    strictPort: false,
  },

  // Preview server configuration
  preview: {
    host: '0.0.0.0',
    port: 4173,
    cors: true,
  },

  // CSS processing options
  css: {
    // Enable CSS modules
    modules: {
      localsConvention: 'camelCaseOnly',
    },
    // PostCSS configuration is handled by postcss.config.js
    // CSS preprocessor options
    preprocessorOptions: {
      scss: {
        additionalData: `@import "@styles/variables.scss";`,
      },
    },
  },

  // Optimization options
  optimizeDeps: {
    // Force include these dependencies
    include: [
      'alpinejs',
      'axios',
      '@alpinejs/persist',
      '@alpinejs/focus',
      'chart.js',
      'laravel-echo',
      'pusher-js',
      'react',
      'react-dom',
      'react-router-dom',
      'vue',
      '@vue/runtime-dom',
      'vue-router',
      'pinia',
      '@angular/core',
      '@angular/common',
      '@angular/platform-browser',
      'rxjs',
    ],
    // Exclude from optimization
    exclude: [
      'laravel-vite-plugin',
    ],
  },

  // Define global constants
  define: {
    __APP_VERSION__: JSON.stringify(process.env.npm_package_version || '1.0.0'),
    __BUILD_TIME__: JSON.stringify(new Date().toISOString()),
  },
});
