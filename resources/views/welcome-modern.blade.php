<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- SEO and Social Meta Tags -->
    <title>HD Tickets - Professional Sports Event Ticket Monitoring Platform</title>
    <meta name="description" content="HD Tickets - Professional Sports Events Entry Tickets Monitoring, Scraping and Purchase System with Advanced Security, Real-time Monitoring & Automated Purchasing across 50+ Platforms">
    <meta name="keywords" content="sports tickets, ticket monitoring, event tickets, automated purchasing, real-time alerts, sports events, ticketmaster, stubhub, seatgeek">
    
    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="HD Tickets - Professional Sports Event Ticket Monitoring Platform">
    <meta property="og:description" content="Never miss your team again. Advanced monitoring, instant alerts, and automated purchasing for sports events across 50+ platforms.">
    <meta property="og:image" content="{{ asset('assets/images/hdTicketsLogo.png') }}">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="HD Tickets">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="HD Tickets - Professional Sports Event Ticket Monitoring Platform">
    <meta name="twitter:description" content="Never miss your team again. Advanced monitoring, instant alerts, and automated purchasing for sports events.">
    <meta name="twitter:image" content="{{ asset('assets/images/hdTicketsLogo.png') }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/hdTicketsLogo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/hdTicketsLogo.png') }}">
    
    <!-- Critical Resource Preloads -->
    <link rel="preload" as="image" href="{{ asset('assets/images/hdTicketsLogo.png') }}" fetchpriority="high">
    @include('layouts.partials.preloads-welcome')
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    
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
    <!-- Header -->
    <header class="welcome-header">
        <nav class="welcome-nav" role="navigation" aria-label="Main navigation">
            <a href="{{ url('/') }}" class="welcome-logo" aria-label="HD Tickets home">
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
                    <a href="{{ route('dashboard') }}" class="welcome-btn welcome-btn-primary">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="welcome-btn welcome-btn-secondary">Sign In</a>
                    <a href="{{ route('register') }}" class="welcome-btn welcome-btn-primary">Get Started</a>
                @endauth
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="welcome-main" role="main">
        <!-- Hero Section -->
        <section class="welcome-hero stadium-lights">
            <div class="welcome-hero-badge">
                <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" 
                     alt="" 
                     aria-hidden="true"
                     width="24" 
                     height="24" 
                     loading="lazy"
                     class="welcome-hero-badge-icon">
            </div>
            
            <h1 class="welcome-hero-title hero-title-enhanced">
                Never Miss the Perfect Ticket
            </h1>
            
            <p class="welcome-hero-subtitle">
                Professional Sports Event Monitoring & Automated Purchasing
            </p>
            
            <p class="welcome-hero-description">
                Advanced monitoring, instant alerts, and automated purchasing for sports events. 
                Save up to 60% on your favourite teams and venues across 50+ platforms.
            </p>
            
            <div class="welcome-hero-cta">
                @auth
                    <a href="{{ route('dashboard') }}" class="welcome-btn welcome-btn-primary welcome-btn-lg">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="welcome-btn welcome-btn-primary welcome-btn-lg">
                        Start 7-Day Free Trial
                    </a>
                    <a href="#features" class="welcome-btn welcome-btn-secondary welcome-btn-lg">
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
                    <span class="welcome-stat-icon">🎯</span>
                    <span class="welcome-stat-number">{{ $stats['platforms'] ?? '50+' }}</span>
                    <span class="welcome-stat-label">Platforms Monitored</span>
                    <span class="welcome-stat-description">Including Ticketmaster, StubHub, SeatGeek</span>
                </div>
                
                <div class="welcome-stat-card scoreboard-stat ticket-stub">
                    <span class="welcome-stat-icon">⚡</span>
                    <span class="welcome-stat-number">{{ $stats['monitoring'] ?? '24/7' }}</span>
                    <span class="welcome-stat-label">Real-time Monitoring</span>
                    <span class="welcome-stat-description">Continuous availability tracking</span>
                </div>
                
                <div class="welcome-stat-card scoreboard-stat ticket-stub">
                    <span class="welcome-stat-icon">👥</span>
                    <span class="welcome-stat-number">{{ $stats['users'] ?? '15K+' }}</span>
                    <span class="welcome-stat-label">Active Users</span>
                    <span class="welcome-stat-description">Satisfied customers worldwide</span>
                </div>
                
                <div class="welcome-stat-card scoreboard-stat ticket-stub">
                    <span class="welcome-stat-icon">💰</span>
                    <span class="welcome-stat-number">{{ $stats['avg_savings'] ?? '35%' }}</span>
                    <span class="welcome-stat-label">Average Savings</span>
                    <span class="welcome-stat-description">Compared to market rates</span>
                </div>
            @endif
        </section>

        <!-- Core Features -->
        <section id="features" class="welcome-features" aria-labelledby="features-heading">
            <h2 id="features-heading" class="welcome-section-title">
                Everything You Need to Score Great Deals
            </h2>
            <p class="welcome-section-subtitle">
                Our advanced platform monitors thousands of tickets across all major platforms, 
                so you never miss out on the best prices.
            </p>
            
            <div class="welcome-features-grid">
                <div class="welcome-feature-card feature-card-enhanced team-color-football">
                    <span class="welcome-feature-icon">🔍</span>
                    <h3 class="welcome-feature-title">Smart Monitoring</h3>
                    <p class="welcome-feature-description">
                        Advanced algorithms monitor ticket prices across 50+ platforms in real-time, 
                        ensuring you never miss a price drop.
                    </p>
                </div>
                
                <div class="welcome-feature-card feature-card-enhanced team-color-basketball">
                    <span class="welcome-feature-icon">🔔</span>
                    <h3 class="welcome-feature-title">Instant Alerts</h3>
                    <p class="welcome-feature-description">
                        Get notified immediately when tickets drop to your target price. 
                        SMS, email, and push notifications available.
                    </p>
                </div>
                
                <div class="welcome-feature-card feature-card-enhanced team-color-baseball">
                    <span class="welcome-feature-icon">🤖</span>
                    <h3 class="welcome-feature-title">Auto Purchase</h3>
                    <p class="welcome-feature-description">
                        Set it and forget it. Our system can automatically purchase tickets 
                        when they meet your criteria.
                    </p>
                </div>
                
                <div class="welcome-feature-card feature-card-enhanced team-color-hockey">
                    <span class="welcome-feature-icon">📊</span>
                    <h3 class="welcome-feature-title">Price Analytics</h3>
                    <p class="welcome-feature-description">
                        Historical price data and trends help you make informed decisions 
                        about when to buy.
                    </p>
                </div>
                
                <div class="welcome-feature-card feature-card-enhanced">
                    <span class="welcome-feature-icon">🛡️</span>
                    <h3 class="welcome-feature-title">Secure & Safe</h3>
                    <p class="welcome-feature-description">
                        Bank-level encryption and secure payment processing. 
                        Your data and payments are always protected.
                    </p>
                </div>
                
                <div class="welcome-feature-card feature-card-enhanced">
                    <span class="welcome-feature-icon">⚙️</span>
                    <h3 class="welcome-feature-title">Team Management</h3>
                    <p class="welcome-feature-description">
                        Follow your favourite teams and venues. Get personalised alerts 
                        for events you care about.
                    </p>
                </div>
            </div>
        </section>

        <!-- Platform Integrations -->
        @if(isset($platform_integrations))
        <section class="welcome-platforms" aria-labelledby="platforms-heading">
            <h2 id="platforms-heading" class="welcome-section-title">
                Trusted Platform Integrations
            </h2>
            <p class="welcome-section-subtitle">
                Seamlessly integrated with major ticketing platforms and sports venues
            </p>
            
            <div class="welcome-platforms-grid">
                @foreach($platform_integrations as $platform)
                <div class="welcome-platform-card">
                    <div class="welcome-platform-icon">🎫</div>
                    <h3 class="welcome-platform-name">{{ $platform['name'] }}</h3>
                    <p class="welcome-platform-description">{{ $platform['description'] }}</p>
                    <div class="welcome-platform-status">
                        <span class="status-indicator status-active">Active</span>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        <!-- Security Features -->
        @if(isset($security_features))
        <section class="welcome-security" aria-labelledby="security-heading">
            <h2 id="security-heading" class="welcome-section-title">
                Enterprise-Grade Security
            </h2>
            <p class="welcome-section-subtitle">
                Multi-layered security architecture with advanced threat protection
            </p>
            
            <div class="welcome-security-grid">
                @foreach($security_features as $feature)
                <div class="welcome-security-card">
                    <div class="welcome-security-icon">🔒</div>
                    <h3 class="welcome-security-title">{{ $feature['title'] }}</h3>
                    <p class="welcome-security-description">{{ $feature['description'] }}</p>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        <!-- Technology Stack -->
        @if(isset($technology_stack))
        <section class="welcome-tech-stack" aria-labelledby="tech-heading">
            <h2 id="tech-heading" class="welcome-section-title">
                Built on Modern Technology
            </h2>
            <p class="welcome-section-subtitle">
                Enterprise-grade architecture for maximum performance and reliability
            </p>
            
            <div class="welcome-tech-grid">
                @foreach($technology_stack['backend'] as $tech)
                <div class="welcome-tech-item">
                    <div class="welcome-tech-icon">⚙️</div>
                    <div class="welcome-tech-name">{{ $tech }}</div>
                </div>
                @endforeach
                
                @foreach($technology_stack['infrastructure'] as $tech)
                <div class="welcome-tech-item">
                    <div class="welcome-tech-icon">🏗️</div>
                    <div class="welcome-tech-name">{{ $tech }}</div>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        <!-- Call to Action -->
        <section class="welcome-cta-section celebration-particles">
            <h2 class="welcome-cta-title">Ready to Save on Your Next Event?</h2>
            <p class="welcome-cta-description">
                Join thousands of fans who never overpay for tickets again. 
                Start your free trial today.
            </p>
            
            <div class="welcome-cta-buttons">
                @auth
                    <a href="{{ route('dashboard') }}" class="welcome-btn welcome-btn-primary welcome-btn-lg">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="welcome-btn welcome-btn-primary welcome-btn-lg">
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
            
            <div class="welcome-footer-links">
                <a href="{{ route('legal.privacy-policy') }}" class="welcome-footer-link">Privacy Policy</a>
                <a href="{{ route('legal.terms-of-service') }}" class="welcome-footer-link">Terms of Service</a>
                <a href="{{ route('legal.disclaimer') }}" class="welcome-footer-link">Disclaimer</a>
                <a href="mailto:support@hd-tickets.com" class="welcome-footer-link">Support</a>
            </div>
            
            <div class="welcome-footer-bottom">
                <p>&copy; {{ date('Y') }} HD Tickets. All rights reserved.</p>
                <p class="welcome-footer-tagline">Professional Sports Events Entry Tickets Monitoring Platform</p>
            </div>
        </div>
    </footer>

    <!-- Analytics & Scripts -->
    <script>
        // Stats animation on intersection
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                }
            });
        }, observerOptions);

        // Observe all animated elements
        document.querySelectorAll('.welcome-stat-card, .welcome-feature-card, .welcome-platform-card').forEach(el => {
            observer.observe(el);
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
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
    </script>
</body>
</html>