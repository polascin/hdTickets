<section class="hero fade-in">
    <div class="hero-badge">
        <span class="badge-icon">🚀</span>
        <span class="badge-text">The Future of Sports Ticketing</span>
    </div>
    <h1 class="hero-title">Never Miss The Game</h1>
    <p class="hero-subtitle">⚽ Track, Monitor & Secure Sports Tickets Across 50+ Platforms 🏀</p>
    <p class="hero-description">
        From premier league matches to championship finals - our AI-powered platform monitors 
        ticket prices 24/7, alerts you to the best deals, and secures your seats before they're gone. 
        Join thousands of sports fans who never miss their team again.
    </p>
    
    @auth
        <div class="auth-section">
            <h2 class="auth-welcome">Welcome back, {{ Auth::user()->name }}! 🎉</h2>
            <p class="auth-subtitle">
                Your role: <strong>{{ ucfirst(Auth::user()->role) }}</strong><br>
                Ready to find tickets for your next great game?
            </p>
            <a href="{{ url('/dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
        </div>
    @else
        <div class="hero-cta">
            <a href="{{ route('register.public') }}" class="btn btn-primary btn-hero">
                <span class="btn-icon">🎯</span>
                Start Free Trial
                <span class="btn-subtitle">Find your tickets in minutes</span>
            </a>
            <a href="{{ route('login') }}" class="btn btn-secondary btn-hero">
                <span class="btn-icon">🔐</span>
                Sign In
                <span class="btn-subtitle">Welcome back!</span>
            </a>
        </div>
        
        <div class="hero-benefits">
            <div class="benefit-item">
                <span class="benefit-icon">💳</span>
                <span class="benefit-text">No credit card required</span>
            </div>
            <div class="benefit-item">
                <span class="benefit-icon">⏰</span>
                <span class="benefit-text">7 days free</span>
            </div>
            <div class="benefit-item">
                <span class="benefit-icon">❌</span>
                <span class="benefit-text">Cancel anytime</span>
            </div>
        </div>
    @endauth
</section>

<style>
/* Hero Badge Styling */
.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 30px;
    padding: 8px 16px;
    margin-bottom: 24px;
    animation: float 3s ease-in-out infinite;
}

.badge-icon {
    font-size: 16px;
}

.badge-text {
    font-size: 14px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.9);
}

/* Enhanced Button Styling */
.btn-hero {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 20px 32px;
    min-width: 200px;
    position: relative;
    overflow: hidden;
}

.btn-hero .btn-icon {
    font-size: 20px;
    margin-bottom: 4px;
}

.btn-hero .btn-subtitle {
    font-size: 12px;
    opacity: 0.8;
    font-weight: 400;
}

.btn-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-hero:hover::before {
    left: 100%;
}

/* Hero Benefits Styling */
.hero-benefits {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 32px;
    margin-top: 32px;
    flex-wrap: wrap;
}

.benefit-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.benefit-item:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}

.benefit-icon {
    font-size: 16px;
}

.benefit-text {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.8);
    font-weight: 500;
}

/* Animations */
@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .hero-cta {
        flex-direction: column;
        gap: 16px;
    }
    
    .btn-hero {
        min-width: 250px;
    }
    
    .hero-benefits {
        gap: 16px;
    }
    
    .benefit-item {
        font-size: 12px;
    }
}
</style>
