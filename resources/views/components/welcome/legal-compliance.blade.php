<section class="legal-compliance slide-up">
    <div class="section-header">
        <h2 class="section-title">Legal Compliance & Trust</h2>
        <p class="section-subtitle">
            Full transparency and compliance with international regulations and industry standards
        </p>
    </div>
    
    <div class="compliance-grid">
        <!-- GDPR Compliance -->
        <div class="compliance-card gdpr">
            <div class="compliance-icon">⚖️</div>
            <h3 class="compliance-title">GDPR Compliant</h3>
            <p class="compliance-description">
                Full compliance with General Data Protection Regulation (GDPR) 
                requirements for European users and data processing.
            </p>
            <ul class="compliance-features">
                <li>Data processing agreements</li>
                <li>Privacy by design</li>
                <li>Right to be forgotten</li>
                <li>Data portability</li>
                <li>Consent management</li>
            </ul>
            <div class="compliance-badge gdpr-badge">
                <strong>GDPR</strong><br>
                Certified Compliant
            </div>
        </div>
        
        <!-- Legal Documentation -->
        <div class="compliance-card legal">
            <div class="compliance-icon">📋</div>
            <h3 class="compliance-title">Mandatory Legal Documents</h3>
            <p class="compliance-description">
                All users must accept comprehensive legal documentation 
                with full audit trail and version tracking.
            </p>
            <ul class="compliance-features">
                <li>Terms of Service</li>
                <li>Service Disclaimer</li>
                <li>Privacy Policy</li>
                <li>Data Processing Agreement</li>
                <li>Cookie Policy</li>
            </ul>
            <div class="compliance-badge legal-badge">
                <strong>Legal</strong><br>
                Documents Required
            </div>
        </div>
        
        <!-- Service Terms -->
        <div class="compliance-card terms">
            <div class="compliance-icon">⚠️</div>
            <h3 class="compliance-title">Service Terms</h3>
            <p class="compliance-description">
                Clear service terms with "as-is" provision and 
                no money-back guarantee policy for all users.
            </p>
            <ul class="compliance-features">
                <li>Service provided "as-is"</li>
                <li>No warranty guarantees</li>
                <li>No money-back guarantee</li>
                <li>All sales are final</li>
                <li>Clear limitation of liability</li>
            </ul>
            <div class="compliance-badge terms-badge">
                <strong>Terms</strong><br>
                No Warranty
            </div>
        </div>
        
        <!-- Audit Trail -->
        <div class="compliance-card audit">
            <div class="compliance-icon">🔍</div>
            <h3 class="compliance-title">Comprehensive Audit Trail</h3>
            <p class="compliance-description">
                Complete tracking of all user actions, document acceptances, 
                and system interactions for legal compliance.
            </p>
            <ul class="compliance-features">
                <li>Document acceptance tracking</li>
                <li>IP address logging</li>
                <li>Timestamp verification</li>
                <li>User action history</li>
                <li>Compliance reporting</li>
            </ul>
            <div class="compliance-badge audit-badge">
                <strong>Audit</strong><br>
                Full Tracking
            </div>
        </div>
    </div>
    
    <div class="legal-documents">
        <h3>Required Legal Documents</h3>
        <p>
            All users must review and accept the following legal documents before using our platform. 
            These documents are regularly updated and users will be notified of any changes.
        </p>
        
        <div class="documents-grid">
            <a href="{{ route('legal.terms-of-service') }}" class="document-link">
                <div class="document-icon">📄</div>
                <div class="document-info">
                    <strong>Terms of Service</strong>
                    <span>Service conditions and user obligations</span>
                </div>
            </a>
            
            <a href="{{ route('legal.disclaimer') }}" class="document-link">
                <div class="document-icon">⚠️</div>
                <div class="document-info">
                    <strong>Service Disclaimer</strong>
                    <span>Service limitations and warranty disclaimers</span>
                </div>
            </a>
            
            <a href="{{ route('legal.privacy-policy') }}" class="document-link">
                <div class="document-icon">🔒</div>
                <div class="document-info">
                    <strong>Privacy Policy</strong>
                    <span>Data collection and privacy practices</span>
                </div>
            </a>
            
            <a href="{{ route('legal.data-processing-agreement') }}" class="document-link">
                <div class="document-icon">⚖️</div>
                <div class="document-info">
                    <strong>Data Processing Agreement</strong>
                    <span>GDPR compliance and data handling</span>
                </div>
            </a>
            
            <a href="{{ route('legal.cookie-policy') }}" class="document-link">
                <div class="document-icon">🍪</div>
                <div class="document-info">
                    <strong>Cookie Policy</strong>
                    <span>Cookie usage and tracking information</span>
                </div>
            </a>
            
            <a href="{{ route('legal.acceptable-use-policy') }}" class="document-link">
                <div class="document-icon">📋</div>
                <div class="document-info">
                    <strong>Acceptable Use Policy</strong>
                    <span>Platform usage guidelines and restrictions</span>
                </div>
            </a>
        </div>
    </div>
    
    <div class="compliance-commitment">
        <h3>Our Compliance Commitment</h3>
        <p>
            We are committed to maintaining the highest standards of legal compliance 
            and transparency. Our platform is designed with privacy and compliance as 
            core principles, ensuring that all user data is handled with the utmost care 
            and in accordance with applicable laws and regulations.
        </p>
        
        <div class="compliance-stats">
            <div class="stat-item">
                <span class="stat-number">100%</span>
                <span class="stat-label">GDPR Compliant</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">6</span>
                <span class="stat-label">Legal Documents</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">24/7</span>
                <span class="stat-label">Audit Logging</span>
            </div>
        </div>
    </div>
</section>

<style>
.legal-compliance {
    margin: 80px 0;
    text-align: center;
}

.compliance-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 32px;
    margin-bottom: 60px;
}

.compliance-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    padding: 32px;
    text-align: left;
    transition: all 0.3s ease;
    position: relative;
}

.compliance-card:hover {
    transform: translateY(-6px);
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.3);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.compliance-icon {
    font-size: 32px;
    margin-bottom: 16px;
    display: block;
}

.compliance-title {
    font-size: 24px;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 12px;
}

.compliance-description {
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.6;
    margin-bottom: 20px;
}

.compliance-features {
    list-style: none;
    padding: 0;
    margin-bottom: 24px;
}

.compliance-features li {
    padding: 6px 0;
    color: rgba(255, 255, 255, 0.7);
    position: relative;
    padding-left: 20px;
}

.compliance-features li::before {
    content: '✓';
    position: absolute;
    left: 0;
    color: #10b981;
    font-weight: bold;
}

.compliance-badge {
    display: inline-block;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    text-align: center;
    line-height: 1.2;
}

.gdpr-badge {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
    border: 2px solid rgba(16, 185, 129, 0.3);
}

.legal-badge {
    background: rgba(59, 130, 246, 0.2);
    color: #3b82f6;
    border: 2px solid rgba(59, 130, 246, 0.3);
}

.terms-badge {
    background: rgba(245, 158, 11, 0.2);
    color: #f59e0b;
    border: 2px solid rgba(245, 158, 11, 0.3);
}

.audit-badge {
    background: rgba(139, 92, 246, 0.2);
    color: #8b5cf6;
    border: 2px solid rgba(139, 92, 246, 0.3);
}

.legal-documents {
    margin-bottom: 60px;
}

.legal-documents h3 {
    font-size: 28px;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 16px;
}

.legal-documents p {
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.6;
    max-width: 700px;
    margin: 0 auto 32px;
}

.documents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.document-link {
    display: flex;
    align-items: center;
    padding: 20px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
    gap: 16px;
}

.document-link:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

.document-icon {
    font-size: 24px;
    flex-shrink: 0;
}

.document-info {
    text-align: left;
    color: rgba(255, 255, 255, 0.9);
}

.document-info strong {
    display: block;
    color: #ffffff;
    font-weight: 600;
    margin-bottom: 4px;
}

.document-info span {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.7);
}

.compliance-commitment {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    padding: 40px;
}

.compliance-commitment h3 {
    font-size: 28px;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 20px;
}

.compliance-commitment p {
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.6;
    max-width: 800px;
    margin: 0 auto 32px;
    font-size: 16px;
}

.compliance-stats {
    display: flex;
    justify-content: center;
    gap: 40px;
    flex-wrap: wrap;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 36px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 8px;
}

.stat-label {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.7);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

@media (max-width: 768px) {
    .compliance-grid {
        grid-template-columns: 1fr;
        gap: 24px;
    }
    
    .compliance-icon {
        font-size: 28px;
        margin-bottom: 12px;
    }
    
    .documents-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .document-icon {
        font-size: 20px;
    }
    
    .compliance-stats {
        flex-direction: column;
        gap: 24px;
    }
    
    .compliance-card {
        padding: 24px;
    }
    
    .compliance-commitment {
        padding: 24px;
    }
}
</style>
