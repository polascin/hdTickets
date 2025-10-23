<!DOCTYPE html>
<html lang="en" class="h-full">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description"
      content="HD Tickets - Professional Sports Event Ticket Monitoring & Automation Platform. Save up to 60% with real-time monitoring across 50+ platforms.">
    <title>HD Tickets - Smart Sports Ticket Monitoring & Automated Purchasing</title>

    <!-- SEO Meta Tags -->
    <meta name="keywords"
      content="sports tickets, ticket monitoring, event tickets, automated purchasing, real-time alerts, sports events, ticketmaster, stubhub, seatgeek">
    <meta name="author" content="HD Tickets">

    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="HD Tickets - Professional Sports Event Ticket Monitoring Platform">
    <meta property="og:description"
      content="Never miss your team again. Advanced monitoring, instant alerts, and automated purchasing for sports events.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="HD Tickets">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="HD Tickets - Smart Sports Ticket Monitoring">
    <meta name="twitter:description"
      content="Save up to 60% on sports tickets with automated monitoring and purchasing.">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/hdTicketsLogo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/hdTicketsLogo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/welcome.css', 'resources/js/welcome.js'])


    <style>
      /* Minimal page-specific overrides - main styles in welcome.css */
      body {
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
        line-height: 1.6;
        color: #1f2937;
        background-color: #ffffff;
        margin: 0;
        padding: 0;
      }
    </style>
  </head>

  <body class="antialiased">
    <!-- Header -->
    <header class="welcome-header" role="banner">
      <div class="welcome-container">
        <nav class="welcome-nav" role="navigation" aria-label="Main navigation">
          <a href="{{ url('/') }}" class="welcome-logo" aria-label="HD Tickets home">
            <div class="welcome-logo-icon">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
              </svg>
            </div>
            <span class="welcome-logo-text">HD Tickets</span>
          </a>

          <div class="welcome-nav-links">
            @auth
              <a href="{{ route('dashboard') }}" class="welcome-btn welcome-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                  stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                Dashboard
              </a>
            @else
              <a href="{{ route('login') }}" class="welcome-btn welcome-btn-secondary">Sign In</a>
              <a href="{{ route('register') }}" class="welcome-btn welcome-btn-primary">Get Started</a>
            @endauth
          </div>
        </nav>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="welcome-hero">
      <div class="welcome-container">
        <div class="welcome-hero-content">
          <h1 class="welcome-hero-title">Never Miss the Perfect Ticket</h1>
          <p class="welcome-hero-subtitle">
            Smart monitoring, instant alerts, and automated purchasing for sports events.
            Save up to 60% on your favourite teams and venues.
          </p>

          <div class="welcome-hero-buttons">
            @auth
              <a href="{{ route('dashboard') }}" class="welcome-btn welcome-btn-primary welcome-btn-lg">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                  stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                </svg>
                Go to Dashboard
              </a>
            @else
              <a href="{{ route('register') }}" class="welcome-btn welcome-btn-primary welcome-btn-lg">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                  stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                </svg>
                Start Free Trial
              </a>
              <a href="#features" class="welcome-btn welcome-btn-secondary welcome-btn-lg">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                  stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                Learn More
              </a>
            @endauth
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="welcome-stats">
      <div class="welcome-container">
        <div class="welcome-stats-grid">
          <div class="welcome-stat-card">
            <div class="welcome-stat-number">{{ $stats['platforms'] ?? '50+' }}</div>
            <div class="welcome-stat-label">Platforms Monitored</div>
          </div>
          <div class="welcome-stat-card">
            <div class="welcome-stat-number">{{ $stats['monitoring'] ?? '24/7' }}</div>
            <div class="welcome-stat-label">Real-time Monitoring</div>
          </div>
          <div class="welcome-stat-card">
            <div class="welcome-stat-number">{{ $stats['users'] ?? '10K+' }}</div>
            <div class="welcome-stat-label">Happy Customers</div>
          </div>
          <div class="welcome-stat-card">
            <div class="welcome-stat-number">{{ $stats['savings'] ?? '35%' }}</div>
            <div class="welcome-stat-label">Average Savings</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="welcome-features">
      <div class="welcome-container">
        <div class="welcome-section-header">
          <h2>Everything You Need to Score Great Deals</h2>
          <p>
            Our advanced platform monitors thousands of tickets across all major platforms,
            so you never miss out on the best prices.
          </p>
        </div>

        <div class="welcome-features-grid">
          <div class="welcome-feature-card">
            <div class="welcome-feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
              </svg>
            </div>
            <h3>Smart Monitoring</h3>
            <p>
              Advanced algorithms monitor ticket prices across 50+ platforms in real-time,
              ensuring you never miss a price drop.
            </p>
          </div>

          <div class="welcome-feature-card">
            <div class="welcome-feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
              </svg>
            </div>
            <h3>Instant Alerts</h3>
            <p>
              Get notified immediately when tickets drop to your target price.
              SMS, email, and push notifications available.
            </p>
          </div>

          <div class="welcome-feature-card">
            <div class="welcome-feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
              </svg>
            </div>
            <h3>Auto Purchase</h3>
            <p>
              Set it and forget it. Our system can automatically purchase tickets
              when they meet your criteria.
            </p>
          </div>

          <div class="welcome-feature-card">
            <div class="welcome-feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
              </svg>
            </div>
            <h3>Price Analytics</h3>
            <p>
              Historical price data and trends help you make informed decisions
              about when to buy.
            </p>
          </div>

          <div class="welcome-feature-card">
            <div class="welcome-feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
              </svg>
            </div>
            <h3>Secure & Safe</h3>
            <p>
              Bank-level encryption and secure payment processing.
              Your data and payments are always protected.
            </p>
          </div>

          <div class="welcome-feature-card">
            <div class="welcome-feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
              </svg>
            </div>
            <h3>Team Management</h3>
            <p>
              Follow your favourite teams and venues. Get personalised alerts
              for events you care about.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="welcome-cta">
      <div class="welcome-container">
        <h2>Ready to Save on Your Next Event?</h2>
        <p>
          Join thousands of fans who never overpay for tickets again.
          Start your free trial today.
        </p>

        @auth
          <a href="{{ route('dashboard') }}" class="welcome-btn welcome-btn-primary welcome-btn-lg">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
              stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
            </svg>
            Go to Dashboard
          </a>
        @else
          <a href="{{ route('register') }}" class="welcome-btn welcome-btn-primary welcome-btn-lg">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
              stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
            </svg>
            Start Free Trial
          </a>
        @endauth

        <p class="welcome-cta-note">
          No credit card required • 14-day free trial • Cancel anytime
        </p>
      </div>
    </section>

    <!-- Footer -->
    <footer class="welcome-footer" role="contentinfo">
      <div class="welcome-container">
        <div class="welcome-footer-content">
          <a href="{{ route('legal.privacy-policy') }}" class="welcome-footer-link">Privacy Policy</a>
          <a href="{{ route('legal.terms-of-service') }}" class="welcome-footer-link">Terms of Service</a>
          <a href="{{ route('legal.disclaimer') }}" class="welcome-footer-link">Service Disclaimer</a>
          <a href="mailto:support@hd-tickets.com" class="welcome-footer-link">Contact Us</a>
        </div>

        <div class="welcome-footer-bottom">
          <p>&copy; {{ date('Y') }} HD Tickets. All rights reserved.</p>
          <p class="welcome-footer-tagline">Professional Sports Event Ticket Monitoring Platform</p>
        </div>
      </div>
    </footer>

    <!-- Smooth scroll for anchor links -->
    <script>
      document.addEventListener('DOMContentLoaded', function() {
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
      });
    </script>
  </body>

</html>
