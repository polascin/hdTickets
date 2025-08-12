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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #ffffff;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
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
            padding: 2rem 0;
            margin-bottom: 3rem;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #60a5fa;
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
        
        /* Main Content */
        .hero-section {
            text-align: center;
            padding: 2rem 0;
        }
        
        .hero-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            color: #60a5fa;
            margin-bottom: 2rem;
        }
        
        .hero-description {
            font-size: 1rem;
            color: #d1d5db;
            max-width: 600px;
            margin: 0 auto 3rem auto;
            line-height: 1.7;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 2rem;
            max-width: 500px;
            margin: 0 auto 3rem auto;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: #9ca3af;
        }
        
        .green { color: #10b981; }
        .blue { color: #3b82f6; }
        .purple { color: #8b5cf6; }
        
        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .feature-card {
            background: rgba(31, 41, 55, 0.6);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(75, 85, 99, 0.3);
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border-color: rgba(96, 165, 250, 0.5);
        }
        
        .feature-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #f9fafb;
        }
        
        .feature-description {
            font-size: 0.875rem;
            color: #9ca3af;
        }
        
        /* Welcome Message */
        .welcome-message {
            background: linear-gradient(135deg, #059669, #047857);
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        /* CTA Section */
        .cta-section {
            text-align: center;
            padding: 2rem 0;
        }
        
        .cta-button {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 1rem 2rem;
            border-radius: 0.75rem;
            text-decoration: none;
            font-size: 1.125rem;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.4);
        }
        
        .registration-notice {
            background: rgba(31, 41, 55, 0.6);
            border: 1px solid rgba(75, 85, 99, 0.3);
            padding: 1.5rem;
            border-radius: 0.75rem;
            max-width: 400px;
            margin: 2rem auto;
            text-align: center;
        }
        
        .registration-notice p {
            font-size: 0.875rem;
            color: #9ca3af;
            margin: 0;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 1rem;
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
            <div class="logo">HD Tickets</div>
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
            <h1 class="hero-title">HD Tickets</h1>
            <p class="hero-subtitle">Never Miss Your Team Again</p>
            
            <p class="hero-description">
                Advanced sports ticket monitoring with real-time alerts and automated purchasing. 
                Track prices across 50+ platforms and never miss your favorite games.
            </p>

            <!-- Stats -->
            <div class="stats-grid">
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
                    <h3 class="feature-title">Real-time Alerts</h3>
                    <p class="feature-description">Get instant notifications when tickets become available for your favorite teams</p>
                </div>
                <div class="feature-card">
                    <h3 class="feature-title">Price Tracking</h3>
                    <p class="feature-description">Monitor ticket prices across multiple platforms and get the best deals</p>
                </div>
                <div class="feature-card">
                    <h3 class="feature-title">24/7 Monitoring</h3>
                    <p class="feature-description">Continuous monitoring ensures you never miss an opportunity to buy</p>
                </div>
                <div class="feature-card">
                    <h3 class="feature-title">Smart Purchasing</h3>
                    <p class="feature-description">Automated purchasing system secures tickets the moment they're available</p>
                </div>
                <div class="feature-card">
                    <h3 class="feature-title">Multi-Sport Coverage</h3>
                    <p class="feature-description">Football, basketball, baseball, hockey, and more - all major sports covered</p>
                </div>
                <div class="feature-card">
                    <h3 class="feature-title">Mobile Alerts</h3>
                    <p class="feature-description">Stay connected with instant mobile notifications wherever you are</p>
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
