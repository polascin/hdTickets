<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LegalDocument;
use Illuminate\Database\Seeder;

class LegalDocumentSeeder extends Seeder
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
                'content'             => $this->getTermsOfServiceContent(),
                'version'             => '1.0',
                'is_active'           => TRUE,
                'requires_acceptance' => TRUE,
                'effective_date'      => now(),
            ],
            [
                'type'                => LegalDocument::TYPE_DISCLAIMER,
                'title'               => 'Service Disclaimer',
                'slug'                => 'disclaimer',
                'content'             => $this->getDisclaimerContent(),
                'version'             => '1.0',
                'is_active'           => TRUE,
                'requires_acceptance' => TRUE,
                'effective_date'      => now(),
            ],
            [
                'type'                => LegalDocument::TYPE_PRIVACY_POLICY,
                'title'               => 'Privacy Policy',
                'slug'                => 'privacy-policy',
                'content'             => $this->getPrivacyPolicyContent(),
                'version'             => '1.0',
                'is_active'           => TRUE,
                'requires_acceptance' => FALSE,
                'effective_date'      => now(),
            ],
            [
                'type'                => LegalDocument::TYPE_DATA_PROCESSING_AGREEMENT,
                'title'               => 'Data Processing Agreement',
                'slug'                => 'data-processing-agreement',
                'content'             => $this->getDataProcessingAgreementContent(),
                'version'             => '1.0',
                'is_active'           => TRUE,
                'requires_acceptance' => TRUE,
                'effective_date'      => now(),
            ],
            [
                'type'                => LegalDocument::TYPE_COOKIE_POLICY,
                'title'               => 'Cookie Policy',
                'slug'                => 'cookie-policy',
                'content'             => $this->getCookiePolicyContent(),
                'version'             => '1.0',
                'is_active'           => TRUE,
                'requires_acceptance' => TRUE,
                'effective_date'      => now(),
            ],
            [
                'type'                => LegalDocument::TYPE_GDPR_COMPLIANCE,
                'title'               => 'GDPR Compliance Statement',
                'slug'                => 'gdpr-compliance',
                'content'             => $this->getGdprComplianceContent(),
                'version'             => '1.0',
                'is_active'           => TRUE,
                'requires_acceptance' => FALSE,
                'effective_date'      => now(),
            ],
            [
                'type'                => LegalDocument::TYPE_ACCEPTABLE_USE_POLICY,
                'title'               => 'Acceptable Use Policy',
                'slug'                => 'acceptable-use-policy',
                'content'             => $this->getAcceptableUsePolicyContent(),
                'version'             => '1.0',
                'is_active'           => TRUE,
                'requires_acceptance' => FALSE,
                'effective_date'      => now(),
            ],
            [
                'type'                => LegalDocument::TYPE_LEGAL_NOTICES,
                'title'               => 'Legal Notices',
                'slug'                => 'legal-notices',
                'content'             => $this->getLegalNoticesContent(),
                'version'             => '1.0',
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

    private function getTermsOfServiceContent(): string
    {
        return '<h1>Terms of Service</h1>

<p><strong>Effective Date:</strong> ' . now()->format('F j, Y') . '</p>

<h2>1. Service Description</h2>
<p>HD Tickets is a professional sports event ticket monitoring, analysis, and purchase facilitation platform. Our service provides automated ticket discovery, price tracking, availability alerts, and purchasing assistance for sporting events across multiple platforms.</p>

<h2>2. Service Provided "As Is"</h2>
<p><strong>IMPORTANT DISCLAIMER:</strong> This service is provided "AS IS" without any warranties, express or implied. We do not guarantee the availability, accuracy, or completeness of ticket information, pricing data, or third-party platform connectivity.</p>

<h2>3. No Warranty</h2>
<p>HD Tickets explicitly disclaims all warranties, including but not limited to:
- Merchantability and fitness for a particular purpose
- Non-infringement of third-party rights
- Accuracy, completeness, or timeliness of information
- Availability of tickets or pricing
- Uninterrupted or error-free service operation
- Compatibility with third-party ticketing platforms</p>

<h2>4. No Money-Back Guarantee</h2>
<p><strong>FINAL PAYMENTS:</strong> All subscription payments, service charges, and fees are final and non-refundable. We do not offer refunds, credits, chargebacks, or money-back guarantees under any circumstances, including but not limited to service dissatisfaction, technical issues, or third-party platform changes.</p>

<h2>5. User Responsibilities and Conduct</h2>
<p>Users must:
- Provide accurate and up-to-date account information
- Comply with all applicable local, national, and international laws
- Respect third-party websites\' terms of service and usage policies
- Not engage in fraudulent, deceptive, or illegal activities
- Not attempt to circumvent security measures or access restrictions
- Not resell or distribute access to our service without authorization
- Use the service solely for personal, non-commercial purposes unless otherwise agreed</p>

<h2>6. Platform Integration and Third-Party Services</h2>
<p>HD Tickets integrates with various third-party ticketing platforms. We are not responsible for:
- Changes to third-party APIs, terms, or availability
- Third-party platform outages or technical issues
- Accuracy of information provided by external sources
- Transaction processing by external payment systems</p>

<h2>7. Limitation of Liability</h2>
<p>In no event shall HD Tickets, its officers, directors, employees, or agents be liable for any direct, indirect, incidental, special, consequential, or punitive damages, including but not limited to loss of profits, data, use, goodwill, or other intangible losses, arising from your use of our service, even if advised of the possibility of such damages.</p>

<h2>8. Subscription Terms and Billing</h2>
<p>Subscription fees are charged in advance on a recurring basis. Failure to pay subscription fees may result in service suspension or termination. Users are responsible for maintaining current payment information.</p>

<h2>9. Service Modifications and Termination</h2>
<p>We reserve the right to modify, suspend, or terminate the service at any time without prior notice. We may also terminate user accounts for violations of these terms.</p>

<h2>10. Acceptance and Binding Agreement</h2>
<p>By creating an account or using our service, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service. Continued use constitutes ongoing acceptance of these terms.</p>';
    }

    private function getDisclaimerContent(): string
    {
        return '<h1>Service Disclaimer</h1>

<p><strong>Effective Date:</strong> ' . now()->format('F j, Y') . '</p>

<h2>Important Notice</h2>
<p>The HD Tickets platform is provided for informational and automation purposes only. By using our service, you acknowledge and agree to the following disclaimers:</p>

<h2>1. "As Is" Service</h2>
<p>This service is provided "AS IS" and "AS AVAILABLE" without warranties of any kind, either express or implied.</p>

<h2>2. No Warranty</h2>
<p>We make no warranties, representations, or guarantees regarding:
- Service availability or uptime
- Accuracy of ticket information
- Success of ticket purchases
- Compatibility with third-party websites</p>

<h2>3. No Money-Back Guarantee</h2>
<p><strong>FINAL PAYMENTS:</strong> All subscription fees, service charges, and payments are final and non-refundable. We do not offer money-back guarantees under any circumstances.</p>

<h2>4. Third-Party Services</h2>
<p>Our service interacts with third-party ticketing platforms. We are not responsible for changes to their terms, availability, or functionality.</p>

<h2>5. Risk Acknowledgment</h2>
<p>Users acknowledge that ticket purchasing involves inherent risks, and HD Tickets shall not be liable for any losses incurred.</p>';
    }

    private function getPrivacyPolicyContent(): string
    {
        return '<h1>Privacy Policy</h1>

<p><strong>Effective Date:</strong> ' . now()->format('F j, Y') . '</p>

<h2>1. Information We Collect</h2>
<p>We collect the following types of information:
- Account information (name, email, phone)
- Payment information (processed by third parties)
- Usage data and preferences
- Device and browser information</p>

<h2>2. How We Use Information</h2>
<p>We use your information to:
- Provide and improve our services
- Process payments and subscriptions
- Send important notifications
- Ensure account security</p>

<h2>3. Information Sharing</h2>
<p>We do not sell, trade, or rent your personal information to third parties, except:
- With your explicit consent
- To process payments (payment processors)
- When required by law</p>

<h2>4. Data Security</h2>
<p>We implement industry-standard security measures to protect your information, including encryption and secure storage.</p>

<h2>5. Your Rights</h2>
<p>You have the right to:
- Access your personal information
- Request corrections or deletions
- Export your data
- Withdraw consent</p>

<h2>6. Contact Us</h2>
<p>For privacy-related questions, contact us at privacy@hdtickets.com</p>';
    }

    private function getDataProcessingAgreementContent(): string
    {
        return '<h1>Data Processing Agreement</h1>

<p><strong>Effective Date:</strong> ' . now()->format('F j, Y') . '</p>

<h2>1. Data Processing Activities</h2>
<p>HD Tickets processes personal data for the following purposes:
- Account management and authentication
- Service delivery and support
- Payment processing
- Communication and notifications</p>

<h2>2. Legal Basis for Processing</h2>
<p>We process your data based on:
- Contractual necessity (service delivery)
- Legitimate interests (service improvement)
- Legal compliance (regulatory requirements)
- Your explicit consent (marketing communications)</p>

<h2>3. Data Retention</h2>
<p>We retain personal data for as long as necessary to provide services and comply with legal obligations.</p>

<h2>4. International Transfers</h2>
<p>Your data may be processed in countries outside your residence. We ensure adequate protection through appropriate safeguards.</p>

<h2>5. Your Rights</h2>
<p>Under applicable data protection laws, you have rights including access, rectification, erasure, and portability.</p>

<h2>6. Data Controller</h2>
<p>HD Tickets acts as the data controller for personal information processed in connection with our services.</p>';
    }

    private function getCookiePolicyContent(): string
    {
        return '<h1>Cookie Policy</h1>

<p><strong>Effective Date:</strong> ' . now()->format('F j, Y') . '</p>

<h2>1. What Are Cookies</h2>
<p>Cookies are small text files stored on your device when you visit our website. They help us provide and improve our services.</p>

<h2>2. Types of Cookies We Use</h2>
<p><strong>Essential Cookies:</strong> Required for basic website functionality
<strong>Performance Cookies:</strong> Help us understand how you use our service
<strong>Preference Cookies:</strong> Remember your settings and preferences
<strong>Security Cookies:</strong> Protect against fraud and ensure secure access</p>

<h2>3. Third-Party Cookies</h2>
<p>We may use third-party services that place cookies on your device for analytics and payment processing.</p>

<h2>4. Managing Cookies</h2>
<p>You can control cookies through your browser settings. However, disabling certain cookies may affect website functionality.</p>

<h2>5. Updates</h2>
<p>We may update this Cookie Policy periodically. Changes will be posted on this page with an updated effective date.</p>';
    }

    private function getGdprComplianceContent(): string
    {
        return '<h1>GDPR Compliance Statement</h1>

<p><strong>Effective Date:</strong> ' . now()->format('F j, Y') . '</p>

<h2>1. Commitment to GDPR</h2>
<p>HD Tickets is committed to protecting your personal data in accordance with the General Data Protection Regulation (GDPR).</p>

<h2>2. Your Rights Under GDPR</h2>
<p>You have the following rights:
- Right to be informed
- Right of access
- Right to rectification
- Right to erasure
- Right to restrict processing
- Right to data portability
- Right to object
- Rights related to automated decision-making</p>

<h2>3. Data Protection Officer</h2>
<p>For GDPR-related inquiries, contact our Data Protection Officer at dpo@hdtickets.com</p>

<h2>4. Lawful Basis for Processing</h2>
<p>We process personal data based on legitimate interests, contractual necessity, legal compliance, and consent.</p>

<h2>5. Data Breach Notification</h2>
<p>We will notify relevant authorities and affected individuals of data breaches within 72 hours when required by law.</p>';
    }

    private function getAcceptableUsePolicyContent(): string
    {
        return '<h1>Acceptable Use Policy</h1>

<p><strong>Effective Date:</strong> ' . now()->format('F j, Y') . '</p>

<h2>1. Permitted Use</h2>
<p>You may use HD Tickets for legitimate ticket monitoring and purchasing activities in compliance with all applicable laws.</p>

<h2>2. Prohibited Activities</h2>
<p>You may not:
- Violate any laws or regulations
- Engage in fraudulent activities
- Attempt to harm or disrupt our services
- Reverse engineer our platform
- Share account credentials
- Use the service for commercial resale without permission</p>

<h2>3. Account Suspension</h2>
<p>We reserve the right to suspend or terminate accounts that violate this policy.</p>

<h2>4. Reporting Violations</h2>
<p>Report policy violations to abuse@hdtickets.com</p>';
    }

    private function getLegalNoticesContent(): string
    {
        return '<h1>Legal Notices</h1>

<p><strong>Effective Date:</strong> ' . now()->format('F j, Y') . '</p>

<h2>1. Copyright Notice</h2>
<p>© 2025 HD Tickets. All rights reserved. The HD Tickets platform and its contents are protected by copyright and other intellectual property laws.</p>

<h2>2. Trademark Notice</h2>
<p>HD Tickets and related logos are trademarks of HD Tickets. All other trademarks are property of their respective owners.</p>

        <h2>3. Governing Law</h2>
<p>These terms are governed by the laws of the United Kingdom, without regard to conflict of law principles.</p>

<h2>4. Severability</h2>
<p>If any provision is found unenforceable, the remaining provisions will continue in full force and effect.</p>

<h2>5. Contact Information</h2>
<p>For legal matters, contact us at legal@hdtickets.com</p>

<h2>6. Updates</h2>
<p>We reserve the right to update these legal notices at any time. Material changes will be communicated to users.</p>';
    }
}
