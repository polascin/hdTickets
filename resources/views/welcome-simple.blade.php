<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="HD Tickets - Professional Sports Event Ticket Monitoring Platform">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>HD Tickets - Sports Ticket Monitoring</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            color: #ffffff;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #8b5cf6 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .logo {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            text-decoration: none;
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
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .main {
            padding: 80px 0;
            text-align: center;
        }

        .hero-title {
            font-size: 64px;
            font-weight: 700;
            margin-bottom: 24px;
        }

        .hero-subtitle {
            font-size: 24px;
            margin-bottom: 16px;
        }

        .hero-description {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.8);
            max-width: 600px;
            margin: 0 auto 40px;
        }

        .hero-cta {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 40px;
        }

        .auth-section {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 40px;
            margin: 40px 0;
        }

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
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="{{ url('/') }}" class="logo">🎫 HD Tickets</a>
                
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
                            @if (Route::has('register.public'))
                                <a href="{{ route('register.public') }}" class="btn btn-primary">Register</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </nav>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <h1 class="hero-title">HD Tickets</h1>
            <p class="hero-subtitle">⚽ Never Miss Your Team Again 🏀</p>
            <p class="hero-description">
                Professional sports ticket monitoring platform with comprehensive user management, 
                subscription-based access, and automated purchasing. Track prices across 50+ platforms.
            </p>
            
            @auth
                <div class="auth-section">
                    <h2>Welcome back, {{ Auth::user()->name }}! 🎉</h2>
                    <p>Ready to find tickets for your next great game?</p>
                    <a href="{{ url('/dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
                </div>
            @else
                <div class="hero-cta">
                    @if (Route::has('register.public'))
                        <a href="{{ route('register.public') }}" class="btn btn-primary">Get Started Free</a>
                    @endif
                    <a href="{{ route('login') }}" class="btn btn-secondary">Sign In</a>
                </div>
            @endauth
        </div>
    </main>
</body>
</html>
