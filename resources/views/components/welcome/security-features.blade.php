<section class="security-features slide-up">
    <div class="section-header">
        <h2 class="section-title">Enterprise-Grade Security</h2>
        <p class="section-subtitle">
            Your data and transactions are protected by industry-leading security measures
        </p>
    </div>
    
    <div class="security-grid">
        <!-- Multi-Factor Authentication -->
        <div class="security-card">
            <div class="security-icon">🔐</div>
            <h3 class="security-title">Multi-Factor Authentication</h3>
            <p class="security-description">
                Secure your account with Google Authenticator 2FA, SMS verification, 
                and backup codes for enhanced protection.
            </p>
            <ul class="security-features-list">
                <li>Google Authenticator support</li>
                <li>SMS verification codes</li>
                <li>Emergency backup codes</li>
                <li>Device trust management</li>
            </ul>
        </div>
        
        <!-- Enhanced Login Security -->
        <div class="security-card">
            <div class="security-icon">🛡️</div>
            <h3 class="security-title">Enhanced Login Security</h3>
            <p class="security-description">
                Advanced device fingerprinting and automated tool detection 
                prevent unauthorized access and protect against bots.
            </p>
            <ul class="security-features-list">
                <li>Device fingerprinting</li>
                <li>Geolocation verification</li>
                <li>Automated tool detection</li>
                <li>Failed attempt monitoring</li>
            </ul>
        </div>
        
        <!-- Data Encryption -->
        <div class="security-card">
            <div class="security-icon">🔒</div>
            <h3 class="security-title">Data Encryption</h3>
            <p class="security-description">
                All data is encrypted in transit and at rest using AES-256 encryption. 
                Sensitive information is tokenized and secured.
            </p>
            <ul class="security-features-list">
                <li>AES-256 encryption</li>
                <li>TLS 1.3 in transit</li>
                <li>Payment tokenization</li>
                <li>Secure key management</li>
            </ul>
        </div>
        
        <!-- Secure Payment Processing -->
        <div class="security-card">
            <div class="security-icon">💳</div>
            <h3 class="security-title">Secure Payment Processing</h3>
            <p class="security-description">
                PCI DSS compliant payment processing with Stripe and PayPal integration. 
                No payment data stored on our servers.
            </p>
            <ul class="security-features-list">
                <li>PCI DSS compliance</li>
                <li>Stripe & PayPal integration</li>
                <li>No stored payment data</li>
                <li>Fraud protection</li>
            </ul>
        </div>
        
        <!-- Session Management -->
        <div class="security-card">
            <div class="security-icon">⏰</div>
            <h3 class="security-title">Secure Session Management</h3>
            <p class="security-description">
                Redis-backed secure sessions with automatic timeout, 
                concurrent session limits, and secure cookie handling.
            </p>
            <ul class="security-features-list">
                <li>Redis session storage</li>
                <li>Automatic timeout</li>
                <li>Secure cookie flags</li>
                <li>Session hijacking prevention</li>
            </ul>
        </div>
        
        <!-- Monitoring & Alerts -->
        <div class="security-card">
            <div class="security-icon">👁️</div>
            <h3 class="security-title">Security Monitoring</h3>
            <p class="security-description">
                Real-time security monitoring with instant alerts for suspicious activity, 
                login anomalies, and potential security threats.
            </p>
            <ul class="security-features-list">
                <li>Real-time monitoring</li>
                <li>Anomaly detection</li>
                <li>Instant security alerts</li>
                <li>Audit trail logging</li>
            </ul>
        </div>
    </div>
    
    <div class="security-badges">
        <div class="badge-item">
            <div class="badge-icon">🔏</div>
            <div class="badge-text">
                <strong>SSL/TLS</strong><br>
                256-bit Encryption
            </div>
        </div>
        <div class="badge-item">
            <div class="badge-icon">⚖️</div>
            <div class="badge-text">
                <strong>GDPR</strong><br>
                Compliant
            </div>
        </div>
        <div class="badge-item">
            <div class="badge-icon">🛡️</div>
            <div class="badge-text">
                <strong>PCI DSS</strong><br>
                Level 1
            </div>
        </div>
        <div class="badge-item">
            <div class="badge-icon">📋</div>
            <div class="badge-text">
                <strong>SOC 2</strong><br>
                Type II
            </div>
        </div>
    </div>
    
    <div class="security-commitment">
        <h3>Our Security Commitment</h3>
        <p>
            We take security seriously. Our platform undergoes regular security audits, 
            penetration testing, and vulnerability assessments. We maintain industry certifications 
            and follow security best practices to protect your data and privacy.
        </p>
        
        <div class="security-actions">
            <a href="#" class="security-link">
                📄 View Security Policy
            </a>
            <a href="#" class="security-link">
                🔍 Security Audit Reports
            </a>
            <a href="#" class="security-link">
                🚨 Report Security Issues
            </a>
        </div>
    </div>
</section>

<style>
.security-features {
    margin: 80px 0;
    text-align: center;
}

.security-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 32px;
    margin-bottom: 60px;
}

.security-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    padding: 32px;
    text-align: left;
    transition: all 0.3s ease;
    position: relative;
}

.security-card:hover {
    transform: translateY(-6px);
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.3);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.security-icon {
    font-size: 32px;
    margin-bottom: 16px;
    display: block;
}

.security-title {
    font-size: 24px;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 12px;
}

.security-description {
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.6;
    margin-bottom: 20px;
}

.security-features-list {
    list-style: none;
    padding: 0;
}

.security-features-list li {
    padding: 6px 0;
    color: rgba(255, 255, 255, 0.7);
    position: relative;
    padding-left: 20px;
}

.security-features-list li::before {
    content: '✓';
    position: absolute;
    left: 0;
    color: #10b981;
    font-weight: bold;
}

.security-badges {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 40px;
    margin-bottom: 60px;
    flex-wrap: wrap;
}

.badge-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 24px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.badge-item:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
}

.badge-icon {
    font-size: 32px;
}

.badge-text {
    color: rgba(255, 255, 255, 0.9);
    font-size: 14px;
    line-height: 1.3;
}

.badge-text strong {
    color: #ffffff;
    font-weight: 600;
}

.security-commitment {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    padding: 40px;
    text-align: center;
}

.security-commitment h3 {
    font-size: 28px;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 20px;
}

.security-commitment p {
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.6;
    max-width: 800px;
    margin: 0 auto 32px;
    font-size: 16px;
}

.security-actions {
    display: flex;
    justify-content: center;
    gap: 24px;
    flex-wrap: wrap;
}

.security-link {
    display: inline-flex;
    align-items: center;
    padding: 12px 20px;
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.3);
    border-radius: 8px;
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.security-link:hover {
    background: rgba(59, 130, 246, 0.2);
    border-color: rgba(59, 130, 246, 0.5);
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .security-grid {
        grid-template-columns: 1fr;
        gap: 24px;
    }
    
    .security-card {
        padding: 24px;
    }
    
    .security-icon {
        font-size: 28px;
        margin-bottom: 12px;
    }
    
    .security-title {
        font-size: 20px;
    }
    
    .security-badges {
        flex-direction: column;
        gap: 20px;
    }
    
    .badge-item {
        justify-content: center;
        padding: 12px 20px;
    }
    
    .badge-icon {
        font-size: 28px;
    }
    
    .security-commitment {
        padding: 24px;
    }
    
    .security-actions {
        flex-direction: column;
        align-items: center;
        gap: 16px;
    }
    
    .security-link {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
}
</style>
