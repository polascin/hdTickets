<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
      content="HD Tickets - Professional Sports Event Ticket Monitoring Platform with Role-Based Access, Subscription Management, and Legal Compliance">
    <meta name="keywords"
      content="sports tickets, ticket monitoring, event tickets, subscription platform, GDPR compliant, 2FA security, role-based access">
    <meta name="author" content="HD Tickets">
    <meta name="robots" content="index, follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="HD Tickets - Professional Sports Ticket Monitoring Platform">
    <meta property="og:description"
      content="Never miss your team again! Professional sports ticket monitoring with role-based access, subscription plans, and enterprise security.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('assets/images/social/og-image.png') }}">
    <meta property="og:site_name" content="HD Tickets">

    <!-- Twitter Card Meta Tags -->
    <meta property="og:image:alt" content="HD Tickets - Sports ticket monitoring platform">
    <meta name="twitter:image:alt" content="HD Tickets - Sports ticket monitoring platform">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="HD Tickets - Professional Sports Ticket Monitoring">
    <meta name="twitter:description"
      content="Monitor 50+ platforms, role-based access, GDPR compliant, 7-day free trial">
    <meta name="twitter:image" content="{{ asset('assets/images/social/twitter-card.png') }}">

    <!-- Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "HD Tickets",
      "description": "Professional Sports Event Ticket Monitoring Platform",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web Browser",
      "offers": {
        "@type": "Offer",
        "price": "29.99",
        "priceCurrency": "USD",
        "availability": "https://schema.org/InStock",
        "validFrom": "{{ date('Y-m-d') }}"
      },
      "aggregateRating": {
        "@type": "AggregateRating",
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
