<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="HD Tickets - Professional Sports Event Ticket Monitoring Platform with Role-Based Access, Subscription Management, and Legal Compliance">
    <meta name="keywords" content="sports tickets, ticket monitoring, event tickets, subscription platform, GDPR compliant, 2FA security, role-based access">
    <meta name="author" content="HD Tickets">
    <meta name="robots" content="index, follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="HD Tickets - Professional Sports Ticket Monitoring Platform">
    <meta property="og:description" content="Never miss your team again! Professional sports ticket monitoring with role-based access, subscription plans, and enterprise security.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('assets/images/social/og-image.png') }}">
    <meta property="og:site_name" content="HD Tickets">
    
    <!-- Twitter Card Meta Tags -->
    <meta property="og:image:alt" content="HD Tickets - Sports ticket monitoring platform">
    <meta name="twitter:image:alt" content="HD Tickets - Sports ticket monitoring platform">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="HD Tickets - Professional Sports Ticket Monitoring">
    <meta name="twitter:description" content="Monitor 50+ platforms, role-based access, GDPR compliant, 7-day free trial">
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
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            width: 100%;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            color: #ffffff;
            background: linear-gradient(135deg, #0f172a 0%, #1e40af 25%, #3b82f6 50%, #8b5cf6 75%, #c084fc 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            position: relative;
            display: grid;
            grid-template-rows: auto 1fr auto;
            place-items: center;
            max-width: 100vw;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a"><stop offset="0%" stop-color="%23ffffff" stop-opacity="0.1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23a)"/><circle cx="800" cy="300" r="150" fill="url(%23a)"/><circle cx="400" cy="700" r="120" fill="url(%23a)"/><circle cx="900" cy="800" r="80" fill="url(%23a)"/></svg>') no-repeat;
            background-size: cover;
            z-index: -1;
            pointer-events: none;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            width: 100%;
            position: relative;
        }

        /* Header Styles */
        .header {
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            width: 100%;
            z-index: 100;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(10px);
            grid-row: 1;
            place-self: stretch;
        }

        .header .container {
            display: block;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            width: 100%;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            text-decoration: none;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .nav-links {
            display: flex;
            gap: 16px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-primary {
            background: #ffffff;
            color: #1e40af;
        }

        .btn-primary:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* Main Content */
        .main {
            padding: 80px 20px;
            text-align: center;
            width: 100%;
            max-width: 1200px;
            position: relative;
            grid-row: 2;
            place-self: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .hero {
            margin: 0 auto 80px auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            width: 100%;
            max-width: 900px;
        }

        .hero-title {
            font-size: 64px;
            font-weight: 700;
            margin-bottom: 24px;
            background: linear-gradient(135deg, #ffffff 0%, #e0e7ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 24px;
            margin-bottom: 16px;
            color: rgba(255, 255, 255, 0.9);
        }

        .hero-description {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.7);
            max-width: 600px;
            margin: 0 auto 40px;
        }

        .hero-cta {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-top: 40px;
        }

        /* Stats Section */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 32px;
            margin: 80px auto;
            width: 100%;
            max-width: 1200px;
            justify-content: center;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 40px 24px;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #10b981, #3b82f6, #8b5cf6, #f59e0b);
            background-size: 200% 100%;
            animation: gradientMove 3s linear infinite;
        }

        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            font-size: 32px;
            margin-bottom: 16px;
            display: block;
            animation: pulse 2s infinite;
        }

        .stat-number {
            font-size: 52px;
            font-weight: 700;
            color: #ffffff;
            display: block;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stat-label {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.9);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .stat-description {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            font-style: italic;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* Features Section */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
            margin: 80px auto;
            width: 100%;
            max-width: 1200px;
            justify-content: center;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 32px;
            text-align: left;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            background: rgba(255, 255, 255, 0.15);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .feature-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #ffffff;
        }

        .feature-description {
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
        }

        /* Auth Section */
        .auth-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 40px;
            margin: 40px 0;
        }

        .auth-welcome {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #ffffff;
        }

        .auth-subtitle {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 24px;
        }

        /* Footer */
        .footer {
            padding: 40px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            width: 100%;
            grid-row: 3;
            place-self: stretch;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                background-size: 200% 200%;
            }
            
            .container {
                padding: 0 16px;
            }
            
            .header {
                padding: 16px 0;
            }
            
            .main {
                padding: 40px 16px;
            }
            
            .hero {
                margin-bottom: 60px;
            }
            
            .hero-title {
                font-size: 42px;
                line-height: 1.1;
            }

            .hero-subtitle {
                font-size: 18px;
                margin-bottom: 12px;
            }
            
            .hero-description {
                font-size: 16px;
                margin-bottom: 32px;
            }

            .hero-cta {
                flex-direction: column;
                align-items: center;
                gap: 12px;
            }

            .nav {
                flex-direction: column;
                gap: 16px;
                align-items: center;
                text-align: center;
                justify-content: center;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .logo {
                font-size: 24px;
            }
            
            .logo-icon {
                width: 32px;
                height: 32px;
                font-size: 16px;
            }

            .stats {
                grid-template-columns: 1fr;
                gap: 20px;
                margin: 60px 0;
            }
            
            .stat-card {
                padding: 32px 20px;
            }
            
            .stat-number {
                font-size: 44px;
            }
            
            .stat-label {
                font-size: 14px;
            }
            
            .stat-description {
                font-size: 12px;
            }

            .features {
                grid-template-columns: 1fr;
                gap: 20px;
                margin: 60px 0;
            }
            
            .feature-card {
                padding: 24px;
            }
            
            .feature-icon {
                width: 48px;
                height: 48px;
                font-size: 20px;
            }
            
            .feature-title {
                font-size: 20px;
            }
            
            .btn {
                padding: 16px 24px;
                font-size: 16px;
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .hero-title {
                font-size: 36px;
            }
            
            .hero-subtitle {
                font-size: 16px;
            }
            
            .hero-description {
                font-size: 15px;
            }
            
            .stats {
                margin: 40px 0;
            }
            
            .stat-card {
                padding: 24px 16px;
            }
            
            .stat-number {
                font-size: 36px;
            }
            
            .stat-icon {
                font-size: 24px;
            }
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-up {
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="stadium-bg">
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="{{ url('/') }}" class="logo">
                    <div class="logo-icon">🎫</div>
                    HD Tickets
                </a>
                
                <div class="nav-links">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn btn-primary">Dashboard</a>
                            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-secondary">Logout</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-secondary">Sign In</a>
                            <a href="{{ route('register.public') }}" class="btn btn-primary">Register</a>
                        @endauth
                    @endif
                </div>
            </nav>
        </div>
    </header>

    <main class="main" role="main">
        <div class="container">
            <!-- Hero Section -->
            @include('components.welcome.hero-section')

            <!-- Statistics Section -->
            <section class="stats slide-up" aria-label="Platform statistics">
                <div class="stat-card premium-stat" x-data="{ count: 0 }" x-intersect="count = 75">
                    <div class="stat-icon">🌐</div>
                    <span class="stat-number" x-text="count + '+'" x-transition></span>
                    <span class="stat-label">Integrated Platforms</span>
                    <span class="stat-description">Major ticket vendors worldwide</span>
                </div>
                <div class="stat-card premium-stat" x-data="{ visible: false }" x-intersect="visible = true">
                    <div class="stat-icon">⚡</div>
                    <span class="stat-number" x-show="visible" x-transition>24/7</span>
                    <span class="stat-label">AI-Powered Monitoring</span>
                    <span class="stat-description">Real-time price tracking</span>
                </div>
                <div class="stat-card premium-stat" x-data="{ count: 0 }" x-intersect="count = 25">
                    <div class="stat-icon">👥</div>
                    <span class="stat-number" x-text="count + 'K+'" x-transition></span>
                    <span class="stat-label">Happy Customers</span>
                    <span class="stat-description">Sports fans like you</span>
                </div>
                <div class="stat-card premium-stat" x-data="{ count: 0 }" x-intersect="count = 2">
                    <div class="stat-icon">💰</div>
                    <span class="stat-number" x-text="'$' + count + 'M+'" x-transition></span>
                    <span class="stat-label">Saved on Tickets</span>
                    <span class="stat-description">By our community</span>
                </div>
            </section>

            <!-- Social Proof Section -->
            @include('components.welcome.social-proof')
            
            <!-- Role Comparison Section -->
            @include('components.welcome.role-comparison')

            <!-- Subscription Showcase Section -->
            @include('components.welcome.subscription-showcase')

            <!-- Security Features Section -->
            @include('components.welcome.security-features')

            <!-- Legal Compliance Section -->
            @include('components.welcome.legal-compliance')
        </div>
    </main>

    <!-- Enhanced Footer with Legal Links -->
    @include('components.welcome.footer-legal')

    <script>
        // Simple fade-in animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe elements for animation
        document.querySelectorAll('.slide-up').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });

        // Add some interactivity to stat cards
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-8px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(-4px) scale(1)';
            });
        });

        // Add click effect to buttons
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const ripple = document.createElement('div');
                ripple.style.position = 'absolute';
                ripple.style.borderRadius = '50%';
                ripple.style.background = 'rgba(255, 255, 255, 0.3)';
                ripple.style.width = ripple.style.height = '10px';
                ripple.style.left = (e.clientX - e.target.offsetLeft - 5) + 'px';
                ripple.style.top = (e.clientY - e.target.offsetTop - 5) + 'px';
                ripple.style.animation = 'ripple 0.6s linear';
                
                btn.style.position = 'relative';
                btn.style.overflow = 'hidden';
                btn.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Add ripple animation CSS
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
