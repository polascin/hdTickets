import laravel from 'laravel-vite-plugin';
import { resolve } from 'path';
import { defineConfig } from 'vite';

export default defineConfig({
  plugins: [
    laravel({
      input: [
        // Core application assets only - no frameworks
        'resources/css/tailwind.css',
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/bootstrap.js',
        // Dashboard assets
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
      refresh: [
        'resources/views/**',
        'app/Http/Controllers/**',
      ],
    }),
  ],

  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources/js'),
      '@css': resolve(__dirname, 'resources/css'),
      '@components': resolve(__dirname, 'resources/js/components'),
      '@utils': resolve(__dirname, 'resources/js/utils'),
      '@tickets': resolve(__dirname, 'resources/js/tickets'),
    },
  },

  build: {
    target: 'es2022',
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['alpinejs', 'axios'],
        },
      },
    },
    minify: 'esbuild',
    cssMinify: 'esbuild',
  },

  optimizeDeps: {
    include: ['alpinejs', 'axios'],
    exclude: ['laravel-vite-plugin'],
  },
});