<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">

    <title>{{ config('app.name', 'HD Tickets') }} - @yield('title', 'Dashboard')</title>
    <meta name="description" content="Professional sports ticket monitoring and alerting platform">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/hdTicketsLogo.png') }}">
    <link rel="manifest" href="/manifest.json">

    @vite(['resources/css/app.css','resources/js/app.js'])
    @stack('styles')
  </head>
  <body class="font-sans antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 bg-white text-blue-700 px-3 py-2 rounded">
      Skip to main content
    </a>

    <div class="min-h-screen bg-gray-100">
      <nav id="navigation" role="navigation" aria-label="Primary navigation">
        @include('layouts.navigation')
      </nav>

      @hasSection('header')
        <header class="bg-white shadow" role="banner">
          <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            @yield('header')
          </div>
        </header>
      @endif

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <main id="main-content" role="main" class="py-6" tabindex="-1">
          @include('layouts.partials.flash-messages')
          @yield('content')
        </main>
      </div>

      @hasSection('footer')
        <footer role="contentinfo" class="bg-white border-t">
          <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            @yield('footer')
          </div>
        </footer>
      @endif
    </div>

    @stack('scripts')
  </body>
</html>
