<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

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

    <title>{{ config('app.name', 'HD Tickets') }} - @yield('title', 'Dashboard')</title>
    <meta name="description" content="Professional sports ticket monitoring and alerting platform">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/hdTicketsLogo.png') }}">

    <!-- Dashboard-specific meta tags -->
    <meta name="dashboard-api"
      content="{{ route('api.dashboard.data', ['type' => $__env->yieldContent('dashboard-type', 'customer')]) ?? '/api/dashboard/customer' }}">
    <meta name="dashboard-type" content="@yield('dashboard-type', 'customer')">
    <meta name="dashboard-refresh" content="@yield('dashboard-refresh', '30000')">

    <!-- Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
      @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
      <!-- Chart.js for dashboard charts -->
      <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>

      <!-- Alpine.js for interactive components -->
      <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

      <!-- Dashboard JavaScript -->
      <script src="{{ asset('js/dashboard-common.js') }}" defer></script>
      <script src="{{ asset('js/dashboard-customer.js') }}" defer></script>
      <script src="{{ asset('js/dashboard-performance.js') }}" defer></script>

      <!-- Alpine.js fallback components -->
      <script>
        document.addEventListener('alpine:init', () => {
          // Register navigationData component fallback
          Alpine.data('navigationData', () => ({
            open: false,
            mobileMenuOpen: false,
            adminDropdownOpen: false,
            profileDropdownOpen: false,
            isScrolled: false,

            init() {
              console.log('🔧 NavigationData fallback component initialized');
              // Track scroll for nav styling
              window.addEventListener('scroll', () => {
                this.isScrolled = window.scrollY > 10;
              });
            },

            toggleAdminDropdown() {
              this.adminDropdownOpen = !this.adminDropdownOpen;
              // Close other dropdowns
              this.profileDropdownOpen = false;
              this.mobileMenuOpen = false;
              console.log('🔧 Admin dropdown:', this.adminDropdownOpen ? 'OPEN' : 'CLOSED');
            },

            toggleProfileDropdown() {
              this.profileDropdownOpen = !this.profileDropdownOpen;
              // Close other dropdowns
              this.adminDropdownOpen = false;
              this.mobileMenuOpen = false;
              console.log('👤 Profile dropdown:', this.profileDropdownOpen ? 'OPEN' : 'CLOSED');
            },

            toggleMobileMenu() {
              this.mobileMenuOpen = !this.mobileMenuOpen;
              // Close other dropdowns
              this.adminDropdownOpen = false;
              this.profileDropdownOpen = false;
              console.log('📱 Mobile menu:', this.mobileMenuOpen ? 'OPEN' : 'CLOSED');
            },

            closeAll() {
              this.adminDropdownOpen = false;
              this.profileDropdownOpen = false;
              this.mobileMenuOpen = false;
            }
          }));

          // Register theme manager component
          Alpine.data('themeManager', () => ({
            theme: localStorage.getItem('theme') || 'light',
            isTransitioning: false,

            init() {
              this.applyTheme();
              this.announceTheme();
            },

            toggleTheme() {
              this.isTransitioning = true;
              this.theme = this.theme === 'light' ? 'dark' : 'light';
              this.applyTheme();
              localStorage.setItem('theme', this.theme);

              // Announce theme change for screen readers
              this.announceTheme();

              setTimeout(() => {
                this.isTransitioning = false;
              }, 300);
            },

            applyTheme() {
              if (this.theme === 'dark') {
                document.documentElement.classList.add('dark');
              } else {
                document.documentElement.classList.remove('dark');
              }
            },

            announceTheme() {
              const announcer = document.getElementById('a11y-announcer');
              if (announcer) {
                announcer.textContent = `Theme changed to ${this.theme} mode`;
              }
            },

            getThemeIcon() {
              return this.theme === 'light' ? 'fa-moon' : 'fa-sun';
            },

            getNextThemeLabel() {
              return this.theme === 'light' ? 'Switch to dark mode' : 'Switch to light mode';
            }
          }));

          // Register global loading state manager
          Alpine.data('globalLoading', () => ({
            isLoading: false,
            loadingMessage: 'Loading...',

            showLoading(message = 'Loading...') {
              this.isLoading = true;
              this.loadingMessage = message;
              const indicator = document.getElementById('loading-indicator');
              if (indicator) {
                indicator.classList.add('show');
                indicator.setAttribute('aria-hidden', 'false');
              }
            },

            hideLoading() {
              this.isLoading = false;
              const indicator = document.getElementById('loading-indicator');
              if (indicator) {
                indicator.classList.remove('show');
                indicator.setAttribute('aria-hidden', 'true');
              }
            }
          }));

          console.log('✅ Alpine.js fallback components registered');
        });
      </script>
    @endif

    <!-- Main Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Additional Styles -->
    @stack('styles')

    @hasSection('dashboard-styles')
      @yield('dashboard-styles')
    @endif
  </head>

  <body class="font-sans antialiased bg-gray-50">
    <!-- Skip to main content link for accessibility -->
    <a href="#main-content"
      class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-blue-600 text-white px-4 py-2 rounded-md z-50">
      Skip to main content
    </a>

    <!-- Dashboard layout container -->
    <div class="min-h-screen">
      @include('layouts.navigation')

      <!-- Main content area with proper spacing -->
      <main id="main-content" class="dashboard-main" role="main" aria-label="Dashboard content">
        <!-- Dashboard content container -->
        <div class="dashboard-container">
          <!-- Page header area -->
          @hasSection('header')
            <div class="dashboard-header">
              <div class="dashboard-header-content">
                @yield('header')
              </div>
            </div>
          @endif

          <!-- Main dashboard content -->
          <div class="dashboard-content">
            @yield('content')
          </div>
        </div>
      </main>
    </div>

    <!-- Loading indicator -->
    <div id="loading-indicator" class="loading-indicator" aria-hidden="true">
      <div class="loading-spinner">
        <div class="spinner"></div>
      </div>
      <div class="mt-4 text-sm text-gray-600">Loading dashboard data...</div>
    </div>

    <!-- Global error notification container -->
    <div id="error-notifications" class="error-notifications" aria-live="polite" aria-atomic="true"></div>

    <!-- Toast notification container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2" aria-live="polite"></div>

    <!-- Accessibility announcements -->
    <div id="a11y-announcer" class="sr-only" aria-live="polite" aria-atomic="true"></div>

    <!-- JavaScript -->
    @stack('scripts')
  </body>

</html>
