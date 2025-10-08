<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

  <head>
    <meta charset="utf-8">
    <meta name="viewport"
      content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="HD Tickets">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#1e40af">
    <meta name="msapplication-navbutton-color" content="#1e40af">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'HD Tickets') }} - Sports Events Entry Tickets</title>
    <meta name="description"
      content="HD Tickets - Professional Sports Events Entry Tickets Monitoring, Scraping and Purchase System">

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/hdTicketsLogo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/hdTicketsLogo.png') }}">

    <!-- Preload critical resources -->
    <link rel="preload" as="image" href="{{ asset('assets/images/hdTicketsLogo.webp') }}" type="image/webp">
    <link rel="preload" as="image" href="{{ asset('assets/images/hdTicketsLogo.png') }}" type="image/png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CDN removed: styles now served via compiled app CSS -->

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Google reCAPTCHA v3 -->
    @if (config('services.recaptcha.enabled'))
      <script>
        window.recaptchaConfig = {
          siteKey: '{{ config('services.recaptcha.site_key') }}',
          enabled: true,
          minimumScore: {{ config('services.recaptcha.minimum_score', 0.5) }}
        };
      </script>
      <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}" async defer>
      </script>
    @else
      <script>
        window.recaptchaConfig = {
          siteKey: '',
          enabled: false,
          minimumScore: 0.5
        };
        // Mock reCAPTCHA for development
        window.grecaptcha = {
          ready: function(callback) {
            callback();
          },
          execute: function() {
            return Promise.resolve('development-token');
          }
        };
      </script>
    @endif

  </head>

  <body class="h-full bg-gradient-to-br from-stadium-50 via-white to-stadium-100 font-sans antialiased">
    <!-- Live Regions for Screen Reader Announcements -->
    <div id="hd-status-region" class="sr-only" aria-live="polite" aria-atomic="true"></div>
    <div id="hd-alert-region" class="sr-only" aria-live="assertive" aria-atomic="true"></div>

    <!-- Background Pattern -->
    <div class="fixed inset-0 -z-10">
      <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60"
        xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%239fa6b2"
        fill-opacity="0.05"%3E%3Ccircle cx="30" cy="30" r="1.5" /%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]
        opacity-40"></div>
    </div>

    <!-- Main Content (semantic auth container) -->
    <x-layout.auth-container>
      <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Logo -->
        <div class="flex justify-center animate-fade-in">
          <picture>
            <source srcset="{{ asset('assets/images/hdTicketsLogo.webp') }}" type="image/webp" width="80"
              height="80">
            <img src="{{ asset('assets/images/hdTicketsLogo.png') }}"
              alt="HD Tickets - Sports Events Entry Tickets Monitoring System"
              class="h-20 w-20 rounded-2xl shadow-lg ring-4 ring-white/20 transition-transform duration-300 hover:scale-105"
              width="80" height="80" loading="eager">
          </picture>
        </div>

        <!-- Brand Title -->
        <h1 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900 animate-slide-up">
          HD Tickets
        </h1>
        <p class="mt-2 text-center text-sm text-gray-600 animate-slide-up">
          Sports Events Entry Tickets System
        </p>
      </div>

      <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md animate-slide-up">
        {{ $slot }}
      </div>

      <!-- Security Info -->
      <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="text-center">
          <div class="inline-flex items-center px-4 py-2 rounded-full bg-green-50 border border-green-200">
            <svg class="h-4 w-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span class="text-sm font-medium text-green-800">Secure & Encrypted</span>
          </div>
        </div>
      </div>
    </x-layout.auth-container>

    <!-- Footer -->
    <div class="fixed bottom-0 left-0 right-0 bg-white/80 backdrop-blur-sm border-t border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-2 sm:space-y-0">
          <p class="text-xs text-gray-500">
            &copy; {{ date('Y') }} HD Tickets. All rights reserved.
          </p>
          <div class="flex items-center space-x-4 text-xs text-gray-400">
            <a href="{{ route('legal.privacy-policy') }}" class="hover:text-gray-600 transition-colors">Privacy
              Policy</a>
            <span>&bull;</span>
            <a href="{{ route('legal.terms-of-service') }}" class="hover:text-gray-600 transition-colors">Terms of
              Service</a>
            <span>&bull;</span>
            <a href="{{ route('legal.disclaimer') }}" class="hover:text-gray-600 transition-colors">Disclaimer</a>
            <span>&bull;</span>
            <a href="mailto:support@hd-tickets.com" class="hover:text-gray-600 transition-colors">Support</a>
          </div>
        </div>
      </div>
    </div>

  </body>

</html>
