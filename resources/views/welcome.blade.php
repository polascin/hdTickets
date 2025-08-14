<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="HD Tickets - Advanced sports ticket monitoring with real-time alerts and automated purchasing.">
    <meta name="keywords" content="sports tickets, monitoring, alerts, purchase, events">
    <meta name="robots" content="index, follow">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    
    <title>HD Tickets - Sports Ticket Monitoring</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Primary CSS Framework -->
    <script src="https://cdn.tailwindcss.com"></script>
    
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
        
        .nav-button.secondary {
            background: #dc2626;
            margin-left: 0.5rem;
        }
        
        .nav-button.secondary:hover {
            background: #b91c1c;
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
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: linear-gradient(45deg, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.1;
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
<body class="loading">
    <div class="container">
        <!-- Navigation -->
        <nav class="navbar">
            <div class="logo">
                <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets Logo">
                HD Tickets
            </div>
            <div>
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="nav-button">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline" style="display: inline;">
                            @csrf
                            <button type="submit" class="nav-button secondary">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="nav-button">Sign In</a>
                    @endauth
                @endif
            </div>
        </nav>

        <!-- Main Content -->
        <main class="hero-section">
            <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets Logo" class="hero-logo">
            <h1 class="hero-title">HD Tickets</h1>
            <p class="hero-subtitle">Never Miss Your Team Again</p>
            
            <p class="hero-description">
                Advanced sports ticket monitoring with real-time alerts and automated purchasing. 
                Track prices across 50+ platforms and never miss your favorite games.
            </p>

            <!-- Stats Bar -->
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-number green">50+</div>
                    <div class="stat-label">Platforms</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number blue">24/7</div>
                    <div class="stat-label">Monitoring</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number purple">15K+</div>
                    <div class="stat-label">Users</div>
                </div>
            </div>

            <!-- Features -->
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon icon-monitoring">🔔</div>
                    <h3 class="feature-title">Real-time Monitoring</h3>
                    <p class="feature-description">24/7 alerts + continuous monitoring across all platforms</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon icon-pricing">💰</div>
                    <h3 class="feature-title">Smart Pricing</h3>
                    <p class="feature-description">Price tracking + automated smart purchasing decisions</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon icon-coverage">🌐</div>
                    <h3 class="feature-title">Multi-Platform Coverage</h3>
                    <p class="feature-description">50+ platforms + comprehensive sports coverage</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon icon-mobile">📱</div>
                    <h3 class="feature-title">Mobile Experience</h3>
                    <p class="feature-description">Optimized mobile app with instant notifications</p>
                </div>
            </div>

            @auth
                <div class="welcome-message">
                    <p><strong>Welcome back, {{ Auth::user()->name }}!</strong></p>
                    <p>Ready to find your next great game?</p>
                </div>
            @endauth

            <!-- CTA Section -->
            <div class="cta-section">
                @auth
                    <a href="{{ url('/dashboard') }}" class="cta-button">Go to Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="cta-button">Get Started</a>
                    <div class="registration-notice">
                        <p>
                            <strong>Administrator Access Required</strong><br>
                            New user registration is restricted to administrators only.<br>
                            Please contact your system administrator for access.
                        </p>
                    </div>
                @endauth
            </div>
        </main>
    </div>
    
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
