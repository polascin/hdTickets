<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="HD Tickets - Professional Sports Event Ticket Monitoring & Automation Platform. Smart alerts, real-time monitoring, and automated purchasing across all major platforms.">
    <meta name="keywords" content="sports tickets, ticket monitoring, automated purchasing, price alerts, ticketmaster, stubhub, sports events">
    <meta name="author" content="HD Tickets">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="HD Tickets - Smart Sports Ticket Monitoring">
    <meta property="og:description" content="Never miss the best ticket deals. Smart monitoring, price alerts, and automated purchasing for sports events.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('assets/images/og-banner.jpg') }}">
    
    <title>HD Tickets - Smart Sports Ticket Monitoring Platform</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Styles -->
    <style>
        :root {
            --primary-600: #2563eb;
            --primary-700: #1d4ed8;
            --primary-800: #1e40af;
            --primary-900: #1e3a8a;
            --accent-500: #10b981;
            --accent-600: #059669;
            --warning-500: #f59e0b;
            --danger-500: #ef4444;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            color: var(--gray-800);
            overflow-x: hidden;
        }

        .gradient-bg {
            background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-700) 25%, var(--primary-800) 50%, var(--primary-900) 100%);
        }

        .gradient-text {
            background: linear-gradient(135deg, var(--primary-600), var(--accent-500));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
        }

        .btn-primary {
            background-color: var(--primary-600);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-700);
            transform: translateY(-1px);
            color: white;
            text-decoration: none;
        }

        .btn-secondary {
            background-color: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
            text-decoration: none;
        }

        .btn-accent {
            background-color: var(--accent-500);
            color: white;
        }

        .btn-accent:hover {
            background-color: var(--accent-600);
            color: white;
            text-decoration: none;
        }

        .floating-animation {
            animation: floating 6s ease-in-out infinite;
        }

        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .fade-in {
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stats-counter {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary-600);
            line-height: 1;
        }

        .feature-icon {
            width: 4rem;
            height: 4rem;
            background: linear-gradient(135deg, var(--primary-600), var(--accent-500));
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .testimonial-card {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: -10px;
            left: 20px;
            font-size: 4rem;
            color: var(--primary-600);
            font-weight: bold;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .stats-counter {
                font-size: 2rem;
            }
            
            .hero h1 {
                font-size: 2.5rem !important;
            }
            
            .hero p {
                font-size: 1.125rem !important;
            }
        }

        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 50;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--gray-200);
        }

        .navbar.scrolled .navbar-brand,
        .navbar.scrolled .nav-link {
            color: var(--gray-800) !important;
        }

        .navbar.scrolled .nav-link:hover {
            color: var(--primary-600) !important;
        }

        /* Live Demo Section */
        .demo-screen {
            background: var(--gray-900);
            border-radius: 1rem;
            padding: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .demo-content {
            background: white;
            border-radius: 0.5rem;
            min-height: 400px;
            position: relative;
            overflow: hidden;
        }

        .live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--accent-500);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            animation: pulse 1s infinite;
        }

        /* Pricing Cards */
        .pricing-card {
            background: white;
            border: 2px solid var(--gray-200);
            border-radius: 1rem;
            padding: 2rem;
            position: relative;
            transition: all 0.3s ease;
        }

        .pricing-card:hover {
            border-color: var(--primary-600);
            transform: translateY(-4px);
        }

        .pricing-card.featured {
            border-color: var(--primary-600);
            background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-700) 100%);
            color: white;
            transform: scale(1.05);
        }

        .pricing-badge {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--accent-500);
            color: white;
            padding: 0.25rem 1rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* FAQ Section */
        .faq-item {
            border-bottom: 1px solid var(--gray-200);
            padding: 1.5rem 0;
        }

        .faq-question {
            cursor: pointer;
            font-weight: 600;
            color: var(--gray-800);
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .faq-answer {
            color: var(--gray-600);
            margin-top: 1rem;
            line-height: 1.7;
        }

        /* Footer */
        .footer {
            background: var(--gray-900);
            color: white;
        }

        .footer-link {
            color: var(--gray-400);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-link:hover {
            color: white;
            text-decoration: none;
        }
    </style>
</head>

<body x-data="{ 
    mobileMenuOpen: false, 
    scrolled: false,
    stats: {
        totalTickets: 0,
        activeEvents: 0,
        satisfiedCustomers: 0,
        avgSavings: 0
    }
}" 
x-init="
    window.addEventListener('scroll', () => scrolled = window.scrollY > 50);
    // Animate counters
    setTimeout(() => {
        stats.totalTickets = {{ $stats['total_tickets'] ?? 50000 }};
        stats.activeEvents = {{ $stats['active_events'] ?? 2500 }};
        stats.satisfiedCustomers = {{ $stats['satisfied_customers'] ?? 10000 }};
        stats.avgSavings = {{ $stats['avg_savings'] ?? 35 }};
    }, 500);
">

    <!-- Navigation -->
    <nav class="navbar" :class="{ 'scrolled': scrolled }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <a href="{{ url('/') }}" class="navbar-brand flex items-center space-x-3 text-white font-bold text-xl">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <span>HD Tickets</span>
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="nav-link text-white hover:text-blue-200 transition-colors">Features</a>
                    <a href="#pricing" class="nav-link text-white hover:text-blue-200 transition-colors">Pricing</a>
                    <a href="#demo" class="nav-link text-white hover:text-blue-200 transition-colors">Live Demo</a>
                    <a href="#testimonials" class="nav-link text-white hover:text-blue-200 transition-colors">Reviews</a>
                    <a href="#faq" class="nav-link text-white hover:text-blue-200 transition-colors">FAQ</a>
                </div>

                <!-- CTA Buttons -->
                <div class="hidden md:flex items-center space-x-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-accent">
                            <i class="fas fa-tachometer-alt mr-2"></i>
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-secondary">Sign In</a>
                        <a href="{{ route('register') }}" class="btn btn-accent">Start Free Trial</a>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-white">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div x-show="mobileMenuOpen" x-transition class="md:hidden bg-white rounded-lg shadow-lg mt-2 p-4">
                <div class="flex flex-col space-y-4">
                    <a href="#features" class="text-gray-800 hover:text-blue-600">Features</a>
                    <a href="#pricing" class="text-gray-800 hover:text-blue-600">Pricing</a>
                    <a href="#demo" class="text-gray-800 hover:text-blue-600">Live Demo</a>
                    <a href="#testimonials" class="text-gray-800 hover:text-blue-600">Reviews</a>
                    <a href="#faq" class="text-gray-800 hover:text-blue-600">FAQ</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-accent">Dashboard</a>
                    @else
                        <div class="flex flex-col space-y-2 pt-4 border-t">
                            <a href="{{ route('login') }}" class="btn btn-secondary">Sign In</a>
                            <a href="{{ route('register') }}" class="btn btn-accent">Start Free Trial</a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-bg min-h-screen flex items-center relative overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-white opacity-10 rounded-full floating-animation"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-white opacity-5 rounded-full floating-animation" style="animation-delay: -3s;"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Content -->
                <div class="text-white fade-in">
                    <div class="inline-flex items-center bg-white bg-opacity-20 rounded-full px-4 py-2 mb-6">
                        <span class="live-indicator">
                            <span class="live-dot"></span>
                            LIVE
                        </span>
                        <span class="ml-2 text-sm">Real-time ticket monitoring</span>
                    </div>

                    <h1 class="text-5xl lg:text-6xl font-black mb-6 leading-tight">
                        Never Miss the 
                        <span class="gradient-text bg-gradient-to-r from-green-400 to-yellow-400 bg-clip-text text-transparent">
                            Perfect Ticket
                        </span>
                    </h1>

                    <p class="text-xl lg:text-2xl mb-8 text-blue-100 leading-relaxed">
                        Smart monitoring, instant alerts, and automated purchasing for sports events across all major platforms. 
                        <strong>Save up to 60%</strong> on your favourite events.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 mb-8">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-accent text-lg px-8 py-4">
                                <i class="fas fa-rocket mr-2"></i>
                                Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="btn btn-accent text-lg px-8 py-4">
                                <i class="fas fa-rocket mr-2"></i>
                                Start Free Trial
                            </a>
                            <a href="#demo" class="btn btn-secondary text-lg px-8 py-4">
                                <i class="fas fa-play mr-2"></i>
                                Watch Demo
                            </a>
                        @endauth
                    </div>
                    
                    @guest
                        <!-- Quick OAuth Access -->
                        <div class="mb-12">
                            <p class="text-blue-200 text-sm mb-4 text-center">Or get started instantly with</p>
                            <div class="flex justify-center">
                                @php
                                    $oauthService = app('App\Services\OAuthUserService');
                                    $providers = $oauthService->getSupportedProviders();
                                @endphp
                                
                                @foreach($providers as $provider => $config)
                                    @if($config['enabled'])
                                        <a href="{{ route('oauth.redirect', ['provider' => $provider]) }}"
                                           class="inline-flex items-center justify-center px-6 py-3 bg-white bg-opacity-20 hover:bg-opacity-30 border border-white border-opacity-30 rounded-xl text-white font-medium transition-all duration-200 transform hover:scale-105 backdrop-blur-sm"
                                           aria-label="Sign up with {{ $config['name'] }}">
                                            
                                            @if($provider === 'google')
                                                <!-- Google Icon -->
                                                <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
                                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                                </svg>
                                            @else
                                                <i class="{{ $config['icon'] }} mr-3"></i>
                                            @endif
                                            
                                            <span>Continue with {{ $config['name'] }}</span>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endguest

                    <!-- Trust Indicators -->
                    <div class="flex flex-wrap items-center gap-8 text-blue-200">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-shield-alt text-green-400"></i>
                            <span class="text-sm">Secure & Private</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-clock text-yellow-400"></i>
                            <span class="text-sm">24/7 Monitoring</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-users text-blue-300"></i>
                            <span class="text-sm">10,000+ Users</span>
                        </div>
                    </div>
                </div>

                <!-- Hero Visual -->
                <div class="relative fade-in" style="animation-delay: 0.3s;">
                    <div class="demo-screen floating-animation">
                        <div class="demo-content relative">
                            <!-- Mock Dashboard UI -->
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-lg font-semibold text-gray-800">Live Ticket Monitoring</h3>
                                    <div class="flex items-center gap-2 text-green-600">
                                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                        <span class="text-sm font-medium">Active</span>
                                    </div>
                                </div>

                                <!-- Sample Ticket Cards -->
                                <div class="space-y-4">
                                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                        <div class="flex justify-between items-start mb-2">
                                            <h4 class="font-medium text-gray-900">Man United vs Liverpool</h4>
                                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded">Hot</span>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-3">Old Trafford • Section 101</p>
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center gap-2">
                                                <span class="text-lg font-bold text-green-600">£120</span>
                                                <span class="text-sm text-gray-500 line-through">£180</span>
                                            </div>
                                            <button class="bg-blue-600 text-white text-xs px-3 py-1 rounded">Buy Now</button>
                                        </div>
                                    </div>

                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex justify-between items-start mb-2">
                                            <h4 class="font-medium text-gray-900">Arsenal vs Chelsea</h4>
                                            <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-1 rounded">Price Drop</span>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-3">Emirates Stadium • Section 24</p>
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center gap-2">
                                                <span class="text-lg font-bold text-green-600">£85</span>
                                                <span class="text-sm text-gray-500 line-through">£110</span>
                                            </div>
                                            <button class="bg-blue-600 text-white text-xs px-3 py-1 rounded">Buy Now</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="stats-counter" x-text="stats.totalTickets.toLocaleString()">0</div>
                    <p class="text-gray-600 mt-2">Tickets Monitored</p>
                </div>
                <div class="text-center">
                    <div class="stats-counter" x-text="stats.activeEvents.toLocaleString()">0</div>
                    <p class="text-gray-600 mt-2">Active Events</p>
                </div>
                <div class="text-center">
                    <div class="stats-counter" x-text="stats.satisfiedCustomers.toLocaleString()">0</div>
                    <p class="text-gray-600 mt-2">Happy Customers</p>
                </div>
                <div class="text-center">
                    <div class="stats-counter" x-text="stats.avgSavings + '%'">0%</div>
                    <p class="text-gray-600 mt-2">Average Savings</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                    Everything You Need to 
                    <span class="gradient-text">Score Great Deals</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Our advanced platform monitors thousands of tickets across all major platforms, 
                    so you never miss out on the best prices.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="card p-8 text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Smart Monitoring</h3>
                    <p class="text-gray-600">
                        Advanced algorithms monitor ticket prices across 20+ platforms in real-time, 
                        ensuring you never miss a price drop.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="card p-8 text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Instant Alerts</h3>
                    <p class="text-gray-600">
                        Get notified immediately when tickets drop to your target price. 
                        SMS, email, and push notifications available.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="card p-8 text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Auto Purchase</h3>
                    <p class="text-gray-600">
                        Set it and forget it. Our system can automatically purchase tickets 
                        when they meet your criteria.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="card p-8 text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Price Analytics</h3>
                    <p class="text-gray-600">
                        Historical price data and trends help you make informed decisions 
                        about when to buy.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="card p-8 text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Secure & Safe</h3>
                    <p class="text-gray-600">
                        Bank-level encryption and secure payment processing. 
                        Your data and payments are always protected.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="card p-8 text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Team Management</h3>
                    <p class="text-gray-600">
                        Follow your favourite teams and venues. Get personalized alerts 
                        for events you care about.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Live Demo Section -->
    <section id="demo" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                    See It In Action
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Watch our platform monitor and alert on real ticket prices in real-time.
                </p>
            </div>

            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gray-800 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    </div>
                    <div class="flex items-center text-white text-sm">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse mr-2"></div>
                        Live Monitoring Active
                    </div>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Live Tickets -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-4">Current Opportunities</h3>
                            <div class="space-y-4">
                                @forelse($featured_tickets ?? [] as $ticket)
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 transition-colors">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-medium text-gray-900">{{ $ticket['event_name'] }}</h4>
                                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded">
                                            {{ $ticket['discount'] }}% OFF
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-3">{{ $ticket['venue'] }} • {{ $ticket['section'] }}</p>
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-2">
                                            <span class="text-lg font-bold text-green-600">£{{ $ticket['current_price'] }}</span>
                                            <span class="text-sm text-gray-500 line-through">£{{ $ticket['original_price'] }}</span>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $ticket['platform'] }}</span>
                                    </div>
                                </div>
                                @empty
                                <!-- Default Demo Data -->
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 transition-colors">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-medium text-gray-900">Liverpool vs Manchester City</h4>
                                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded">25% OFF</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-3">Anfield Stadium • Kop End</p>
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-2">
                                            <span class="text-lg font-bold text-green-600">£150</span>
                                            <span class="text-sm text-gray-500 line-through">£200</span>
                                        </div>
                                        <span class="text-xs text-gray-500">StubHub</span>
                                    </div>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Price Chart -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-4">Price Trends</h3>
                            <div class="bg-gray-100 rounded-lg h-64 flex items-center justify-center">
                                <div class="text-center text-gray-500">
                                    <i class="fas fa-chart-line text-3xl mb-2"></i>
                                    <p>Interactive price charts show<br>historical trends and predictions</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                    What Our Users Say
                </h2>
                <p class="text-xl text-gray-600">
                    Join thousands of satisfied customers saving money on sports tickets
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="testimonial-card">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-700 mb-4">
                        HD Tickets saved me over £300 on Champions League final tickets. 
                        The alerts are instant and the platform is so easy to use.
                    </p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold mr-4">
                            JD
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">James Davidson</h4>
                            <p class="text-gray-600 text-sm">Manchester United Fan</p>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-700 mb-4">
                        As a season ticket holder for Arsenal, I use HD Tickets to find 
                        away game tickets. It's found me deals I never would have seen.
                    </p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center text-white font-bold mr-4">
                            ST
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Sarah Thompson</h4>
                            <p class="text-gray-600 text-sm">Arsenal Season Ticket Holder</p>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-700 mb-4">
                        The automated purchasing feature is a game-changer. 
                        I got Wimbledon final tickets while I was sleeping!
                    </p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center text-white font-bold mr-4">
                            MR
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Mike Roberts</h4>
                            <p class="text-gray-600 text-sm">Tennis Enthusiast</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                    Simple, Transparent Pricing
                </h2>
                <p class="text-xl text-gray-600">
                    Choose the plan that's right for you. No hidden fees, cancel anytime.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Free Plan -->
                <div class="pricing-card">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Free</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gray-900">£0</span>
                        <span class="text-gray-600">/month</span>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Monitor up to 5 events</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Email alerts</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Basic price history</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-times text-gray-400 mr-3"></i>
                            <span class="text-gray-400">SMS alerts</span>
                        </li>
                    </ul>
                    <a href="{{ route('register') }}" class="w-full btn btn-secondary text-center">
                        Get Started Free
                    </a>
                </div>

                <!-- Pro Plan -->
                <div class="pricing-card featured">
                    <div class="pricing-badge">Most Popular</div>
                    <h3 class="text-2xl font-bold mb-2">Pro</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-bold">£19</span>
                        <span class="opacity-90">/month</span>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-400 mr-3"></i>
                            <span>Monitor unlimited events</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-400 mr-3"></i>
                            <span>SMS & push alerts</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-400 mr-3"></i>
                            <span>Advanced analytics</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-400 mr-3"></i>
                            <span>Auto-purchasing</span>
                        </li>
                    </ul>
                    <a href="{{ route('register') }}" class="w-full btn btn-accent text-center">
                        Start Pro Trial
                    </a>
                </div>

                <!-- Enterprise Plan -->
                <div class="pricing-card">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Enterprise</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gray-900">£49</span>
                        <span class="text-gray-600">/month</span>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Everything in Pro</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Priority support</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>API access</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Custom integrations</span>
                        </li>
                    </ul>
                    <a href="{{ route('register') }}" class="w-full btn btn-primary text-center">
                        Contact Sales
                    </a>
                </div>
            </div>

            <div class="text-center mt-12">
                <p class="text-gray-600">
                    All plans include a 14-day free trial. No credit card required.
                    <a href="#" class="text-blue-600 hover:underline">Money back guarantee</a>
                </p>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-20" x-data="{ openFaq: null }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                    Frequently Asked Questions
                </h2>
                <p class="text-xl text-gray-600">
                    Everything you need to know about HD Tickets
                </p>
            </div>

            <div class="space-y-1">
                <!-- FAQ Item 1 -->
                <div class="faq-item">
                    <div class="faq-question flex justify-between items-center" @click="openFaq = openFaq === 1 ? null : 1">
                        <span>How does HD Tickets work?</span>
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': openFaq === 1 }"></i>
                    </div>
                    <div x-show="openFaq === 1" x-transition class="faq-answer">
                        HD Tickets monitors ticket prices across major platforms like Ticketmaster, StubHub, SeatGeek, and more. 
                        When prices drop to your target, you'll get instant notifications. Our advanced algorithms check thousands 
                        of listings every minute to ensure you never miss a deal.
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="faq-item">
                    <div class="faq-question flex justify-between items-center" @click="openFaq = openFaq === 2 ? null : 2">
                        <span>Is automated purchasing safe and legal?</span>
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': openFaq === 2 }"></i>
                    </div>
                    <div x-show="openFaq === 2" x-transition class="faq-answer">
                        Yes, absolutely. We only purchase tickets from official platforms and authorized resellers. 
                        All transactions are processed securely using the platform's own payment system. 
                        We comply with all platform terms and use ethical practices.
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="faq-item">
                    <div class="faq-question flex justify-between items-center" @click="openFaq = openFaq === 3 ? null : 3">
                        <span>What platforms do you monitor?</span>
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': openFaq === 3 }"></i>
                    </div>
                    <div x-show="openFaq === 3" x-transition class="faq-answer">
                        We monitor over 20 major platforms including Ticketmaster, StubHub, SeatGeek, Viagogo, 
                        Vivid Seats, TickPick, and many more. We're constantly adding new platforms to ensure 
                        you have access to the best deals from every source.
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="faq-item">
                    <div class="faq-question flex justify-between items-center" @click="openFaq = openFaq === 4 ? null : 4">
                        <span>How much can I save with HD Tickets?</span>
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': openFaq === 4 }"></i>
                    </div>
                    <div x-show="openFaq === 4" x-transition class="faq-answer">
                        Our users save an average of 35% on ticket purchases. Savings can range from 10% to 70% 
                        depending on the event, timing, and market conditions. The platform pays for itself 
                        with just one or two successful purchases.
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div class="faq-item">
                    <div class="faq-question flex justify-between items-center" @click="openFaq = openFaq === 5 ? null : 5">
                        <span>Can I cancel my subscription anytime?</span>
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': openFaq === 5 }"></i>
                    </div>
                    <div x-show="openFaq === 5" x-transition class="faq-answer">
                        Yes, you can cancel your subscription at any time with no cancellation fees. 
                        You'll continue to have access to all features until the end of your current billing period. 
                        We also offer a 14-day money-back guarantee if you're not satisfied.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 gradient-bg">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl lg:text-5xl font-bold text-white mb-6">
                Ready to Save on Your Next Event?
            </h2>
            <p class="text-xl text-blue-100 mb-8">
                Join thousands of fans who never overpay for tickets again.
                Start your free trial today.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-accent text-lg px-8 py-4">
                        <i class="fas fa-rocket mr-2"></i>
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-accent text-lg px-8 py-4">
                        <i class="fas fa-rocket mr-2"></i>
                        Start Free Trial
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-secondary text-lg px-8 py-4">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Sign In
                    </a>
                @endauth
            </div>
            <p class="text-blue-200 text-sm mt-4">
                No credit card required • 14-day free trial • Cancel anytime
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-green-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-ticket-alt text-white"></i>
                        </div>
                        <span class="text-2xl font-bold text-white">HD Tickets</span>
                    </div>
                    <p class="text-gray-400 mb-6 max-w-md">
                        The smart way to buy sports tickets. Never overpay again with our 
                        advanced monitoring and automated purchasing platform.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-linkedin text-xl"></i>
                        </a>
                    </div>
                </div>

                <!-- Product -->
                <div>
                    <h3 class="text-white font-semibold mb-4">Product</h3>
                    <ul class="space-y-2">
                        <li><a href="#features" class="footer-link">Features</a></li>
                        <li><a href="#pricing" class="footer-link">Pricing</a></li>
                        <li><a href="#demo" class="footer-link">Demo</a></li>
                        <li><a href="{{ route('register') }}" class="footer-link">Free Trial</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h3 class="text-white font-semibold mb-4">Support</h3>
                    <ul class="space-y-2">
                        <li><a href="#faq" class="footer-link">FAQ</a></li>
                        <li><a href="#" class="footer-link">Help Center</a></li>
                        <li><a href="#" class="footer-link">Contact Us</a></li>
                        <li><a href="{{ route('legal.terms-of-service') }}" class="footer-link">Terms of Service</a></li>
                        <li><a href="{{ route('legal.privacy-policy') }}" class="footer-link">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-12 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-400 text-sm">
                        © {{ date('Y') }} HD Tickets. All rights reserved.
                    </p>
                    <div class="flex items-center space-x-6 mt-4 md:mt-0">
                        <div class="flex items-center space-x-2 text-gray-400 text-sm">
                            <i class="fas fa-shield-alt text-green-500"></i>
                            <span>Secure & Private</span>
                        </div>
                        <div class="flex items-center space-x-2 text-gray-400 text-sm">
                            <i class="fas fa-clock text-blue-500"></i>
                            <span>24/7 Support</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>