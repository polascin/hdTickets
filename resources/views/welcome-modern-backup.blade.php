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
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 800;
            font-size: 1.5rem;
            text-decoration: none;
            color: white;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: white;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background-color: #10b981;
            color: white;
        }

        .btn-primary:hover {
            background-color: #059669;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: transparent;
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .btn-secondary:hover {
            background-color: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.5);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #8b5cf6 100%);
            color: white;
            padding: 5rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" style="stop-color:rgba(255,255,255,0.1);stop-opacity:1" /><stop offset="100%" style="stop-color:rgba(255,255,255,0);stop-opacity:0" /></radialGradient></defs><ellipse cx="50" cy="10" rx="50" ry="10" fill="url(%23a)" /></svg>') repeat-x;
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.1;
        }

        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2.5rem;
            opacity: 0.95;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .hero .btn {
            padding: 1rem 2rem;
            font-size: 1rem;
        }

        /* Features Section */
        .features {
            padding: 5rem 0;
            background-color: #f9fafb;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1rem;
        }

        .section-header p {
            font-size: 1.125rem;
            color: #6b7280;
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3b82f6, #10b981);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .feature-card h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: #6b7280;
            line-height: 1.6;
        }

        /* Stats Section */
        .stats {
            padding: 4rem 0;
            background: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: #3b82f6;
            line-height: 1;
        }

        .stat-label {
            color: #6b7280;
            font-weight: 500;
            margin-top: 0.5rem;
        }

        /* CTA Section */
        .cta {
            padding: 5rem 0;
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            color: white;
            text-align: center;
        }

        .cta h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .cta p {
            font-size: 1.125rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Footer */
        .footer {
            background: #111827;
            color: #9ca3af;
            padding: 3rem 0 2rem;
            text-align: center;
        }

        .footer-content {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-link {
            color: #9ca3af;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-link:hover {
            color: white;
        }

        .footer-bottom {
            border-top: 1px solid #374151;
            padding-top: 2rem;
            font-size: 0.875rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                gap: 1rem;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.125rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .section-header h2 {
                font-size: 2rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .footer-content {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="{{ url('/') }}" class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    HD Tickets
                </a>
                
                <div class="nav-links">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="nav-link">Sign In</a>
                        <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                    @endauth
                </div>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Never Miss the Perfect Ticket</h1>
                <p>
                    Smart monitoring, instant alerts, and automated purchasing for sports events. 
                    Save up to 60% on your favourite teams and venues.
                </p>
                
                <div class="hero-buttons">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-rocket"></i>
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-primary">
                            <i class="fas fa-rocket"></i>
                            Start Free Trial
                        </a>
                        <a href="#features" class="btn btn-secondary">
                            <i class="fas fa-info-circle"></i>
                            Learn More
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div>
                    <div class="stat-number">50K+</div>
                    <div class="stat-label">Tickets Monitored</div>
                </div>
                <div>
                    <div class="stat-number">2.5K+</div>
                    <div class="stat-label">Active Events</div>
                </div>
                <div>
                    <div class="stat-number">10K+</div>
                    <div class="stat-label">Happy Customers</div>
                </div>
                <div>
                    <div class="stat-number">35%</div>
                    <div class="stat-label">Average Savings</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-header">
                <h2>Everything You Need to Score Great Deals</h2>
                <p>
                    Our advanced platform monitors thousands of tickets across all major platforms, 
                    so you never miss out on the best prices.
                </p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Smart Monitoring</h3>
                    <p>
                        Advanced algorithms monitor ticket prices across 20+ platforms in real-time, 
                        ensuring you never miss a price drop.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3>Instant Alerts</h3>
                    <p>
                        Get notified immediately when tickets drop to your target price. 
                        SMS, email, and push notifications available.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h3>Auto Purchase</h3>
                    <p>
                        Set it and forget it. Our system can automatically purchase tickets 
                        when they meet your criteria.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Price Analytics</h3>
                    <p>
                        Historical price data and trends help you make informed decisions 
                        about when to buy.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Secure & Safe</h3>
                    <p>
                        Bank-level encryption and secure payment processing. 
                        Your data and payments are always protected.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
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
    <section class="cta">
        <div class="container">
            <h2>Ready to Save on Your Next Event?</h2>
            <p>
                Join thousands of fans who never overpay for tickets again. 
                Start your free trial today.
            </p>
            
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary">
                    <i class="fas fa-rocket"></i>
                    Go to Dashboard
                </a>
            @else
                <a href="{{ route('register') }}" class="btn btn-primary">
                    <i class="fas fa-rocket"></i>
                    Start Free Trial
                </a>
            @endauth
            
            <p style="margin-top: 1rem; font-size: 0.875rem; opacity: 0.8;">
                No credit card required • 14-day free trial • Cancel anytime
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <a href="{{ route('legal.privacy-policy') }}" class="footer-link">Privacy Policy</a>
                <a href="{{ route('legal.terms-of-service') }}" class="footer-link">Terms of Service</a>
                <a href="#" class="footer-link">Contact Us</a>
                <a href="#" class="footer-link">Help Center</a>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} HD Tickets. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>