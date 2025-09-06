<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">

    <!-- Enhanced Mobile Meta Tags -->
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="HD Tickets">

    <!-- Prevent iOS zoom on form fields -->
    <meta name="disable-ios-zoom" content="true">

    <!-- Feature Detection Script (loads immediately for browser compatibility) -->
    <script src="{{ asset('assets/js/feature-detection.js') }}"></script>

    <title>{{ config('app.name', 'HD Tickets') }} - @yield('title', 'Dashboard')</title>
    <meta name="description" content="Professional sports ticket monitoring and alerting platform">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/hdTicketsLogo.png') }}">

    <!-- PWA Manifest and Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}?t={{ time() }}">
    <meta name="user-role" content="{{ auth()->user()->role ?? 'guest' }}">

    <!-- Service Worker Registration -->
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
          navigator.serviceWorker.register('/sw.js').then(function(registration) {
            console.log('SW registered with scope:', registration.scope);

            // Check for updates
            registration.update();

            // Handle service worker updates
            registration.addEventListener('updatefound', () => {
              const newWorker = registration.installing;
              newWorker.addEventListener('statechange', () => {
                if (newWorker.state === 'installed' && navigator.serviceWorker
                  .controller) {
                  // New version available
                  if (confirm('New version available! Reload to update?')) {
                    window.location.reload();
                  }
                }
              });
            });
          }).catch(function(err) {
            console.log('SW registration failed:', err);
          });
        });
      }

      // Handle PWA install prompt
      let deferredPrompt;
      window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;

        // Show install button or banner
        const installBanner = document.createElement('div');
        installBanner.innerHTML = `
                    <div style="position: fixed; top: 0; left: 0; right: 0; background: #3b82f6; color: white; padding: 10px; text-align: center; z-index: 9999;">
                        <span>Install HD Tickets for better experience!</span>
                        <button onclick="installPWA()" style="margin-left: 10px; background: white; color: #3b82f6; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Install</button>
                        <button onclick="this.parentElement.parentElement.remove()" style="margin-left: 5px; background: transparent; color: white; border: 1px solid white; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Dismiss</button>
                    </div>
                `;
        document.body.appendChild(installBanner);
      });

      function installPWA() {
        if (deferredPrompt) {
          deferredPrompt.prompt();
          deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
              console.log('User accepted the install prompt');
            }
            deferredPrompt = null;
          });
        }
        // Remove install banner
        const banner = document.querySelector('[style*="position: fixed"]');
        if (banner) banner.remove();
      }
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="{{ css_with_timestamp('https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap') }}"
      rel="stylesheet" />

    <!-- Shared Design Tokens (must load first) -->
    <link href="{{ asset('assets/css/shared-design-tokens.css') }}" rel="stylesheet">

    <!-- Design System CSS -->
    <link href="{{ asset('assets/css/design-system.css') }}" rel="stylesheet">

    <!-- Standardized Components CSS -->
    <link href="{{ asset('assets/css/components.css') }}" rel="stylesheet">

    <!-- Browser Compatibility CSS -->
    <link href="{{ asset('assets/css/browser-compatibility.css') }}" rel="stylesheet">

    <!-- Accessibility Enhancement CSS -->
    <link href="{{ asset('assets/css/accessibility.css') }}" rel="stylesheet">

    <!-- Theme System CSS -->
    <link href="{{ asset('css/theme-system.css') }}" rel="stylesheet">

    <!-- Enhanced HD Tickets CSS -->
    <link href="{{ asset('css/hd-notifications.css') }}" rel="stylesheet">

    <!-- Navigation & Dashboard Fixes -->
    <link href="{{ asset('css/navigation-dashboard-fixes.css') }}" rel="stylesheet">

    <!-- Enhanced Navigation CSS -->
    <link href="{{ asset('css/navigation-enhanced.css') }}" rel="stylesheet">

    <!-- Dropdown Enhancements CSS -->
    <link href="{{ asset('css/dropdown-enhancements.css') }}" rel="stylesheet">

    <!-- Dropdown Z-Index Fix CSS -->
    <link href="{{ asset('css/dropdown-zindex-fix.css') }}" rel="stylesheet">

    <!-- Nuclear Dropdown Fix CSS (Last Resort) -->
    <link href="{{ asset('css/dropdown-nuclear-fix.css') }}" rel="stylesheet">

    <!-- Mobile Enhancements CSS -->
    <link href="{{ asset('css/mobile-enhancements.css') }}" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="{{ css_with_timestamp('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css') }}"
      rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Theme Manager -->
    <script src="{{ asset('resources/js/utils/themeManager.js') }}" defer></script>

    <!-- Mobile Touch Utils -->
    <script src="{{ asset('resources/js/utils/mobileTouchUtils.js') }}" defer></script>

    <!-- Enhanced HD Tickets JavaScript -->
    <script src="{{ asset('js/notificationManager.js') }}" defer></script>
    <script src="{{ asset('js/performanceMonitor.js') }}" defer></script>

    <!-- Dropdown Position Fix JavaScript -->
    <script src="{{ asset('js/dropdown-position-fix.js') }}" defer></script>

    @if(app()->environment('local'))
    <!-- Dropdown Debug JavaScript (Development Only) -->
    <script src="{{ asset('js/dropdown-debug.js') }}" defer></script>
    @endif

    <!-- Inline Critical CSS for Above-the-Fold Content -->
    <style>
      {!! file_get_contents(resource_path('css/critical.css')) !!}
    </style>

    <!-- Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
      @vite(['resources/css/app.css', 'resources/js/app.js'])
      <script type="module" src="{{ asset('resources/js/utils/assetOptimizer.js') }}"></script>
    @else
      <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
      <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
      <!-- Fallback Alpine.js components registration -->
      <script>
        document.addEventListener('alpine:init', () => {
          // Register navigationData component for CDN fallback
          Alpine.data('navigationData', () => ({
            open: false,
            mobileMenuOpen: false,
            adminDropdownOpen: false,
            profileDropdownOpen: false,

            init() {
              console.log('🔧 NavigationData fallback component initialized');

              // Close dropdowns when clicking outside
              document.addEventListener('click', (e) => {
                if (!this.$el.contains(e.target)) {
                  this.adminDropdownOpen = false;
                  this.profileDropdownOpen = false;
                }
              });

              // Close dropdowns on ESC key
              document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                  this.closeAll();
                }
              });

              // Close dropdowns when navigating (if using SPA routing)
              window.addEventListener('popstate', () => {
                this.closeAll();
              });
            },

            closeAll() {
              this.adminDropdownOpen = false;
              this.profileDropdownOpen = false;
              this.mobileMenuOpen = false;
              
              // Remove dropdown state classes
              document.body.classList.remove('dropdown-open');
              document.querySelector('.customer-dashboard')?.classList.remove('dropdown-open');
            },

            toggleMobileMenu() {
              this.mobileMenuOpen = !this.mobileMenuOpen;
              // Close desktop dropdowns when opening mobile menu
              if (this.mobileMenuOpen) {
                this.adminDropdownOpen = false;
                this.profileDropdownOpen = false;
              }
            },

            toggleAdminDropdown() {
              this.adminDropdownOpen = !this.adminDropdownOpen;
              // Close other dropdowns
              this.profileDropdownOpen = false;
              this.mobileMenuOpen = false;
              
              // Add/remove dropdown state class to body for CSS targeting
              if (this.adminDropdownOpen || this.profileDropdownOpen) {
                document.body.classList.add('dropdown-open');
                document.querySelector('.customer-dashboard')?.classList.add('dropdown-open');
              } else {
                document.body.classList.remove('dropdown-open');
                document.querySelector('.customer-dashboard')?.classList.remove('dropdown-open');
              }
              
              console.log('🔧 Admin dropdown:', this.adminDropdownOpen ? 'OPEN' : 'CLOSED');
            },

            toggleProfileDropdown() {
              this.profileDropdownOpen = !this.profileDropdownOpen;
              // Close other dropdowns
              this.adminDropdownOpen = false;
              this.mobileMenuOpen = false;
              
              // Add/remove dropdown state class to body for CSS targeting
              if (this.adminDropdownOpen || this.profileDropdownOpen) {
                document.body.classList.add('dropdown-open');
                document.querySelector('.customer-dashboard')?.classList.add('dropdown-open');
              } else {
                document.body.classList.remove('dropdown-open');
                document.querySelector('.customer-dashboard')?.classList.remove('dropdown-open');
              }
              
              console.log('👤 Profile dropdown:', this.profileDropdownOpen ? 'OPEN' : 'CLOSED');
            },

            // Helper method for dropdown links to close dropdown after click
            handleDropdownItemClick(callback = null) {
              if (callback && typeof callback === 'function') {
                callback();
              }
              // Close dropdowns after a brief delay to allow for navigation
              setTimeout(() => {
                this.closeAll();
              }, 100);
            }
          }));

          console.log('✅ Alpine.js fallback navigationData component registered');
        });
      </script>
      <style>
        /* Comprehensive Tailwind CSS fallback for modern dashboard */
        .font-sans {
          font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
        }

        .antialiased {
          -webkit-font-smoothing: antialiased;
          -moz-osx-font-smoothing: grayscale;
        }

        .min-h-screen {
          min-height: 100vh;
        }

        .bg-gray-100 {
          background-color: #f3f4f6;
        }

        .bg-white {
          background-color: #ffffff;
        }

        .shadow {
          box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }

        .max-w-7xl {
          max-width: 80rem;
        }

        .mx-auto {
          margin-left: auto;
          margin-right: auto;
        }

        .py-6 {
          padding-top: 1.5rem;
          padding-bottom: 1.5rem;
        }

        .px-4 {
          padding-left: 1rem;
          padding-right: 1rem;
        }

        .sm\:px-6 {
          padding-left: 1.5rem;
          padding-right: 1.5rem;
        }

        .lg\:px-8 {
          padding-left: 2rem;
          padding-right: 2rem;
        }

        @media (min-width: 640px) {
          .sm\:px-6 {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
          }
        }

        @media (min-width: 1024px) {
          .lg\:px-8 {
            padding-left: 2rem;
            padding-right: 2rem;
          }
        }

        /* Dashboard specific styles */
        .dashboard-card {
          background: white;
          border-radius: 0.5rem;
          box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
          padding: 1.5rem;
          transition: all 0.2s;
        }

        .dashboard-card:hover {
          box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
          transform: translateY(-1px);
        }

        /* Navigation enhancements */
        .nav-shadow {
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .nav-dropdown {
          backdrop-filter: blur(8px);
        }

        .nav-item-active {
          position: relative;
        }

        .nav-item-active::after {
          content: '';
          position: absolute;
          bottom: -2px;
          left: 0;
          right: 0;
          height: 2px;
          background: linear-gradient(90deg, #3b82f6, #1d4ed8);
          border-radius: 1px;
        }

        .mobile-nav-open {
          max-height: 100vh;
          overflow-y: auto;
        }

        .mobile-nav-closed {
          max-height: 0;
          overflow: hidden;
        }

        .stat-card {
          background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-value {
          font-size: 2.25rem;
          font-weight: 700;
          color: white;
        }

        .stat-label {
          color: rgba(255, 255, 255, 0.9);
          font-size: 0.875rem;
        }

        .chart-container {
          position: relative;
          height: 300px;
        }

        .grid-cols-1 {
          display: grid;
          grid-template-columns: repeat(1, minmax(0, 1fr));
          gap: 1.5rem;
        }

        .grid-cols-2 {
          display: grid;
          grid-template-columns: repeat(2, minmax(0, 1fr));
          gap: 1.5rem;
        }

        .grid-cols-3 {
          display: grid;
          grid-template-columns: repeat(3, minmax(0, 1fr));
          gap: 1.5rem;
        }

        .grid-cols-4 {
          display: grid;
          grid-template-columns: repeat(4, minmax(0, 1fr));
          gap: 1.5rem;
        }

        @media (min-width: 768px) {
          .md\:grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
          }

          .md\:grid-cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
          }

          .md\:grid-cols-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
          }
        }

        @media (min-width: 1024px) {
          .lg\:grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
          }

          .lg\:grid-cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
          }

          .lg\:grid-cols-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
          }
        }

        .text-sm {
          font-size: 0.875rem;
        }

        .text-lg {
          font-size: 1.125rem;
        }

        .text-xl {
          font-size: 1.25rem;
        }

        .text-2xl {
          font-size: 1.5rem;
        }

        .font-medium {
          font-weight: 500;
        }

        .font-semibold {
          font-weight: 600;
        }

        .font-bold {
          font-weight: 700;
        }

        .text-gray-500 {
          color: #6b7280;
        }

        .text-gray-600 {
          color: #4b5563;
        }

        .text-gray-700 {
          color: #374151;
        }

        .text-gray-900 {
          color: #111827;
        }

        .bg-blue-500 {
          background-color: #3b82f6;
        }

        .bg-green-500 {
          background-color: #10b981;
        }

        .bg-yellow-500 {
          background-color: #f59e0b;
        }

        .bg-red-500 {
          background-color: #ef4444;
        }

        .text-blue-600 {
          color: #2563eb;
        }

        .text-green-600 {
          color: #059669;
        }

        .text-yellow-600 {
          color: #d97706;
        }

        .text-red-600 {
          color: #dc2626;
        }

        .rounded-lg {
          border-radius: 0.5rem;
        }

        .rounded-xl {
          border-radius: 0.75rem;
        }

        .rounded-full {
          border-radius: 9999px;
        }

        .p-4 {
          padding: 1rem;
        }

        .p-6 {
          padding: 1.5rem;
        }

        .px-4 {
          padding-left: 1rem;
          padding-right: 1rem;
        }

        .py-2 {
          padding-top: 0.5rem;
          padding-bottom: 0.5rem;
        }

        .mb-2 {
          margin-bottom: 0.5rem;
        }

        .mb-4 {
          margin-bottom: 1rem;
        }

        .mb-6 {
          margin-bottom: 1.5rem;
        }

        .mt-4 {
          margin-top: 1rem;
        }

        .mt-6 {
          margin-top: 1.5rem;
        }

        .flex {
          display: flex;
        }

        .items-center {
          align-items: center;
        }

        .justify-between {
          justify-content: space-between;
        }

        .space-y-4>*+* {
          margin-top: 1rem;
        }

        .w-8 {
          width: 2rem;
        }

        .h-8 {
          height: 2rem;
        }

        .w-12 {
          width: 3rem;
        }

        .h-12 {
          height: 3rem;
        }

        .w-full {
          width: 100%;
        }

        .border {
          border-width: 1px;
        }

        .border-gray-200 {
          border-color: #e5e7eb;
        }

        .divide-y {
          border-top: 1px solid #e5e7eb;
        }

        .overflow-hidden {
          overflow: hidden;
        }

        .truncate {
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap;
        }

        .transition {
          transition: all 0.15s ease-in-out;
        }

        .hover\:shadow-lg:hover {
          box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .cursor-pointer {
          cursor: pointer;
        }

        .select-none {
          user-select: none;
        }

        /* Mobile-first responsive enhancements */
        @media (max-width: 768px) {

          /* Header adjustments */
          .header-mobile {
            flex-direction: column;
            gap: 1rem;
          }

          .button-mobile {
            width: 100%;
            justify-content: center;
            min-height: 48px;
          }

          /* Touch-friendly form elements */
          input,
          select,
          textarea,
          button {
            min-height: 48px !important;
            font-size: 16px !important;
          }

          /* Table responsive behavior */
          .table-mobile {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
          }

          .table-mobile table {
            min-width: 100%;
          }

          /* Card layout mobile optimization */
          .card-mobile {
            margin: 0.5rem;
            border-radius: 0.75rem;
          }

          .card-grid-mobile {
            grid-template-columns: 1fr;
            gap: 1rem;
          }

          /* Modal mobile optimization */
          .modal-mobile {
            margin: 1rem;
            width: calc(100% - 2rem);
            max-width: none;
          }

          /* Action buttons mobile layout */
          .actions-mobile {
            flex-direction: column;
            gap: 0.5rem;
          }

          .actions-mobile button,
          .actions-mobile a {
            width: 100%;
            text-align: center;
          }

          /* Search and filter mobile */
          .search-mobile {
            grid-template-columns: 1fr;
          }

          /* Pagination mobile */
          .pagination-mobile {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
          }
        }

        /* Alpine.js x-cloak directive */
        [x-cloak] {
          display: none !important;
        }

        /* Touch-friendly improvements for all screen sizes */
        button,
        a[role="button"],
        input[type="button"],
        input[type="submit"] {
          min-height: 44px;
          padding: 0.75rem 1rem;
          touch-action: manipulation;
        }

        /* Improved tap targets */
        .tap-target {
          min-height: 44px;
          min-width: 44px;
        }

        /* Better spacing for mobile */
        @media (max-width: 640px) {
          .mobile-padding {
            padding: 1rem;
          }

          .mobile-margin {
            margin: 0.5rem;
          }

          .mobile-text {
            font-size: 0.875rem;
          }
        }
      </style>
    @endif
    <!-- PWA Manager -->
    <script src="{{ asset('resources/js/utils/pwaManager.js') }}?t={{ time() }}" defer></script>
    <!-- Enhanced Feedback System -->
    <script src="{{ asset('resources/js/utils/feedbackSystem.js') }}?t={{ time() }}" defer></script>
    <!-- Mobile Enhancement CSS -->
    <link rel="stylesheet" href="{{ asset('css/mobile-enhancements.css') }}?t={{ time() }}">
    <!-- Mobile Touch Utilities -->
    <script src="{{ asset('resources/js/utils/mobileTouchUtils.js') }}?t={{ time() }}" defer></script>

    <!-- Accessibility Enhancement Utilities -->
    <script src="{{ asset('assets/js/accessibility-utils.js') }}" defer></script>

    <!-- Performance Optimization Scripts -->
    <script src="{{ asset('assets/js/performance-monitor.js') }}" defer></script>
    <script src="{{ asset('assets/js/lazy-loading.js') }}" defer></script>
    <script src="{{ asset('assets/js/critical-css-optimizer.js') }}" defer></script>

    <!-- React-Blade Integration Bridge -->
    <script src="{{ asset('assets/js/react-blade-bridge.js') }}" defer></script>

    <!-- Additional Mobile Optimizations -->
    <script>
      // Initialize comprehensive mobile enhancements
      document.addEventListener('DOMContentLoaded', function() {
        if (window.mobileTouchUtils) {
          window.mobileTouchUtils.initializeAllFeatures();
        }

        // Add mobile-specific CSS classes for progressive enhancement
        const deviceType = window.innerWidth <= 768 ? 'mobile' : 'desktop';
        document.documentElement.classList.add(deviceType);

        // Handle viewport orientation changes
        function handleOrientationChange() {
          setTimeout(() => {
            // Update viewport height
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);

            // Update device orientation class
            const orientation = window.innerHeight > window.innerWidth ? 'portrait' : 'landscape';
            document.documentElement.classList.remove('portrait', 'landscape');
            document.documentElement.classList.add(orientation);
          }, 150);
        }

        window.addEventListener('orientationchange', handleOrientationChange);
        window.addEventListener('resize', handleOrientationChange);

        // Initial orientation setup
        handleOrientationChange();

        // Enhanced form handling for mobile
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
          form.addEventListener('submit', function(e) {
            // Add loading state
            const submitBtn = form.querySelector(
              'button[type="submit"], input[type="submit"]');
            if (submitBtn) {
              submitBtn.disabled = true;
              submitBtn.textContent = submitBtn.textContent.replace(/^(?!.*Loading)/,
                'Loading... ');

              // Re-enable after timeout (safety net)
              setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = submitBtn.textContent.replace(
                  'Loading... ', '');
              }, 30000);
            }
          });
        });

        // Mobile-specific error handling
        window.addEventListener('error', function(e) {
          if (window.mobileTouchUtils) {
            window.mobileTouchUtils.showMobileNotification(
              'Something went wrong. Please try again.',
              'error',
              5000
            );
          }
        });

        // Network status notifications
        window.addEventListener('online', function() {
          if (window.mobileTouchUtils) {
            window.mobileTouchUtils.showMobileNotification(
              'Connection restored',
              'success',
              2000
            );
          }
        });

        window.addEventListener('offline', function() {
          if (window.mobileTouchUtils) {
            window.mobileTouchUtils.showMobileNotification(
              'No internet connection',
              'warning',
              5000
            );
          }
        });
      });
    </script>
    <!-- Dashboard Manager Component -->
    <script src="{{ asset('resources/js/components/dashboardManager.js') }}?t={{ time() }}" defer></script>
    <script>
      // Initialize PWA features when page loads
      document.addEventListener('DOMContentLoaded', function() {
        if (window.pwaManager) {
          // Setup advanced push notifications if user is logged in
          @auth
          setTimeout(() => {
            window.pwaManager.setupPeriodicSync();

            // Show notification preferences for first-time users
            if (!localStorage.getItem('hd-tickets-notification-preferences')) {
              window.pwaManager.setupAdvancedPushNotifications();
            }
          }, 2000);
        @endauth
      }
      });

      // Connection status indicator
      function updateConnectionStatus() {
        const indicator = document.querySelector('.connection-indicator');
        if (indicator) {
          if (navigator.onLine) {
            indicator.classList.add('online');
            indicator.classList.remove('offline');
            indicator.title = 'Online';
          } else {
            indicator.classList.add('offline');
            indicator.classList.remove('online');
            indicator.title = 'Offline';
          }
        }
      }

      window.addEventListener('online', updateConnectionStatus);
      window.addEventListener('offline', updateConnectionStatus);
      updateConnectionStatus();
    </script>

    <style>
      /* Connection indicator styles */
      .connection-indicator {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #ef4444;
        position: relative;
        margin-right: 8px;
      }

      .connection-indicator.online {
        background-color: #10b981;
      }

      .connection-indicator::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: inherit;
        animation: pulse 2s infinite;
      }

      @keyframes pulse {
        0% {
          transform: scale(1);
          opacity: 1;
        }

        100% {
          transform: scale(2);
          opacity: 0;
        }
      }
    </style>
  </head>

  <body class="font-sans antialiased">
    <!-- Skip Links for Screen Reader Navigation -->
    <div class="hd-skip-links">
      <a href="#main-content" class="hd-skip-link">Skip to main content</a>
      <a href="#navigation" class="hd-skip-link">Skip to navigation</a>
      @auth
        @if (auth()->user()->role === 'admin')
          <a href="#admin-tools" class="hd-skip-link">Skip to admin tools</a>
        @endif
      @endauth
    </div>

    <!-- Live Regions for Screen Reader Announcements (created by JS but adding fallback) -->
    <div id="hd-live-polite" class="hd-live-region" aria-live="polite" aria-atomic="true"></div>
    <div id="hd-live-assertive" class="hd-live-region" aria-live="assertive" aria-atomic="true"></div>
    <div id="hd-status-region" class="hd-live-region" role="status" aria-live="polite"></div>

    <div class="min-h-screen bg-gray-100">
      <!-- Primary Navigation Landmark -->
      <nav id="navigation" role="navigation" aria-label="Primary navigation">
        @include('layouts.navigation')
      </nav>

      <!-- Page Header Landmark -->
      @hasSection('header')
        <header class="bg-white shadow" role="banner">
          <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            @yield('header')
          </div>
        </header>
      @endif

      <!-- Main Content Container -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Main Content Landmark -->
        <main id="main-content" role="main" class="py-6 hd-main-content" tabindex="-1">
          <!-- Status/Alert Messages -->
          @if (session('success'))
            <div role="alert" class="hd-alert hd-alert--success hd-status-message hd-status-message--success"
              aria-live="polite">
              <span class="hd-sr-only">Success:</span>
              {{ session('success') }}
            </div>
          @endif

          @if (session('error'))
            <div role="alert" class="hd-alert hd-alert--error hd-status-message hd-status-message--error"
              aria-live="assertive">
              <span class="hd-sr-only">Error:</span>
              {{ session('error') }}
            </div>
          @endif

          @if (isset($errors) && $errors->any())
            <div role="alert" class="hd-alert hd-alert--error hd-status-message hd-status-message--error"
              aria-live="assertive">
              <span class="hd-sr-only">Form errors:</span>
              <ul>
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <!-- Page Content -->
          @yield('content')
        </main>
      </div>

      <!-- Footer Landmark (if needed) -->
      @hasSection('footer')
        <footer role="contentinfo" class="bg-white border-t">
          <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            @yield('footer')
          </div>
        </footer>
      @endif
    </div>

    <!-- Accessibility Enhancement Initialization -->
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Initialize accessibility utilities if available
        if (window.HDTickets && window.HDTickets.AccessibilityUtils) {
          console.log('🔍 HD Tickets Accessibility Utils loaded and initialized');
        }

        // Enhanced keyboard navigation for the application
        document.addEventListener('keydown', function(e) {
          // Global keyboard shortcuts with accessibility in mind
          if (e.altKey) {
            switch (e.key) {
              case 'h': // Alt+H: Go to home/dashboard
                e.preventDefault();
                const homeLink = document.querySelector('a[href*="dashboard"], a[href="/"]');
                if (homeLink) {
                  window.HDTickets?.AccessibilityUtils?.announce('Navigating to dashboard');
                  homeLink.click();
                }
                break;
              case 'm': // Alt+M: Focus main content
                e.preventDefault();
                const mainContent = document.getElementById('main-content');
                if (mainContent) {
                  mainContent.focus();
                  window.HDTickets?.AccessibilityUtils?.announce('Focused main content');
                }
                break;
              case 'n': // Alt+N: Focus navigation
                e.preventDefault();
                const navigation = document.getElementById('navigation');
                if (navigation) {
                  const firstLink = navigation.querySelector('a, button');
                  if (firstLink) {
                    firstLink.focus();
                    window.HDTickets?.AccessibilityUtils?.announce(
                      'Focused navigation menu');
                  }
                }
                break;
            }
          }
        });

        // Enhanced form accessibility
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
          // Add form landmarks if they don't exist
          if (!form.hasAttribute('role') && form.querySelectorAll('input, select, textarea').length >
            1) {
            form.setAttribute('role', 'form');
          }

          // Enhanced form submission feedback
          form.addEventListener('submit', function(e) {
            if (window.HDTickets?.AccessibilityUtils) {
              window.HDTickets.AccessibilityUtils.announceStatus(
                'Form submitted, processing...');
            }
          });
        });

        // Auto-announce page changes for SPA-like behavior
        const originalPushState = history.pushState;
        const originalReplaceState = history.replaceState;

        history.pushState = function() {
          originalPushState.apply(history, arguments);
          setTimeout(() => {
            if (window.HDTickets?.AccessibilityUtils) {
              window.HDTickets.AccessibilityUtils.announce('Page navigation completed');
            }
          }, 100);
        };

        history.replaceState = function() {
          originalReplaceState.apply(history, arguments);
          setTimeout(() => {
            if (window.HDTickets?.AccessibilityUtils) {
              window.HDTickets.AccessibilityUtils.announce('Page content updated');
            }
          }, 100);
        };
      });
    </script>
  </body>

</html>
