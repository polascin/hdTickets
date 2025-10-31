<!DOCTYPE html>
<html lang="en-GB" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- SEO and Social Meta Tags -->
    <title>HD Tickets - Professional Sports Events Entry Tickets Monitoring Platform</title>
    <meta name="description" content="Comprehensive Sports Events Entry Tickets Monitoring, Scraping and Purchase System. Real-time monitoring across 40+ platforms with automated alerts and purchasing.">
    <meta name="keywords" content="sports tickets, ticket monitoring, event tickets, automated purchasing, real-time alerts, sports events, ticketmaster, stubhub, seatgeek">
    <link rel="canonical" href="{{ url('/') }}">
    
    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="HD Tickets - Professional Sports Events Entry Tickets Monitoring Platform">
    <meta property="og:description" content="Never miss your team again. Advanced monitoring, instant alerts, and automated purchasing for sports events across 40+ platforms.">
    <meta property="og:image" content="{{ asset('assets/images/hdTicketsLogo.png') }}">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="HD Tickets">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="HD Tickets - Professional Sports Events Entry Tickets Monitoring Platform">
    <meta name="twitter:description" content="Never miss your team again. Advanced monitoring, instant alerts, and automated purchasing for sports events.">
    <meta name="twitter:image" content="{{ asset('assets/images/hdTicketsLogo.png') }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/hdTicketsLogo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/hdTicketsLogo.png') }}">
    
    <!-- Critical Resource Preloads -->
    <link rel="preload" as="image" href="{{ asset('assets/images/hdTicketsLogo.png') }}" fetchpriority="high">
    @include('layouts.partials.preloads-welcome')
    
    <!-- Fonts with optimised loading -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    
    <!-- Vite Assets -->
    @vite(['resources/css/welcome.css', 'resources/js/welcome.js'])
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "HD Tickets",
        "description": "Professional Sports Events Entry Tickets Monitoring, Scraping and Purchase System",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web Browser",
        "offers": {
            "@type": "Offer",
            "price": "{{ $pricing['monthly_price'] ?? 29.99 }}",
            "priceCurrency": "{{ $pricing['currency'] ?? 'GBP' }}",
            "priceValidUntil": "{{ now()->addYear()->format('Y-m-d') }}"
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.8",
            "reviewCount": "1247"
        }
    }
    </script>
</head>

<body class="welcome-layout stadium-bg field-pattern">
    <!-- Skip to main content link for accessibility -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-white focus:text-blue-600 focus:rounded focus:shadow-lg">
        Skip to main content
    </a>

    <!-- Header -->
    <header class="welcome-header">
        <nav class="welcome-nav" role="navigation" aria-label="Main navigation">
            <a href="{{ url('/') }}" class="welcome-logo focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-600 rounded-lg" aria-label="HD Tickets home">
                <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" 
                     alt="HD Tickets logo" 
                     width="40" 
                     height="40" 
                     decoding="async" 
                     class="welcome-logo-icon">
                <span class="welcome-logo-text">HD Tickets</span>
            </a>
            
            <div class="welcome-nav-links">
                @auth
                    <a href="{{ route('dashboard') }}" class="welcome-btn welcome-btn-primary focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="welcome-btn welcome-btn-secondary focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2">Sign In</a>
                    <a href="{{ route('register') }}" class="welcome-btn welcome-btn-primary focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2">Get Started</a>
                @endauth
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main id="main-content" class="welcome-main" role="main">
        <!-- Hero Section -->
        <section class="welcome-hero stadium-lights" aria-labelledby="hero-heading">
            <div class="welcome-hero-badge" aria-hidden="true">
                <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" 
                     alt="" 
                     width="24" 
                     height="24" 
                     loading="lazy"
                     class="welcome-hero-badge-icon">
            </div>
            
            <h1 id="hero-heading" class="welcome-hero-title hero-title-enhanced">
                Never Miss Your Favourite Team Again
            </h1>
            
            <p class="welcome-hero-subtitle">
                Comprehensive Sports Events Entry Tickets Monitoring & Automated Purchasing
            </p>
            
            <p class="welcome-hero-description">
                Real-time monitoring across 40+ ticket platforms. Advanced scraping, instant alerts, 
                and automated purchasing workflows. Save up to 60% on tickets for your favourite teams and venues.
            </p>
            
            <div class="welcome-hero-cta">
                @auth
                    <a href="{{ route('dashboard') }}" class="welcome-btn welcome-btn-primary welcome-btn-lg focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2">
                        <span aria-hidden="true">🎯</span>
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="welcome-btn welcome-btn-primary welcome-btn-lg focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2">
                        <span aria-hidden="true">🚀</span>
                        Start 7-Day Free Trial
                    </a>
                    <a href="#features" class="welcome-btn welcome-btn-secondary welcome-btn-lg focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2">
                        <span aria-hidden="true">📖</span>
                        Learn More
                    </a>
                @endauth
            </div>
        </section>

        <!-- Stats Section -->
        <section class="welcome-stats" aria-labelledby="stats-heading">
            <h2 id="stats-heading" class="sr-only">Platform Statistics</h2>
            
            @if(isset($stats))
                <div class="welcome-stat-card scoreboard-stat ticket-stub">
                    <span class="welcome-stat-icon" aria-hidden="true">🎯</span>
                    <span class="welcome-stat-number" data-stat="platforms">{{ $stats['platforms'] ?? '40+' }}</span>
                    <span class="welcome-stat-label">Ticket Platforms Monitored</span>
                    <span class="welcome-stat-description">Including Ticketmaster, StubHub, SeatGeek</span>
                </div>
                
                <div class="welcome-stat-card scoreboard-stat ticket-stub">
                    <span class="welcome-stat-icon" aria-hidden="true">⚡</span>
                    <span class="welcome-stat-number" data-stat="monitoring">{{ $stats['monitoring'] ?? '24/7' }}</span>
                    <span class="welcome-stat-label">Real-time Monitoring</span>
                    <span class="welcome-stat-description">Continuous availability tracking</span>
                </div>
                
                <div class="welcome-stat-card scoreboard-stat ticket-stub">
                    <span class="welcome-stat-icon" aria-hidden="true">👥</span>
                    <span class="welcome-stat-number" data-stat="users">{{ $stats['users'] ?? '15K+' }}</span>
                    <span class="welcome-stat-label">Active Users</span>
                    <span class="welcome-stat-description">Satisfied customers worldwide</span>
                </div>
                
                <div class="welcome-stat-card scoreboard-stat ticket-stub">
                    <span class="welcome-stat-icon" aria-hidden="true">💰</span>
                    <span class="welcome-stat-number" data-stat="savings">{{ $stats['avg_savings'] ?? '35%' }}</span>
                    <span class="welcome-stat-label">Average Savings</span>
                    <span class="welcome-stat-description">Compared to market rates</span>
                </div>
            @endif
        </section>

        <!-- Core Features -->
        <section id="features" class="welcome-features" aria-labelledby="features-heading">
            <h2 id="features-heading" class="welcome-section-title">
                Everything You Need for Smart Ticket Monitoring
            </h2>
            <p class="welcome-section-subtitle">
                Our advanced platform monitors thousands of sports events tickets across all major platforms, 
                ensuring you never miss out on the best prices.
            </p>
            
            <div class="welcome-features-grid">
                <article class="welcome-feature-card feature-card-enhanced team-color-football">
                    <span class="welcome-feature-icon" aria-hidden="true">🔍</span>
                    <h3 class="welcome-feature-title">Smart Monitoring</h3>
                    <p class="welcome-feature-description">
                        Advanced scraping algorithms monitor ticket prices across 40+ platforms in real-time, 
                        ensuring you never miss a price drop for your favourite events.
                    </p>
                </article>
                
                <article class="welcome-feature-card feature-card-enhanced team-color-basketball">
                    <span class="welcome-feature-icon" aria-hidden="true">🔔</span>
                    <h3 class="welcome-feature-title">Instant Alerts</h3>
                    <p class="welcome-feature-description">
                        Get notified immediately when tickets drop to your target price. 
                        SMS, email, and push notifications available for critical events.
                    </p>
                </article>
                
                <article class="welcome-feature-card feature-card-enhanced team-color-baseball">
                    <span class="welcome-feature-icon" aria-hidden="true">🤖</span>
                    <h3 class="welcome-feature-title">Automated Purchasing</h3>
                    <p class="welcome-feature-description">
                        Set it and forget it. Our system can automatically purchase tickets 
                        when they meet your criteria with secure payment processing.
                    </p>
                </article>
                
                <article class="welcome-feature-card feature-card-enhanced team-color-hockey">
                    <span class="welcome-feature-icon" aria-hidden="true">📊</span>
                    <h3 class="welcome-feature-title">Price Analytics</h3>
                    <p class="welcome-feature-description">
                        Historical price data and trend analysis help you make informed decisions 
                        about the best time to purchase tickets.
                    </p>
                </article>
                
                <article class="welcome-feature-card feature-card-enhanced">
                    <span class="welcome-feature-icon" aria-hidden="true">🛡️</span>
                    <h3 class="welcome-feature-title">Secure & Safe</h3>
                    <p class="welcome-feature-description">
                        Bank-level encryption and PCI DSS compliant payment processing. 
                        Your data and payments are always protected.
                    </p>
                </article>
                
                <article class="welcome-feature-card feature-card-enhanced">
                    <span class="welcome-feature-icon" aria-hidden="true">⚙️</span>
                    <h3 class="welcome-feature-title">Event Management</h3>
                    <p class="welcome-feature-description">
                        Follow your favourite teams and venues. Get personalised alerts 
                        for events and matches you care about most.
                    </p>
                </article>
            </div>
        </section>

        <!-- Platform Integrations -->
        @if(isset($platform_integrations) && count($platform_integrations) > 0)
        <section class="welcome-platforms" aria-labelledby="platforms-heading">
            <h2 id="platforms-heading" class="welcome-section-title">
                Trusted Platform Integrations
            </h2>
            <p class="welcome-section-subtitle">
                Seamlessly integrated with major ticketing platforms and sports venues
            </p>
            
            <div class="welcome-platforms-grid">
                @foreach($platform_integrations as $platform)
                <article class="welcome-platform-card">
                    <div class="welcome-platform-icon" aria-hidden="true">🎫</div>
                    <h3 class="welcome-platform-name">{{ $platform['name'] }}</h3>
                    <p class="welcome-platform-description">{{ $platform['description'] }}</p>
                    <div class="welcome-platform-status">
                        <span class="status-indicator status-active">Active</span>
                    </div>
                </article>
                @endforeach
            </div>
        </section>
        @endif

        <!-- Security Features -->
        @if(isset($security_features) && count($security_features) > 0)
        <section class="welcome-security" aria-labelledby="security-heading">
            <h2 id="security-heading" class="welcome-section-title">
                Enterprise-Grade Security
            </h2>
            <p class="welcome-section-subtitle">
                Multi-layered security architecture with advanced threat protection
            </p>
            
            <div class="welcome-security-grid">
                @foreach($security_features as $feature)
                <article class="welcome-security-card">
                    <div class="welcome-security-icon" aria-hidden="true">🔒</div>
                    <h3 class="welcome-security-title">{{ $feature['title'] }}</h3>
                    <p class="welcome-security-description">{{ $feature['description'] }}</p>
                </article>
                @endforeach
            </div>
        </section>
        @endif

        <!-- Call to Action -->
        <section class="welcome-cta-section celebration-particles" aria-labelledby="cta-heading">
            <h2 id="cta-heading" class="welcome-cta-title">Ready to Save on Your Next Event?</h2>
            <p class="welcome-cta-description">
                Join thousands of fans who never overpay for sports tickets again. 
                Start your free trial today – no credit card required.
            </p>
            
            <div class="welcome-cta-buttons">
                @auth
                    <a href="{{ route('dashboard') }}" class="welcome-btn welcome-btn-primary welcome-btn-lg focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="welcome-btn welcome-btn-primary welcome-btn-lg focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2">
                        Start 7-Day Free Trial
                    </a>
                @endauth
            </div>
            
            <p class="welcome-cta-disclaimer">
                No credit card required • 7-day free trial • Cancel anytime
            </p>
        </section>
    </main>

    <!-- Footer -->
    <footer class="welcome-footer" role="contentinfo">
        <div class="welcome-footer-content">
            <div class="welcome-footer-brand">
                <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" 
                     alt="HD Tickets logo" 
                     width="32" 
                     height="32" 
                     loading="lazy"
                     class="welcome-footer-logo">
                <span class="welcome-footer-brand-text">HD Tickets</span>
            </div>
            
            <nav class="welcome-footer-links" aria-label="Footer navigation">
                <a href="{{ route('legal.privacy-policy') }}" class="welcome-footer-link focus:outline-none focus:underline">Privacy Policy</a>
                <a href="{{ route('legal.terms-of-service') }}" class="welcome-footer-link focus:outline-none focus:underline">Terms of Service</a>
                <a href="{{ route('legal.disclaimer') }}" class="welcome-footer-link focus:outline-none focus:underline">Service Disclaimer</a>
                <a href="mailto:support@hd-tickets.com" class="welcome-footer-link focus:outline-none focus:underline">Support</a>
            </nav>
            
            <div class="welcome-footer-bottom">
                <p>&copy; {{ date('Y') }} HD Tickets. All rights reserved.</p>
                <p class="welcome-footer-tagline">Professional Sports Events Entry Tickets Monitoring Platform</p>
            </div>
        </div>
    </footer>

    <!-- Minimal inline script for critical functionality only -->
    <script>
        // Reduced motion support
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        
        if (!prefersReducedMotion) {
            // Stats animation on intersection
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fade-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            // Observe all animated elements
            document.querySelectorAll('.welcome-stat-card, .welcome-feature-card, .welcome-platform-card').forEach(el => {
                observer.observe(el);
            });
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href === '#') return;
                
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: prefersReducedMotion ? 'auto' : 'smooth',
                        block: 'start'
                    });
                    // Focus management for accessibility
                    target.focus();
                }
            });
        });
    </script>
</body>
</html>
