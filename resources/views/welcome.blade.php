@php@php

    $user = auth()->user();  $user = auth()->user();

@endphp ?>@endphp



<!DOCTYPE html>
<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
  <html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">



    <head>

      <head>

        <meta charset="utf-8">
        <meta charset="utf-8">

        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">



        <!-- SEO Meta Tags --> <!-- SEO Meta Tags -->

        <title>hdTickets - Professional Sports Event Ticket Monitoring Platform</title>
        <title>hdTickets - Professional Sports Event Ticket Monitoring Platform</title>

        <meta name="description"
          content="Advanced sports event ticket monitoring with automated purchasing and real-time analytics across multiple platforms. Never miss your favorite events again.">
        <meta name="description"
          content="Advanced sports event ticket monitoring with automated purchasing and real-time analytics across multiple platforms. Never miss your favorite events again.">

        <meta name="keywords" <meta name="keywords"
          content="sports tickets, ticket monitoring, event tickets, sports events, automated purchasing, ticket alerts, NFL tickets, NBA tickets, MLB tickets, NHL tickets">
        content="sports tickets, ticket monitoring, event tickets, sports events, automated purchasing, ticket alerts, NFL tickets, NBA tickets, MLB tickets, NHL tickets">

        <meta name="author" content="HD Tickets">
        <meta name="author" content="HD Tickets">



        <!-- Open Graph / Social Media --> <!-- Open Graph / Social Media -->

        <meta property="og:type" content="website">
        <meta property="og:type" content="website">

        <meta property="og:site_name" content="HD Tickets">
        <meta property="og:site_name" content="HD Tickets">

        <meta property="og:title" content="hdTickets - Professional Sports Event Ticket Monitoring Platform">
        <meta property="og:title" content="hdTickets - Professional Sports Event Ticket Monitoring Platform">

        <meta property="og:description"
          content="Advanced sports event ticket monitoring with automated purchasing and real-time analytics across multiple platforms. Never miss your favorite events again.">
        <meta property="og:description"
          content="Advanced sports event ticket monitoring with automated purchasing and real-time analytics across multiple platforms. Never miss your favorite events again.">

        <meta property="og:image" content="{{ asset('assets/images/og-image.jpg') }}">
        <meta property="og:image" content="{{ asset('assets/images/og-image.jpg') }}">

        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:url" content="{{ url()->current() }}">



        <!-- Twitter Card --> <!-- Twitter Card -->

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:card" content="summary_large_image">

        <meta name="twitter:title" content="hdTickets - Professional Sports Event Ticket Monitoring Platform">
        <meta name="twitter:title" content="hdTickets - Professional Sports Event Ticket Monitoring Platform">

        <meta name="twitter:description"
          content="Advanced sports event ticket monitoring with automated purchasing and real-time analytics across multiple platforms. Never miss your favorite events again.">
        <meta name="twitter:description"
          content="Advanced sports event ticket monitoring with automated purchasing and real-time analytics across multiple platforms. Never miss your favorite events again.">

        <meta name="twitter:image" content="{{ asset('assets/images/og-image.jpg') }}">
        <meta name="twitter:image" content="{{ asset('assets/images/og-image.jpg') }}">



        <!-- Theme and PWA --> <!-- Theme and PWA -->

        <meta name="theme-color" content="#1e40af">
        <meta name="theme-color" content="#1e40af">

        <meta name="color-scheme" content="light dark">
        <meta name="color-scheme" content="light dark">

        <meta name="mobile-web-app-capable" content="yes">
        <meta name="mobile-web-app-capable" content="yes">

        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">

        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">



        <!-- Performance hints --> <!-- Performance hints -->

        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>

        <link rel="dns-prefetch" href="//fonts.bunny.net">
        <link rel="dns-prefetch" href="//fonts.bunny.net">



        <!-- Fonts with font-display for better loading --> <!-- Fonts with font-display for better loading -->

        <link rel="preload" href="https://fonts.bunny.net/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
          <link rel="preload"
          href="https://fonts.bunny.net/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" as="style"
          onload="this.onload=null;this.rel='stylesheet'"> as="style"
        onload="this.onload=null;this.rel='stylesheet'">

        <noscript> <noscript>

            <link rel="stylesheet"
              href="https://fonts.bunny.net/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
            <link rel="stylesheet"
              href="https://fonts.bunny.net/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">

          </noscript> </noscript>



        <!-- Icons with proper sizes --> <!-- Icons with proper sizes -->

        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">

        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">

        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

        <link rel="manifest" href="{{ asset('site.webmanifest') }}">
        <link rel="manifest" href="{{ asset('site.webmanifest') }}">



        <!-- Styles --> <!-- Styles -->

        @vite(['resources/css/app-v3.css', 'resources/js/app.js']) @vite(['resources/css/app-v3.css', 'resources/js/app.js'])



        <!-- Performance and analytics (structured data) --> <!-- Performance and analytics (structured data) -->

        <script type="application/ld+json">    <script type="application/ld+json">

    {    {

      "@context": "https://schema.org",      "@context": "https://schema.org",

      "@type": "SoftwareApplication",      "@type": "SoftwareApplication",

      "name": "HD Tickets",      "name": "HD Tickets",

      "description": "Professional sports event ticket monitoring platform",      "description": "Professional sports event ticket monitoring platform",

      "applicationCategory": "BusinessApplication",      "applicationCategory": "BusinessApplication",

      "operatingSystem": "Web",      "operatingSystem": "Web",

      "offers": {      "offers": {

        "@type": "Offer",        "@type": "Offer",

        "price": "29",        "price": "29",

        "priceCurrency": "USD"        "priceCurrency": "USD"

      }      }

    }    }

    </script>
        </script>



        <!-- Welcome Page Specific Styles --> <!-- Welcome Page Specific Styles -->

        <style>
          <style>
          /* Prevent horizontal overflow and ensure usability */
          /* Prevent horizontal overflow and ensure usability */

          body {
            body {

              overflow-x: hidden;
              overflow-x: hidden;

            }
          }



          .max-w-7xl {
            .max-w-7xl {

              max-width: min(80rem, calc(100vw - 2rem));
              max-width: min(80rem, calc(100vw - 2rem));

            }
          }



          /* Enhanced card styles */
          /* Enhanced card styles */

          .feature-card {
            .feature-card {

              @apply bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 p-6 sm:p-8 border border-gray-200 dark:border-gray-700;
              @apply bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 p-6 sm:p-8 border border-gray-200 dark:border-gray-700;

              min-height: 320px;
              min-height: 320px;

              display: flex;
              /* Increased for better content distribution */

              flex-direction: column;
              display: flex;

            }

            flex-direction: column;

          }

          .feature-card:hover {

            @apply transform -translate-y-1;

            .feature-card:hover {}

            @apply transform -translate-y-1;

          }

          .feature-icon {

            @apply mx-auto flex items-center justify-center rounded-xl mb-4 sm:mb-6 transition-colors duration-300;

            .feature-icon {

              width: 3rem !important;
              @apply mx-auto flex items-center justify-center rounded-xl mb-4 sm:mb-6 transition-colors duration-300;

              height: 3rem !important;
              width: 3rem !important;

              flex-shrink: 0;
              height: 3rem !important;

            }

            flex-shrink: 0;

          }

          /* Fix potential SVG sizing issues */

          .feature-icon svg,
          /* Fix potential SVG sizing issues */

          .feature-card svg {
            .feature-icon svg,

            flex-shrink: 0 !important;

            .feature-card svg {

              max-width: none !important;
              flex-shrink: 0 !important;

            }

            max-width: none !important;

          }

          .feature-icon svg {

            width: 1.5rem !important;

            .feature-icon svg {

              height: 1.5rem !important;
              width: 1.5rem !important;

            }

            height: 1.5rem !important;

          }

          /* Ensure consistent icon sizing */

          .w-4 {
            /* Ensure consistent icon sizing */

            width: 1rem !important;

            .w-4 {}

            width: 1rem !important;

          }

          .h-4 {

            height: 1rem !important;

            .h-4 {}

            height: 1rem !important;

          }

          .w-5 {

            width: 1.25rem !important;

            .w-5 {}

            width: 1.25rem !important;

          }

          .h-5 {

            height: 1.25rem !important;

            .h-5 {}

            height: 1.25rem !important;

          }

          .w-6 {

            width: 1.5rem !important;

            .w-6 {}

            width: 1.5rem !important;

          }

          .h-6 {

            height: 1.5rem !important;

            .h-6 {}

            height: 1.5rem !important;

          }

          /* Pricing card styles */

          .pricing-card {
            /* Pricing card styles */

            @apply bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 sm:p-8 border border-gray-200 dark:border-gray-700 relative;

            .pricing-card {

              min-height: 450px;
              @apply bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 sm:p-8 border border-gray-200 dark:border-gray-700 relative;

              display: flex;
              min-height: 450px;

              flex-direction: column;
              /* Ensure consistent pricing card heights */

            }

            display: flex;

            flex-direction: column;

            .pricing-card:hover {}

            @apply transform -translate-y-2;

          }

          .pricing-card:hover {

            @apply transform -translate-y-2;

            .pricing-card-featured {}

            @apply ring-2 ring-blue-500 dark:ring-blue-400;

            transform: scale(1.02);

            .pricing-card-featured {}

            @apply ring-2 ring-blue-500 dark:ring-blue-400;

            transform: scale(1.02);

            .pricing-badge {
              /* Reduced from 1.05 to prevent layout issues */

              @apply absolute -top-4 left-1/2 transform -translate-x-1/2 z-10;
            }

          }

          .pricing-badge {

            .pricing-header {
              @apply absolute -top-4 left-1/2 transform -translate-x-1/2 z-10;

              @apply text-center flex-shrink-0;
            }

          }

          .pricing-header {

            .pricing-amount {
              @apply text-center flex-shrink-0;

              @apply flex items-baseline justify-center mb-4;
            }

          }

          .pricing-amount {

            .pricing-features {
              @apply flex items-baseline justify-center mb-4;

              @apply space-y-3 mb-8 flex-1;
            }

            min-height: 160px;

          }

          .pricing-features {

            @apply space-y-3 mb-8 flex-1;

            .pricing-footer {
              min-height: 160px;

              @apply mt-auto flex-shrink-0;
              /* Ensure consistent feature list height */

            }
          }



          .feature-item {
            .pricing-footer {

              @apply flex items-start;
              @apply mt-auto flex-shrink-0;

            }
          }



          .pricing-cta {
            .feature-item {

              @apply block w-full text-center font-semibold py-4 px-6 rounded-xl transition-all duration-200 focus:outline-none focus:ring-4;
              @apply flex items-start;

            }
          }



          .pricing-cta-primary {
            .pricing-cta {

              @apply bg-blue-600 hover:bg-blue-700 focus:bg-blue-700 text-white focus:ring-blue-300;
              @apply block w-full text-center font-semibold py-4 px-6 rounded-xl transition-all duration-200 focus:outline-none focus:ring-4;

            }
          }



          .pricing-cta-secondary {
            .pricing-cta-primary {

              @apply bg-gray-100 hover:bg-gray-200 focus:bg-gray-200 text-gray-900 focus:ring-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white dark:focus:ring-gray-500;
              @apply bg-blue-600 hover:bg-blue-700 focus:bg-blue-700 text-white focus:ring-blue-300;

            }
          }



          /* Animation utilities */
          .pricing-cta-secondary {

            .fade-in {
              @apply bg-gray-100 hover:bg-gray-200 focus:bg-gray-200 text-gray-900 focus:ring-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white dark:focus:ring-gray-500;

              opacity: 0;
            }

            transform: translateY(20px);

            transition: opacity 0.6s ease-out,
            transform 0.6s ease-out;
            /* Animation utilities */

          }

          .fade-in {

            opacity: 0;

            .fade-in.visible {
              transform: translateY(20px);

              opacity: 1;
              transition: opacity 0.6s ease-out, transform 0.6s ease-out;

              transform: translateY(0);
            }

          }

          .fade-in.visible {

            /* Loading states for stats */
            opacity: 1;

            [data-stat].loading::after {
              transform: translateY(0);

              content: '';
            }

            position: absolute;

            top: 0;
            /* Loading states for stats */

            left: -100%;

            [data-stat].loading::after {

              height: 100%;
              content: '';

              width: 100%;
              position: absolute;

              background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
              top: 0;

              animation: loading 1.5s infinite;
              left: -100%;

            }

            height: 100%;

            width: 100%;

            @keyframes loading {
              background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);

              0% {
                animation: loading 1.5s infinite;

                left: -100%;
              }

            }

            100% {
              @keyframes loading {

                left: 100%;

                0% {}

                left: -100%;

              }
            }



            /* Responsive adjustments */
            100% {

              @media (max-width: 640px) {
                left: 100%;

                .hero-section {}

                min-height: 85vh;
              }

            }

            /* Responsive adjustments */

            .feature-card {
              @media (max-width: 640px) {

                min-height: auto;

                .hero-section {}

                min-height: 85vh;

              }

              .pricing-card {

                min-height: auto;

                .feature-card {}

                min-height: auto;

              }
            }



            /* SVG containment */
            .pricing-card {

              svg {
                min-height: auto;

                overflow: visible;
              }

              max-width: 100%;
            }

            height: auto;

          }

          /* SVG containment */
        </style>
        </style>

      </head>
    </head>



    <body<body
      class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 antialiased flex flex-col">
      class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 antialiased flex flex-col">

      <!-- Skip to main content for accessibility --> <!-- Skip to main content for accessibility -->

      <a href="#main-content" <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-blue-600 text-white px-4 py-2 rounded-md z-50 transition-all duration-200 hover:bg-blue-700">
        class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-blue-600 text-white px-4 py-2 rounded-md z-50 transition-all duration-200 hover:bg-blue-700">

        Skip to main content Skip to main content

      </a> </a>



      <!-- Enhanced Navigation --> <!-- Enhanced Navigation -->

      <nav <nav
        class="bg-white/90 dark:bg-gray-900/90 backdrop-blur-md border-b border-gray-200/30 dark:border-gray-700/30 sticky top-0 z-40 shadow-sm">
        class="bg-white/90 dark:bg-gray-900/90 backdrop-blur-md border-b border-gray-200/30 dark:border-gray-700/30 sticky top-0 z-40 shadow-sm">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex justify-between items-center h-16">
              <div class="flex justify-between items-center h-16">

                <div class="flex items-center">
                  <div class="flex items-center">

                    <a href="{{ route('home') }}" class="flex items-center space-x-3 group"> <a
                        href="{{ route('home') }}" class="flex items-center space-x-3 group">

                        <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets Logo" <img
                          src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets Logo"
                          class="w-8 h-8 transition-transform duration-200 group-hover:scale-110">
                        class="w-8 h-8 transition-transform duration-200 group-hover:scale-110">

                        <span <span
                          class="font-bold text-xl text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200">HD
                          class="font-bold text-xl text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200">HD

                          Tickets</span> Tickets</span>

                      </a> </a>

                  </div>
                </div>



                <div class="flex items-center space-x-4">
                  <div class="flex items-center space-x-4">

                    @if ($user)
                      @if ($user)
                        <div class="flex items-center space-x-4">
                          <div class="flex items-center space-x-4">

                            <span class="text-sm text-gray-700 dark:text-gray-300 hidden sm:inline-block"> <span
                                class="text-sm text-gray-700 dark:text-gray-300 hidden sm:inline-block">

                                Welcome, <span class="font-medium">{{ $user->name }}</span> Welcome, <span
                                  class="font-medium">{{ $user->name }}</span>

                              </span> </span>

                            <a href="/dashboard" <a href="/dashboard"
                              class="text-sm text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors duration-200">
                              class="text-sm text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors duration-200">

                              Dashboard Dashboard

                            </a> </a>

                            <form method="POST" action="{{ route('logout') }}" class="inline">
                              <form method="POST" action="{{ route('logout') }}" class="inline">

                                @csrf @csrf

                                <button type="submit" <button type="submit"
                                  class="text-sm text-gray-700 hover:text-red-600 dark:text-gray-300 dark:hover:text-red-400 transition-colors duration-200">
                                  class="text-sm text-gray-700 hover:text-red-600 dark:text-gray-300 dark:hover:text-red-400 transition-colors duration-200">

                                  Sign Out Sign Out

                                </button> </button>

                              </form>
                            </form>

                          </div>
                        </div>
                      @else

                      @else
                        <a href="{{ route('login') }}" <a href="{{ route('login') }}"
                          class="text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors duration-200">
                          class="text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors duration-200">

                          Sign In Sign In

                        </a> </a>

                        <a href="{{ route('register') }}" <a href="{{ route('register') }}"
                          class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 focus:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-blue-300 shadow-sm hover:shadow-md">
                          class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 focus:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-blue-300 shadow-sm hover:shadow-md">

                          Get Started Get Started

                        </a> </a>
                      @endif
                    @endif

                  </div>
                </div>

              </div>
            </div>

          </div>
        </div>

      </nav>
      </nav>



      <!-- Main Content --> <!-- Main Content -->

      <main id="main-content" class="flex-1">
        <main id="main-content" class="flex-1">

          <!-- Hero Section --> </style>

          <section< /head>

            class="hero-section relative bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 text-white overflow-hidden"
            "ratingValue": "4.8",

            aria-label="Welcome to hdTickets"> "reviewCount": "1250"

            <div class="absolute inset-0 bg-black bg-opacity-20" aria-hidden="true"></div> }

            <!-- Decorative background pattern --> }

            <div class="absolute inset-0 opacity-10 pointer-events-none overflow-hidden" aria-hidden="true">
              </script>

              <svg class="absolute inset-0 w-full h-full min-w-full min-h-full" viewBox="0 0 100 100"
                preserveAspectRatio="xMidYMid slice">
                <title>HD Tickets - Professional Sports Ticket Monitoring Platform | Role-Based Access & GDPR Compliance
                </title>

                <defs>

                  <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                    <!-- Fonts -->

                    <path d="M 10 0 L 0 0 0 10" fill="none" stroke="currentColor" stroke-width="0.5" />
                    <link rel="preconnect" href="https://fonts.bunny.net">

                  </pattern>
                  <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

                </defs>

                <rect width="100%" height="100%" fill="url(#grid)" /> <!-- Vite assets -->

              </svg> @vite(['resources/css/welcome.css', 'resources/js/welcome.js'])

            </div>

            <!-- Route-specific preloads -->

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 lg:py-28">
              @include('layouts.partials.preloads-welcome')

              <div class="text-center">

                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold mb-4 sm:mb-6 leading-tight">
                  <style>
                    hdTickets
                    /* Critical CSS for immediate rendering */

                    <span class="block text-lg sm:text-xl md:text-2xl lg:text-3xl font-normal text-blue-200 mt-2">* {

                      Professional Sports Event Ticket Monitoring margin: 0;

                      </span>padding: 0;

                      </h1>box-sizing: border-box;

                      <p class="text-lg sm:text-xl lg:text-2xl text-blue-100 max-w-4xl mx-auto mb-6 sm:mb-8 leading-relaxed">
                    }

                    Advanced ticket monitoring,
                    automated purchasing,
                    and comprehensive analytics for sports event tickets across multiple platforms. <span class="font-semibold text-yellow-300">Never miss your favorite events again.</span>html,

                    </p>body {

                      width: 100%;

                      < !-- Hero Stats -->overflow-x: hidden;

                      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 max-w-4xl mx-auto mb-8 sm:mb-10" margin: 0;

                      role="region" aria-label="Platform statistics">padding: 0;

                      <div
                    }

                    class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/20 hover:bg-white/15 transition-all duration-300"><div class="text-2xl sm:text-3xl lg:text-4xl font-bold text-yellow-400 mb-1" id="total-tickets"
                    /* Prevent flash of unstyled content */

                    data-stat="total_tickets" aria-label="Total tickets monitored">.welcome-layout {

                      {{ number_format($total_tickets ?? 12500) }} opacity: 0;

                      </div>animation: fadeIn 0.5s ease-out forwards;

                      <div class="text-xs sm:text-sm text-blue-200 font-medium">Tickets Monitored</div>
                    }

                    </div><div @keyframes fadeIn {

                      class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/20 hover:bg-white/15 transition-all duration-300">to {

                        <div class="text-2xl sm:text-3xl lg:text-4xl font-bold text-green-400 mb-1" id="active-events" opacity: 1;

                        data-stat="active_events" aria-label="Currently active events">
                      }

                      {{ number_format($active_events ?? 342) }}
                    }

                    </div>
                  </style>

                  <div class="text-xs sm:text-sm text-blue-200 font-medium">Active Events</div>
                  </head>

              </div>

              <div <body class="stadium-bg field-pattern welcome-layout">

                class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/20 hover:bg-white/15 transition-all duration-300">
                <!-- Header -->

                <div class="text-2xl sm:text-3xl lg:text-4xl font-bold text-purple-400 mb-1" id="satisfied-customers"
                  <header class="welcome-header">

                  data-stat="satisfied_customers" aria-label="Number of satisfied customers"> <nav
                    class="welcome-nav">

                    {{ number_format($satisfied_customers ?? 1247) }} <a href="{{ url('/') }}"
                      class="welcome-logo">

                </div>
                <div class="welcome-logo-icon">🎫</div>

                <div class="text-xs sm:text-sm text-blue-200 font-medium">Happy Customers</div> HD Tickets

              </div> </a>

              <div
                class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/20 hover:bg-white/15 transition-all duration-300">
                <div class="welcome-nav-links">

                  <div class="text-2xl sm:text-3xl lg:text-4xl font-bold text-orange-400 mb-1" id="avg-savings"
                    @if (Route::has('login')) data-stat="avg_savings" aria-label="Average savings per customer">            @auth

                  ${{ number_format($avg_savings ?? 127) }}              <a href="{{ url('/dashboard') }}" class="welcome-btn welcome-btn-primary">Dashboard</a>

                </div>              <form method="POST" action="{{ route('logout') }}" style="display: inline;">

                <div class="text-xs sm:text-sm text-blue-200 font-medium">Avg. Savings</div>                @csrf

              </div>                <button type="submit" class="welcome-btn welcome-btn-secondary">Logout</button>

            </div>              </form>

            @else

            <!-- Call-to-action buttons -->              <a href="{{ route('login') }}" class="welcome-btn welcome-btn-secondary">Sign In</a>

            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center max-w-md mx-auto">              <a href="{{ route('register.public') }}" class="welcome-btn welcome-btn-primary">Register</a>

              <a href="/register"            @endauth

                class="w-full sm:w-auto inline-flex items-center justify-center bg-yellow-500 hover:bg-yellow-400 focus:bg-yellow-600 text-black font-semibold px-8 py-4 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-yellow-300 group" @endif
                    aria-label="Start monitoring sports events - Free trial available"> </div>

                  <span class="mr-2">Start Monitoring</span> </nav>

                  <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform duration-200" fill="none"
                    </header>

                    viewBox="0 0 24 24" stroke="currentColor">

                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 7l5 5m0 0l-5 5m5-5H6" /> <!-- Main Content -->

                  </svg>
                  <main class="welcome-main" role="main">

                    </a> <!-- Hero Section -->

                    <a href="/login" <section class="welcome-hero">

                      class="w-full sm:w-auto inline-flex items-center justify-center bg-transparent border-2 border-white hover:bg-white hover:text-blue-900 focus:bg-white focus:text-blue-900 text-white font-semibold px-8 py-4 rounded-xl transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-white/50"
                      <h1 class="welcome-hero-title hero-title-enhanced">

                        aria-label="Sign in to your existing account"> Never Miss Your Team Again

                        Sign In </h1>

                    </a>
                    <h2 class="welcome-hero-subtitle">

                </div> Professional Sports Ticket Monitoring Platform

                </h2>

                <!-- Trust indicators -->
                <p class="welcome-hero-description">

                <div class="mt-8 sm:mt-12 text-center"> Monitor 50+ ticket platforms, get instant alerts for your
                  favorite teams,

                  <p class="text-sm text-blue-200 mb-4 font-medium">Trusted by sports fans worldwide</p> and never pay
                  full price again. Role-based access, enterprise security,

                  <div class="flex justify-center items-center space-x-8 opacity-60"> and GDPR compliance included.

                    <div class="text-xs text-blue-300">🏈 NFL</div>
                    </p>

                    <div class="text-xs text-blue-300">🏀 NBA</div>
                    <div class="welcome-hero-cta">

                      <div class="text-xs text-blue-300">⚾ MLB</div> <a href="{{ route('register.public') }}"
                        class="welcome-btn welcome-btn-primary stadium-lights">

                        <div class="text-xs text-blue-300">🏒 NHL</div> Start Free Trial

                        <div class="text-xs text-blue-300">⚽ MLS</div>
                      </a>

                    </div> <a href="#features" class="welcome-btn welcome-btn-secondary">

                  </div> Learn More

                </div> </a>

              </div>
            </div>

            </section>
            </section>



            <!-- Key Features Section --> <!-- Live Stats Section -->

            <section class="py-16 sm:py-20 lg:py-24 bg-gray-50 dark:bg-gray-900" aria-label="Platform features">
              <section class="welcome-stats" x-data="welcomeStats()">

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                  <div class="welcome-stat-card scoreboard-stat" x-data="{ count: 0 }" x-intersect="count = 15000">

                    <div class="text-center mb-12 sm:mb-16">
                      <div class="welcome-stat-icon">🎯</div>

                      <h2
                        class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4 sm:mb-6">
                        <span class="welcome-stat-number" x-text="count.toLocaleString() + '+'" x-transition></span>

                        Comprehensive Ticket Monitoring Platform <span class="welcome-stat-label">Active Alerts</span>

                      </h2> <span class="welcome-stat-description">Currently monitoring</span>

                      <p class="text-lg sm:text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto leading-relaxed">
                    </div>

                    Everything you need to monitor, analyze, and purchase sports event tickets across multiple platforms
                    with

                    intelligent automation <div class="welcome-stat-card scoreboard-stat" x-data="{ count: 0 }"
                      x-intersect="count = 50">

                      </p>
                      <div class="welcome-stat-icon">🏟️</div>

                    </div> <span class="welcome-stat-number" x-text="count + '+'" x-transition></span>

                    <span class="welcome-stat-label">Platforms</span>

                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 lg:gap-10"> <span
                        class="welcome-stat-description">Monitored daily</span>

                      <!-- Real-time Monitoring --> </div>

                    <div class="feature-card group">

                      <div
                        class="feature-icon bg-blue-100 dark:bg-blue-900 group-hover:bg-blue-200 dark:group-hover:bg-blue-800">
                        <div class="welcome-stat-card scoreboard-stat" x-data="{ count: 0 }"
                          x-intersect="count = 25000">

                          <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none"
                            viewBox="0 0 24 24" <div class="welcome-stat-icon">👥
                        </div>

                        stroke="currentColor" aria-hidden="true"> <span class="welcome-stat-number"
                          x-text="count.toLocaleString() + '+'" x-transition></span>

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" <span
                          class="welcome-stat-label">Happy Users</span>

                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /> <span class="welcome-stat-description">Sports fans
                            like you</span>

                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" </div>

                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542
                            7-4.477 0-8.268-2.943-9.542-7z" />

                            </svg>
                            <div class="welcome-stat-card scoreboard-stat" x-data="{ count: 0 }"
                              x-intersect="count = 2">

                            </div>
                            <div class="welcome-stat-icon">💰</div>

                            <h3 class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-white mb-3">Real-time
                              Monitoring</h3> <span class="welcome-stat-number" x-text="'$' + count + 'M+'"
                              x-transition></span>

                            <p class="text-gray-600 dark:text-gray-400 mb-4 leading-relaxed"> <span
                                class="welcome-stat-label">Saved on Tickets</span>

                              24/7 monitoring of ticket availability and prices across multiple platforms with instant
                              notifications when <span class="welcome-stat-description">By our community</span>

                              your criteria are met
                      </div>

                      </p>
              </section>

              <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-2">

                <li class="flex items-center"> <!-- Features Section -->

                  <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" <section
                    id="features" class="welcome-features">

                    stroke="currentColor"> <div class="welcome-feature-card feature-card-enhanced ticket-stub">

                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      <div class="welcome-feature-icon">⚡</div>

                  </svg>
                  <h3 class="welcome-feature-title">Real-Time Monitoring</h3>

                  Ticketmaster integration <p class="welcome-feature-description">

                </li> Get instant notifications when tickets become available for your favorite teams and events.

                <li class="flex items-center">
                  </p>

                  <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" </div>

                    stroke="currentColor">

                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    <div class="welcome-feature-card feature-card-enhanced ticket-stub">

                  </svg>
                  <div class="welcome-feature-icon">🔒</div>

                  StubHub & secondary markets <h3 class="welcome-feature-title">Enterprise Security</h3>

                </li>
                <p class="welcome-feature-description">

                  <li class="flex items-center"> 2FA authentication, role-based access control, and GDPR compliance for
                    peace of mind.

                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" </p>

                      stroke="currentColor"> </div>

                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />

                    </svg>
                    <div class="welcome-feature-card feature-card-enhanced ticket-stub">

                      SeatGeek & Vivid Seats tracking <div class="welcome-feature-icon">📊</div>

                  </li>
                <h3 class="welcome-feature-title">Price Analytics</h3>

              </ul>
              <p class="welcome-feature-description">

                </div> Advanced analytics to help you find the best deals and track pricing trends.

              </p>

              <!-- Automated Purchasing --> </div>

              <div class="feature-card group">

                <div <div class="welcome-feature-card feature-card-enhanced ticket-stub">

                  class="feature-icon bg-green-100 dark:bg-green-900 group-hover:bg-green-200 dark:group-hover:bg-green-800">
                  <div class="welcome-feature-icon">📱</div>

                  <svg class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0" fill="none"
                    viewBox="0 0 24 24" <h3 class="welcome-feature-title">Multi-Platform</h3>

                    stroke="currentColor" aria-hidden="true"> <p class="welcome-feature-description">

                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" /> Monitor StubHub, Ticketmaster, SeatGeek, and 50+ other
                      platforms from one dashboard.

                  </svg> </p>

                </div>
              </div>

              <h3 class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-white mb-3">Automated Purchasing
              </h3>

              <p class="text-gray-600 dark:text-gray-400 mb-4 leading-relaxed">
              <div class="welcome-feature-card feature-card-enhanced ticket-stub">

                Smart automated purchasing based on your preferences, budget, and seat selection criteria with
                lightning-fast <div class="welcome-feature-icon">⚙️</div>

                execution <h3 class="welcome-feature-title">Smart Automation</h3>

                </p>
                <p class="welcome-feature-description">

                <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-2"> Set custom filters, price alerts, and
                  automated purchasing rules to never miss a deal.

                  <li class="flex items-center">
                    </p>

                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" </div>

                      stroke="currentColor">

                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      <div class="welcome-feature-card feature-card-enhanced ticket-stub">

                    </svg>
                    <div class="welcome-feature-icon">🎯</div>

                    Price threshold triggers <h3 class="welcome-feature-title">Team-Specific Alerts</h3>

                  </li>
                  <p class="welcome-feature-description">

                    <li class="flex items-center"> Follow your favorite teams across all sports and get notified about
                      home and away games.

                      <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" </p>

                        stroke="currentColor">
              </div>

              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </section>

            </svg>

            Smart seat preference matching <!-- Call to Action Section -->

            </li>
            <section class="welcome-cta-section celebration-particles">

              <li class="flex items-center">
                <h2 class="welcome-cta-title">Ready to Join the Game?</h2>

                <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" <p
                  class="welcome-cta-description">

                  stroke="currentColor"> Start your 7-day free trial today and never miss another game.

                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /> No
                  credit card required, cancel anytime.

                </svg> </p>

                Instant success notifications <div class="welcome-cta-buttons">

              </li> <a href="{{ route('register.public') }}" class="welcome-btn welcome-btn-primary">

                </ul> Start Free Trial

                </div> </a>

              <a href="{{ route('login') }}" class="welcome-btn welcome-btn-secondary">

                <!-- Advanced Analytics --> Sign In

                <div class="feature-card group">
              </a>

              <div </div>

                class="feature-icon bg-purple-100 dark:bg-purple-900 group-hover:bg-purple-200 dark:group-hover:bg-purple-800">
            </section>

            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400 flex-shrink-0" fill="none"
              viewBox="0 0 24 24" </main>

              stroke="currentColor" aria-hidden="true">

              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" <!-- Footer -->

                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012
                2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                <footer class="welcome-footer">

            </svg>
            <div class="welcome-footer-content">

            </div>
            <div class="welcome-footer-links">

              <h3 class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-white mb-3">Advanced Analytics</h3>
              <a href="{{ route('legal.privacy-policy') }}" class="welcome-footer-link">Privacy Policy</a>

              <p class="text-gray-600 dark:text-gray-400 mb-4 leading-relaxed"> <a
                  href="{{ route('legal.terms-of-service') }}" class="welcome-footer-link">Terms of Service</a>

                Comprehensive analytics dashboard with price trends, availability patterns, and market insights to
                optimize <a href="{{ route('legal.disclaimer') }}" class="welcome-footer-link">Disclaimer</a>

                your ticket purchasing strategy <a href="mailto:support@hdtickets.com"
                  class="welcome-footer-link">Support</a>

              </p>
            </div>

            <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-2">
              <p>&copy; {{ date('Y') }} HD Tickets. All rights reserved.</p>

              <li class="flex items-center">
                </div>

                <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" </footer>

                  stroke="currentColor">

                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  <!-- Alpine.js for interactivity -->

                </svg>
                <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

                Historical price analysis

              </li>
              <script>
                < li class = "flex items-center" > // Welcome page stats animation

                <
                svg class = "w-4 h-4 text-green-500 mr-2 flex-shrink-0"
                fill = "none"
                viewBox = "0 0 24 24"
                function welcomeStats() {

                  stroke = "currentColor" >
                    return {

                      <
                      path stroke - linecap = "round"
                      stroke - linejoin = "round"
                      stroke - width = "2"
                      d = "M5 13l4 4L19 7" / > init() {

                        <
                        /svg>            / / Initialize any additional stats functionality

                        Market trend predictions
                      }

                      <
                      /li>        }

                      <
                      li class = "flex items-center" >
                    }

                    <
                    svg class = "w-4 h-4 text-green-500 mr-2 flex-shrink-0"
                  fill = "none"
                  viewBox = "0 0 24 24"

                  stroke = "currentColor" > // Smooth scrolling for anchor links

                    <
                    path stroke - linecap = "round"
                  stroke - linejoin = "round"
                  stroke - width = "2"
                  d = "M5 13l4 4L19 7" / > document.querySelectorAll('a[href^="#"]').forEach(anchor => {

                      <
                      /svg>        anchor.addEventListener('click', function(e) {

                      ROI optimization reports e.preventDefault();

                      <
                      /li>          const target = document.querySelector(this.getAttribute('href'));

                      <
                      /ul>          if (target) {

                      <
                      /div>            target.scrollIntoView({

                      <
                      /div>              behavior: 'smooth',

                      <
                      /div>              block: 'start'

                      <
                      /section>            });

                    }

                    <
                    !--Pricing Section-- >
                  });

                <
                section class = "py-16 sm:py-20 lg:py-24 bg-white dark:bg-gray-800"
                aria - label = "Subscription pricing" >
                });

                <
                div class = "max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" >

                <
                div class = "text-center mb-12 sm:mb-16" > // Add intersection observer for animations

                <
                h2 class = "text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4 sm:mb-6" >
                const observerOptions = {

                  Choose Your Plan threshold: 0.1,

                  <
                  /h2>        rootMargin: '0px 0px -50px 0px'

                  <
                  p class = "text-lg sm:text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto leading-relaxed" >
                };

                Flexible subscription options designed
                for casual fans to professional ticket brokers

                  <
                  /p>      const observer = new IntersectionObserver((entries) => {

                  <
                  /div>        entries.forEach(entry => {

                if (entry.isIntersecting) {

                  <
                  div class = "grid md:grid-cols-3 gap-6 sm:gap-8 lg:gap-10 max-w-5xl mx-auto" > entry.target.style
                    .animationPlayState = 'running';

                  <
                  !--Starter Plan-- >
                }

                <
                div class = "pricing-card" >
                });

                <
                div class = "pricing-header" >
                }, observerOptions);

                <
                h3 class = "text-xl sm:text-2xl font-semibold text-gray-900 dark:text-white mb-2" > Starter < /h3>

                  <
                  div class = "pricing-amount" > // Observe elements for animation

                  <
                  span class = "text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white" > $19 <
                  /span>      document.querySelectorAll('.welcome-feature-card, .welcome-stat-card').forEach(el => {

                  <
                  span class = "text-gray-600 dark:text-gray-400 ml-2" > /month</span > observer.observe(el);

                <
                /div>      });

                <
                p class = "text-gray-600 dark:text-gray-400 text-sm" > Perfect
                for casual fans < /p>

                  <
                  /div>      / / Add ripple effect to buttons

                document.querySelectorAll('.welcome-btn').forEach(button => {

                      <
                      div class = "pricing-features" > button.addEventListener('click', function(e) {

                            <
                            div class = "feature-item" >
                            const ripple = document.createElement('span');

                            <
                            svg class = "w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5"
                            fill = "none"
                            viewBox = "0 0 24 24"
                            const rect = this.getBoundingClientRect();

                            stroke = "currentColor" >
                              const size = Math.max(rect.width, rect.height);

                            <
                            path stroke - linecap = "round"
                            stroke - linejoin = "round"
                            stroke - width = "2"
                            d = "M5 13l4 4L19 7" / >
                              const x = e.clientX - rect.left - size / 2;

                            <
                            /svg>          const y = e.clientY - rect.top - size /
                            2;

                            <
                            span class = "text-gray-700 dark:text-gray-300" > Monitor up to 5 events < /span>

                              <
                              /div>          ripple.style.cssText = `

                <
                div class = "feature-item" > position: absolute;

              <
              svg class = "w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5"
              fill = "none"
              viewBox = "0 0 24 24"
              width: $ {
                size
              }
              px;

              stroke = "currentColor" > height: $ {
                size
              }
              px;

              <
              path stroke - linecap = "round"
              stroke - linejoin = "round"
              stroke - width = "2"
              d = "M5 13l4 4L19 7" / > left: $ {
                x
              }
              px;

              <
              /svg>                    top: ${y}px;

              <
              span class = "text-gray-700 dark:text-gray-300" > Email notifications <
                /span>                    background: rgba(255, 255, 255, 0.3);

                <
                /div>                    border-radius: 50%;

                <
                div class = "feature-item" > transform: scale(0);

              <
              svg class = "w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5"
              fill = "none"
              viewBox = "0 0 24 24"
              animation: ripple 0.6 s linear;

              stroke = "currentColor" > pointer - events: none;

              <
              path stroke - linecap = "round"
              stroke - linejoin = "round"
              stroke - width = "2"
              d = "M5 13l4 4L19 7" / > `;

                                </svg>

                                <span class="text-gray-700 dark:text-gray-300">Basic analytics</span>          this.style.position = 'relative';

                              </div>          this.style.overflow = 'hidden';

                              <div class="feature-item">          this.appendChild(ripple);

                                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"

                                  stroke="currentColor">          setTimeout(() => ripple.remove(), 600);

                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />        });

                                </svg>      });

                                <span class="text-gray-700 dark:text-gray-300">Community support</span>

                              </div>      // Add ripple animation

                            </div>      const style = document.createElement('style');

                    style.textContent = `

                <
                div class = "pricing-footer" > @keyframes ripple {

                  <
                  a href = "/register?plan=starter"
                  class = "pricing-cta pricing-cta-secondary" > to {

                    Start Free Trial transform: scale(4);

                    <
                    /a>                    opacity: 0;

                    <
                    /div>                }

                    <
                    /div>            }

                    `;

                          <!-- Professional Plan (Featured) -->      document.head.appendChild(style);

                          <div class="pricing-card pricing-card-featured">    
              </script>

              <div class="pricing-badge">
                </body>

                <span class="bg-blue-600 text-white px-4 py-1 rounded-full text-sm font-semibold">Most Popular</span>

  </html>

  </div>
  <div class="pricing-header">
    <h3 class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-white mb-2">Professional</h3>
    <div class="pricing-amount">
      <span class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">$49</span>
      <span class="text-gray-600 dark:text-gray-400 ml-2">/month</span>
    </div>
    <p class="text-gray-600 dark:text-gray-400 text-sm">For serious ticket hunters</p>
  </div>

  <div class="pricing-features">
    <div class="feature-item">
      <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
        stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
      <span class="text-gray-700 dark:text-gray-300">Monitor up to 25 events</span>
    </div>
    <div class="feature-item">
      <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
        stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
      <span class="text-gray-700 dark:text-gray-300">SMS + Email notifications</span>
    </div>
    <div class="feature-item">
      <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
        stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
      <span class="text-gray-700 dark:text-gray-300">Advanced analytics & reports</span>
    </div>
    <div class="feature-item">
      <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
        stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
      <span class="text-gray-700 dark:text-gray-300">Automated purchasing</span>
    </div>
    <div class="feature-item">
      <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
        stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
      <span class="text-gray-700 dark:text-gray-300">Priority support</span>
    </div>
  </div>

  <div class="pricing-footer">
    <a href="/register?plan=professional" class="pricing-cta pricing-cta-primary">
      Start Free Trial
    </a>
  </div>
  </div>

  <!-- Enterprise Plan -->
  <div class="pricing-card">
    <div class="pricing-header">
      <h3 class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-white mb-2">Enterprise</h3>
      <div class="pricing-amount">
        <span class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">$149</span>
        <span class="text-gray-600 dark:text-gray-400 ml-2">/month</span>
      </div>
      <p class="text-gray-600 dark:text-gray-400 text-sm">For ticket brokers & agencies</p>
    </div>

    <div class="pricing-features">
      <div class="feature-item">
        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span class="text-gray-700 dark:text-gray-300">Unlimited event monitoring</span>
      </div>
      <div class="feature-item">
        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span class="text-gray-700 dark:text-gray-300">Multi-channel notifications</span>
      </div>
      <div class="feature-item">
        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span class="text-gray-700 dark:text-gray-300">Custom analytics & API access</span>
      </div>
      <div class="feature-item">
        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span class="text-gray-700 dark:text-gray-300">Advanced automated purchasing</span>
      </div>
      <div class="feature-item">
        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span class="text-gray-700 dark:text-gray-300">Dedicated account manager</span>
      </div>
    </div>

    <div class="pricing-footer">
      <a href="/contact?plan=enterprise" class="pricing-cta pricing-cta-secondary">
        Contact Sales
      </a>
    </div>
  </div>
  </div>

  <!-- Trust indicators -->
  <div class="text-center mt-12 sm:mt-16">
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
      ✓ 7-day free trial on all plans ✓ No setup fees ✓ Cancel anytime
    </p>
    <div class="flex justify-center items-center space-x-8 text-xs text-gray-500 dark:text-gray-400">
      <div class="flex items-center">
        <svg class="w-4 h-4 mr-1 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
        SSL Secured
      </div>
      <div class="flex items-center">
        <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
        </svg>
        PCI Compliant
      </div>
      <div class="flex items-center">
        <svg class="w-4 h-4 mr-1 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
        GDPR Ready
      </div>
    </div>
  </div>
  </div>
  </section>
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

  <!-- Welcome Page JavaScript -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize the welcome page manager
      if (typeof WelcomePageManager !== 'undefined') {
        const welcomeManager = new WelcomePageManager();
      }

      // Initialize Alpine.js components for interactivity
      if (typeof Alpine !== 'undefined') {
        Alpine.start();
      }

      // Progressive enhancement for users without JavaScript
      if (typeof WelcomePageManager === 'undefined') {
        console.warn('Welcome page JavaScript not loaded, some features may be limited');
      }

      // Handle stats loading errors gracefully
      window.addEventListener('error', function(e) {
        if (e.filename && e.filename.includes('welcome.js')) {
          console.warn('Welcome page enhanced features not available');
        }
      });

      // Fade-in animation observer
      const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
      };

      const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
          }
        });
      }, observerOptions);

      // Observe elements for animation
      document.querySelectorAll('.feature-card, .pricing-card').forEach(el => {
        el.classList.add('fade-in');
        observer.observe(el);
      });

      // Stats counter animation
      const statsElements = document.querySelectorAll('[data-stat]');
      statsElements.forEach(function(element) {
        const target = parseInt(element.textContent.replace(/,/g, ''));
        const duration = 2000;
        const start = Date.now();

        function updateCounter() {
          const now = Date.now();
          const progress = Math.min((now - start) / duration, 1);
          const current = Math.floor(progress * target);
          element.textContent = current.toLocaleString();

          if (progress < 1) {
            requestAnimationFrame(updateCounter);
          } else {
            element.textContent = target.toLocaleString();
          }
        }

        // Start animation when element comes into view
        const statsObserver = new IntersectionObserver(function(entries) {
          entries.forEach(function(entry) {
            if (entry.isIntersecting) {
              updateCounter();
              statsObserver.unobserve(entry.target);
            }
          });
        }, {
          threshold: 0.5
        });

        statsObserver.observe(element);
      });
    });
  </script>

  <!-- Vite Scripts -->
  @vite(['resources/js/welcome.js'])
  </body>

</html>
