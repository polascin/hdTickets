@php
  $user = auth()->user();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Meta Tags -->
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('description', 'Professional sports event ticket monitoring platform with automated purchasing and real-time analytics across multiple platforms.')">
    <meta name="keywords"
      content="sports tickets, ticket monitoring, event tickets, sports events, automated purchasing, ticket alerts, NFL tickets, NBA tickets, MLB tickets, NHL tickets">
    <meta name="author" content="HD Tickets">

    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="HD Tickets">
    <meta property="og:title" content="@yield('title', 'HD Tickets - Professional Sports Event Ticket Monitoring')">
    <meta property="og:description" content="@yield('description', 'Never miss your favorite sports events. Advanced ticket monitoring with automated purchasing.')">
    <meta property="og:image" content="{{ asset('assets/images/og-image.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'HD Tickets - Professional Sports Event Ticket Monitoring')">
    <meta name="twitter:description" content="@yield('description', 'Never miss your favorite sports events. Advanced ticket monitoring with automated purchasing.')">
    <meta name="twitter:image" content="{{ asset('assets/images/og-image.jpg') }}">

    <!-- Theme and PWA -->
    <meta name="theme-color" content="#1e40af">
    <meta name="color-scheme" content="light dark">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <!-- Performance hints -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="dns-prefetch" href="//fonts.bunny.net">

    <!-- Fonts with font-display for better loading -->
    <link rel="preload" href="https://fonts.bunny.net/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
      as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
      <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    </noscript>

    <!-- Icons with proper sizes -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <!-- Styles -->
    @vite(['resources/css/app-v3.css', 'resources/js/app.js'])

    <!-- Route-specific preloads -->
    @includeWhen(request()->routeIs(['home', 'welcome']), 'layouts.partials.preloads-welcome')

    @stack('styles')

    <!-- Performance and analytics (structured data could go here) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "HD Tickets",
      "description": "Professional sports event ticket monitoring platform",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web",
      "offers": {
        "@type": "Offer",
        "price": "29",
        "priceCurrency": "USD"
      }
    }
    </script>
  </head>

  <body
    class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 antialiased flex flex-col">
    <!-- Skip to main content for accessibility -->
    <a href="#main-content"
      class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-blue-600 text-white px-4 py-2 rounded-md z-50 transition-all duration-200 hover:bg-blue-700">
      Skip to main content
    </a>

    <!-- Enhanced Navigation -->
    <nav
      class="bg-white/90 dark:bg-gray-900/90 backdrop-blur-md border-b border-gray-200/30 dark:border-gray-700/30 sticky top-0 z-40 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center">
            <a href="{{ route('home') }}" class="flex items-center space-x-3 group">
              <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets Logo"
                class="w-8 h-8 transition-transform duration-200 group-hover:scale-110">
              <span
                class="font-bold text-xl text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200">HD
                Tickets</span>
            </a>
          </div>

          <div class="flex items-center space-x-4">
            @if ($user)
              <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-700 dark:text-gray-300 hidden sm:inline-block">
                  Welcome, <span class="font-medium">{{ $user->name }}</span>
                </span>
                <a href="/dashboard"
                  class="text-sm text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors duration-200">
                  Dashboard
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                  @csrf
                  <button type="submit"
                    class="text-sm text-gray-700 hover:text-red-600 dark:text-gray-300 dark:hover:text-red-400 transition-colors duration-200">
                    Sign Out
                  </button>
                </form>
              </div>
            @else
              <a href="{{ route('login') }}"
                class="text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors duration-200">
                Sign In
              </a>
              <a href="{{ route('register') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 focus:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-blue-300 shadow-sm hover:shadow-md">
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

    <!-- Enhanced Footer -->
    <footer class="bg-gray-900 text-white border-t border-gray-800" role="contentinfo">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid md:grid-cols-4 gap-8 mb-8">
          <div class="md:col-span-2">
            <div class="flex items-center space-x-3 mb-4">
              <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets Logo" class="w-8 h-8">
              <span class="font-bold text-xl">HD Tickets</span>
            </div>
            <p class="text-gray-400 mb-4 leading-relaxed">
              Professional sports event ticket monitoring platform designed for serious fans, agents, and organizations.
            </p>
            <div class="flex space-x-4">
              <a href="mailto:support@hdtickets.com"
                class="text-gray-400 hover:text-white transition-colors duration-200 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                support@hdtickets.com
              </a>
            </div>
          </div>

          <div>
            <h4 class="font-semibold mb-4 text-lg">Features</h4>
            <ul class="space-y-3 text-gray-400">
              <li><a href="#features" class="hover:text-white transition-colors duration-200">Real-time Monitoring</a>
              </li>
              <li><a href="#features" class="hover:text-white transition-colors duration-200">Automated Purchasing</a>
              </li>
              <li><a href="#features" class="hover:text-white transition-colors duration-200">Analytics Dashboard</a>
              </li>
              <li><a href="#features" class="hover:text-white transition-colors duration-200">Multi-Platform
                  Support</a></li>
            </ul>
          </div>

          <div>
            <h4 class="font-semibold mb-4 text-lg">Legal & Support</h4>
            <ul class="space-y-3 text-gray-400">
              <li><a href="/legal/terms-of-service" class="hover:text-white transition-colors duration-200">Terms of
                  Service</a></li>
              <li><a href="/legal/privacy-policy" class="hover:text-white transition-colors duration-200">Privacy
                  Policy</a></li>
              <li><a href="/contact" class="hover:text-white transition-colors duration-200">Contact Support</a></li>
              <li><a href="/legal/gdpr-compliance" class="hover:text-white transition-colors duration-200">GDPR
                  Compliance</a></li>
            </ul>
          </div>
        </div>

        <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center">
          <p class="text-gray-400 text-sm">
            &copy; {{ date('Y') }} HD Tickets. All rights reserved.
          </p>
          <div class="flex items-center space-x-4 mt-4 md:mt-0">
            <span class="text-xs text-gray-500">Built with Laravel {{ app()->version() }}</span>
            <span class="text-xs text-gray-500">•</span>
            <span class="text-xs text-gray-500">Secured & GDPR Compliant</span>
          </div>
        </div>
      </div>
    </footer>

    <!-- Global scripts -->
    @stack('scripts')
  </body>

</html>
