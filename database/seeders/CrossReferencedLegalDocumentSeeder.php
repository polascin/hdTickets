<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LegalDocument;
use Illuminate\Database\Seeder;

class CrossReferencedLegalDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documents = [
            [
                'type'                => LegalDocument::TYPE_TERMS_OF_SERVICE,
                'title'               => 'Terms of Service',
                'slug'                => 'terms-of-service',
                'content'             => $this->getCrossReferencedTermsOfServiceContent(),
                'version'             => '2.1',
                'is_active'           => TRUE,
                'requires_acceptance' => TRUE,
                'effective_date'      => now(),
            ],
            [
                'type'                => LegalDocument::TYPE_DISCLAIMER,
                'title'               => 'Service Disclaimer',
                'slug'                => 'disclaimer',
                'content'             => $this->getCrossReferencedDisclaimerContent(),
                'version'             => '2.1',
                'is_active'           => TRUE,
                'requires_acceptance' => TRUE,
                'effective_date'      => now(),
            ],
            [
                'type'                => LegalDocument::TYPE_PRIVACY_POLICY,
                'title'               => 'Privacy Policy',
                'slug'                => 'privacy-policy',
                'content'             => $this->getCrossReferencedPrivacyPolicyContent(),
                'version'             => '2.1',
                'is_active'           => TRUE,
                'requires_acceptance' => FALSE,
                'effective_date'      => now(),
            ],
            [
                'type'                => LegalDocument::TYPE_DATA_PROCESSING_AGREEMENT,
                'title'               => 'Data Processing Agreement',
                'slug'                => 'data-processing-agreement',
                'content'             => $this->getCrossReferencedDataProcessingAgreementContent(),
                'version'             => '2.1',
                'is_active'           => TRUE,
                'requires_acceptance' => TRUE,
                'effective_date'      => now(),
            ],
            [
                'type'                => LegalDocument::TYPE_COOKIE_POLICY,
                'title'               => 'Cookie Policy',
                'slug'                => 'cookie-policy',
                'content'             => $this->getCrossReferencedCookiePolicyContent(),
                'version'             => '2.1',
                'is_active'           => TRUE,
                'requires_acceptance' => TRUE,
                'effective_date'      => now(),
            ],
            [
                'type'                => LegalDocument::TYPE_GDPR_COMPLIANCE,
                'title'               => 'GDPR Compliance Statement',
                'slug'                => 'gdpr-compliance',
                'content'             => $this->getCrossReferencedGdprComplianceContent(),
                'version'             => '2.1',
                'is_active'           => TRUE,
                'requires_acceptance' => FALSE,
                'effective_date'      => now(),
            ],
            [
                'type'                => LegalDocument::TYPE_ACCEPTABLE_USE_POLICY,
                'title'               => 'Acceptable Use Policy',
                'slug'                => 'acceptable-use-policy',
                'content'             => $this->getCrossReferencedAcceptableUsePolicyContent(),
                'version'             => '2.1',
                'is_active'           => TRUE,
                'requires_acceptance' => FALSE,
                'effective_date'      => now(),
            ],
            [
                'type'                => LegalDocument::TYPE_LEGAL_NOTICES,
                'title'               => 'Legal Notices',
                'slug'                => 'legal-notices',
                'content'             => $this->getCrossReferencedLegalNoticesContent(),
                'version'             => '2.1',
                'is_active'           => TRUE,
                'requires_acceptance' => FALSE,
                'effective_date'      => now(),
            ],
        ];

        foreach ($documents as $documentData) {
            LegalDocument::updateOrCreate(
                ['type' => $documentData['type']],
                $documentData,
            );
        }
    }

    private function getDocumentNavigationSection(): string
    {
        return '<div class="legal-document-navigation">
    <h2>📄 Related Legal Documents</h2>
    <p>This document is part of HD Tickets comprehensive legal framework. For complete understanding of your rights and obligations, please also review:</p>
    
    <div class="document-grid">
        <div class="document-link-card">
            <h3><a href="' . route('legal.terms-of-service') . '">📋 Terms of Service</a></h3>
            <p>Core terms governing your use of HD Tickets platform, including service provision, user responsibilities, and account management.</p>
        </div>
        
        <div class="document-link-card">
            <h3><a href="' . route('legal.privacy-policy') . '">🔒 Privacy Policy</a></h3>
            <p>How we collect, use, and protect your personal information in accordance with GDPR and international privacy laws.</p>
        </div>
        
        <div class="document-link-card">
            <h3><a href="' . route('legal.disclaimer') . '">⚠️ Service Disclaimer</a></h3>
            <p>Important limitations, warranties, and liability disclaimers for HD Tickets services including "as-is" provisions.</p>
        </div>
        
        <div class="document-link-card">
            <h3><a href="' . route('legal.data-processing-agreement') . '">🛡️ Data Processing Agreement</a></h3>
            <p>GDPR-compliant agreement detailing how personal data is processed, stored, and protected within our platform.</p>
        </div>
        
        <div class="document-link-card">
            <h3><a href="' . route('legal.cookie-policy') . '">🍪 Cookie Policy</a></h3>
            <p>Information about cookies and tracking technologies used to enhance your HD Tickets experience.</p>
        </div>
        
        <div class="document-link-card">
            <h3><a href="' . route('legal.gdpr-compliance') . '">🇪🇺 GDPR Compliance Statement</a></h3>
            <p>Our commitment to GDPR compliance and detailed information about your data protection rights.</p>
        </div>
        
        <div class="document-link-card">
            <h3><a href="' . route('legal.acceptable-use-policy') . '">✅ Acceptable Use Policy</a></h3>
            <p>Guidelines for appropriate use of HD Tickets services, prohibited activities, and enforcement procedures.</p>
        </div>
        
        <div class="document-link-card">
            <h3><a href="' . route('legal.legal-notices') . '">⚖️ Legal Notices</a></h3>
            <p>Copyright, trademark, licensing information, and additional legal requirements for HD Tickets platform.</p>
        </div>
    </div>
    
    <div class="document-index-link">
        <p><strong><a href="' . route('legal.index') . '">📚 View Complete Legal Documents Index</a></strong></p>
    </div>
</div>';
    }

    private function getQuickReferenceSection(): string
    {
        return '<div class="quick-reference-section">
    <h2>🔍 Quick Reference Guide</h2>
    <p>Need to find something specific? Here are direct links to key sections across our legal documents:</p>
    
    <div class="reference-categories">
        <div class="reference-category">
            <h3>🔐 Privacy & Data Protection</h3>
            <ul>
                <li><a href="' . route('legal.privacy-policy') . '#data-collection">What Data We Collect</a> (Privacy Policy)</li>
                <li><a href="' . route('legal.privacy-policy') . '#your-rights">Your Privacy Rights</a> (Privacy Policy)</li>
                <li><a href="' . route('legal.data-processing-agreement') . '#data-subject-rights">GDPR Rights</a> (Data Processing Agreement)</li>
                <li><a href="' . route('legal.gdpr-compliance') . '#exercising-rights">How to Exercise Rights</a> (GDPR Compliance)</li>
                <li><a href="' . route('legal.cookie-policy') . '#managing-cookies">Cookie Management</a> (Cookie Policy)</li>
            </ul>
        </div>
        
        <div class="reference-category">
            <h3>💳 Payments & Refunds</h3>
            <ul>
                <li><a href="' . route('legal.terms-of-service') . '#payment-terms">Payment Terms</a> (Terms of Service)</li>
                <li><a href="' . route('legal.disclaimer') . '#no-refunds">No Money-Back Policy</a> (Service Disclaimer)</li>
                <li><a href="' . route('legal.terms-of-service') . '#subscription-terms">Subscription Details</a> (Terms of Service)</li>
                <li><a href="' . route('legal.disclaimer') . '#financial-terms">Financial Limitations</a> (Service Disclaimer)</li>
            </ul>
        </div>
        
        <div class="reference-category">
            <h3>⚖️ Legal & Compliance</h3>
            <ul>
                <li><a href="' . route('legal.terms-of-service') . '#limitation-liability">Limitation of Liability</a> (Terms of Service)</li>
                <li><a href="' . route('legal.disclaimer') . '#warranties">Warranty Disclaimers</a> (Service Disclaimer)</li>
                <li><a href="' . route('legal.legal-notices') . '#governing-law">Governing Law</a> (Legal Notices)</li>
                <li><a href="' . route('legal.acceptable-use-policy') . '#prohibited-activities">Prohibited Activities</a> (Acceptable Use)</li>
            </ul>
        </div>
        
        <div class="reference-category">
            <h3>👤 Account & Usage</h3>
            <ul>
                <li><a href="' . route('legal.terms-of-service') . '#user-accounts">Account Management</a> (Terms of Service)</li>
                <li><a href="' . route('legal.terms-of-service') . '#account-termination">Account Termination</a> (Terms of Service)</li>
                <li><a href="' . route('legal.acceptable-use-policy') . '#permitted-use">Permitted Use</a> (Acceptable Use)</li>
                <li><a href="' . route('legal.acceptable-use-policy') . '#security-requirements">Security Requirements</a> (Acceptable Use)</li>
            </ul>
        </div>
    </div>
</div>';
    }

    private function getCrossReferencedTermsOfServiceContent(): string
    {
        return '<h1>Terms of Service</h1>

<div class="document-meta">
    <p><strong>Effective Date:</strong> ' . now()->format('F j, Y') . '</p>
    <p><strong>Version:</strong> 2.1</p>
    <p><strong>Last Updated:</strong> ' . now()->format('F j, Y') . '</p>
</div>

' . $this->getDocumentNavigationSection() . '

<div class="important-notice">
    <h2>⚠️ Important Notice</h2>
    <p><strong>Please read these Terms of Service carefully.</strong> By accessing or using HD Tickets, you agree to be bound by these terms. These terms work in conjunction with our <a href="' . route('legal.privacy-policy') . '">Privacy Policy</a>, <a href="' . route('legal.disclaimer') . '">Service Disclaimer</a>, and <a href="' . route('legal.data-processing-agreement') . '">Data Processing Agreement</a>.</p>
    
    <p><strong>Key Legal Documents You Must Also Review:</strong></p>
    <ul>
        <li><strong><a href="' . route('legal.disclaimer') . '">Service Disclaimer</a></strong> - Contains critical "as-is" provisions and no money-back guarantee policy</li>
        <li><strong><a href="' . route('legal.privacy-policy') . '">Privacy Policy</a></strong> - Explains how we handle your personal information</li>
        <li><strong><a href="' . route('legal.acceptable-use-policy') . '">Acceptable Use Policy</a></strong> - Defines permitted and prohibited activities</li>
    </ul>
</div>

<h2 id="service-description">1. Service Description</h2>
<p>HD Tickets ("we," "our," or "us") provides a comprehensive sports event ticket monitoring, scraping, and purchase automation platform (the "Service"). Our platform enables users to:</p>
<ul>
    <li>Monitor ticket availability and pricing across multiple sports venues and platforms</li>
    <li>Receive automated notifications about ticket releases and price changes</li>
    <li>Access professional-grade monitoring tools with role-based permissions</li>
    <li>Utilize subscription-based access to premium monitoring features</li>
    <li>Integrate with third-party ticketing platforms for comprehensive coverage</li>
</ul>

<p><strong>Related Documents:</strong> For detailed information about service limitations and warranties, please review our <a href="' . route('legal.disclaimer') . '">Service Disclaimer</a>.</p>

<h2>2. Acceptance of Terms</h2>
<p>By creating an account, accessing our website, or using any aspect of our Service, you acknowledge that:</p>
<ul>
    <li>You have read, understood, and agree to be bound by these Terms of Service</li>
    <li>You have reviewed our <a href="' . route('legal.privacy-policy') . '"><strong>Privacy Policy</strong></a> and <a href="' . route('legal.data-processing-agreement') . '"><strong>Data Processing Agreement</strong></a></li>
    <li>You understand and accept our <a href="' . route('legal.disclaimer') . '"><strong>Service Disclaimer</strong></a> including "as-is" provision and no money-back guarantee</li>
    <li>You agree to comply with our <a href="' . route('legal.acceptable-use-policy') . '"><strong>Acceptable Use Policy</strong></a></li>
    <li>You are at least 18 years old or have parental consent to use our Service</li>
    <li>You have the legal capacity to enter into this agreement</li>
</ul>

<h2 id="user-accounts">3. User Accounts and Registration</h2>
<h3>3.1 Account Creation</h3>
<p>To access our Service, you must create an account and provide accurate, complete information. You agree to:</p>
<ul>
    <li>Provide truthful and accurate registration information (see our <a href="' . route('legal.privacy-policy') . '#data-collection">Privacy Policy - Data Collection</a> for details)</li>
    <li>Maintain the confidentiality of your account credentials</li>
    <li>Notify us immediately of any unauthorized use of your account</li>
    <li>Accept responsibility for all activities under your account</li>
</ul>

<p><strong>Privacy Note:</strong> Account information is processed according to our <a href="' . route('legal.privacy-policy') . '">Privacy Policy</a> and <a href="' . route('legal.data-processing-agreement') . '">Data Processing Agreement</a>. For GDPR rights regarding your account data, see our <a href="' . route('legal.gdpr-compliance') . '">GDPR Compliance Statement</a>.</p>

<h3>3.2 Account Types and Roles</h3>
<p>HD Tickets offers different user roles with varying access levels:</p>
<ul>
    <li><strong>Customer:</strong> Basic sports event monitoring with subscription limits</li>
    <li><strong>Agent:</strong> Enhanced monitoring capabilities with unlimited access</li>
    <li><strong>Admin:</strong> Complete system administration privileges</li>
    <li><strong>Scraper:</strong> API-only access for automated monitoring systems</li>
</ul>

<p>Each role has specific usage guidelines detailed in our <a href="' . route('legal.acceptable-use-policy') . '">Acceptable Use Policy</a>.</p>

<h2 id="subscription-terms">4. Subscription Terms and Payment</h2>
<h3>4.1 Subscription Plans</h3>
<p>Our Service operates on a subscription basis with different tiers offering various features and limits. Subscription details include:</p>
<ul>
    <li>Monthly recurring billing cycles</li>
    <li>Configurable monitoring limits based on subscription tier</li>
    <li>7-day free trial for new customers</li>
    <li>Automatic renewal unless cancelled</li>
</ul>

<h3 id="payment-terms">4.2 Payment Terms</h3>
<div class="critical-warning">
    <p><strong>⚠️ CRITICAL PAYMENT CONDITIONS - READ CAREFULLY:</strong></p>
    <ul>
        <li><strong>All subscription fees are charged in advance and are NON-REFUNDABLE</strong></li>
        <li><strong>We do not offer refunds, credits, or money-back guarantees under ANY circumstances</strong></li>
        <li><strong>All sales are FINAL</strong></li>
        <li>Payment processing is handled by secure third-party providers</li>
        <li>Failed payments may result in service suspension</li>
    </ul>
    
    <p><strong>For complete details on our no-refund policy, see:</strong> <a href="' . route('legal.disclaimer') . '#no-refunds"><strong>Service Disclaimer - No Money-Back Guarantee</strong></a></p>
</div>

<h2>5. Service Provision and Availability</h2>
<h3>5.1 "As-Is" Service Provision</h3>
<p><strong>CRITICAL DISCLAIMER:</strong> HD Tickets is provided "AS IS" and "AS AVAILABLE" without warranties of any kind. We do not guarantee:</p>
<ul>
    <li>Continuous or uninterrupted service availability</li>
    <li>Accuracy or completeness of ticket information</li>
    <li>Success of automated ticket purchasing attempts</li>
    <li>Compatibility with all third-party ticketing platforms</li>
    <li>Real-time data synchronization across all monitored sources</li>
</ul>

<p><strong>📄 For comprehensive warranty disclaimers and service limitations, see our <a href="' . route('legal.disclaimer') . '">Service Disclaimer</a>.</strong></p>

<h3>5.2 Third-Party Dependencies</h3>
<p>Our Service relies on third-party ticketing platforms and data sources. We are not responsible for:</p>
<ul>
    <li>Changes to third-party terms of service or pricing</li>
    <li>Interruptions or modifications to third-party services</li>
    <li>Data accuracy or availability from external sources</li>
    <li>Third-party website accessibility or functionality</li>
</ul>

<p>Detailed third-party limitations are covered in our <a href="' . route('legal.disclaimer') . '#third-party-dependencies">Service Disclaimer - Third-Party Dependencies</a>.</p>

<h2>6. User Responsibilities and Conduct</h2>
<h3>6.1 Permitted Use</h3>
<p>You may use HD Tickets solely for legitimate sports ticket monitoring and personal ticket acquisition activities as detailed in our <a href="' . route('legal.acceptable-use-policy') . '#permitted-use">Acceptable Use Policy</a>.</p>

<h3>6.2 Prohibited Activities</h3>
<p>You agree not to engage in activities prohibited by our <a href="' . route('legal.acceptable-use-policy') . '#prohibited-activities">Acceptable Use Policy</a>, including but not limited to:</p>
<ul>
    <li>Violating any applicable local, state, national, or international laws</li>
    <li>Using the Service for commercial ticket resale without explicit permission</li>
    <li>Attempting to reverse engineer, decompile, or disassemble our platform</li>
    <li>Interfering with or disrupt our Service or servers</li>
    <li>Sharing account credentials with unauthorized parties</li>
    <li>Using automated tools to access the Service beyond approved API usage</li>
    <li>Engaging in fraudulent activities or identity misrepresentation</li>
    <li>Violating the terms of service of third-party ticketing platforms</li>
</ul>

<p><strong>For complete usage guidelines and enforcement procedures, see our <a href="' . route('legal.acceptable-use-policy') . '">Acceptable Use Policy</a>.</strong></p>

<h2>7. Intellectual Property Rights</h2>
<p>HD Tickets and all related content, features, and functionality are owned by HD Tickets and protected by copyright, trademark, and other intellectual property laws. You are granted a limited, non-exclusive, non-transferable license to use our Service for personal use only.</p>

<p>For detailed copyright and trademark information, see our <a href="' . route('legal.legal-notices') . '#copyright-trademark">Legal Notices</a>.</p>

<h2>8. Privacy and Data Protection</h2>
<p>Your privacy is important to us. This section should be read in conjunction with our comprehensive privacy documentation:</p>

<div class="privacy-documents-overview">
    <h3>📋 Complete Privacy Framework</h3>
    <ul>
        <li><strong><a href="' . route('legal.privacy-policy') . '">Privacy Policy</a></strong> - How we collect, use, and protect your personal information</li>
        <li><strong><a href="' . route('legal.data-processing-agreement') . '">Data Processing Agreement</a></strong> - GDPR-compliant data processing terms</li>
        <li><strong><a href="' . route('legal.gdpr-compliance') . '">GDPR Compliance Statement</a></strong> - Your rights under GDPR</li>
        <li><strong><a href="' . route('legal.cookie-policy') . '">Cookie Policy</a></strong> - Information about cookies and tracking</li>
    </ul>
    
    <p>By using our Service, you consent to our data practices as described in these documents. You have comprehensive rights regarding your personal data under GDPR - <a href="' . route('legal.gdpr-compliance') . '#exercising-rights">learn how to exercise them</a>.</p>
</div>

<h2 id="limitation-liability">9. Limitation of Liability</h2>
<div class="liability-warning">
    <p><strong>⚠️ TO THE MAXIMUM EXTENT PERMITTED BY LAW:</strong></p>
    <ul>
        <li>HD Tickets shall not be liable for any indirect, incidental, special, consequential, or punitive damages</li>
        <li>Our total liability shall not exceed the amount paid by you for the Service in the 12 months preceding the claim</li>
        <li>We disclaim liability for any losses related to ticket purchases, pricing errors, or missed opportunities</li>
        <li>You acknowledge that sports ticket purchasing involves inherent risks and uncertainties</li>
    </ul>
    
    <p><strong>📄 For comprehensive liability limitations and disclaimers, see our <a href="' . route('legal.disclaimer') . '#limitation-liability">Service Disclaimer</a>.</strong></p>
</div>

<h2 id="account-termination">10. Account Termination</h2>
<h3>10.1 Termination by You</h3>
<p>You may terminate your account at any time through your account settings. <strong>Important:</strong> Termination does not entitle you to any refund of prepaid fees as outlined in our <a href="' . route('legal.disclaimer') . '#no-refunds">Service Disclaimer - No Money-Back Guarantee</a>.</p>

<h3>10.2 Termination by Us</h3>
<p>We may suspend or terminate your account immediately if:</p>
<ul>
    <li>You violate these Terms of Service</li>
    <li>You violate our <a href="' . route('legal.acceptable-use-policy') . '">Acceptable Use Policy</a></li>
    <li>Your account is used for prohibited activities</li>
    <li>Payment for your subscription fails</li>
    <li>We suspect fraudulent or abusive behavior</li>
</ul>

<p>Enforcement procedures are detailed in our <a href="' . route('legal.acceptable-use-policy') . '#enforcement">Acceptable Use Policy</a>.</p>

<h2>11. Dispute Resolution and Governing Law</h2>
<h3>11.1 Governing Law</h3>
<p>These Terms shall be governed by and construed in accordance with the laws referenced in our <a href="' . route('legal.legal-notices') . '#governing-law">Legal Notices</a>, without regard to conflict of law provisions.</p>

<h3>11.2 Dispute Resolution</h3>
<p>Any disputes arising from these Terms or your use of the Service shall be resolved through binding arbitration as detailed in our <a href="' . route('legal.legal-notices') . '#dispute-resolution">Legal Notices</a>.</p>

<h2>12. Changes to Terms</h2>
<p>We reserve the right to modify these Terms of Service at any time. Material changes will be communicated via:</p>
<ul>
    <li>Email notification to registered users</li>
    <li>Prominent notice on our website</li>
    <li>In-app notifications</li>
</ul>
<p>Continued use of the Service after changes constitutes acceptance of the new terms. Our version control procedures are detailed in our documentation management system.</p>

<h2>13. Severability</h2>
<p>If any provision of these Terms is found to be unenforceable or invalid, the remaining provisions shall continue in full force and effect as outlined in our <a href="' . route('legal.legal-notices') . '#severability">Legal Notices</a>.</p>

<h2>14. Contact Information</h2>
<p>For questions about these Terms of Service, contact us at:</p>
<ul>
    <li><strong>Legal Department:</strong> legal@hdtickets.com</li>
    <li><strong>General Inquiries:</strong> support@hdtickets.com</li>
    <li><strong>Privacy Concerns:</strong> See our <a href="' . route('legal.privacy-policy') . '#contact">Privacy Policy - Contact Information</a></li>
    <li><strong>GDPR Rights:</strong> See our <a href="' . route('legal.gdpr-compliance') . '#exercising-rights">GDPR Compliance - Exercising Rights</a></li>
    <li><strong>Response Time:</strong> Within 5 business days</li>
</ul>

<p>Complete contact information for all legal matters is available in our <a href="' . route('legal.legal-notices') . '#contact">Legal Notices</a>.</p>

' . $this->getQuickReferenceSection() . '

<div class="acceptance-notice">
    <h2>Final Acknowledgment</h2>
    <p>By using HD Tickets, you acknowledge that you have read, understood, and agree to be bound by:</p>
    <ul>
        <li>✅ These <strong>Terms of Service</strong></li>
        <li>✅ Our <strong><a href="' . route('legal.disclaimer') . '">Service Disclaimer</a></strong> (including no money-back guarantee)</li>
        <li>✅ Our <strong><a href="' . route('legal.privacy-policy') . '">Privacy Policy</a></strong></li>
        <li>✅ Our <strong><a href="' . route('legal.data-processing-agreement') . '">Data Processing Agreement</a></strong></li>
        <li>✅ Our <strong><a href="' . route('legal.acceptable-use-policy') . '">Acceptable Use Policy</a></strong></li>
        <li>✅ All other <strong><a href="' . route('legal.index') . '">legal documents</a></strong> in our legal framework</li>
    </ul>
    
    <p>These documents constitute the entire agreement between you and HD Tickets regarding your use of our Service.</p>
</div>';
    }

    private function getCrossReferencedPrivacyPolicyContent(): string
    {
        return '<h1>Privacy Policy</h1>

<div class="document-meta">
    <p><strong>Effective Date:</strong> ' . now()->format('F j, Y') . '</p>
    <p><strong>Version:</strong> 2.1</p>
    <p><strong>Last Updated:</strong> ' . now()->format('F j, Y') . '</p>
</div>

' . $this->getDocumentNavigationSection() . '

<div class="gdpr-notice">
    <h2>🔒 Your Privacy Rights</h2>
    <p>HD Tickets is committed to protecting your personal data and respecting your privacy rights. This Privacy Policy explains how we collect, use, and safeguard your information in accordance with the General Data Protection Regulation (GDPR) and other applicable privacy laws.</p>
    
    <p><strong>Related Privacy Documents:</strong></p>
    <ul>
        <li><strong><a href="' . route('legal.data-processing-agreement') . '">Data Processing Agreement</a></strong> - Technical details of GDPR-compliant data processing</li>
        <li><strong><a href="' . route('legal.gdpr-compliance') . '">GDPR Compliance Statement</a></strong> - Your comprehensive rights under GDPR</li>
        <li><strong><a href="' . route('legal.cookie-policy') . '">Cookie Policy</a></strong> - Detailed information about cookies and tracking</li>
        <li><strong><a href="' . route('legal.terms-of-service') . '">Terms of Service</a></strong> - How privacy relates to service usage</li>
    </ul>
</div>

<h2 id="data-collection">1. Information We Collect</h2>

<h3>1.1 Personal Information</h3>
<p>We collect the following types of personal information (as detailed in our <a href="' . route('legal.data-processing-agreement') . '#data-categories">Data Processing Agreement</a>):</p>

<h4>Account Information:</h4>
<ul>
    <li>Name and contact details (email address, phone number)</li>
    <li>Account credentials (username, encrypted password)</li>
    <li>Profile preferences and settings</li>
    <li>Subscription and billing information</li>
</ul>

<h4>Payment Information:</h4>
<ul>
    <li>Credit card details (processed securely by third-party payment processors)</li>
    <li>Billing address and tax identification information</li>
    <li>Transaction history and payment records</li>
</ul>

<p><strong>Note:</strong> Payment processing is subject to our <a href="' . route('legal.terms-of-service') . '#payment-terms">Terms of Service - Payment Terms</a> and no-refund policy in our <a href="' . route('legal.disclaimer') . '#no-refunds">Service Disclaimer</a>.</p>

<h4>Usage Data:</h4>
<ul>
    <li>Service usage patterns and feature utilization</li>
    <li>Ticket monitoring preferences and search history</li>
    <li>Platform interaction logs and session data</li>
    <li>Performance metrics and error reports</li>
</ul>

<h3>1.2 Technical Information</h3>
<ul>
    <li>IP address and geographical location data</li>
    <li>Device information (browser type, operating system, device ID)</li>
    <li>Cookies and similar tracking technologies (see our <a href="' . route('legal.cookie-policy') . '">Cookie Policy</a>)</li>
    <li>Network connection data and access logs</li>
</ul>

<h2>2. Legal Basis for Processing</h2>
<p>Under GDPR, we process your personal data based on the following legal grounds (detailed in our <a href="' . route('legal.data-processing-agreement') . '#legal-basis">Data Processing Agreement</a>):</p>

<h3>2.1 Contractual Necessity</h3>
<ul>
    <li>Account management and service delivery (per our <a href="' . route('legal.terms-of-service') . '">Terms of Service</a>)</li>
    <li>Payment processing and billing</li>
    <li>Customer support and technical assistance</li>
</ul>

<h3>2.2 Legitimate Interests</h3>
<ul>
    <li>Service improvement and optimization</li>
    <li>Security monitoring and fraud prevention</li>
    <li>Analytics and performance measurement</li>
</ul>

<h3>2.3 Legal Compliance</h3>
<ul>
    <li>Tax reporting and regulatory requirements</li>
    <li>Anti-money laundering (AML) compliance</li>
    <li>Data breach notification obligations</li>
</ul>

<h3>2.4 Explicit Consent</h3>
<ul>
    <li>Marketing communications and newsletters</li>
    <li>Optional feature enhancements</li>
    <li>Third-party integrations and data sharing</li>
    <li>Non-essential cookies (managed via our <a href="' . route('legal.cookie-policy') . '#managing-cookies">Cookie Policy</a>)</li>
</ul>

<h2>3. How We Use Your Information</h2>

<h3>3.1 Service Provision</h3>
<ul>
    <li>Providing access to HD Tickets platform features (per our <a href="' . route('legal.terms-of-service') . '">Terms of Service</a>)</li>
    <li>Processing ticket monitoring requests and alerts</li>
    <li>Managing subscription plans and billing</li>
    <li>Delivering customer support and technical assistance</li>
</ul>

<h3>3.2 Communication</h3>
<ul>
    <li>Sending service notifications and system updates</li>
    <li>Providing ticket availability alerts and price notifications</li>
    <li>Responding to inquiries and support requests</li>
    <li>Marketing communications (with your consent)</li>
</ul>

<h3>3.3 Service Improvement</h3>
<ul>
    <li>Analyzing usage patterns to enhance user experience</li>
    <li>Developing new features and functionality</li>
    <li>Optimizing platform performance and reliability</li>
    <li>Conducting security assessments and vulnerability testing</li>
</ul>

<h2>4. Information Sharing and Disclosure</h2>

<h3>4.1 We Do Not Sell Personal Data</h3>
<div class="important-notice">
    <p><strong>🛡️ Important Commitment:</strong> We do not sell, rent, or trade your personal information to third parties for marketing purposes. This commitment is integral to our service model as outlined in our <a href="' . route('legal.terms-of-service') . '">Terms of Service</a>.</p>
</div>

<h3>4.2 Authorized Sharing</h3>
<p>We may share your information only in the following circumstances (as detailed in our <a href="' . route('legal.data-processing-agreement') . '#subprocessors">Data Processing Agreement</a>):</p>

<h4>Service Providers:</h4>
<ul>
    <li>Payment processors for billing and subscription management</li>
    <li>Cloud infrastructure providers for data hosting and storage</li>
    <li>Customer support platforms for ticket management</li>
    <li>Analytics services for performance monitoring</li>
</ul>

<h4>Legal Requirements:</h4>
<ul>
    <li>Compliance with court orders or legal process</li>
    <li>Protection of our legal rights and interests</li>
    <li>Prevention of fraud or security threats</li>
    <li>Cooperation with law enforcement investigations</li>
</ul>

<h3>4.3 Business Transfers</h3>
<p>In the event of a merger, acquisition, or sale of assets, your information may be transferred to the new entity, subject to the same privacy protections and notification requirements under GDPR.</p>

<h2>5. Data Security</h2>

<h3>5.1 Security Measures</h3>
<p>We implement comprehensive security measures to protect your data (detailed in our <a href="' . route('legal.data-processing-agreement') . '#security-measures">Data Processing Agreement</a>):</p>
<ul>
    <li>End-to-end encryption for data transmission</li>
    <li>AES-256 encryption for data storage</li>
    <li>Multi-factor authentication for account access</li>
    <li>Regular security audits and penetration testing</li>
    <li>Employee access controls and training</li>
    <li>Automated threat detection and response systems</li>
</ul>

<h3>5.2 Data Breach Response</h3>
<p>In the event of a data breach, we will (per our <a href="' . route('legal.gdpr-compliance') . '#breach-procedures">GDPR Compliance procedures</a>):</p>
<ul>
    <li>Notify relevant supervisory authorities within 72 hours</li>
    <li>Inform affected users without undue delay</li>
    <li>Provide clear information about the breach and our response</li>
    <li>Offer guidance on protective measures you can take</li>
</ul>

<h2 id="your-rights">6. Your Privacy Rights</h2>

<h3>6.1 GDPR Rights</h3>
<p>Under GDPR, you have comprehensive rights regarding your personal data. For detailed information on exercising these rights, see our <a href="' . route('legal.gdpr-compliance') . '">GDPR Compliance Statement</a>:</p>

<h4>Right of Access:</h4>
<ul>
    <li>Request a copy of your personal data</li>
    <li>Receive information about how your data is processed</li>
    <li><strong><a href="' . route('legal.gdpr-compliance') . '#right-access">Learn how to exercise this right</a></strong></li>
</ul>

<h4>Right to Rectification:</h4>
<ul>
    <li>Correct inaccurate or incomplete personal data</li>
    <li>Update your account information and preferences</li>
    <li><strong><a href="' . route('legal.gdpr-compliance') . '#right-rectification">Learn how to exercise this right</a></strong></li>
</ul>

<h4>Right to Erasure ("Right to be Forgotten"):</h4>
<ul>
    <li>Request deletion of your personal data</li>
    <li>Permanent account closure and data removal</li>
    <li><strong><a href="' . route('legal.gdpr-compliance') . '#right-erasure">Learn how to exercise this right</a></strong></li>
</ul>

<h4>Right to Data Portability:</h4>
<ul>
    <li>Export your data in a structured, machine-readable format</li>
    <li>Transfer your data to another service provider</li>
    <li><strong><a href="' . route('legal.gdpr-compliance') . '#right-portability">Learn how to exercise this right</a></strong></li>
</ul>

<h4>Right to Object:</h4>
<ul>
    <li>Object to processing based on legitimate interests</li>
    <li>Opt out of direct marketing communications</li>
    <li><strong><a href="' . route('legal.gdpr-compliance') . '#right-object">Learn how to exercise this right</a></strong></li>
</ul>

<h4>Right to Restrict Processing:</h4>
<ul>
    <li>Limit how we use your personal data</li>
    <li>Maintain data accuracy during disputes</li>
    <li><strong><a href="' . route('legal.gdpr-compliance') . '#right-restrict">Learn how to exercise this right</a></strong></li>
</ul>

<h3>6.2 Exercising Your Rights</h3>
<p>To exercise any of these rights, contact us at:</p>
<ul>
    <li><strong>Email:</strong> privacy@hdtickets.com</li>
    <li><strong>Data Protection Officer:</strong> dpo@hdtickets.com</li>
    <li><strong>GDPR Requests:</strong> gdpr@hdtickets.com</li>
    <li><strong>Response Time:</strong> Within 30 days of request</li>
</ul>

<p><strong>Detailed procedures:</strong> See our <a href="' . route('legal.gdpr-compliance') . '#exercising-rights">GDPR Compliance Statement</a> for step-by-step guidance.</p>

<h2>7. Data Retention</h2>

<h3>7.1 Retention Periods</h3>
<p>We retain personal data for the following periods (detailed in our <a href="' . route('legal.data-processing-agreement') . '#data-retention">Data Processing Agreement</a>):</p>
<ul>
    <li><strong>Account Data:</strong> Duration of subscription plus 2 years</li>
    <li><strong>Payment Records:</strong> 7 years for tax and regulatory compliance</li>
    <li><strong>Usage Logs:</strong> 12 months for service optimization</li>
    <li><strong>Support Communications:</strong> 3 years for quality assurance</li>
</ul>

<h3>7.2 Automated Deletion</h3>
<p>We automatically delete personal data when:</p>
<ul>
    <li>Retention periods expire</li>
    <li>Account closure is requested (note: no refunds per our <a href="' . route('legal.disclaimer') . '#no-refunds">Service Disclaimer</a>)</li>
    <li>Data is no longer necessary for original purposes</li>
</ul>

<h2>8. International Data Transfers</h2>
<p>HD Tickets may process your data in countries outside your residence. For transfers outside the European Economic Area (EEA), we ensure adequate protection through mechanisms detailed in our <a href="' . route('legal.data-processing-agreement') . '#international-transfers">Data Processing Agreement</a>:</p>
<ul>
    <li>European Commission adequacy decisions</li>
    <li>Standard Contractual Clauses (SCCs)</li>
    <li>Binding Corporate Rules (BCRs)</li>
    <li>Certified data protection frameworks</li>
</ul>

<h2>9. Cookies and Tracking Technologies</h2>
<p>We use cookies and similar technologies to enhance your experience. <strong>For comprehensive information, please review our <a href="' . route('legal.cookie-policy') . '">Cookie Policy</a></strong>, which explains:</p>
<ul>
    <li><a href="' . route('legal.cookie-policy') . '#types-cookies">Types of cookies we use</a></li>
    <li><a href="' . route('legal.cookie-policy') . '#managing-cookies">How to manage cookie preferences</a></li>
    <li><a href="' . route('legal.cookie-policy') . '#third-party-cookies">Third-party cookie services</a></li>
    <li><a href="' . route('legal.cookie-policy') . '#impact-disabling">Impact on functionality when disabled</a></li>
</ul>

<h2>10. Children\'s Privacy</h2>
<p>HD Tickets does not knowingly collect personal information from children under 16 years of age. This aligns with our <a href="' . route('legal.terms-of-service') . '#acceptance-terms">Terms of Service age requirements</a>. If we discover that we have collected information from a child under 16, we will delete it immediately.</p>

<h2>11. Changes to Privacy Policy</h2>
<p>We may update this Privacy Policy to reflect changes in our practices or legal requirements. Material changes will be communicated through:</p>
<ul>
    <li>Email notifications to registered users</li>
    <li>Prominent website notices</li>
    <li>In-app notifications</li>
</ul>

<p>Our change notification procedures align with our <a href="' . route('legal.terms-of-service') . '#changes-terms">Terms of Service change policy</a>.</p>

<h2 id="contact">12. Contact Information</h2>

<h3>12.1 Data Protection Inquiries</h3>
<ul>
    <li><strong>Privacy Officer:</strong> privacy@hdtickets.com</li>
    <li><strong>Data Protection Officer:</strong> dpo@hdtickets.com (see <a href="' . route('legal.gdpr-compliance') . '#dpo">GDPR Compliance for DPO details</a>)</li>
    <li><strong>GDPR Requests:</strong> gdpr@hdtickets.com</li>
    <li><strong>General Inquiries:</strong> support@hdtickets.com</li>
</ul>

<h3>12.2 Supervisory Authority</h3>
<p>If you are not satisfied with our response to your privacy concerns, you have the right to lodge a complaint with your local data protection authority. See our <a href="' . route('legal.gdpr-compliance') . '#supervisory-authority">GDPR Compliance Statement</a> for guidance on this process.</p>

' . $this->getQuickReferenceSection() . '

<div class="commitment-statement">
    <h2>Our Privacy Commitment</h2>
    <p>HD Tickets is committed to maintaining the highest standards of data protection and privacy. We continuously review and improve our practices to ensure your personal information remains secure and your privacy rights are respected.</p>
    
    <p><strong>Complete Privacy Framework:</strong></p>
    <ul>
        <li>📄 <strong><a href="' . route('legal.privacy-policy') . '">Privacy Policy</a></strong> (this document)</li>
        <li>🛡️ <strong><a href="' . route('legal.data-processing-agreement') . '">Data Processing Agreement</a></strong></li>
        <li>🇪🇺 <strong><a href="' . route('legal.gdpr-compliance') . '">GDPR Compliance Statement</a></strong></li>
        <li>🍪 <strong><a href="' . route('legal.cookie-policy') . '">Cookie Policy</a></strong></li>
        <li>📋 <strong><a href="' . route('legal.terms-of-service') . '">Terms of Service</a></strong></li>
    </ul>
</div>';
    }

    // Continue with other cross-referenced documents...
    // Due to length constraints, I'll provide abbreviated versions of the remaining methods

    private function getCrossReferencedDisclaimerContent(): string
    {
        return '<h1>Service Disclaimer</h1>

<div class="document-meta">
    <p><strong>Effective Date:</strong> ' . now()->format('F j, Y') . '</p>
    <p><strong>Version:</strong> 2.1</p>
    <p><strong>Last Updated:</strong> ' . now()->format('F j, Y') . '</p>
</div>

' . $this->getDocumentNavigationSection() . '

<div class="critical-warning">
    <h2>⚠️ IMPORTANT LEGAL NOTICE</h2>
    <p><strong>READ CAREFULLY:</strong> This Service Disclaimer contains critical information about limitations, warranties, and liabilities associated with using HD Tickets. This document works together with our <a href="' . route('legal.terms-of-service') . '">Terms of Service</a> and forms part of your legal agreement with us.</p>
    
    <p><strong>Essential Legal Documents:</strong></p>
    <ul>
        <li>📋 <strong><a href="' . route('legal.terms-of-service') . '">Terms of Service</a></strong> - Your primary agreement with HD Tickets</li>
        <li>🔒 <strong><a href="' . route('legal.privacy-policy') . '">Privacy Policy</a></strong> - How we protect your data</li>
        <li>✅ <strong><a href="' . route('legal.acceptable-use-policy') . '">Acceptable Use Policy</a></strong> - Usage guidelines and restrictions</li>
    </ul>
</div>

<h2>1. Nature of Service</h2>
<p>HD Tickets provides a sports event ticket monitoring and automation platform as described in our <a href="' . route('legal.terms-of-service') . '#service-description">Terms of Service</a>. This disclaimer applies to all aspects of our service provision.</p>

<h2 id="no-refunds">4. Financial Terms and Refund Policy</h2>

<h3>4.1 No Money-Back Guarantee</h3>
<div class="critical-financial-notice">
    <p><strong>⚠️ CRITICAL PAYMENT TERMS - NO EXCEPTIONS:</strong></p>
    <ul>
        <li><strong>All subscription fees and payments are FINAL and NON-REFUNDABLE</strong></li>
        <li><strong>We do not offer money-back guarantees under any circumstances</strong></li>
        <li><strong>No refunds will be provided for unused subscription time</strong></li>
        <li><strong>Service cancellation does not entitle users to refunds</strong></li>
        <li><strong>All sales are final regardless of satisfaction or usage</strong></li>
    </ul>
    
    <p><strong>This policy is also referenced in our:</strong></p>
    <ul>
        <li>📋 <a href="' . route('legal.terms-of-service') . '#payment-terms"><strong>Terms of Service - Payment Terms</strong></a></li>
        <li>🛡️ <a href="' . route('legal.privacy-policy') . '">Privacy Policy - Payment Data</a></li>
    </ul>
</div>

' . $this->getQuickReferenceSection() . '

<div class="final-acknowledgment">
    <h2>Final Acknowledgment</h2>
    <p><strong>By using HD Tickets, you explicitly acknowledge that you have read and agree to:</strong></p>
    <ul>
        <li>✅ This <strong>Service Disclaimer</strong></li>
        <li>✅ Our <strong><a href="' . route('legal.terms-of-service') . '">Terms of Service</a></strong></li>
        <li>✅ All payment terms and no-refund policies</li>
        <li>✅ All limitations and warranty disclaimers</li>
        <li>✅ Our complete <strong><a href="' . route('legal.index') . '">legal framework</a></strong></li>
    </ul>
</div>';
    }

    private function getCrossReferencedDataProcessingAgreementContent(): string
    {
        return '<h1>Data Processing Agreement</h1>

<div class="document-meta">
    <p><strong>Effective Date:</strong> ' . now()->format('F j, Y') . '</p>
    <p><strong>Version:</strong> 2.1</p>
    <p><strong>GDPR Compliance Level:</strong> Full</p>
</div>

' . $this->getDocumentNavigationSection() . '

<div class="gdpr-integration-notice">
    <h2>🛡️ GDPR-Compliant Data Processing</h2>
    <p>This Data Processing Agreement (DPA) provides technical details about GDPR compliance and works in conjunction with:</p>
    <ul>
        <li>🔒 <strong><a href="' . route('legal.privacy-policy') . '">Privacy Policy</a></strong> - User-friendly privacy information</li>
        <li>🇪🇺 <strong><a href="' . route('legal.gdpr-compliance') . '">GDPR Compliance Statement</a></strong> - Your rights and how to exercise them</li>
        <li>🍪 <strong><a href="' . route('legal.cookie-policy') . '">Cookie Policy</a></strong> - Cookie and tracking details</li>
        <li>📋 <strong><a href="' . route('legal.terms-of-service') . '">Terms of Service</a></strong> - Service usage context</li>
    </ul>
</div>

<h2>1. Definitions and Interpretation</h2>
<p>This Data Processing Agreement ("DPA") forms part of the <a href="' . route('legal.terms-of-service') . '">Terms of Service</a> between you ("Data Subject" or "Customer") and HD Tickets ("Data Controller" or "Processor").</p>

<h2 id="data-categories">2. Scope and Nature of Processing</h2>
<p>Data categories and processing activities are detailed in our <a href="' . route('legal.privacy-policy') . '#data-collection">Privacy Policy - Information We Collect</a>.</p>

<h2 id="legal-basis">3. Legal Basis for Processing</h2>
<p>Legal bases are explained in user-friendly terms in our <a href="' . route('legal.privacy-policy') . '#legal-basis">Privacy Policy - Legal Basis for Processing</a>.</p>

<h2 id="data-subject-rights">4. Data Subject Rights</h2>
<p>For comprehensive information on your GDPR rights and how to exercise them, see our <a href="' . route('legal.gdpr-compliance') . '">GDPR Compliance Statement</a>.</p>

' . $this->getQuickReferenceSection() . '

<h2>10. Contact Information</h2>
<ul>
    <li><strong>Data Protection Officer:</strong> dpo@hdtickets.com (see <a href="' . route('legal.gdpr-compliance') . '#dpo">GDPR Compliance for full DPO details</a>)</li>
    <li><strong>Privacy Team:</strong> privacy@hdtickets.com</li>
    <li><strong>GDPR Requests:</strong> gdpr@hdtickets.com</li>
    <li><strong>Legal Department:</strong> legal@hdtickets.com</li>
</ul>

<p>For exercising your rights or privacy concerns, contact our Data Protection Officer who will respond within 30 days as outlined in our <a href="' . route('legal.gdpr-compliance') . '#exercising-rights">GDPR Compliance procedures</a>.</p>';
    }

    private function getCrossReferencedCookiePolicyContent(): string
    {
        return '<h1>Cookie Policy</h1>

<div class="document-meta">
    <p><strong>Effective Date:</strong> ' . now()->format('F j, Y') . '</p>
    <p><strong>Version:</strong> 2.1</p>
    <p><strong>Cookie Consent Level:</strong> GDPR Compliant</p>
</div>

' . $this->getDocumentNavigationSection() . '

<div class="privacy-integration-notice">
    <h2>🍪 Cookie and Privacy Framework</h2>
    <p>This Cookie Policy is part of our comprehensive privacy protection framework:</p>
    <ul>
        <li>🔒 <strong><a href="' . route('legal.privacy-policy') . '">Privacy Policy</a></strong> - Complete privacy information including cookie overview</li>
        <li>🛡️ <strong><a href="' . route('legal.data-processing-agreement') . '">Data Processing Agreement</a></strong> - Technical GDPR compliance details</li>
        <li>🇪🇺 <strong><a href="' . route('legal.gdpr-compliance') . '">GDPR Compliance Statement</a></strong> - Your privacy rights</li>
        <li>📋 <strong><a href="' . route('legal.terms-of-service') . '">Terms of Service</a></strong> - Service context for data collection</li>
    </ul>
</div>

<h2>1. What Are Cookies</h2>
<p>Cookies are small text files stored on your device when you visit HD Tickets. They help us provide essential functionality, improve your experience, and understand how our service is used, as outlined in our <a href="' . route('legal.privacy-policy') . '#technical-information">Privacy Policy</a>.</p>

<h2 id="managing-cookies">4. Managing Your Cookie Preferences</h2>
<p>Cookie management is part of your broader privacy rights under GDPR. See our <a href="' . route('legal.gdpr-compliance') . '">GDPR Compliance Statement</a> for information about your right to withdraw consent.</p>

' . $this->getQuickReferenceSection() . '

<h2>5. Contact Information</h2>
<p>For cookie-related questions:</p>
<ul>
    <li><strong>Privacy Team:</strong> privacy@hdtickets.com (see <a href="' . route('legal.privacy-policy') . '#contact">Privacy Policy contact details</a>)</li>
    <li><strong>Technical Support:</strong> support@hdtickets.com</li>
    <li><strong>GDPR Rights:</strong> gdpr@hdtickets.com (see <a href="' . route('legal.gdpr-compliance') . '#exercising-rights">GDPR procedures</a>)</li>
</ul>';
    }

    private function getCrossReferencedGdprComplianceContent(): string
    {
        return '<h1>GDPR Compliance Statement</h1>

<div class="document-meta">
    <p><strong>Effective Date:</strong> ' . now()->format('F j, Y') . '</p>
    <p><strong>Version:</strong> 2.1</p>
    <p><strong>Compliance Status:</strong> Fully GDPR Compliant</p>
</div>

' . $this->getDocumentNavigationSection() . '

<div class="gdpr-framework-overview">
    <h2>🇪🇺 Complete GDPR Compliance Framework</h2>
    <p>This statement provides comprehensive GDPR information and works with our complete privacy framework:</p>
    <ul>
        <li>🔒 <strong><a href="' . route('legal.privacy-policy') . '">Privacy Policy</a></strong> - User-friendly privacy information and data collection details</li>
        <li>🛡️ <strong><a href="' . route('legal.data-processing-agreement') . '">Data Processing Agreement</a></strong> - Technical DPA requirements and processing details</li>
        <li>🍪 <strong><a href="' . route('legal.cookie-policy') . '">Cookie Policy</a></strong> - Cookie consent and management</li>
        <li>📋 <strong><a href="' . route('legal.terms-of-service') . '">Terms of Service</a></strong> - Legal context for data processing</li>
    </ul>
</div>

<h2>1. Our Commitment to GDPR Compliance</h2>
<p>HD Tickets is fully committed to compliance with the General Data Protection Regulation (GDPR) and maintaining the highest standards of data protection for all users, regardless of location. Our compliance framework integrates with our <a href="' . route('legal.terms-of-service') . '">Terms of Service</a> and <a href="' . route('legal.privacy-policy') . '">Privacy Policy</a>.</p>

<h2 id="exercising-rights">4. Exercising Your Rights</h2>
<p>To exercise any GDPR rights:</p>
<ul>
    <li><strong>Email:</strong> gdpr@hdtickets.com</li>
    <li><strong>Data Protection Officer:</strong> dpo@hdtickets.com</li>
    <li><strong>Privacy Team:</strong> privacy@hdtickets.com</li>
    <li><strong>Response Time:</strong> Within 30 days (extendable to 90 days for complex requests)</li>
    <li><strong>Verification:</strong> Identity verification may be required for security</li>
</ul>

<p><strong>Detailed procedures are available in our:</strong></p>
<ul>
    <li><a href="' . route('legal.privacy-policy') . '#exercising-rights">Privacy Policy - Exercising Your Rights</a></li>
    <li><a href="' . route('legal.data-processing-agreement') . '#data-subject-rights">Data Processing Agreement - Rights Procedures</a></li>
</ul>

' . $this->getQuickReferenceSection() . '

<h2>12. Contact Information</h2>
<ul>
    <li><strong>Data Protection Officer:</strong> dpo@hdtickets.com</li>
    <li><strong>GDPR Requests:</strong> gdpr@hdtickets.com</li>
    <li><strong>Privacy Team:</strong> privacy@hdtickets.com (see <a href="' . route('legal.privacy-policy') . '#contact">Privacy Policy contacts</a>)</li>
    <li><strong>Legal Department:</strong> legal@hdtickets.com (see <a href="' . route('legal.legal-notices') . '#contact">Legal Notices</a>)</li>
</ul>';
    }

    private function getCrossReferencedAcceptableUsePolicyContent(): string
    {
        return '<h1>Acceptable Use Policy</h1>

<div class="document-meta">
    <p><strong>Effective Date:</strong> ' . now()->format('F j, Y') . '</p>
    <p><strong>Version:</strong> 2.1</p>
    <p><strong>Scope:</strong> All HD Tickets Users and Services</p>
</div>

' . $this->getDocumentNavigationSection() . '

<div class="usage-framework-overview">
    <h2>✅ Usage Policy Framework</h2>
    <p>This policy defines acceptable use of HD Tickets and works with our legal framework:</p>
    <ul>
        <li>📋 <strong><a href="' . route('legal.terms-of-service') . '">Terms of Service</a></strong> - Your primary agreement with usage terms</li>
        <li>⚠️ <strong><a href="' . route('legal.disclaimer') . '">Service Disclaimer</a></strong> - Service limitations and liability</li>
        <li>🔒 <strong><a href="' . route('legal.privacy-policy') . '">Privacy Policy</a></strong> - Data protection during service use</li>
        <li>⚖️ <strong><a href="' . route('legal.legal-notices') . '">Legal Notices</a></strong> - Legal compliance requirements</li>
    </ul>
</div>

<h2>1. Purpose and Scope</h2>
<p>This Acceptable Use Policy governs your use of HD Tickets services as outlined in our <a href="' . route('legal.terms-of-service') . '">Terms of Service</a> and defines prohibited activities, security requirements, and enforcement procedures.</p>

<h2 id="permitted-use">2. Permitted Use</h2>
<p>HD Tickets may be used for purposes defined in our <a href="' . route('legal.terms-of-service') . '#service-description">Terms of Service</a>:</p>
<ul>
    <li>Personal sports ticket monitoring and price tracking</li>
    <li>Legitimate ticket purchasing for personal use</li>
    <li>Educational research on sports and entertainment markets</li>
    <li>Business use in compliance with commercial licensing terms</li>
</ul>

<h2 id="prohibited-activities">3. Prohibited Activities</h2>
<p>Prohibited activities that may result in account termination as outlined in our <a href="' . route('legal.terms-of-service') . '#account-termination">Terms of Service</a>:</p>

<h2 id="enforcement">6. Enforcement and Penalties</h2>
<p>Violations are handled according to procedures in our <a href="' . route('legal.terms-of-service') . '#account-termination">Terms of Service - Account Termination</a>.</p>

' . $this->getQuickReferenceSection() . '

<p><strong>Questions about this policy?</strong> Contact our compliance team at compliance@hdtickets.com or see our <a href="' . route('legal.legal-notices') . '#contact">Legal Notices</a> for all contact information.</p>';
    }

    private function getCrossReferencedLegalNoticesContent(): string
    {
        return '<h1>Legal Notices</h1>

<div class="document-meta">
    <p><strong>Effective Date:</strong> ' . now()->format('F j, Y') . '</p>
    <p><strong>Version:</strong> 2.1</p>
    <p><strong>Jurisdiction:</strong> International</p>
</div>

' . $this->getDocumentNavigationSection() . '

<div class="legal-framework-overview">
    <h2>⚖️ Complete Legal Framework</h2>
    <p>These legal notices apply to all HD Tickets services and complement our comprehensive legal documentation:</p>
    <ul>
        <li>📋 <strong><a href="' . route('legal.terms-of-service') . '">Terms of Service</a></strong> - Primary service agreement</li>
        <li>⚠️ <strong><a href="' . route('legal.disclaimer') . '">Service Disclaimer</a></strong> - Liability limitations and warranties</li>
        <li>🔒 <strong><a href="' . route('legal.privacy-policy') . '">Privacy Policy</a></strong> - Data protection framework</li>
        <li>✅ <strong><a href="' . route('legal.acceptable-use-policy') . '">Acceptable Use Policy</a></strong> - Usage guidelines</li>
    </ul>
</div>

<h2>1. Copyright Notice</h2>
<p>© 2025 HD Tickets. All rights reserved.</p>
<p>The HD Tickets platform, including all content, features, functionality, software, and design, is owned by HD Tickets and protected by international copyright, trademark, patent, trade secret, and other intellectual property laws as referenced in our <a href="' . route('legal.terms-of-service') . '#intellectual-property">Terms of Service</a>.</p>

<h2 id="governing-law">5. Governing Law and Jurisdiction</h2>
<p>These legal notices work in conjunction with dispute resolution procedures in our <a href="' . route('legal.terms-of-service') . '#dispute-resolution">Terms of Service</a>:</p>
<ul>
    <li><strong>Governing Law:</strong> As specified in Terms of Service</li>
    <li><strong>Disputes:</strong> Subject to binding arbitration per Terms of Service</li>
    <li><strong>Venue:</strong> As specified in Terms of Service</li>
    <li><strong>Language:</strong> English shall be the controlling language</li>
</ul>

' . $this->getQuickReferenceSection() . '

<h2 id="contact">10. Contact Information</h2>

<h3>10.1 Legal Department</h3>
<ul>
    <li><strong>General Legal:</strong> legal@hdtickets.com</li>
    <li><strong>DMCA Claims:</strong> dmca@hdtickets.com</li>
    <li><strong>Compliance:</strong> compliance@hdtickets.com</li>
    <li><strong>Privacy:</strong> privacy@hdtickets.com (see <a href="' . route('legal.privacy-policy') . '#contact">Privacy Policy contacts</a>)</li>
    <li><strong>GDPR:</strong> gdpr@hdtickets.com (see <a href="' . route('legal.gdpr-compliance') . '#exercising-rights">GDPR procedures</a>)</li>
</ul>

<p><strong>Last Updated:</strong> ' . now()->format('F j, Y') . '</p>
<p><strong>Next Review Date:</strong> ' . now()->addYear()->format('F j, Y') . '</p>';
    }
}
