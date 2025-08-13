import { defineConfig, loadEnv } from 'vite';
import vue from '@vitejs/plugin-vue';
import legacy from '@vitejs/plugin-legacy';
import { VitePWA } from 'vite-plugin-pwa';
import WindiCSS from 'vite-plugin-windicss';
import eslint from 'vite-plugin-eslint';
import { resolve } from 'path';
import { visualizer } from 'rollup-plugin-visualizer';
import autoprefixer from 'autoprefixer';

export default defineConfig(({ command, mode }) => {
  const env = loadEnv(mode, process.cwd(), '');
  const isProduction = mode === 'production';
  const isAnalyze = mode === 'analyze';

  return {
    plugins: [
      vue({
        template: {
          compilerOptions: {
            // Treat all tags starting with 'x-' as custom elements
            isCustomElement: tag => tag.startsWith('x-')
          }
        }
      }),
      
      WindiCSS({
        config: {
          // WindiCSS configuration
          darkMode: 'class',
          extract: {
            include: ['**/*.{vue,html,jsx,tsx,blade.php}'],
            exclude: ['node_modules', '.git']
          },
          theme: {
            extend: {
              colors: {
                primary: {
                  50: '#eff6ff',
                  100: '#dbeafe',
                  200: '#bfdbfe',
                  300: '#93c5fd',
                  400: '#60a5fa',
                  500: '#3b82f6',
                  600: '#2563eb',
                  700: '#1d4ed8',
                  800: '#1e40af',
                  900: '#1e3a8a',
                },
                secondary: {
                  50: '#f5f3ff',
                  100: '#ede9fe',
                  200: '#ddd6fe',
                  300: '#c4b5fd',
                  400: '#a78bfa',
                  500: '#8b5cf6',
                  600: '#7c3aed',
                  700: '#6d28d9',
                  800: '#5b21b6',
                  900: '#4c1d95',
                },
                accent: {
                  50: '#ecfeff',
                  100: '#cffafe',
                  200: '#a5f3fc',
                  300: '#67e8f9',
                  400: '#22d3ee',
                  500: '#06b6d4',
                  600: '#0891b2',
                  700: '#0e7490',
                  800: '#155e75',
                  900: '#164e63',
                },
              },
              animation: {
                'fade-in': 'fadeIn 0.5s ease-in-out',
                'slide-up': 'slideUp 0.3s ease-out',
                'bounce-subtle': 'bounceSubtle 0.6s ease-in-out',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
              },
              keyframes: {
                fadeIn: {
                  '0%': { opacity: '0' },
                  '100%': { opacity: '1' },
                },
                slideUp: {
                  '0%': { transform: 'translateY(20px)', opacity: '0' },
                  '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                bounceSubtle: {
                  '0%, 100%': { transform: 'translateY(0)' },
                  '50%': { transform: 'translateY(-5px)' },
                },
              },
            },
          },
        },
      }),

      // ESLint only in development mode
      ...(command === 'serve' ? [
        eslint({
          cache: false,
          include: ['**/*.{vue,js,ts}'],
          exclude: ['node_modules']
        })
      ] : []),


      // Legacy browser support
      legacy({
        targets: ['defaults', 'not IE 11'],
        additionalLegacyPolyfills: ['regenerator-runtime/runtime'],
        polyfills: [
          'es.symbol',
          'es.array.filter',
          'es.promise',
          'es.promise.finally'
        ]
      }),

      // Bundle analyzer
      ...(isAnalyze ? [
        visualizer({
          filename: 'dist/stats.html',
          open: true,
          gzipSize: true,
          brotliSize: true
        })
      ] : []),

    ],

    resolve: {
      alias: {
        '@': resolve(import.meta.dirname || __dirname, 'resources/js'),
        '@components': resolve(import.meta.dirname || __dirname, 'resources/js/components'),
        '@composables': resolve(import.meta.dirname || __dirname, 'resources/js/composables'),
        '@stores': resolve(import.meta.dirname || __dirname, 'resources/js/stores'),
        '@utils': resolve(import.meta.dirname || __dirname, 'resources/js/utils'),
        '@types': resolve(import.meta.dirname || __dirname, 'resources/js/types'),
        '@assets': resolve(import.meta.dirname || __dirname, 'resources/assets'),
        '@css': resolve(import.meta.dirname || __dirname, 'resources/css'),
      }
    },

    build: {
      outDir: 'public/build',
      assetsDir: 'assets',
      sourcemap: !isProduction,
      minify: isProduction ? 'terser' : false,
      cssMinify: isProduction,
      terserOptions: isProduction ? {
        compress: {
          drop_console: true,
          drop_debugger: true,
          pure_funcs: ['console.log', 'console.info'],
          passes: 2
        },
        mangle: {
          safari10: true
        },
        format: {
          comments: false
        }
      } : {},
      rollupOptions: {
        onwarn(warning, warn) {
          // Suppress warnings about missing imports during build
          if (warning.code === 'UNRESOLVED_IMPORT') {
            return;
          }
          warn(warning);
        },
        // Laravel requires explicit input files
        input: {
          app: resolve(import.meta.dirname || __dirname, isProduction ? 'resources/js/app.prod.js' : 'resources/js/app.js'),
          css: resolve(import.meta.dirname || __dirname, 'resources/css/app.css')
        },
        // External dependencies that shouldn't be bundled
        external: (id) => {
          // Keep all node_modules as internal for bundling
          return false;
        },
        output: {
          // Advanced manual chunking strategy
          manualChunks: (id) => {
            // Core Vue ecosystem
            if (id.includes('vue') && !id.includes('node_modules/@vue')) {
              return 'vue-core';
            }
            if (id.includes('vue-router')) {
              return 'vue-router';
            }
            if (id.includes('pinia')) {
              return 'vue-store';
            }
            
            // UI libraries
            if (id.includes('@headlessui') || id.includes('@heroicons')) {
              return 'ui-framework';
            }
            
            // Charts and visualization
            if (id.includes('chart.js') || id.includes('chartjs-adapter')) {
              return 'charts';
            }
            
            // Utilities
            if (id.includes('lodash-es') || id.includes('date-fns')) {
              return 'utils';
            }
            
            // HTTP and WebSocket
            if (id.includes('axios') || id.includes('socket.io-client')) {
              return 'networking';
            }
            
            // Animation libraries
            if (id.includes('framer-motion') || id.includes('@vueuse/motion')) {
              return 'animations';
            }
            
            // Large third-party libraries
            if (id.includes('node_modules')) {
              // Split large vendors into separate chunks
              if (id.includes('zod') || id.includes('fuse.js')) {
                return 'vendor-large';
              }
              return 'vendor';
            }
            
            // Application code splitting by directory
            if (id.includes('/components/')) {
              return 'components';
            }
            if (id.includes('/composables/')) {
              return 'composables';
            }
            if (id.includes('/utils/')) {
              return 'app-utils';
            }
            if (id.includes('/stores/')) {
              return 'app-stores';
            }
          },
          // Chunk file naming
          chunkFileNames: (chunkInfo) => {
            const facadeModuleId = chunkInfo.facadeModuleId
              ? chunkInfo.facadeModuleId.split('/').pop()
              : 'chunk';
            return `js/[name]-[hash].js`;
          },
          assetFileNames: (assetInfo) => {
            const info = assetInfo.name.split('.');
            let extType = info[info.length - 1];
            if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(extType)) {
              extType = 'images';
            } else if (/woff2?|eot|ttf|otf/i.test(extType)) {
              extType = 'fonts';
            }
            return `${extType}/[name]-[hash][extname]`;
          }
        }
      },
      chunkSizeWarningLimit: 1000,
      // Optimize asset processing
      assetsInlineLimit: 4096,
      cssCodeSplit: true
    },

    server: {
      port: 5173,
      host: true,
      hmr: {
        port: 5173,
      },
      proxy: {
        '/api': {
          target: env.VITE_API_URL || 'http://localhost:80',
          changeOrigin: true,
        },
        '/broadcasting': {
          target: env.VITE_WEBSOCKET_URL || 'http://localhost:6001',
          changeOrigin: true,
          ws: true,
        }
      }
    },

    define: {
      __VUE_OPTIONS_API__: true,
      __VUE_PROD_DEVTOOLS__: !isProduction,
    },

    css: {
      preprocessorOptions: {
        scss: {
          additionalData: '@import "@css/variables.scss";'
        }
      },
      postcss: {
        plugins: [
          autoprefixer()
        ]
      }
    },

    optimizeDeps: {
      include: [
        'vue',
        'vue-router',
        'pinia',
        '@vueuse/core',
        '@vueuse/components',
        'chart.js',
        'chartjs-adapter-date-fns',
        'axios',
        'lodash-es',
        'date-fns',
        '@headlessui/vue',
        '@heroicons/vue',
        'socket.io-client',
        'fuse.js',
        'sortablejs',
        'mitt'
      ],
      // Pre-bundle these dependencies for better dev performance
      force: !isProduction,
      // Exclude problematic dependencies
      exclude: ['virtual-keyboard']
    },

    // Performance optimizations
    esbuild: {
      target: 'esnext',
      drop: isProduction ? ['console', 'debugger'] : []
    },

    // Worker optimizations
    worker: {
      format: 'es',
      plugins: () => []
    }
  };
});
