<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="HD Tickets - Professional Sports Event Ticket Monitoring Platform">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>HD Tickets - Sports Ticket Monitoring</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            color: #ffffff;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #8b5cf6 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        .header {
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
            padding: 80px 0;
            text-align: center;
        }

        .hero {
            margin-bottom: 80px;
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin: 80px 0;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 32px 24px;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            background: rgba(255, 255, 255, 0.15);
        }

        .stat-number {
            font-size: 48px;
            font-weight: 700;
            color: #ffffff;
            display: block;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.7);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Features Section */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
            margin: 80px 0;
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
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 48px;
            }

            .hero-subtitle {
                font-size: 20px;
            }

            .hero-cta {
                flex-direction: column;
                align-items: center;
            }

            .nav {
                flex-direction: column;
                gap: 20px;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }

            .stats {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .features {
                grid-template-columns: 1fr;
                gap: 24px;
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
<body>
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

    <main class="main">
        <div class="container">
            <section class="hero fade-in">
                <h1 class="hero-title">HD Tickets</h1>
                <p class="hero-subtitle">⚽ Never Miss Your Team Again 🏀</p>
                <p class="hero-description">
                    Professional sports ticket monitoring platform with comprehensive user management, 
                    subscription-based access, and automated purchasing. Track prices across 50+ platforms 
                    with role-based permissions and legal compliance.
                </p>
                
                @auth
                    <div class="auth-section">
                        <h2 class="auth-welcome">Welcome back, {{ Auth::user()->name }}! 🎉</h2>
                        <p class="auth-subtitle">Ready to find tickets for your next great game?</p>
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
                    </div>
                @else
                    <div class="hero-cta">
                        <a href="{{ route('register.public') }}" class="btn btn-primary">Get Started Free</a>
                        <a href="{{ route('login') }}" class="btn btn-secondary">Sign In</a>
                    </div>
                @endauth
            </section>

            <section class="stats slide-up">
                <div class="stat-card">
                    <span class="stat-number">50+</span>
                    <span class="stat-label">Platforms</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">Monitoring</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number">15K+</span>
                    <span class="stat-label">Users</span>
                </div>
            </section>

            <section class="features slide-up">
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3 class="feature-title">Role-Based Access</h3>
                    <p class="feature-description">
                        Customer, Agent, Admin & Scraper roles with tailored permissions and features
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">💳</div>
                    <h3 class="feature-title">Subscription System</h3>
                    <p class="feature-description">
                        Monthly plans with configurable limits, 7-day free trial, and unlimited agent access
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">⚖️</div>
                    <h3 class="feature-title">Legal Compliance</h3>
                    <p class="feature-description">
                        GDPR compliant with mandatory legal document acceptance and audit trails
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3 class="feature-title">Enhanced Security</h3>
                    <p class="feature-description">
                        2FA, device fingerprinting, email/SMS verification, and secure payment processing
                    </p>
                </div>
            </section>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} HD Tickets. All rights reserved.</p>
            <p>Professional Sports Event Ticket Monitoring Platform</p>
        </div>
    </footer>

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
