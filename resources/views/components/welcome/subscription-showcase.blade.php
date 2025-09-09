<section class="subscription-showcase slide-up">
    <div class="section-header">
        <h2 class="section-title">Subscription Plans</h2>
        <p class="section-subtitle">
            Flexible pricing designed to grow with your ticket monitoring needs
        </p>
    </div>
    
    <div class="subscription-grid">
        <!-- Free Trial -->
        <div class="subscription-card trial">
            <div class="card-header">
                <div class="plan-icon">🎁</div>
                <h3 class="plan-name">Free Trial</h3>
                <div class="plan-badge trial-badge">7 Days Free</div>
            </div>
            
            <div class="plan-pricing">
                <span class="price">$0</span>
                <span class="period">for 7 days</span>
            </div>
            
            <ul class="plan-features">
                <li>✅ Full platform access</li>
                <li>✅ 100 tickets included</li>
                <li>✅ Email verification</li>
                <li>✅ Basic support</li>
                <li>✅ No credit card required</li>
                <li>✅ Cancel anytime</li>
            </ul>
            
            <div class="plan-note">
                <p>Perfect for testing the platform</p>
            </div>
            
            <a href="{{ route('register.public') }}" class="plan-cta btn-trial">
                Start Free Trial
            </a>
        </div>
        
        <!-- Monthly Subscription -->
        <div class="subscription-card monthly popular">
            <div class="card-header">
                <div class="plan-icon">📅</div>
                <h3 class="plan-name">Monthly Plan</h3>
                <div class="plan-badge popular-badge">Most Popular</div>
            </div>
            
            <div class="plan-pricing">
                <span class="price">$29.99</span>
                <span class="period">/month</span>
            </div>
            
            <ul class="plan-features">
                <li>✅ 100 tickets per month</li>
                <li>✅ Real-time monitoring</li>
                <li>✅ Price alerts</li>
                <li>✅ Email & SMS notifications</li>
                <li>✅ Purchase automation</li>
                <li>✅ Priority support</li>
                <li>✅ Legal compliance</li>
                <li>✅ 2FA security</li>
            </ul>
            
            <div class="plan-note">
                <p>Best for regular ticket buyers</p>
            </div>
            
            <a href="{{ route('register.public') }}" class="plan-cta btn-monthly">
                Subscribe Monthly
            </a>
        </div>
        
        <!-- Professional/Agent -->
        <div class="subscription-card professional">
            <div class="card-header">
                <div class="plan-icon">🏆</div>
                <h3 class="plan-name">Professional</h3>
                <div class="plan-badge professional-badge">Unlimited</div>
            </div>
            
            <div class="plan-pricing">
                <span class="price">Unlimited</span>
                <span class="period">tickets</span>
            </div>
            
            <ul class="plan-features">
                <li>✅ Unlimited ticket access</li>
                <li>✅ No monthly subscription</li>
                <li>✅ Advanced monitoring tools</li>
                <li>✅ Performance analytics</li>
                <li>✅ API access</li>
                <li>✅ Bulk operations</li>
                <li>✅ White-label options</li>
                <li>✅ Dedicated support</li>
            </ul>
            
            <div class="plan-note">
                <p>For ticket professionals & agents</p>
            </div>
            
            <a href="{{ route('register.public') }}" class="plan-cta btn-professional">
                Contact Sales
            </a>
        </div>
    </div>
    
    <div class="subscription-features">
        <h3>All Plans Include</h3>
        <div class="feature-grid">
            <div class="feature-item">
                <div class="feature-icon">🔒</div>
                <span>Enterprise Security</span>
            </div>
            <div class="feature-item">
                <div class="feature-icon">⚖️</div>
                <span>Legal Compliance</span>
            </div>
            <div class="feature-item">
                <div class="feature-icon">🌍</div>
                <span>50+ Platform Integration</span>
            </div>
            <div class="feature-item">
                <div class="feature-icon">📱</div>
                <span>Mobile Optimized</span>
            </div>
            <div class="feature-item">
                <div class="feature-icon">🎯</div>
                <span>Smart Alerts</span>
            </div>
            <div class="feature-item">
                <div class="feature-icon">💳</div>
                <span>Secure Payments</span>
            </div>
        </div>
    </div>
    
    <div class="subscription-disclaimer">
        <p>
            <strong>No Money-Back Guarantee Policy:</strong> All sales are final. Service provided "as-is" with no warranties. 
            Please review our <a href="#" class="link">Terms of Service</a> and 
            <a href="#" class="link">Service Disclaimer</a> before subscribing.
        </p>
    </div>
</section>

<style>
.subscription-showcase {
    margin: 80px 0;
    text-align: center;
}

.subscription-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 32px;
    margin-bottom: 60px;
}

.subscription-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    padding: 32px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.subscription-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #6b7280, #9ca3af);
}

.subscription-card.trial::before {
    background: linear-gradient(90deg, #10b981, #059669);
}

.subscription-card.popular::before {
    background: linear-gradient(90deg, #3b82f6, #1d4ed8);
}

.subscription-card.professional::before {
    background: linear-gradient(90deg, #f59e0b, #d97706);
}

.subscription-card:hover {
    transform: translateY(-8px);
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.3);
}

.card-header {
    margin-bottom: 24px;
}

.plan-icon {
    font-size: 48px;
    margin-bottom: 16px;
}

.plan-name {
    font-size: 28px;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 8px;
}

.plan-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.trial-badge {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.popular-badge {
    background: rgba(59, 130, 246, 0.2);
    color: #3b82f6;
    border: 1px solid rgba(59, 130, 246, 0.3);
}

.professional-badge {
    background: rgba(245, 158, 11, 0.2);
    color: #f59e0b;
    border: 1px solid rgba(245, 158, 11, 0.3);
}

.plan-pricing {
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

.plan-features {
    list-style: none;
    padding: 0;
    margin-bottom: 24px;
    text-align: left;
}

.plan-features li {
    padding: 8px 0;
    color: rgba(255, 255, 255, 0.8);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.plan-features li:last-child {
    border-bottom: none;
}

.plan-note {
    margin-bottom: 24px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    font-style: italic;
    color: rgba(255, 255, 255, 0.7);
}

.plan-cta {
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

.btn-trial {
    background: #10b981;
    color: #ffffff;
}

.btn-trial:hover {
    background: #059669;
    transform: translateY(-2px);
}

.btn-monthly {
    background: #3b82f6;
    color: #ffffff;
}

.btn-monthly:hover {
    background: #1d4ed8;
    transform: translateY(-2px);
}

.btn-professional {
    background: #f59e0b;
    color: #ffffff;
}

.btn-professional:hover {
    background: #d97706;
    transform: translateY(-2px);
}

.subscription-features {
    margin-bottom: 40px;
}

.subscription-features h3 {
    font-size: 24px;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 32px;
}

.feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 24px;
}

.feature-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    color: rgba(255, 255, 255, 0.8);
}

.feature-item .feature-icon {
    font-size: 32px;
    margin-bottom: 8px;
}

.subscription-disclaimer {
    background: rgba(220, 38, 38, 0.1);
    border: 1px solid rgba(220, 38, 38, 0.3);
    border-radius: 12px;
    padding: 20px;
    color: rgba(255, 255, 255, 0.8);
    font-size: 14px;
    line-height: 1.5;
}

.link {
    color: #3b82f6;
    text-decoration: underline;
}

.link:hover {
    color: #1d4ed8;
}

@media (max-width: 768px) {
    .subscription-grid {
        grid-template-columns: 1fr;
        gap: 24px;
    }
    
    .feature-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 16px;
    }
}
</style>
