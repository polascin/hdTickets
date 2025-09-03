@props([
    'title' => 'HD Tickets',
    'subtitle' => '',
    'showSidebar' => true,
    'sidebarCollapsed' => false,
    'breadcrumbs' => [],
    'meta' => [],
    'headerActions' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="robots" content="noindex, nofollow">

    <!-- SEO Meta Tags -->
    <title>{{ $title }} - {{ config('app.name') }}</title>
    @if (!empty($subtitle))
      <meta name="description" content="{{ $subtitle }}">
    @else
      <meta name="description" content="Professional sports ticket monitoring and alerting platform">
    @endif

    <!-- Favicon and PWA Icons -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/hdTicketsLogo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/hdTicketsLogo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Preload Critical Resources -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preload" href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" as="style">

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">

    <!-- Design System CSS (timestamp auto-appended by middleware) -->
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Additional Styles Stack -->
    @stack('styles')

    <style>
      /* Unified Sports Theme Application Layout */
      .hd-unified-layout {
        display: flex;
        height: 100vh;
        background-color: #f8fafc;
        font-family: var(--hd-font-family);
      }

      .hd-unified-sidebar {
        width: 280px;
        background-color: white;
        border-right: 1px solid var(--hd-gray-200);
        transition: transform var(--hd-transition-base);
        position: relative;
        z-index: var(--hd-z-30);
        overflow-y: auto;
      }

      .hd-unified-sidebar--collapsed {
        transform: translateX(-100%);
      }

      .hd-unified-sidebar__header {
        padding: var(--hd-space-6);
        border-bottom: 1px solid var(--hd-gray-200);
        background-color: white;
        position: sticky;
        top: 0;
        z-index: var(--hd-z-10);
      }

      .hd-unified-sidebar__nav {
        padding: var(--hd-space-4);
      }

      .hd-unified-nav-item {
        display: flex;
        align-items: center;
        gap: var(--hd-space-3);
        padding: var(--hd-space-3) var(--hd-space-4);
        color: var(--hd-gray-600);
        text-decoration: none;
        border-radius: var(--hd-radius-md);
        transition: all var(--hd-transition-fast);
        font-size: var(--hd-text-sm);
        font-weight: var(--hd-font-medium);
        min-height: 44px;
        margin-bottom: var(--hd-space-1);
      }

      .hd-unified-nav-item:hover {
        background-color: var(--hd-gray-50);
        color: var(--hd-gray-900);
        transform: translateX(4px);
      }

      .hd-unified-nav-item--active {
        background-color: rgba(37, 99, 235, 0.1);
        color: var(--hd-primary);
        border-left: 3px solid var(--hd-primary);
        padding-left: calc(var(--hd-space-4) - 3px);
      }

      .hd-unified-nav-item__icon {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
      }

      .hd-unified-main-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background-color: #f8fafc;
      }

      .hd-unified-header {
        background-color: white;
        border-bottom: 1px solid var(--hd-gray-200);
        padding: var(--hd-space-4) var(--hd-space-6);
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 64px;
        position: sticky;
        top: 0;
        z-index: var(--hd-z-20);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      }

      .hd-unified-content {
        flex: 1;
        overflow-y: auto;
        padding: var(--hd-space-6);
        background-color: #f8fafc;
      }

      .hd-mobile-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: var(--hd-z-20);
      }

      /* Sports Theme Enhancements */
      .hd-logo-container {
        display: flex;
        align-items: center;
        gap: var(--hd-space-3);
        margin-bottom: var(--hd-space-4);
      }

      .hd-logo-container img {
        width: 40px;
        height: 40px;
        border-radius: var(--hd-radius-lg);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }

      .hd-app-title {
        font-size: var(--hd-text-lg);
        font-weight: var(--hd-font-bold);
        color: var(--hd-gray-900);
        margin: 0;
      }

      .hd-app-subtitle {
        font-size: var(--hd-text-xs);
        color: var(--hd-gray-500);
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.05em;
      }

      /* Mobile Styles */
      @media (max-width: 768px) {
        .hd-unified-sidebar {
          position: fixed;
          top: 0;
          left: 0;
          height: 100vh;
          z-index: var(--hd-z-40);
        }

        .hd-mobile-overlay--visible {
          display: block;
        }

        .hd-mobile-nav-toggle {
          display: flex;
        }

        .hd-unified-content {
          padding: var(--hd-space-4);
        }

        .hd-unified-header {
          padding: var(--hd-space-3) var(--hd-space-4);
        }
      }

      @media (min-width: 769px) {
        .hd-mobile-nav-toggle {
          display: none;
        }
      }

      /* Enhanced Animation States */
      .hd-loading-skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
      }

      @keyframes loading {
        0% {
          background-position: 200% 0;
        }

        100% {
          background-position: -200% 0;
        }
      }

      .hd-loading-skeleton--spinner {
        width: 2rem;
        height: 2rem;
        border: 3px solid #f3f4f6;
        border-top: 3px solid #3b82f6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
      }

      @keyframes spin {
        0% {
          transform: rotate(0deg);
        }

        100% {
          transform: rotate(360deg);
        }
      }

      /* User Profile Section */
      .hd-sidebar-footer {
        position: sticky;
        bottom: 0;
        background-color: white;
        border-top: 1px solid var(--hd-gray-200);
        padding: var(--hd-space-4);
      }

      .hd-user-profile {
        display: flex;
        items: center;
        gap: var(--hd-space-3);
        padding: var(--hd-space-3);
        border-radius: var(--hd-radius-md);
        background-color: var(--hd-gray-50);
      }

      .hd-user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: var(--hd-primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: var(--hd-font-medium);
        font-size: var(--hd-text-xs);
      }

      .hd-user-info h4 {
        font-size: var(--hd-text-sm);
        font-weight: var(--hd-font-medium);
        color: var(--hd-gray-900);
        margin: 0;
      }

      .hd-user-info p {
        font-size: var(--hd-text-xs);
        color: var(--hd-gray-500);
        margin: 0;
        text-transform: capitalize;
      }

      /* Dark mode support */
      [x-cloak] {
        display: none !important;
      }
    </style>
  </head>

  <body class="h-full font-sans antialiased">
    <div id="app" class="hd-unified-layout" x-data="unifiedLayout()" x-init="init()">
      <!-- Mobile Overlay -->
      <div class="hd-mobile-overlay" :class="{ 'hd-mobile-overlay--visible': sidebarOpen }"
        x-show="sidebarOpen && isMobile" @click="closeSidebar()"
        x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
      </div>

      @if ($showSidebar)
        <!-- Unified Sidebar -->
        <aside class="hd-unified-sidebar" :class="{ 'hd-unified-sidebar--collapsed': !sidebarOpen && isMobile }"
          x-show="sidebarOpen || !isMobile" x-transition:enter="transform transition ease-in-out duration-300"
          x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
          x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-x-0"
          x-transition:leave-end="-translate-x-full">

          <!-- Sidebar Header -->
          <div class="hd-unified-sidebar__header">
            <div class="hd-logo-container">
              <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets Logo">
              <div>
                <h1 class="hd-app-title">HD Tickets</h1>
                <p class="hd-app-subtitle">Sports Monitoring</p>
              </div>
            </div>
          </div>

          <!-- Navigation -->
          <nav class="hd-unified-sidebar__nav">
            <div class="space-y-1">
              <!-- Dashboard -->
              <a href="{{ route('dashboard') }}"
                class="hd-unified-nav-item {{ request()->routeIs('dashboard') ? 'hd-unified-nav-item--active' : '' }}">
                <svg class="hd-unified-nav-item__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v0"></path>
                </svg>
                <span>Dashboard</span>
              </a>

              @if (Auth::user()->isAdmin() || Auth::user()->isAgent())
                <!-- Sports Tickets -->
                <a href="{{ route('tickets.scraping.index') }}"
                  class="hd-unified-nav-item {{ request()->routeIs('tickets.scraping.*') ? 'hd-unified-nav-item--active' : '' }}">
                  <svg class="hd-unified-nav-item__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z">
                    </path>
                  </svg>
                  <span>Sports Tickets</span>
                </a>

                <!-- Alerts -->
                <a href="{{ route('tickets.alerts.index') }}"
                  class="hd-unified-nav-item {{ request()->routeIs('tickets.alerts.*') ? 'hd-unified-nav-item--active' : '' }}">
                  <svg class="hd-unified-nav-item__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                  </svg>
                  <span>My Alerts</span>
                </a>

                <!-- Purchase Queue -->
                <a href="{{ route('purchase-decisions.index') }}"
                  class="hd-unified-nav-item {{ request()->routeIs('purchase-decisions.*') ? 'hd-unified-nav-item--active' : '' }}">
                  <svg class="hd-unified-nav-item__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13a2 2 0 100 4 2 2 0 000-4zm-8 4a2 2 0 11-4 0 2 2 0 014 0z">
                    </path>
                  </svg>
                  <span>Purchase Queue</span>
                </a>
              @endif

              <!-- Profile -->
              <a href="{{ route('profile.show') }}"
                class="hd-unified-nav-item {{ request()->routeIs('profile.*') ? 'hd-unified-nav-item--active' : '' }}">
                <svg class="hd-unified-nav-item__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span>Profile</span>
                @php $profileCompletion = Auth::user()->getProfileCompletion(); @endphp
                @if ($profileCompletion['percentage'] < 90)
                  <span class="ml-auto w-2 h-2 bg-yellow-400 rounded-full"></span>
                @endif
              </a>

              @if (Auth::user()->isAdmin())
                <!-- Admin Section -->
                <div class="pt-4 mt-4 border-t border-gray-200">
                  <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                    Administration</p>

                  <a href="{{ route('admin.dashboard') }}"
                    class="hd-unified-nav-item {{ request()->routeIs('admin.dashboard') ? 'hd-unified-nav-item--active' : '' }}">
                    <svg class="hd-unified-nav-item__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                      </path>
                    </svg>
                    <span>Admin Dashboard</span>
                  </a>

                  @if (Auth::user()->canManageUsers())
                    <a href="{{ route('admin.users.index') }}"
                      class="hd-unified-nav-item {{ request()->routeIs('admin.users.*') ? 'hd-unified-nav-item--active' : '' }}">
                      <svg class="hd-unified-nav-item__icon" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                        </path>
                      </svg>
                      <span>Users</span>
                    </a>
                  @endif

                  <a href="{{ route('admin.reports.index') }}"
                    class="hd-unified-nav-item {{ request()->routeIs('admin.reports.*') ? 'hd-unified-nav-item--active' : '' }}">
                    <svg class="hd-unified-nav-item__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                      </path>
                    </svg>
                    <span>Reports</span>
                  </a>
                </div>
              @endif
            </div>
          </nav>

          <!-- User Profile Footer -->
          <div class="hd-sidebar-footer">
            <div class="hd-user-profile">
              @php $user = Auth::user(); @endphp
              <div class="hd-user-avatar">
                {{ strtoupper(substr($user->name, 0, 1)) }}
              </div>
              <div class="hd-user-info flex-1">
                <h4>{{ $user->name }}</h4>
                <p>{{ ucfirst($user->role) }}</p>
              </div>
              <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="p-1 text-gray-400 hover:text-gray-600 transition-colors"
                  title="Logout">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                    </path>
                  </svg>
                </button>
              </form>
            </div>
          </div>
        </aside>
      @endif

      <!-- Main Content -->
      <div class="hd-unified-main-content">
        <!-- Header -->
        <header class="hd-unified-header">
          <div class="flex items-center gap-4">
            @if ($showSidebar)
              <!-- Mobile menu button -->
              <button @click="toggleSidebar()" class="hd-mobile-nav-toggle hd-button hd-button--ghost hd-button--sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                  </path>
                </svg>
                <span class="hd-sr-only">Toggle sidebar</span>
              </button>
            @endif

            <div>
              <h1 class="hd-heading-3 !mb-0">{{ $title }}</h1>
              @if (!empty($subtitle))
                <p class="hd-text-small">{{ $subtitle }}</p>
              @endif
            </div>
          </div>

          <div class="flex items-center gap-4">
            @if (!empty($headerActions))
              {{ $headerActions }}
            @endif

            <!-- Theme Toggle -->
            <button @click="toggleDarkMode()" class="hd-button hd-button--ghost hd-button--sm"
              :title="darkMode ? 'Switch to light mode' : 'Switch to dark mode'">
              <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z">
                </path>
              </svg>
              <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z">
                </path>
              </svg>
            </button>
          </div>
        </header>

        <!-- Content -->
        <main class="hd-unified-content">
          <!-- Flash Messages -->
          @if (session('success'))
            <div class="hd-card mb-6 border-l-4 border-l-green-500 bg-green-50">
              <div class="hd-card__content">
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <p class="text-green-800">{{ session('success') }}</p>
                </div>
              </div>
            </div>
          @endif

          @if (session('error'))
            <div class="hd-card mb-6 border-l-4 border-l-red-500 bg-red-50">
              <div class="hd-card__content">
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <p class="text-red-800">{{ session('error') }}</p>
                </div>
              </div>
            </div>
          @endif

          <!-- Page Content -->
          {{ $slot }}
        </main>
      </div>
    </div>

    <!-- Additional Scripts Stack -->
    @stack('scripts')

    <!-- Unified Layout Alpine.js Component -->
    <script>
      function unifiedLayout() {
        return {
          sidebarOpen: false,
          isMobile: false,
          darkMode: localStorage.getItem('darkMode') === 'true',

          init() {
            this.checkMobile();
            window.addEventListener('resize', () => this.checkMobile());

            // Initialize dark mode
            document.documentElement.classList.toggle('dark', this.darkMode);
          },

          checkMobile() {
            this.isMobile = window.innerWidth < 769;
            if (!this.isMobile) {
              this.sidebarOpen = true;
            }
          },

          toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
          },

          closeSidebar() {
            if (this.isMobile) {
              this.sidebarOpen = false;
            }
          },

          toggleDarkMode() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('darkMode', this.darkMode);
            document.documentElement.classList.toggle('dark', this.darkMode);
          }
        }
      }
    </script>
  </body>

</html>
