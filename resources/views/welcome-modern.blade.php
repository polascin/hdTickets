<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="HD Tickets - Professional Sports Event Ticket Monitoring & Automation Platform">
    <title>HD Tickets - Smart Sports Ticket Monitoring</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background-color: #ffffff;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .nav {
            display: flex;
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