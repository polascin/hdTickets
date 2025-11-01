<!DOCTYPE html>
<html lang="en-GB" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#059669">
  
  {{-- Page Title --}}
  <title>@yield('title', 'HD Tickets - Smart Sports Ticket Monitoring')</title>
  
  {{-- Primary Meta Tags --}}
  <meta name="title" content="@yield('meta_title', 'HD Tickets - Smart Sports Ticket Monitoring')">
  <meta name="description" content="@yield('meta_description', 'Never miss your favourite sports events. Get instant alerts when tickets become available for Premier League, Champions League and more. Smart automation for serious fans.')">
  <meta name="keywords" content="@yield('meta_keywords', 'sports tickets, Premier League tickets, Champions League tickets, ticket alerts, automated ticket monitoring, football tickets')">
  <meta name="robots" content="@yield('robots', 'index, follow, max-image-preview:large')">
  <meta name="author" content="HD Tickets">
  
  {{-- Canonical URL --}}
  <link rel="canonical" href="@yield('canonical', url()->current())">
  
  {{-- Open Graph / Facebook --}}
  <meta property="og:type" content="@yield('og_type', 'website')">
  <meta property="og:url" content="@yield('og_url', url()->current())">
  <meta property="og:title" content="@yield('og_title', 'HD Tickets - Smart Sports Ticket Monitoring')">
  <meta property="og:description" content="@yield('og_description', 'Never miss your favourite sports events. Get instant alerts when tickets become available.')">
  <meta property="og:image" content="@yield('og_image', asset('assets/branding/hdTicketsLogo.png'))">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="HD Tickets Logo">
  <meta property="og:locale" content="en_GB">
  <meta property="og:site_name" content="HD Tickets">
  
  {{-- Twitter Card --}}
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="@yield('twitter_url', url()->current())">
  <meta property="twitter:title" content="@yield('twitter_title', 'HD Tickets - Smart Sports Ticket Monitoring')">
  <meta property="twitter:description" content="@yield('twitter_description', 'Never miss your favourite sports events.')">
  <meta property="twitter:image" content="@yield('twitter_image', asset('assets/branding/hdTicketsLogo.png'))">
  
  {{-- Favicons --}}
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/branding/icon-32x32.png') ?? asset('assets/branding/hdTicketsLogo.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/branding/icon-16x16.png') ?? asset('assets/branding/hdTicketsLogo.png') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/branding/icon-192x192.png') ?? asset('assets/branding/hdTicketsLogo.png') }}">
  <link rel="manifest" href="{{ asset('manifest.json') }}">
  
  {{-- Performance: DNS Prefetch & Preconnect --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="dns-prefetch" href="https://fonts.googleapis.com">
  
  {{-- Preload Critical Assets --}}
  <link rel="preload" href="{{ asset('assets/branding/hdTicketsLogo.webp') }}" as="image" type="image/webp" fetchpriority="high">
  <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" as="style">
  
  {{-- Main Assets --}}
  @vite(['resources/css/marketing.css', 'resources/js/marketing.js'])
  
  {{-- Additional Styles --}}
  @stack('styles')
  
  {{-- Structured Data (JSON-LD) --}}
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "WebApplication",
    "name": "HD Tickets",
    "description": "Smart sports ticket monitoring service for Premier League, Champions League and European matches",
    "url": "{{ config('app.url') }}",
    "logo": "{{ asset('assets/branding/hdTicketsLogo.png') }}",
    "applicationCategory": "SportsApplication",
    "operatingSystem": "Web Browser",
    "offers": {
      "@type": "Offer",
      "category": "Sports Ticket Monitoring",
      "priceCurrency": "GBP"
    },
    "publisher": {
      "@type": "Organization",
      "name": "HD Tickets",
      "logo": "{{ asset('assets/branding/hdTicketsLogo.png') }}"
    }
  }
  </script>
  
  @stack('schema')
  
  {{-- Google Analytics 4 (Conditional) --}}
  @if(config('services.analytics.ga4'))
  <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.analytics.ga4') }}"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ config('services.analytics.ga4') }}', {
      'anonymize_ip': true,
      'cookie_flags': 'SameSite=None;Secure'
    });
  </script>
  @endif
</head>

<body class="min-h-full font-sans antialiased hero-gradient">
  {{-- Skip to main content (Accessibility) --}}
  <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-100 bg-white px-4 py-2 rounded-lg shadow-lg text-blue-600 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
    Skip to main content
  </a>
  
  {{-- Header --}}
  <header role="banner" class="sticky top-0 z-50">
    @include('public.partials.header')
  </header>
  
  {{-- Main Content --}}
  <main id="main-content" role="main" tabindex="-1" class="flex-1">
    @yield('content')
  </main>
  
  {{-- Footer --}}
  <footer role="contentinfo">
    @include('public.partials.footer')
  </footer>
  
  {{-- Toast Container --}}
  <div id="toast-container" class="fixed top-4 right-4 z-90 space-y-2" aria-live="polite" aria-atomic="true"></div>
  
  {{-- Additional Scripts --}}
  @stack('scripts')
</body>
</html>
