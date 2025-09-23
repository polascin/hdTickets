@php
  $user = auth()->user();
  $currentRoute = request()->route() ? request()->route()->getName() : '';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Meta Tags -->
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('description', 'Professional sports event ticket monitoring platform')">
    <meta name="keywords" content="sports tickets, ticket monitoring, event tickets, sports events">

    <!-- Open Graph -->
    <meta property="og:title" content="@yield('title', config('app.name'))">
    <meta property="og:description" content="@yield('description', 'Professional sports event ticket monitoring platform')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">

    <!-- Theme and PWA -->
    <meta name="theme-color" content="#1e40af">
    <meta name="color-scheme" content="light dark">
    <link rel="manifest" href="/manifest.json">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
  </head>

  <body class="h-full bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100 font-sans antialiased"
    x-data="{
        theme: localStorage.getItem('theme') || 'light',
        sidebarOpen: false,
        init() {
            this.$watch('theme', value => {
                localStorage.setItem('theme', value);
                document.documentElement.classList.toggle('dark', value === 'dark');
            });
            document.documentElement.classList.toggle('dark', this.theme === 'dark');
        },
        toggleTheme() {
            this.theme = this.theme === 'light' ? 'dark' : 'light';
        }
    }">

    <!-- Skip to main content -->
    <a href="#main-content"
      class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-blue-600 text-white px-4 py-2 rounded-md z-50">
      Skip to main content
    </a>

    <!-- Mobile menu overlay -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden"
      aria-hidden="true">
    </div>

    <div class="flex h-full">
      <!-- Sidebar -->
      <aside x-show="sidebarOpen" x-transition:enter="transition-transform duration-300 ease-out"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform duration-300 ease-in" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 lg:static lg:translate-x-0 lg:z-auto">

        <!-- Sidebar Header -->
        <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200 dark:border-gray-700">
          <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
            <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets" class="w-8 h-8">
            <span class="font-bold text-lg text-gray-900 dark:text-white">HD Tickets</span>
          </a>
          <button @click="sidebarOpen = false"
            class="lg:hidden p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700"
            aria-label="Close sidebar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
          @yield('sidebar')
        </nav>

        <!-- User Menu -->
        @if ($user)
          <div class="border-t border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                <span class="text-white text-sm font-medium">
                  {{ substr($user->name, 0, 1) }}
                </span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                  {{ $user->name }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ $user->email }}
                </p>
              </div>
            </div>
            <div class="mt-3 space-y-1">
              <a href="{{ route('profile.edit') }}"
                class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md dark:text-gray-300 dark:hover:bg-gray-700">
                Profile Settings
              </a>
              <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit"
                  class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md dark:text-gray-300 dark:hover:bg-gray-700">
                  Sign Out
                </button>
              </form>
            </div>
          </div>
        @endif
      </aside>

      <!-- Main Content -->
      <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Navigation -->
        <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 lg:hidden">
          <div class="flex items-center justify-between h-16 px-4">
            <button @click="sidebarOpen = true"
              class="p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700"
              aria-label="Open sidebar">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                </path>
              </svg>
            </button>

            <div class="flex items-center space-x-4">
              <button @click="toggleTheme()"
                class="p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700"
                :aria-label="theme === 'light' ? 'Switch to dark mode' : 'Switch to light mode'">
                <svg x-show="theme === 'light'" class="w-5 h-5" fill="none" stroke="currentColor"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                </svg>
                <svg x-show="theme === 'dark'" class="w-5 h-5" fill="none" stroke="currentColor"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z">
                  </path>
                </svg>
              </button>
            </div>
          </div>
        </header>

        <!-- Page Content -->
        <main id="main-content" class="flex-1 overflow-y-auto focus:outline-none">
          @yield('content')
        </main>
      </div>
    </div>

    <!-- Global scripts -->
    @stack('scripts')
  </body>

</html>
