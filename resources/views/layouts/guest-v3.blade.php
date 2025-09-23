@php
  $user = auth()->user();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Meta Tags -->
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('description', 'Professional sports event ticket monitoring platform')">
    <meta name="keywords" content="sports tickets, ticket monitoring, event tickets, sports events">

    <!-- Theme and PWA -->
    <meta name="theme-color" content="#1e40af">
    <meta name="color-scheme" content="light dark">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Styles -->
    @vite(['resources/css/app-v3.css', 'resources/js/app.js'])

    @stack('styles')
  </head>

  <body
    class="h-full bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
    <!-- Skip to main content -->
    <a href="#main-content"
      class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-blue-600 text-white px-4 py-2 rounded-md z-50">
      Skip to main content
    </a>

    <!-- Navigation -->
    <nav class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm border-b border-gray-200/20 dark:border-gray-700/20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <a href="{{ route('welcome') }}" class="flex items-center space-x-3">
              <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets" class="w-8 h-8">
              <span class="font-bold text-xl text-gray-900 dark:text-white">HD Tickets</span>
            </a>
          </div>

          <div class="flex items-center space-x-4">
            @if ($user)
              <span class="text-sm text-gray-700 dark:text-gray-300">Welcome, {{ $user->name }}</span>
              <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit"
                  class="text-sm text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                  Sign Out
                </button>
              </form>
            @else
              <a href="{{ route('login') }}"
                class="text-sm text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                Sign In
              </a>
              <a href="{{ route('register') }}" class="btn btn-primary">
                Get Started
              </a>
            @endif
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main id="main-content" class="flex-1">
      @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center text-sm text-gray-500 dark:text-gray-400">
          <p>&copy; {{ date('Y') }} HD Tickets. All rights reserved.</p>
        </div>
      </div>
    </footer>

    <!-- Global scripts -->
    @stack('scripts')
  </body>

</html>
