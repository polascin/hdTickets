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
    <title>hdTickets - Professional Sports Event Ticket Monitoring Platform</title>
    <meta name="description" content="Advanced sports event ticket monitoring with automated purchasing and real-time analytics across multiple platforms. Never miss your favorite events again.">
    <meta name="keywords"
      content="sports tickets, ticket monitoring, event tickets, sports events, automated purchasing, ticket alerts, NFL tickets, NBA tickets, MLB tickets, NHL tickets">
    <meta name="author" content="HD Tickets">

    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="HD Tickets">
    <meta property="og:title" content="hdTickets - Professional Sports Event Ticket Monitoring Platform">
    <meta property="og:description" content="Advanced sports event ticket monitoring with automated purchasing and real-time analytics across multiple platforms. Never miss your favorite events again.">
    <meta property="og:image" content="{{ asset('assets/images/og-image.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="hdTickets - Professional Sports Event Ticket Monitoring Platform">
    <meta name="twitter:description" content="Advanced sports event ticket monitoring with automated purchasing and real-time analytics across multiple platforms. Never miss your favorite events again.">
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

    <!-- Performance and analytics (structured data) -->
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

    <!-- Welcome Page Specific Styles -->
    <style>
    /* Prevent horizontal overflow and ensure usability */
    body {
      overflow-x: hidden;
    }
    
    .max-w-7xl {
      max-width: min(80rem, calc(100vw - 2rem));
    }

    /* Enhanced card styles */
    .feature-card {
      @apply bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 p-6 sm:p-8 border border-gray-200 dark:border-gray-700;
      min-height: 320px;
      /* Increased for better content distribution */
      display: flex;
      flex-direction: column;
    }

    .feature-card:hover {
      @apply transform -translate-y-1;
    }

    .feature-icon {
      @apply mx-auto flex items-center justify-center rounded-xl mb-4 sm:mb-6 transition-colors duration-300;
      width: 3rem !important;
      height: 3rem !important;
      flex-shrink: 0;
    }

    /* Fix potential SVG sizing issues */
    .feature-icon svg,
    .feature-card svg {
      flex-shrink: 0 !important;
      max-width: none !important;
    }

    .feature-icon svg {
      width: 1.5rem !important;
      height: 1.5rem !important;
    }

    /* Ensure consistent icon sizing */
    .w-4 {
      width: 1rem !important;
    }

    .h-4 {
      height: 1rem !important;
    }

    .w-5 {
      width: 1.25rem !important;
    }

    .h-5 {
      height: 1.25rem !important;
    }

    .w-6 {
      width: 1.5rem !important;
    }

    .h-6 {
      height: 1.5rem !important;
    }

    /* Pricing card styles */
    .pricing-card {
      @apply bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 sm:p-8 border border-gray-200 dark:border-gray-700 relative;
      min-height: 450px;
      /* Ensure consistent pricing card heights */
      display: flex;
      flex-direction: column;
    }

    .pricing-card:hover {
      @apply transform -translate-y-2;
    }

    .pricing-card-featured {
      @apply ring-2 ring-blue-500 dark:ring-blue-400;
      transform: scale(1.02);
      /* Reduced from 1.05 to prevent layout issues */
    }

    .pricing-badge {
      @apply absolute -top-4 left-1/2 transform -translate-x-1/2 z-10;
    }

    .pricing-header {
      @apply text-center flex-shrink-0;
    }

    .pricing-amount {
      @apply flex items-baseline justify-center mb-4;
    }

    .pricing-features {
      @apply space-y-3 mb-8 flex-1;
      min-height: 160px;
      /* Ensure consistent feature list height */
    }

    .pricing-footer {
      @apply mt-auto flex-shrink-0;
    }

    .feature-item {
      @apply flex items-start;
    }

    .pricing-cta {
      @apply block w-full text-center font-semibold py-4 px-6 rounded-xl transition-all duration-200 focus:outline-none focus:ring-4;
    }

    .pricing-cta-primary {
      @apply bg-blue-600 hover:bg-blue-700 focus:bg-blue-700 text-white focus:ring-blue-300;
    }

    .pricing-cta-secondary {
      @apply bg-gray-100 hover:bg-gray-200 focus:bg-gray-200 text-gray-900 focus:ring-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white dark:focus:ring-gray-500;
    }

    /* Animation utilities */
    .fade-in {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }

    .fade-in.visible {
      opacity: 1;
      transform: translateY(0);
    }

    /* Loading states for stats */
    [data-stat].loading::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      height: 100%;
      width: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      animation: loading 1.5s infinite;
    }

    @keyframes loading {
      0% {
        left: -100%;
      }

      100% {
        left: 100%;
      }
    }

    /* Responsive adjustments */
    @media (max-width: 640px) {
      .hero-section {
        min-height: 85vh;
      }

      .feature-card {
        min-height: auto;
      }

      .pricing-card {
        min-height: auto;
      }
    }

    /* SVG containment */
    </style>
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
    </style>
</head>
        "ratingValue": "4.8",
        "reviewCount": "1250"
      }
    }
    </script>

    <title>HD Tickets - Professional Sports Ticket Monitoring Platform | Role-Based Access & GDPR Compliance</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Vite assets -->
    @vite(['resources/css/welcome.css', 'resources/js/welcome.js'])

    <!-- Route-specific preloads -->
    @include('layouts.partials.preloads-welcome')

    <style>
      /* Critical CSS for immediate rendering */
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      html,
      body {
        width: 100%;
        overflow-x: hidden;
        margin: 0;
        padding: 0;
      }

      /* Prevent flash of unstyled content */
      .welcome-layout {
        opacity: 0;
        animation: fadeIn 0.5s ease-out forwards;
      }

      @keyframes fadeIn {
        to {
          opacity: 1;
        }
      }
    </style>
  </head>

  <body class="stadium-bg field-pattern welcome-layout">
    <!-- Header -->
    <header class="welcome-header">
      <nav class="welcome-nav">
        <a href="{{ url('/') }}" class="welcome-logo">
          <div class="welcome-logo-icon">🎫</div>
          HD Tickets
        </a>

        <div class="welcome-nav-links">
          @if (Route::has('login'))
            @auth
              <a href="{{ url('/dashboard') }}" class="welcome-btn welcome-btn-primary">Dashboard</a>
              <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="welcome-btn welcome-btn-secondary">Logout</button>
              </form>
            @else
              <a href="{{ route('login') }}" class="welcome-btn welcome-btn-secondary">Sign In</a>
              <a href="{{ route('register.public') }}" class="welcome-btn welcome-btn-primary">Register</a>
            @endauth
          @endif
        </div>
      </nav>
    </header>

    <!-- Main Content -->
    <main class="welcome-main" role="main">
      <!-- Hero Section -->
      <section class="welcome-hero">
        <h1 class="welcome-hero-title hero-title-enhanced">
          Never Miss Your Team Again
        </h1>
        <h2 class="welcome-hero-subtitle">
          Professional Sports Ticket Monitoring Platform
        </h2>
        <p class="welcome-hero-description">
          Monitor 50+ ticket platforms, get instant alerts for your favorite teams,
          and never pay full price again. Role-based access, enterprise security,
          and GDPR compliance included.
        </p>
        <div class="welcome-hero-cta">
          <a href="{{ route('register.public') }}" class="welcome-btn welcome-btn-primary stadium-lights">
            Start Free Trial
          </a>
          <a href="#features" class="welcome-btn welcome-btn-secondary">
            Learn More
          </a>
        </div>
      </section>

      <!-- Live Stats Section -->
      <section class="welcome-stats" x-data="welcomeStats()">
        <div class="welcome-stat-card scoreboard-stat" x-data="{ count: 0 }" x-intersect="count = 15000">
          <div class="welcome-stat-icon">🎯</div>
          <span class="welcome-stat-number" x-text="count.toLocaleString() + '+'" x-transition></span>
          <span class="welcome-stat-label">Active Alerts</span>
          <span class="welcome-stat-description">Currently monitoring</span>
        </div>

        <div class="welcome-stat-card scoreboard-stat" x-data="{ count: 0 }" x-intersect="count = 50">
          <div class="welcome-stat-icon">🏟️</div>
          <span class="welcome-stat-number" x-text="count + '+'" x-transition></span>
          <span class="welcome-stat-label">Platforms</span>
          <span class="welcome-stat-description">Monitored daily</span>
        </div>

        <div class="welcome-stat-card scoreboard-stat" x-data="{ count: 0 }" x-intersect="count = 25000">
          <div class="welcome-stat-icon">👥</div>
          <span class="welcome-stat-number" x-text="count.toLocaleString() + '+'" x-transition></span>
          <span class="welcome-stat-label">Happy Users</span>
          <span class="welcome-stat-description">Sports fans like you</span>
        </div>

        <div class="welcome-stat-card scoreboard-stat" x-data="{ count: 0 }" x-intersect="count = 2">
          <div class="welcome-stat-icon">💰</div>
          <span class="welcome-stat-number" x-text="'$' + count + 'M+'" x-transition></span>
          <span class="welcome-stat-label">Saved on Tickets</span>
          <span class="welcome-stat-description">By our community</span>
        </div>
      </section>

      <!-- Features Section -->
      <section id="features" class="welcome-features">
        <div class="welcome-feature-card feature-card-enhanced ticket-stub">
          <div class="welcome-feature-icon">⚡</div>
          <h3 class="welcome-feature-title">Real-Time Monitoring</h3>
          <p class="welcome-feature-description">
            Get instant notifications when tickets become available for your favorite teams and events.
          </p>
        </div>

        <div class="welcome-feature-card feature-card-enhanced ticket-stub">
          <div class="welcome-feature-icon">🔒</div>
          <h3 class="welcome-feature-title">Enterprise Security</h3>
          <p class="welcome-feature-description">
            2FA authentication, role-based access control, and GDPR compliance for peace of mind.
          </p>
        </div>

        <div class="welcome-feature-card feature-card-enhanced ticket-stub">
          <div class="welcome-feature-icon">📊</div>
          <h3 class="welcome-feature-title">Price Analytics</h3>
          <p class="welcome-feature-description">
            Advanced analytics to help you find the best deals and track pricing trends.
          </p>
        </div>

        <div class="welcome-feature-card feature-card-enhanced ticket-stub">
          <div class="welcome-feature-icon">📱</div>
          <h3 class="welcome-feature-title">Multi-Platform</h3>
          <p class="welcome-feature-description">
            Monitor StubHub, Ticketmaster, SeatGeek, and 50+ other platforms from one dashboard.
          </p>
        </div>

        <div class="welcome-feature-card feature-card-enhanced ticket-stub">
          <div class="welcome-feature-icon">⚙️</div>
          <h3 class="welcome-feature-title">Smart Automation</h3>
          <p class="welcome-feature-description">
            Set custom filters, price alerts, and automated purchasing rules to never miss a deal.
          </p>
        </div>

        <div class="welcome-feature-card feature-card-enhanced ticket-stub">
          <div class="welcome-feature-icon">🎯</div>
          <h3 class="welcome-feature-title">Team-Specific Alerts</h3>
          <p class="welcome-feature-description">
            Follow your favorite teams across all sports and get notified about home and away games.
          </p>
        </div>
      </section>

      <!-- Call to Action Section -->
      <section class="welcome-cta-section celebration-particles">
        <h2 class="welcome-cta-title">Ready to Join the Game?</h2>
        <p class="welcome-cta-description">
          Start your 7-day free trial today and never miss another game.
          No credit card required, cancel anytime.
        </p>
        <div class="welcome-cta-buttons">
          <a href="{{ route('register.public') }}" class="welcome-btn welcome-btn-primary">
            Start Free Trial
          </a>
          <a href="{{ route('login') }}" class="welcome-btn welcome-btn-secondary">
            Sign In
          </a>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <footer class="welcome-footer">
      <div class="welcome-footer-content">
        <div class="welcome-footer-links">
          <a href="{{ route('legal.privacy-policy') }}" class="welcome-footer-link">Privacy Policy</a>
          <a href="{{ route('legal.terms-of-service') }}" class="welcome-footer-link">Terms of Service</a>
          <a href="{{ route('legal.disclaimer') }}" class="welcome-footer-link">Disclaimer</a>
          <a href="mailto:support@hdtickets.com" class="welcome-footer-link">Support</a>
        </div>
        <p>&copy; {{ date('Y') }} HD Tickets. All rights reserved.</p>
      </div>
    </footer>

    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
      // Welcome page stats animation
      function welcomeStats() {
        return {
          init() {
            // Initialize any additional stats functionality
          }
        }
      }

      // Smooth scrolling for anchor links
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
          e.preventDefault();
          const target = document.querySelector(this.getAttribute('href'));
          if (target) {
            target.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
          }
        });
      });

      // Add intersection observer for animations
      const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
      };

      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.animationPlayState = 'running';
          }
        });
      }, observerOptions);

      // Observe elements for animation
      document.querySelectorAll('.welcome-feature-card, .welcome-stat-card').forEach(el => {
        observer.observe(el);
      });

      // Add ripple effect to buttons
      document.querySelectorAll('.welcome-btn').forEach(button => {
        button.addEventListener('click', function(e) {
          const ripple = document.createElement('span');
          const rect = this.getBoundingClientRect();
          const size = Math.max(rect.width, rect.height);
          const x = e.clientX - rect.left - size / 2;
          const y = e.clientY - rect.top - size / 2;

          ripple.style.cssText = `
                    position: absolute;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    background: rgba(255, 255, 255, 0.3);
                    border-radius: 50%;
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    pointer-events: none;
                `;

          this.style.position = 'relative';
          this.style.overflow = 'hidden';
          this.appendChild(ripple);

          setTimeout(() => ripple.remove(), 600);
        });
      });

      // Add ripple animation
      const style = document.createElement('style');
      style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
      document.head.appendChild(style);
    </script>
  </body>

</html>
