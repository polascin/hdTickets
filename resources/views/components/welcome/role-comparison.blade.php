<section class="role-comparison slide-up">
    <div class="section-header">
        <h2 class="section-title">Choose Your Role</h2>
        <p class="section-subtitle">
            Different access levels designed for different needs
        </p>
    </div>
    
    <div class="role-grid">
        <!-- Customer Role -->
        <div class="role-card popular">
            <div class="role-header">
                <div class="role-icon">👤</div>
                <h3 class="role-name">Customer</h3>
                <div class="role-badge">Most Popular</div>
            </div>
            
            <div class="role-pricing">
                <span class="price">$29.99</span>
                <span class="period">/month</span>
            </div>
            
            <ul class="role-features">
                <li>✅ 7-day free trial</li>
                <li>✅ 100 tickets/month</li>
                <li>✅ Email verification</li>
                <li>✅ Optional 2FA</li>
                <li>✅ Legal document compliance</li>
                <li>✅ Purchase access</li>
                <li>✅ Basic monitoring</li>
            </ul>
            
            <a href="{{ route('register.public') }}" class="role-cta btn-primary">
                Start Free Trial
            </a>
        </div>
        
        <!-- Agent Role -->
        <div class="role-card premium">
            <div class="role-header">
                <div class="role-icon">🏆</div>
                <h3 class="role-name">Agent</h3>
                <div class="role-badge premium-badge">Professional</div>
            </div>
            
            <div class="role-pricing">
                <span class="price">Unlimited</span>
                <span class="period">Access</span>
            </div>
            
            <ul class="role-features">
                <li>✅ Unlimited tickets</li>
                <li>✅ No subscription required</li>
                <li>✅ Advanced monitoring</li>
                <li>✅ Performance metrics</li>
                <li>✅ Priority support</li>
                <li>✅ Automation features</li>
                <li>✅ Professional tools</li>
            </ul>
            
            <a href="{{ route('register.public') }}" class="role-cta btn-premium">
                Contact Sales
            </a>
        </div>
        
        <!-- Administrator Role -->
        <div class="role-card admin">
            <div class="role-header">
                <div class="role-icon">👑</div>
                <h3 class="role-name">Administrator</h3>
                <div class="role-badge admin-badge">Enterprise</div>
            </div>
            
            <div class="role-pricing">
                <span class="price">Full</span>
                <span class="period">Control</span>
            </div>
            
            <ul class="role-features">
                <li>✅ Complete system access</li>
                <li>✅ User management</li>
                <li>✅ Financial reports</li>
                <li>✅ Analytics dashboard</li>
                <li>✅ API management</li>
                <li>✅ System configuration</li>
                <li>✅ White-label options</li>
            </ul>
            
            <a href="{{ route('register.public') }}" class="role-cta btn-admin">
                Enterprise Demo
            </a>
        </div>
    </div>
    
    <div class="role-note">
        <p>
            <strong>Note:</strong> Scraper role is system-only for automated operations and cannot login to the web interface.
        </p>
    </div>
</section>

<style>
.role-comparison {
    margin: 80px 0;
}

.section-header {
    text-align: center;
    margin-bottom: 60px;
}

.section-title {
    font-size: 48px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 16px;
}

.section-subtitle {
    font-size: 18px;
    color: rgba(255, 255, 255, 0.7);
    max-width: 600px;
    margin: 0 auto;
}

.role-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 32px;
    margin-bottom: 40px;
}

.role-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    padding: 32px;
    text-align: center;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.role-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
}

.role-card.popular::before {
    background: linear-gradient(90deg, #10b981, #3b82f6);
}

.role-card.premium::before {
    background: linear-gradient(90deg, #f59e0b, #f97316);
}

.role-card.admin::before {
    background: linear-gradient(90deg, #dc2626, #b91c1c);
}

.role-card:hover {
    transform: translateY(-8px);
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.3);
}

.role-header {
    margin-bottom: 24px;
    position: relative;
}

.role-icon {
    font-size: 32px;
    margin-bottom: 12px;
}

.role-name {
    font-size: 28px;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 8px;
}

.role-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.premium-badge {
    background: rgba(245, 158, 11, 0.2);
    color: #f59e0b;
    border-color: rgba(245, 158, 11, 0.3);
}

.admin-badge {
    background: rgba(220, 38, 38, 0.2);
    color: #dc2626;
    border-color: rgba(220, 38, 38, 0.3);
}

.role-pricing {
    margin-bottom: 32px;
}

.price {
    font-size: 36px;
    font-weight: 700;
    color: #ffffff;
}

.period {
    font-size: 16px;
    color: rgba(255, 255, 255, 0.7);
    margin-left: 4px;
}

.role-features {
    list-style: none;
    padding: 0;
    margin-bottom: 32px;
    text-align: left;
}

.role-features li {
    padding: 8px 0;
    color: rgba(255, 255, 255, 0.8);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.role-features li:last-child {
    border-bottom: none;
}

.role-cta {
    display: block;
    width: 100%;
    padding: 16px 24px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: #10b981;
    color: #ffffff;
}

.btn-primary:hover {
    background: #059669;
    transform: translateY(-2px);
}

.btn-premium {
    background: #f59e0b;
    color: #ffffff;
}

.btn-premium:hover {
    background: #d97706;
    transform: translateY(-2px);
}

.btn-admin {
    background: #dc2626;
    color: #ffffff;
}

.btn-admin:hover {
    background: #b91c1c;
    transform: translateY(-2px);
}

.role-note {
    text-align: center;
    padding: 20px;
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.2);
    border-radius: 12px;
    color: rgba(255, 255, 255, 0.8);
}

@media (max-width: 768px) {
    .section-title {
        font-size: 36px;
    }
    
    .role-grid {
        grid-template-columns: 1fr;
        gap: 24px;
    }
    
    .role-card {
        padding: 24px;
    }
    
    .role-icon {
        font-size: 28px;
        margin-bottom: 8px;
    }
    
    .role-name {
        font-size: 24px;
    }
    
    .price {
        font-size: 32px;
    }
}
</style>
