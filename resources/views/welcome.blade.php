<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="description" content="HD Tickets: Professional sports event ticket monitoring platform with subscription-based access, role-based permissions, automated purchasing, and legal compliance. Track prices across 50+ platforms with 2FA security.">
    <meta name="keywords" content="sports tickets monitoring, ticket price tracking, automated ticket purchasing, sports events, subscription ticket service, role-based access, GDPR compliant ticketing, 2FA security, professional ticket monitoring, real-time alerts, ticket scraping, sports platforms">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="theme-color" content="#2563eb">
    <meta name="color-scheme" content="light dark">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="HD Tickets">
    <meta name="generator" content="Laravel {{ app()->version() }}">
    <meta name="revisit-after" content="7 days">
    <meta name="rating" content="general">
    <meta name="distribution" content="global">
    <meta name="language" content="en">
    <meta name="geo.region" content="US">
    <meta name="geo.placename" content="United States">
    <link rel="canonical" href="{{ url('/') }}">
    <link rel="alternate" hreflang="en" href="{{ url('/') }}">
    <link rel="alternate" hreflang="x-default" href="{{ url('/') }}">
    
    <!-- PWA Meta Tags -->
    <meta name="application-name" content="HD Tickets">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="HD Tickets">
    <meta name="mobile-web-app-capable" content="yes">
    
    <!-- Icons -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/hdTicketsLogo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="HD Tickets">
    <meta property="og:title" content="HD Tickets - Professional Sports Ticket Monitoring Platform">
    <meta property="og:description" content="Professional sports event ticket monitoring with subscription-based access, role-based permissions, automated purchasing, and GDPR compliance. Track prices across 50+ platforms with enterprise-grade security.">
    <meta property="og:image" content="{{ asset('assets/images/hdTicketsLogo.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="HD Tickets - Professional Sports Ticket Monitoring Platform Logo">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:locale" content="en_US">
    <meta property="og:updated_time" content="{{ now()->toISOString() }}">
    <meta property="article:publisher" content="HD Tickets">
    <meta property="business:contact_data:street_address" content="Professional Sports Monitoring Services">
    <meta property="business:contact_data:locality" content="United States">
    <meta property="business:contact_data:country_name" content="USA">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@hdtickets">
    <meta name="twitter:creator" content="@hdtickets">
    <meta name="twitter:title" content="HD Tickets - Professional Sports Ticket Monitoring Platform">
    <meta name="twitter:description" content="Professional sports ticket monitoring with subscription-based access, role-based permissions, and automated purchasing. GDPR compliant with enterprise security.">
    <meta name="twitter:image" content="{{ asset('assets/images/hdTicketsLogo.png') }}">
    <meta name="twitter:image:alt" content="HD Tickets Professional Monitoring Platform">
    <meta name="twitter:domain" content="{{ parse_url(url('/'), PHP_URL_HOST) }}">
    <meta name="twitter:url" content="{{ url('/') }}">
    
    <!-- Additional Social Meta Tags -->
    <meta property="fb:app_id" content="">
    <meta name="pinterest-rich-pin" content="true">
    <meta name="linkedin:owner" content="HD Tickets">
    <meta property="al:web:url" content="{{ url('/') }}">
    <meta property="article:author" content="HD Tickets">
    <meta property="article:section" content="Sports Technology">
    <meta property="article:tag" content="Sports Tickets, Monitoring, Automation, GDPR Compliance">
    
    <!-- Structured Data -->
    <x-seo.structured-data type="homepage" />
    
    <!-- Analytics Integration -->
    <x-seo.analytics />
    
    <!-- Performance Optimization -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//www.google-analytics.com">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preload" href="{{ asset('assets/css/app.css') }}" as="style">
    <link rel="preload" href="{{ asset('assets/js/app.js') }}" as="script">
    <link rel="preload" href="{{ asset('assets/images/hdTicketsLogo.png') }}" as="image" type="image/png">
    
    <!-- Resource Hints -->
    <link rel="modulepreload" href="{{ asset('assets/js/modules/analytics.js') }}" crossorigin>
    <link rel="prefetch" href="{{ route('register') }}">
    <link rel="prefetch" href="{{ route('login') }}">
    
    <title>HD Tickets - Sports Ticket Monitoring</title>
    
    <!-- Preconnect to optimize font loading -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preload" href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700&display=swap"></noscript>
    
    <!-- Preload critical assets -->
    <link rel="preload" href="{{ asset('assets/images/hdTicketsLogo.webp') }}" as="image" type="image/webp">
    <link rel="preload" href="{{ asset('assets/images/hdTicketsLogo.png') }}" as="image" type="image/png">
    
    <!-- Compiled CSS and JavaScript -->
    @vite(['resources/css/app.css', 'resources/css/welcome.css', 'resources/js/app.js', 'resources/js/welcome.js'])
    
    <!-- Fallback CSS for better reliability -->
    <style>
        /* CSS Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #ffffff;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 35%, #8b5cf6 70%, #1e40af 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        /* Navigation Styles */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            margin-bottom: 1.5rem;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: #60a5fa;
        }
        
        .logo img {
            width: 40px;
            height: 40px;
            border-radius: 0.5rem;
            object-fit: contain;
        }
        
        .nav-button {
            background: #2563eb;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-block;
        }
        
        .nav-button:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        
        .nav-button.secondary, .btn-secondary {
            background: #059669;
            margin-left: 0.5rem;
        }
        
        .nav-button.secondary:hover, .btn-secondary:hover {
            background: #047857;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }
        
        /* Main Content - Compact Layout */
        .hero-section {
            text-align: center;
            padding: 1.5rem;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(139, 92, 246, 0.1));
            border-radius: 1rem;
            margin-bottom: 1rem;
        }
        
        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .hero-logo {
            width: 120px;
            height: 120px;
            margin: 0 auto 1rem;
            display: block;
            border-radius: 1rem;
            object-fit: contain;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }
        
        .hero-logo:hover {
            transform: scale(1.05);
        }
        
        .hero-title {
            font-size: 2.25rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
            background: linear-gradient(45deg, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.1;
            text-shadow: 
                0 4px 8px rgba(0, 0, 0, 0.5),
                0 2px 4px rgba(37, 99, 235, 0.3),
                0 8px 16px rgba(139, 92, 246, 0.2);
        }
        
        .hero-subtitle {
            font-size: 1rem;
            color: #60a5fa;
            margin-bottom: 0.75rem;
        }
        
        .hero-description {
            font-size: 0.875rem;
            color: #d1d5db;
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.5;
        }
        
        /* Stats Bar - Horizontal Inline Display */
        .stats-bar {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 2rem;
            height: 80px;
            background: rgba(31, 41, 55, 0.4);
            border-radius: 0.75rem;
            margin: 1rem 0;
            padding: 0 1rem;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(75, 85, 99, 0.2);
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-align: left;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }
        
        .stat-label {
            font-size: 0.75rem;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .green { color: #10b981; }
        .blue { color: #3b82f6; }
        .purple { color: #8b5cf6; }
        
        /* Features Section - Optimized Horizontal Cards */
        .features-section {
            margin: 1.5rem 0;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .feature-card {
            background: rgba(31, 41, 55, 0.4);
            backdrop-filter: blur(8px);
            padding: 1rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(75, 85, 99, 0.2);
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .feature-card:hover {
            transform: translateY(-4px);
            background: rgba(31, 41, 55, 0.7);
            border-color: rgba(96, 165, 250, 0.4);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #10b981, #f59e0b);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .feature-card:hover::before {
            opacity: 1;
        }
        
        .feature-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .feature-title {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
            color: #f9fafb;
            line-height: 1.2;
        }
        
        .feature-description {
            font-size: 0.75rem;
            color: #9ca3af;
            line-height: 1.4;
        }
        
        /* Icon colors */
        .icon-monitoring { color: #3b82f6; }
        .icon-pricing { color: #10b981; }
        .icon-coverage { color: #8b5cf6; }
        .icon-mobile { color: #f59e0b; }
        
        /* Welcome Message */
        .welcome-message {
            background: linear-gradient(135deg, #059669, #047857);
            padding: 1rem;
            border-radius: 0.5rem;
            margin: 1rem 0;
            text-align: center;
        }
        
        /* CTA Section */
        .cta-section {
            text-align: center;
            padding: 1rem 0;
            margin-top: 1rem;
        }
        
        .cta-button {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }
        
        .registration-notice {
            background: rgba(31, 41, 55, 0.4);
            border: 1px solid rgba(75, 85, 99, 0.2);
            padding: 1rem;
            border-radius: 0.5rem;
            max-width: 350px;
            margin: 1rem auto 0;
            text-align: center;
        }
        
        .registration-notice p {
            font-size: 0.875rem;
            color: #9ca3af;
            margin: 0;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-section {
                padding: 1rem;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .feature-card {
                padding: 1.25rem;
            }
            
            .feature-icon {
                font-size: 2rem;
                margin-bottom: 0.75rem;
            }
            
            .feature-title {
                font-size: 1rem;
                margin-bottom: 0.5rem;
            }
            
            .feature-description {
                font-size: 0.875rem;
            }
            
            .stats-bar {
                gap: 1rem;
                height: 70px;
                flex-wrap: wrap;
                justify-content: space-around;
            }
            
            .stat-item {
                gap: 0.25rem;
            }
            
            .stat-number {
                font-size: 1.25rem;
            }
            
            .container {
                padding: 0 1rem;
            }
            
            .navbar {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }
        
        /* Loading States */
        .loading {
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Error States */
        .error-fallback {
            background: #fecaca;
            color: #991b1b;
            padding: 1rem;
            border-radius: 0.5rem;
            margin: 1rem 0;
            text-align: center;
        }
    </style>
</head>
<body class="stadium-bg field-pattern min-h-screen" 
      x-data="welcomePage" 
      x-init="init()" 
      :class="{ 'dark': darkMode }"
      x-cloak>
    
    <!-- Skip Navigation Link for Accessibility -->
    <a href="#main-content" 
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-primary-600 text-white px-4 py-2 rounded-md z-50"
       role="button"
       aria-label="Skip to main content">
        Skip to main content
    </a>
    
    <!-- Theme Switcher -->
    <div x-data="themeSwitcher" class="fixed top-4 right-4 z-40">
        <button @click="toggle()" 
                :disabled="isTransitioning"
                class="p-3 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white hover:bg-white/20 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
                :aria-label="themeLabel"
                role="button">
            <span x-text="themeIcon" class="text-lg" aria-hidden="true"></span>
        </button>
    </div>
    
    <!-- PWA Install Button -->
    <div class="fixed top-4 right-20 z-40">
        <button id="install-app-btn" 
                style="display: none;"
                class="p-3 rounded-full bg-primary-600/80 backdrop-blur-md border border-primary-500/30 text-white hover:bg-primary-700/80 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
                aria-label="Install HD Tickets App"
                role="button">
            <span class="text-lg" aria-hidden="true">📱</span>
        </button>
    </div>
    
    <!-- Notification System -->
    <div x-data="notificationSystem" class="fixed top-4 left-4 z-50 space-y-2" role="region" aria-label="Notifications">
        <template x-for="notification in notifications" :key="notification.id">
            <div x-show="notification.visible"
                 x-transition:enter="transform transition ease-out duration-300"
                 x-transition:enter-start="translate-x-full opacity-0"
                 x-transition:enter-end="translate-x-0 opacity-100"
                 x-transition:leave="transform transition ease-in duration-300"
                 x-transition:leave-start="translate-x-0 opacity-100"
                 x-transition:leave-end="translate-x-full opacity-0"
                 :class="getClasses(notification.type)"
                 role="alert"
                 :aria-live="notification.type === 'error' ? 'assertive' : 'polite'">
                
                <div class="flex items-start">
                    <span x-text="getIcon(notification.type)" class="mr-2 text-lg" aria-hidden="true"></span>
                    <div class="flex-1">
                        <p x-text="notification.message" class="text-sm font-medium"></p>
                        <div class="w-full bg-black/20 rounded-full h-1 mt-2" x-show="notification.duration > 0">
                            <div class="bg-current h-1 rounded-full transition-all duration-100" 
                                 :style="`width: ${notification.progress}%`"></div>
                        </div>
                    </div>
                    <button @click="remove(notification.id)" 
                            class="ml-2 text-current hover:opacity-70 focus:outline-none focus:ring-2 focus:ring-current"
                            aria-label="Close notification">
                        ✕
                    </button>
                </div>
            </div>
        </template>
    </div>
    
    <!-- Loading Overlay -->
    <div x-show="isLoading" 
         x-transition:leave="transition-opacity duration-500"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-primary-900 flex items-center justify-center z-50" 
         role="status" 
         aria-label="Loading HD Tickets">
        <div class="text-center text-white">
            <div class="animate-spin w-16 h-16 border-4 border-white/30 border-t-white rounded-full mx-auto mb-4" aria-hidden="true"></div>
            <p class="text-xl font-semibold">Loading HD Tickets...</p>
            <p class="text-primary-200 mt-2">Preparing your sports experience</p>
        </div>
    </div>
    
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Navigation -->
        <nav class="flex items-center justify-between py-6" role="navigation" aria-label="Main navigation">
            <div class="flex items-center space-x-3">
                <picture>
                    <source srcset="{{ asset('assets/images/hdTicketsLogo.webp') }}" type="image/webp">
                    <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" 
                         alt="HD Tickets Logo" 
                         class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg object-contain stadium-lights"
                         loading="eager"
                         width="48" height="48">
                </picture>
                <span class="text-xl sm:text-2xl font-bold text-white">HD Tickets</span>
            </div>
            
            <div class="flex items-center space-x-2">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" 
                           class="btn-primary ticket-stub focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                           role="button">
                            Dashboard
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="btn-secondary ml-2 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                    role="button">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" 
                           class="btn-primary ticket-stub focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                           role="button">
                            Sign In
                        </a>
                        <a href="{{ route('register.public') }}" 
                           class="btn-secondary ml-2 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                           role="button">
                            Register
                        </a>
                    @endauth
                @endif
            </div>
        </nav>

        <!-- Main Content -->
        <main id="main-content" class="text-center py-8 sm:py-12" role="main">
            <!-- Hero Section -->
            <div class="glass-effect rounded-2xl p-6 sm:p-8 lg:p-12 mb-8 relative overflow-hidden">
                <!-- Animated Background Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <div class="w-full h-full" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 40px 40px;"></div>
                </div>
                
                <div class="relative z-10 max-w-4xl mx-auto">
                    <picture class="block mb-6">
                        <source srcset="{{ asset('assets/images/hdTicketsLogo.webp') }}" type="image/webp">
                        <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" 
                             alt="HD Tickets - Sports Event Monitoring" 
                             class="w-24 h-24 sm:w-32 sm:h-32 mx-auto rounded-2xl object-contain shadow-stadium animate-float stadium-lights"
                             loading="eager"
                             width="128" height="128">
                    </picture>
                    
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-gradient mb-4 hero-title-enhanced">
                        HD Tickets
                    </h1>
                    
                    <p class="text-xl sm:text-2xl text-primary-200 mb-6 font-semibold">
                        ⚽ Never Miss Your Team Again 🏀
                    </p>
                    
                    <p class="text-lg text-white/90 max-w-2xl mx-auto mb-8 leading-relaxed">
                        Professional sports ticket monitoring platform with comprehensive user management, 
                        subscription-based access, and automated purchasing. Track prices across 50+ platforms 
                        with role-based permissions and legal compliance.
                    </p>
                    
                    <!-- Connection Status -->
                    <div x-data="realTimeStats" class="inline-flex items-center space-x-2 text-sm text-white/70 mb-6">
                        <span x-text="statusIcon" :class="statusColor" class="text-base" aria-hidden="true"></span>
                        <span x-text="connectionStatus" class="capitalize"></span>
                        <span x-show="lastUpdate" x-text="'Updated: ' + (lastUpdate ? lastUpdate.toLocaleTimeString() : '')"></span>
                    </div>
                </div>
            </div>

            <!-- Real-time Stats Bar -->
            <div x-data="realTimeStats" 
                 class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8"
                 role="region" 
                 aria-label="Live Statistics">
                
                <div class="scoreboard-stat rounded-xl p-6 text-center transform hover:scale-105 transition-transform duration-300">
                    <div class="text-3xl sm:text-4xl font-bold text-green-400 mb-2" 
                         x-text="stats.platforms"
                         :class="{ 'welcome-skeleton': isLoading }">
                        50+
                    </div>
                    <div class="text-sm text-gray-300 uppercase tracking-wider">Platforms</div>
                </div>
                
                <div class="scoreboard-stat rounded-xl p-6 text-center transform hover:scale-105 transition-transform duration-300">
                    <div class="text-3xl sm:text-4xl font-bold text-blue-400 mb-2" 
                         x-text="stats.monitoring"
                         :class="{ 'welcome-skeleton': isLoading }">
                        24/7
                    </div>
                    <div class="text-sm text-gray-300 uppercase tracking-wider">Monitoring</div>
                </div>
                
                <div class="scoreboard-stat rounded-xl p-6 text-center transform hover:scale-105 transition-transform duration-300">
                    <div class="text-3xl sm:text-4xl font-bold text-purple-400 mb-2" 
                         x-text="stats.users"
                         :class="{ 'welcome-skeleton': isLoading }">
                        15K+
                    </div>
                    <div class="text-sm text-gray-300 uppercase tracking-wider">Users</div>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12" role="region" aria-label="Key Features">
                <div class="feature-card-enhanced glass-effect rounded-xl p-6 text-center team-color-football cursor-pointer"
                     @click="handleFeatureClick('role-based-access')"
                     tabindex="0"
                     role="button"
                     aria-label="Learn about role-based access control"
                     @keydown.enter="handleFeatureClick('role-based-access')"
                     @keydown.space.prevent="handleFeatureClick('role-based-access')">
                    
                    <div class="text-4xl mb-4 animate-bounce-gentle" aria-hidden="true">👥</div>
                    <h3 class="text-lg font-semibold text-white mb-3">Role-Based Access</h3>
                    <p class="text-sm text-white/80 leading-relaxed">Customer, Agent, Admin & Scraper roles with tailored permissions and features</p>
                </div>
                
                <div class="feature-card-enhanced glass-effect rounded-xl p-6 text-center team-color-basketball cursor-pointer"
                     @click="handleFeatureClick('subscription-system')"
                     tabindex="0"
                     role="button"
                     aria-label="Learn about subscription system"
                     @keydown.enter="handleFeatureClick('subscription-system')"
                     @keydown.space.prevent="handleFeatureClick('subscription-system')">
                    
                    <div class="text-4xl mb-4 animate-bounce-gentle" aria-hidden="true">💳</div>
                    <h3 class="text-lg font-semibold text-white mb-3">Subscription System</h3>
                    <p class="text-sm text-white/80 leading-relaxed">Monthly plans with configurable limits, 7-day free trial, and unlimited agent access</p>
                </div>
                
                <div class="feature-card-enhanced glass-effect rounded-xl p-6 text-center team-color-baseball cursor-pointer"
                     @click="handleFeatureClick('legal-compliance')"
                     tabindex="0"
                     role="button"
                     aria-label="Learn about legal compliance"
                     @keydown.enter="handleFeatureClick('legal-compliance')"
                     @keydown.space.prevent="handleFeatureClick('legal-compliance')">
                    
                    <div class="text-4xl mb-4 animate-bounce-gentle" aria-hidden="true">⚖️</div>
                    <h3 class="text-lg font-semibold text-white mb-3">Legal Compliance</h3>
                    <p class="text-sm text-white/80 leading-relaxed">GDPR compliant with mandatory legal document acceptance and audit trails</p>
                </div>
                
                <div class="feature-card-enhanced glass-effect rounded-xl p-6 text-center team-color-hockey cursor-pointer"
                     @click="handleFeatureClick('enhanced-security')"
                     tabindex="0"
                     role="button"
                     aria-label="Learn about enhanced security"
                     @keydown.enter="handleFeatureClick('enhanced-security')"
                     @keydown.space.prevent="handleFeatureClick('enhanced-security')">
                    
                    <div class="text-4xl mb-4 animate-bounce-gentle" aria-hidden="true">🔒</div>
                    <h3 class="text-lg font-semibold text-white mb-3">Enhanced Security</h3>
                    <p class="text-sm text-white/80 leading-relaxed">2FA, device fingerprinting, email/SMS verification, and secure payment processing</p>
                </div>
            </div>
            
            <!-- Team Theme Cycler -->
            <div class="mb-8">
                <button @click="cycleTeamTheme()" 
                        class="glass-effect px-6 py-3 rounded-full text-white hover:bg-white/20 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        role="button"
                        aria-label="Change team color theme">
                    🎨 Switch Team Colors
                </button>
            </div>

            @auth
                <div class="glass-effect rounded-xl p-6 mb-8 bg-gradient-to-r from-green-600/80 to-emerald-600/80">
                    <p class="text-xl font-bold text-white mb-2">🎉 Welcome back, {{ Auth::user()->name }}!</p>
                    <p class="text-green-100">Ready to find tickets for your next great game?</p>
                </div>
            @endauth

            <!-- Call to Action -->
            <div class="text-center space-y-6">
                @auth
                    <a href="{{ url('/dashboard') }}" 
                       class="inline-block bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-bold py-4 px-8 rounded-xl text-lg transition-all duration-300 transform hover:scale-105 ticket-stub focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                       role="button">
                        🏆 Go to Dashboard
                    </a>
                    
                    <button @click="triggerCelebration()" 
                            class="block mx-auto mt-4 text-white/70 hover:text-white transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded"
                            role="button"
                            aria-label="Celebrate with confetti">
                        🎊 Celebrate Your Success!
                    </button>
                @else
                    <div class="space-y-4">
                        <a href="{{ route('register.public') }}" 
                           class="inline-block bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold py-4 px-8 rounded-xl text-lg transition-all duration-300 transform hover:scale-105 ticket-stub focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 mr-4"
                           role="button">
                            🎫 Register Now
                        </a>
                        
                        <a href="{{ route('login') }}" 
                           class="inline-block bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-bold py-4 px-8 rounded-xl text-lg transition-all duration-300 transform hover:scale-105 ticket-stub focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                           role="button">
                            🔐 Sign In
                        </a>
                    </div>
                    
                    <div class="glass-effect rounded-lg p-6 max-w-2xl mx-auto mt-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div class="text-center">
                                <div class="text-green-400 text-2xl mb-2">📝</div>
                                <strong class="text-green-300">Free Registration</strong><br>
                                <span class="text-white/80">7-day free trial included</span>
                            </div>
                            <div class="text-center">
                                <div class="text-blue-400 text-2xl mb-2">💳</div>
                                <strong class="text-blue-300">Flexible Plans</strong><br>
                                <span class="text-white/80">$29.99/month, no refunds</span>
                            </div>
                            <div class="text-center">
                                <div class="text-purple-400 text-2xl mb-2">⚖️</div>
                                <strong class="text-purple-300">Legal Compliance</strong><br>
                                <span class="text-white/80">GDPR & privacy compliant</span>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-white/20">
                            <p class="text-xs text-white/70 text-center">
                                <strong class="text-yellow-300">Service Provided "As-Is"</strong> • 
                                No Warranty • No Money-Back Guarantee<br>
                                By registering, you agree to our 
                                <a href="{{ route('legal.terms-of-service') }}" class="text-blue-300 hover:text-blue-200 underline">Terms of Service</a>, 
                                <a href="{{ route('legal.disclaimer') }}" class="text-blue-300 hover:text-blue-200 underline">Disclaimer</a>, and 
                                <a href="{{ route('legal.privacy-policy') }}" class="text-blue-300 hover:text-blue-200 underline">Privacy Policy</a>
                            </p>
                        </div>
                    </div>
                @endauth
                
                <!-- Social Sharing -->
                <div class="mt-8 space-x-4">
                    <button @click="share({ title: 'HD Tickets', text: 'Check out HD Tickets for sports event monitoring!', url: window.location.href })" 
                            class="text-white/70 hover:text-white transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded px-3 py-2"
                            role="button"
                            aria-label="Share HD Tickets">
                        📤 Share
                    </button>
                    
                    <button @click="copyToClipboard(window.location.href).then(success => success ? notify.success('Link copied!') : notify.error('Copy failed'))" 
                            class="text-white/70 hover:text-white transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded px-3 py-2"
                            role="button"
                            aria-label="Copy link to clipboard">
                        📋 Copy Link
                    </button>
                </div>
            </div>
            
            <!-- FAQ Section for SEO -->
            <section class="mt-16 max-w-4xl mx-auto" role="region" aria-label="Frequently Asked Questions">
                <div class="glass-effect rounded-xl p-8">
                    <h2 class="text-3xl font-bold text-center text-white mb-8">Frequently Asked Questions</h2>
                    
                    <div class="space-y-6" x-data="{ openFaq: null }">
                        <div class="border-b border-white/20 pb-6">
                            <button @click="openFaq = openFaq === 1 ? null : 1"
                                    class="w-full text-left flex justify-between items-center text-white hover:text-blue-300 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded p-2"
                                    role="button"
                                    :aria-expanded="openFaq === 1"
                                    aria-controls="faq-1">
                                <span class="text-lg font-semibold">What is HD Tickets?</span>
                                <span x-text="openFaq === 1 ? '−' : '+'"
                                      class="text-2xl font-bold"
                                      aria-hidden="true"></span>
                            </button>
                            <div x-show="openFaq === 1"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                                 x-transition:enter-end="opacity-100 transform translate-y-0"
                                 id="faq-1"
                                 class="mt-4 text-white/80 leading-relaxed">
                                HD Tickets is a professional sports event ticket monitoring platform that provides <a href="{{ route('subscription.plans') }}" class="text-blue-300 hover:text-blue-200 underline">subscription-based access</a> to ticket price tracking, availability alerts, and automated purchasing across 50+ ticket platforms. Our system features <a href="{{ route('register.public') }}" class="text-blue-300 hover:text-blue-200 underline">role-based permissions</a>, enterprise-grade security, and full <a href="{{ route('legal.gdpr-compliance') }}" class="text-blue-300 hover:text-blue-200 underline">GDPR compliance</a>.
                            </div>
                        </div>
                        
                        <div class="border-b border-white/20 pb-6">
                            <button @click="openFaq = openFaq === 2 ? null : 2"
                                    class="w-full text-left flex justify-between items-center text-white hover:text-blue-300 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded p-2"
                                    role="button"
                                    :aria-expanded="openFaq === 2"
                                    aria-controls="faq-2">
                                <span class="text-lg font-semibold">How much does HD Tickets cost?</span>
                                <span x-text="openFaq === 2 ? '−' : '+'"
                                      class="text-2xl font-bold"
                                      aria-hidden="true"></span>
                            </button>
                            <div x-show="openFaq === 2"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                                 x-transition:enter-end="opacity-100 transform translate-y-0"
                                 id="faq-2"
                                 class="mt-4 text-white/80 leading-relaxed">
                                HD Tickets offers a <a href="{{ route('subscription.plans') }}" class="text-blue-300 hover:text-blue-200 underline">subscription-based service</a> starting at $29.99 per month, with a 7-day free trial for new customers. Different plans offer varying ticket limits and features. Please note our <a href="{{ route('legal.disclaimer') }}" class="text-blue-300 hover:text-blue-200 underline">no money-back guarantee policy</a> as stated in our Terms of Service.
                            </div>
                        </div>
                        
                        <div class="border-b border-white/20 pb-6">
                            <button @click="openFaq = openFaq === 3 ? null : 3"
                                    class="w-full text-left flex justify-between items-center text-white hover:text-blue-300 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded p-2"
                                    role="button"
                                    :aria-expanded="openFaq === 3"
                                    aria-controls="faq-3">
                                <span class="text-lg font-semibold">Is HD Tickets GDPR compliant?</span>
                                <span x-text="openFaq === 3 ? '−' : '+'"
                                      class="text-2xl font-bold"
                                      aria-hidden="true"></span>
                            </button>
                            <div x-show="openFaq === 3"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                                 x-transition:enter-end="opacity-100 transform translate-y-0"
                                 id="faq-3"
                                 class="mt-4 text-white/80 leading-relaxed">
                                Yes, HD Tickets is fully GDPR compliant with comprehensive <a href="{{ route('legal.data-processing-agreement') }}" class="text-blue-300 hover:text-blue-200 underline">data processing agreements</a>, <a href="{{ route('legal.privacy-policy') }}" class="text-blue-300 hover:text-blue-200 underline">privacy controls</a>, and user consent management systems in place. We maintain detailed audit trails and ensure all user data is processed according to GDPR requirements.
                            </div>
                        </div>
                        
                        <div class="border-b border-white/20 pb-6">
                            <button @click="openFaq = openFaq === 4 ? null : 4"
                                    class="w-full text-left flex justify-between items-center text-white hover:text-blue-300 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded p-2"
                                    role="button"
                                    :aria-expanded="openFaq === 4"
                                    aria-controls="faq-4">
                                <span class="text-lg font-semibold">What security features does HD Tickets offer?</span>
                                <span x-text="openFaq === 4 ? '−' : '+'"
                                      class="text-2xl font-bold"
                                      aria-hidden="true"></span>
                            </button>
                            <div x-show="openFaq === 4"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                                 x-transition:enter-end="opacity-100 transform translate-y-0"
                                 id="faq-4"
                                 class="mt-4 text-white/80 leading-relaxed">
                                HD Tickets provides enterprise-grade security including <a href="{{ route('profile.security') }}" class="text-blue-300 hover:text-blue-200 underline">two-factor authentication (2FA)</a>, role-based access control, device fingerprinting, session management, and comprehensive audit trails. We also offer email and SMS verification for enhanced account security.
                            </div>
                        </div>
                        
                        <div class="pb-6">
                            <button @click="openFaq = openFaq === 5 ? null : 5"
                                    class="w-full text-left flex justify-between items-center text-white hover:text-blue-300 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded p-2"
                                    role="button"
                                    :aria-expanded="openFaq === 5"
                                    aria-controls="faq-5">
                                <span class="text-lg font-semibold">What user roles are available?</span>
                                <span x-text="openFaq === 5 ? '−' : '+'"
                                      class="text-2xl font-bold"
                                      aria-hidden="true"></span>
                            </button>
                            <div x-show="openFaq === 5"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                                 x-transition:enter-end="opacity-100 transform translate-y-0"
                                 id="faq-5"
                                 class="mt-4 text-white/80 leading-relaxed">
                                HD Tickets supports four distinct user roles: <strong class="text-green-400">Customer</strong> (subscription-based access), <strong class="text-blue-400">Agent</strong> (unlimited professional access), <strong class="text-red-400">Admin</strong> (full system access), and <strong class="text-gray-400">Scraper</strong> (system-only for ticket scraping operations). Each role has tailored permissions and features. <a href="{{ route('register.public') }}" class="text-blue-300 hover:text-blue-200 underline">Register now</a> to get started!
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
        
        <!-- Footer with Legal Links -->
        <footer class="mt-16 py-8 border-t border-white/20" role="contentinfo">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
                    <div>
                        <h4 class="text-white font-semibold mb-3">Legal Documents</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="{{ route('legal.terms-of-service') }}" class="text-white/70 hover:text-white transition-colors">Terms of Service</a></li>
                            <li><a href="{{ route('legal.disclaimer') }}" class="text-white/70 hover:text-white transition-colors">Service Disclaimer</a></li>
                            <li><a href="{{ route('legal.privacy-policy') }}" class="text-white/70 hover:text-white transition-colors">Privacy Policy</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-3">Compliance</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="{{ route('legal.data-processing-agreement') }}" class="text-white/70 hover:text-white transition-colors">Data Processing</a></li>
                            <li><a href="{{ route('legal.gdpr-compliance') }}" class="text-white/70 hover:text-white transition-colors">GDPR Compliance</a></li>
                            <li><a href="{{ route('legal.cookie-policy') }}" class="text-white/70 hover:text-white transition-colors">Cookie Policy</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-3">Policies</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="{{ route('legal.acceptable-use-policy') }}" class="text-white/70 hover:text-white transition-colors">Acceptable Use</a></li>
                            <li><a href="{{ route('legal.legal-notices') }}" class="text-white/70 hover:text-white transition-colors">Legal Notices</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-3">User Roles</h4>
                        <ul class="space-y-2 text-sm text-white/70">
                            <li><span class="text-green-400">•</span> Customer (Subscription)</li>
                            <li><span class="text-blue-400">•</span> Agent (Unlimited)</li>
                            <li><span class="text-red-400">•</span> Admin (Full Access)</li>
                            <li><span class="text-gray-400">•</span> Scraper (System Only)</li>
                        </ul>
                    </div>
                </div>
                
                <div class="border-t border-white/20 pt-6">
                    <div class="flex flex-col md:flex-row justify-between items-center">
                        <div class="text-white/60 text-sm mb-4 md:mb-0">
                            © {{ date('Y') }} HD Tickets. All rights reserved. Service provided "as-is" with no warranty or money-back guarantee.
                        </div>
                        <div class="flex space-x-4 text-sm">
                            <span class="text-white/60">Built with Laravel {{ app()->version() }}</span>
                            <span class="text-white/60">•</span>
                            <span class="text-white/60">PHP {{ PHP_VERSION }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Celebration Particles Container -->
    <div class="celebration-particles" x-show="showParticles" aria-hidden="true"></div>
    
    <!-- JavaScript for Enhanced UX -->
    <script>
        // Fade in animation
        document.addEventListener('DOMContentLoaded', function() {
            document.body.style.opacity = '1';
            
            // Add click animations
            const buttons = document.querySelectorAll('.nav-button, .cta-button');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    // Create ripple effect
                    const ripple = document.createElement('div');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.classList.add('ripple');
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => ripple.remove(), 300);
                });
            });
            
            // Parallax effect for feature cards
            const cards = document.querySelectorAll('.feature-card');
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                cards.forEach((card, index) => {
                    const rate = scrolled * -0.5;
                    card.style.transform = `translateY(${rate * (index * 0.1)}px)`;
                });
            });
            
            // Error handling for external resources
            window.addEventListener('error', function(e) {
                if (e.target.tagName === 'SCRIPT' && e.target.src.includes('tailwindcss')) {
                    console.warn('Tailwind CSS failed to load, using fallback styles');
                    // The fallback CSS is already in place
                }
            });
        });
    </script>
    
    <style>
        /* Ripple effect */
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: scale(0);
            animation: ripple-animation 0.3s ease-out;
            pointer-events: none;
        }
        
        @keyframes ripple-animation {
            to {
                transform: scale(1);
                opacity: 0;
            }
        }
        
        /* Button positioning for ripple effect */
        .nav-button, .cta-button {
            position: relative;
            overflow: hidden;
        }
    </style>
    </body>
</html>
