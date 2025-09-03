<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#3b82f6">

    <title>@yield('title', 'HD Tickets') - {{ config('app.name', 'HD Tickets') }}</title>

    @if (View::hasSection('description'))
      <meta name="description" content="@yield('description')">
    @else
      <meta name="description" content="Advanced Sports Ticket Monitoring Platform">
    @endif

    <!-- Preload Critical Resources -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preload" href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
      as="style">

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Main Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- UI Enhancement Scripts -->
    <script src="{{ asset('js/ui-enhancements.js') }}" defer></script>

    <!-- Custom Styles with Timestamp -->
    @stack('styles')

    <!-- Additional CSS with Cache Busting -->
    @if (View::hasSection('additional-css'))
      @yield('additional-css')
    @endif

    <!-- Alpine.js is loaded via Vite bundle in app.js -->

    <style>
      [x-cloak] {
        display: none !important;
      }

      /* Modern Dashboard Base Styles */
      .modern-card {
        @apply bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 transition-all duration-200 hover:shadow-md;
      }

      .dashboard-card {
        @apply bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-200 hover:shadow-md;
      }

      .hero-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      }

      .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      }

      .stat-value {
        @apply text-2xl font-bold text-white;
      }

      .stat-label {
        @apply text-white/90 text-sm;
      }

      .animate-float {
        animation: float 3s ease-in-out infinite;
      }

      @keyframes float {

        0%,
        100% {
          transform: translateY(0px);
        }

        50% {
          transform: translateY(-10px);
        }
      }

      .animate-pulse-slow {
        animation: pulse-slow 2s ease-in-out infinite;
      }

      @keyframes pulse-slow {

        0%,
        100% {
          opacity: 1;
        }

        50% {
          opacity: 0.5;
        }
      }

      .modern-input {
        @apply w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white;
      }

      .modern-button {
        @apply inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200;
      }

      .status-indicator {
        @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
      }

      .status-active {
        @apply bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200;
      }

      .status-inactive {
        @apply bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300;
      }

      .status-warning {
        @apply bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200;
      }

      .status-error {
        @apply bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200;
      }

      /* Loading States */
      .loading-skeleton {
        @apply animate-pulse bg-gray-200 dark:bg-gray-700 rounded;
      }

      /* Responsive utilities */
      @media (max-width: 640px) {
        .modern-card {
          @apply mx-2 rounded-lg;
        }
      }
    </style>
  </head>

  <body class="h-full font-sans antialiased bg-gray-50 dark:bg-gray-900">
    <div id="app" class="min-h-full">
      <!-- Navigation -->
      @include('layouts.navigation')

      <!-- Page Header -->
      @if (View::hasSection('header'))
        <header class="bg-white dark:bg-gray-800 shadow-sm">
          <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            @yield('header')
          </div>
        </header>
      @endif

      <!-- Main Content -->
      <main class="flex-1">
        <!-- Flash Messages -->
        @if (session('success'))
          <div x-data="{ show: true }" x-show="show" x-transition class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-md p-4">
              <div class="flex">
                <div class="flex-shrink-0">
                  <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-green-800 dark:text-green-200">
                    {{ session('success') }}
                  </p>
                </div>
                <div class="ml-auto pl-3">
                  <button @click="show = false" class="text-green-400 hover:text-green-600">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        @endif

        @if (session('error'))
          <div x-data="{ show: true }" x-show="show" x-transition class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-md p-4">
              <div class="flex">
                <div class="flex-shrink-0">
                  <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-red-800 dark:text-red-200">
                    {{ session('error') }}
                  </p>
                </div>
                <div class="ml-auto pl-3">
                  <button @click="show = false" class="text-red-400 hover:text-red-600">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        @endif

        @yield('content')
      </main>

      <!-- Footer -->
      @if (View::hasSection('footer'))
        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
          <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            @yield('footer')
          </div>
        </footer>
      @endif
    </div>

    <!-- Global Loading Overlay - TEMPORARILY DISABLED -->
    <div x-data="loadingOverlay()" x-show="false" x-cloak @loading.window="setLoading($event.detail)"
      @@stop-loading.window="stopLoading()" style="display: none !important;"
      class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300">
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-xl max-w-sm mx-4">
        <div class="flex flex-col items-center space-y-4">
          <div class="animate-spin rounded-full h-10 w-10 border-4 border-blue-600 border-t-transparent"></div>
          <div class="text-center">
            <div class="text-lg font-medium text-gray-900 dark:text-gray-100" x-text="loadingMessage">Loading...</div>
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1" x-show="duration > 3"
              x-text="`${duration} seconds`"></div>
            <div class="text-xs text-gray-400 dark:text-gray-500 mt-2" x-show="duration > 10">
              This is taking longer than expected...
            </div>
          </div>
          <div class="w-full" x-show="progress !== null">
            <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-2">
              <div class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                :style="`width: ${Math.min(progress, 100)}%`"></div>
            </div>
            <div class="text-xs text-center text-gray-500 mt-1" x-text="`${Math.min(progress, 100)}%`"></div>
          </div>
          <button x-show="canCancel && duration > 15" @click="cancelLoading()"
            class="text-sm text-red-600 hover:text-red-800 transition-colors">
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Toast Notifications -->
    <div x-data="{ notifications: [] }"
      @notify.window="notifications.push({id: Date.now(), ...$event.detail}); setTimeout(() => notifications.shift(), 5000)"
      class="fixed top-4 right-4 z-50">
      <template x-for="notification in notifications" :key="notification.id">
        <div x-transition:enter="transform ease-out duration-300 transition"
          x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
          x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
          x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
          x-transition:leave-end="opacity-0"
          class="max-w-sm w-full bg-white dark:bg-gray-800 shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 mb-3">
          <div class="p-4">
            <div class="flex items-start">
              <div class="flex-shrink-0">
                <i
                  :class="{
                      'fas fa-check-circle text-green-400': notification.type === 'success',
                      'fas fa-exclamation-circle text-red-400': notification.type === 'error',
                      'fas fa-info-circle text-blue-400': notification.type === 'info',
                      'fas fa-exclamation-triangle text-yellow-400': notification.type === 'warning'
                  }"></i>
              </div>
              <div class="ml-3 w-0 flex-1 pt-0.5">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="notification.title"></p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="notification.message"></p>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- Scripts -->
    @stack('scripts')

    <script>
      // IMMEDIATE FIX: Force hide loading overlay
      (function() {
        console.log('🔧 Immediate loading overlay fix running...');

        // Hide any visible loading overlays immediately
        const hideLoadingOverlays = () => {
          const loadingOverlays = document.querySelectorAll('[x-data*="loadingOverlay"]');
          loadingOverlays.forEach(overlay => {
            overlay.style.display = 'none';
            console.log('🔧 Force hiding loading overlay via CSS');
          });
        };

        // Run immediately
        hideLoadingOverlays();

        // Run after a delay to catch dynamically created overlays
        setTimeout(hideLoadingOverlays, 100);
        setTimeout(hideLoadingOverlays, 500);
        setTimeout(hideLoadingOverlays, 1000);

        // Global emergency stop function
        window.emergencyStopLoading = function() {
          console.log('🔧 EMERGENCY STOP LOADING called');
          hideLoadingOverlays();

          // Also dispatch events
          try {
            window.dispatchEvent(new CustomEvent('force-stop-loading'));
            window.dispatchEvent(new CustomEvent('stop-loading'));
          } catch (e) {
            console.log('🔧 Event dispatch failed, but CSS hide should work');
          }
        };

        console.log('🔧 Emergency stop loading function available as emergencyStopLoading()');
      })();

      // All Alpine.js components are now registered in app.js

      // Initialize dark mode on page load
      if (localStorage.getItem('darkMode') === 'true') {
        document.documentElement.classList.add('dark');
      }

      // Force stop any loading overlays when page is loaded
      window.addEventListener('load', function() {
        console.log('🔧 Page fully loaded, stopping any active loading overlays');
        setTimeout(() => {
          window.dispatchEvent(new CustomEvent('force-stop-loading'));
        }, 100);
      });

      // Also stop loading on DOMContentLoaded
      document.addEventListener('DOMContentLoaded', function() {
        console.log('🔧 DOM loaded, stopping any active loading overlays');
        setTimeout(() => {
          window.dispatchEvent(new CustomEvent('force-stop-loading'));
        }, 100);
      });

      // CSS Cache Busting Functions
      window.addTimestampedCSS = function(href, id = null) {
        const timestamp = {{ time() }};
        const url = href + (href.includes('?') ? '&' : '?') + 'v=' + timestamp;

        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.type = 'text/css';
        link.href = url;

        if (id) {
          link.id = id;
          // Remove existing link with same ID
          const existing = document.getElementById(id);
          if (existing) existing.remove();
        }

        document.head.appendChild(link);
        return link;
      };

      window.updateAllCSS = function() {
        const links = document.querySelectorAll('link[rel="stylesheet"]');
        const timestamp = {{ time() }};

        links.forEach(link => {
          if (link.href && !link.href.includes('unpkg.com') && !link.href.includes('cdnjs.cloudflare.com')) {
            const baseUrl = link.href.split('?')[0];
            link.href = baseUrl + '?v=' + timestamp;
          }
        });
      };

      // Real-time Dashboard Alpine.js component
      function realtimeDashboard() {
        return {
          connected: false,
          monitoring: false,
          stats: {
            watchedTickets: 0,
            activeScrapers: 0,
            alertsSent: 0
          },
          updates: [],

          init() {
            this.connectWebSocket();
            this.fetchInitialData();
          },

          connectWebSocket() {
            // WebSocket connection logic will be handled by websocketManager
            if (window.websocketManager) {
              window.websocketManager.connect();
            }
          },

          fetchInitialData() {
            // This will be called by the existing JavaScript in the template
          },

          startMonitoring() {
            this.monitoring = true;
            // API call handled in existing JavaScript
          },

          stopMonitoring() {
            this.monitoring = false;
            // API call handled in existing JavaScript
          },

          addUpdate(update) {
            this.updates.unshift({
              ...update,
              timestamp: new Date().toLocaleTimeString(),
              id: Date.now()
            });

            // Keep only last 50 updates
            if (this.updates.length > 50) {
              this.updates.pop();
            }
          },

          clearUpdates() {
            this.updates = [];
          }
        }
      }
    </script>
  </body>

</html>
