<footer class="footer-legal">
    <div class="container">
        <div class="footer-content">
            <!-- Company Info -->
            <div class="footer-section">
                <div class="footer-logo">
                    <div class="logo-icon">🎫</div>
                    <span>HD Tickets</span>
                </div>
                <p class="footer-description">
                    Professional Sports Event Ticket Monitoring Platform with comprehensive 
                    user management, subscription-based access, and legal compliance.
                </p>
                <div class="footer-stats">
                    <span>50+ Platforms</span> • 
                    <span>24/7 Monitoring</span> • 
                    <span>15K+ Users</span>
                </div>
            </div>
            
            <!-- Legal Documents -->
            <div class="footer-section">
                <h4 class="footer-title">Legal Documents</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('legal.terms-of-service') }}" class="footer-link">Terms of Service</a></li>
                    <li><a href="{{ route('legal.disclaimer') }}" class="footer-link">Service Disclaimer</a></li>
                    <li><a href="{{ route('legal.privacy-policy') }}" class="footer-link">Privacy Policy</a></li>
                    <li><a href="{{ route('legal.data-processing-agreement') }}" class="footer-link">Data Processing Agreement</a></li>
                    <li><a href="{{ route('legal.cookie-policy') }}" class="footer-link">Cookie Policy</a></li>
                    <li><a href="{{ route('legal.acceptable-use-policy') }}" class="footer-link">Acceptable Use Policy</a></li>
                </ul>
            </div>
            
            <!-- Platform Features -->
            <div class="footer-section">
                <h4 class="footer-title">Platform Features</h4>
                <ul class="footer-links">
                    <li><a href="#" class="footer-link">Role-Based Access</a></li>
                    <li><a href="#" class="footer-link">Subscription Plans</a></li>
                    <li><a href="#" class="footer-link">Security Features</a></li>
                    <li><a href="#" class="footer-link">API Documentation</a></li>
                    <li><a href="#" class="footer-link">Integration Guide</a></li>
                    <li><a href="#" class="footer-link">Mobile App</a></li>
                </ul>
            </div>
            
            <!-- Support & Contact -->
            <div class="footer-section">
                <h4 class="footer-title">Support</h4>
                <ul class="footer-links">
                    <li><a href="#" class="footer-link">Help Center</a></li>
                    <li><a href="#" class="footer-link">Contact Support</a></li>
                    <li><a href="#" class="footer-link">System Status</a></li>
                    <li><a href="#" class="footer-link">Report Security Issue</a></li>
                    <li><a href="#" class="footer-link">Feature Requests</a></li>
                    <li><a href="#" class="footer-link">Community Forum</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Compliance Badges -->
        <div class="compliance-badges">
            <div class="compliance-badge">
                <span class="badge-icon">🔒</span>
                <span class="badge-text">SSL Secured</span>
            </div>
            <div class="compliance-badge">
                <span class="badge-icon">⚖️</span>
                <span class="badge-text">GDPR Compliant</span>
            </div>
            <div class="compliance-badge">
                <span class="badge-icon">🛡️</span>
                <span class="badge-text">PCI DSS Level 1</span>
            </div>
            <div class="compliance-badge">
                <span class="badge-icon">📋</span>
                <span class="badge-text">SOC 2 Type II</span>
            </div>
        </div>
        
        <!-- Service Disclaimer -->
        <div class="service-disclaimer">
            <div class="disclaimer-icon">⚠️</div>
            <div class="disclaimer-content">
                <h5>Important Service Notice</h5>
                <p>
                    <strong>No Money-Back Guarantee:</strong> Service provided "as-is" with no warranties. 
                    All sales are final. Please review our 
                    <a href="{{ route('legal.terms-of-service') }}" class="footer-link">Terms of Service</a> and 
                    <a href="{{ route('legal.disclaimer') }}" class="footer-link">Service Disclaimer</a> before subscribing.
                </p>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-copyright">
                <p>&copy; {{ date('Y') }} HD Tickets. All rights reserved.</p>
                <p>Professional Sports Event Ticket Monitoring Platform</p>
            </div>
            
            <div class="footer-social">
                <a href="#" class="social-link" aria-label="Twitter">
                    <span class="social-icon">🐦</span>
                </a>
                <a href="#" class="social-link" aria-label="LinkedIn">
                    <span class="social-icon">💼</span>
                </a>
                <a href="#" class="social-link" aria-label="GitHub">
                    <span class="social-icon">🐙</span>
                </a>
                <a href="#" class="social-link" aria-label="Email">
                    <span class="social-icon">📧</span>
                </a>
            </div>
            
            <div class="footer-version">
                <p>Platform Version 2.1.0</p>
                <p>Last Updated: {{ date('M Y') }}</p>
            </div>
        </div>
    </div>
</footer>

<style>
.footer-legal {
    background: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(10px);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding: 60px 0 20px;
    color: rgba(255, 255, 255, 0.8);
}

.footer-content {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 40px;
    margin-bottom: 40px;
}

.footer-section {
    display: flex;
    flex-direction: column;
}

.footer-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 24px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 16px;
}

.footer-logo .logo-icon {
    font-size: 28px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    padding: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.footer-description {
    line-height: 1.6;
    margin-bottom: 16px;
    color: rgba(255, 255, 255, 0.7);
}

.footer-stats {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.6);
    font-weight: 500;
}

.footer-title {
    font-size: 18px;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 20px;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-link {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: color 0.3s ease;
    font-size: 14px;
}

.footer-link:hover {
    color: #ffffff;
    text-decoration: underline;
}

.compliance-badges {
    display: flex;
    justify-content: center;
    gap: 24px;
    margin-bottom: 40px;
    flex-wrap: wrap;
}

.compliance-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    transition: all 0.3s ease;
}

.compliance-badge:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
}

.badge-icon {
    font-size: 16px;
}

.badge-text {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.9);
}

.service-disclaimer {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 24px;
    background: rgba(245, 158, 11, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.3);
    border-radius: 12px;
    margin-bottom: 40px;
}

.disclaimer-icon {
    font-size: 24px;
    flex-shrink: 0;
    margin-top: 2px;
}

.disclaimer-content h5 {
    font-size: 16px;
    font-weight: 600;
    color: #f59e0b;
    margin-bottom: 8px;
}

.disclaimer-content p {
    font-size: 14px;
    line-height: 1.5;
    color: rgba(255, 255, 255, 0.8);
}

.footer-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    flex-wrap: wrap;
    gap: 20px;
}

.footer-copyright {
    text-align: left;
}

.footer-copyright p {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.6);
    margin-bottom: 4px;
}

.footer-social {
    display: flex;
    gap: 16px;
}

.social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-link:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    transform: translateY(-2px);
}

.social-icon {
    font-size: 16px;
}

.footer-version {
    text-align: right;
}

.footer-version p {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.5);
    margin-bottom: 2px;
}

@media (max-width: 1024px) {
    .footer-content {
        grid-template-columns: 1fr 1fr;
        gap: 32px;
    }
}

@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
        gap: 32px;
    }
    
    .compliance-badges {
        flex-direction: column;
        align-items: center;
        gap: 16px;
    }
    
    .footer-bottom {
        flex-direction: column;
        text-align: center;
        gap: 24px;
    }
    
    .footer-copyright,
    .footer-version {
        text-align: center;
    }
}
</style>
