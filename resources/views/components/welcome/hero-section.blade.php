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
            <p class="auth-subtitle">
                Your role: <strong>{{ ucfirst(Auth::user()->role) }}</strong><br>
                Ready to find tickets for your next great game?
            </p>
            <a href="{{ url('/dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
        </div>
    @else
        <div class="hero-cta">
            <a href="{{ route('register.public') }}" class="btn btn-primary">
                Start 7-Day Free Trial
            </a>
            <a href="{{ route('login') }}" class="btn btn-secondary">Sign In</a>
        </div>
        
        <p class="trial-info">
            No credit card required • 7 days free • Cancel anytime
        </p>
    @endauth
</section>

<style>
.trial-info {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.6);
    margin-top: 16px;
}
</style>
