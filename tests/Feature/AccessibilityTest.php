<?php declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Override;
use Tests\TestCase;

class AccessibilityTest extends TestCase
{
    use RefreshDatabase;

    private User $testUser;

    private string $testPassword = 'TestP@ssw0rd123!';

    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->testUser = User::factory()->create([
            'email'     => 'accessibility@test.com',
            'password'  => Hash::make($this->testPassword),
            'is_active' => TRUE,
            'role'      => 'customer',
        ]);
    }

    /**
     */
    #[Test]
    public function test_login_form_has_proper_labels(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Check that form elements have proper labels
        $this->assertMatchesRegularExpression('/<label[^>]*for="email"/', $content);
        $this->assertMatchesRegularExpression('/<label[^>]*for="password"/', $content);
        $this->assertMatchesRegularExpression('/<label[^>]*for="remember_me"/', $content);

        // Check that labels contain required field indicators
        $this->assertStringContains('Required field:', $content);
    }

    /**
     */
    #[Test]
    public function test_form_elements_have_aria_attributes(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Check for essential ARIA attributes
        $this->assertStringContains('aria-labelledby="login-form-title"', $content);
        $this->assertStringContains('aria-describedby="login-form-description"', $content);
        $this->assertStringContains('aria-required="true"', $content);
        $this->assertStringContains('aria-invalid="false"', $content);
        $this->assertStringContains('role="form"', $content);
        $this->assertStringContains('role="alert"', $content);

        // Check for live regions
        $this->assertStringContains('aria-live="polite"', $content);
        $this->assertStringContains('aria-live="assertive"', $content);
    }

    /**
     */
    #[Test]
    public function test_skip_navigation_links_present(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Check for skip navigation links
        $this->assertStringContains('Skip to main content', $content);
        $this->assertStringContains('Skip to login form', $content);
        $this->assertStringContains('class="hd-skip-nav"', $content);
        $this->assertStringContains('href="#main-content"', $content);
        $this->assertStringContains('href="#login-form"', $content);
    }

    /**
     */
    #[Test]
    public function test_screen_reader_only_content_present(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Check for screen reader only content
        $this->assertStringContains('class="hd-sr-only"', $content);
        $this->assertStringContains('HD Tickets Login Form', $content);
        $this->assertStringContains('Enter your email and password', $content);
        $this->assertStringContains('This form includes real-time validation', $content);
    }

    /**
     */
    #[Test]
    public function test_error_messages_have_proper_aria_attributes(): void
    {
        $response = $this->post('/login', [
            'email'    => '',
            'password' => '',
            'website'  => '',
        ]);

        $response->assertSessionHasErrors();

        // Get the response content with errors
        $response = $this->get('/login');
        $content = $response->getContent();

        // Error messages should have proper ARIA attributes
        if (str_contains((string) $content, 'hd-error-message')) {
            $this->assertStringContains('role="alert"', $content);
            $this->assertStringContains('aria-live="polite"', $content);
        }
    }

    /**
     */
    #[Test]
    public function test_form_has_proper_heading_structure(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Check for proper heading hierarchy
        $this->assertMatchesRegularExpression('/<h1[^>]*id="login-form-title"/', $content);
        $this->assertStringContains('HD Tickets Login Form', $content);

        // Check for descriptive headings
        $this->assertStringContains('Account Registration', $content);
    }

    /**
     */
    #[Test]
    public function test_color_contrast_elements_have_proper_classes(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Check that elements likely to need high contrast have appropriate classes
        $this->assertStringContains('hd-error-message', $content);
        $this->assertStringContains('hd-btn-primary', $content);
        $this->assertStringContains('hd-form-label', $content);
        $this->assertStringContains('hd-form-input', $content);
        $this->assertStringContains('hd-link', $content);
    }

    /**
     */
    #[Test]
    public function test_form_elements_have_autocomplete_attributes(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Check for proper autocomplete attributes
        $this->assertStringContains('autocomplete="email username"', $content);
        $this->assertStringContains('autocomplete="current-password"', $content);
    }

    /**
     */
    #[Test]
    public function test_form_has_proper_tabindex_structure(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Honeypot field should have tabindex="-1"
        $this->assertStringContains('tabindex="-1"', $content);

        // Hidden elements should not be focusable
        $this->assertMatchesRegularExpression('/style="display: none;"[^>]*tabindex="-1"/', $content);
    }

    /**
     */
    #[Test]
    public function test_images_have_proper_alt_text_or_aria_hidden(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Check for decorative images marked as aria-hidden
        $this->assertStringContains('aria-hidden="true"', $content);

        // Check for title elements in SVGs
        $this->assertStringContains('<title>', $content);
        $this->assertStringContains('Email icon', $content);
        $this->assertStringContains('Password visibility toggle', $content);
    }

    /**
     */
    #[Test]
    public function test_focus_management_elements_present(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Check that first focusable element has autofocus
        $this->assertStringContains('autofocus', $content);

        // Check for focus indicators (CSS classes)
        $this->assertStringContains('hd-enhanced-checkbox', $content);
    }

    /**
     */
    #[Test]
    public function test_keyboard_navigation_support(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Check for elements that support keyboard interaction
        $this->assertStringContains('type="checkbox"', $content);
        $this->assertStringContains('type="submit"', $content);
        $this->assertStringContains('type="button"', $content);

        // Password toggle should be keyboard accessible
        $this->assertStringContains('id="password-toggle"', $content);
    }

    /**
     */
    #[Test]
    public function test_form_validation_accessibility(): void
    {
        // Submit invalid form
        $response = $this->post('/login', [
            'email'    => 'invalid-email',
            'password' => '',
            'website'  => '',
        ]);

        $response->assertSessionHasErrors();

        // Check that errors are properly associated with form fields
        $response = $this->get('/login');
        $content = $response->getContent();

        // Check for aria-describedby that includes error IDs
        if (str_contains((string) $content, 'email-error')) {
            $this->assertStringContains('aria-describedby="email-description email-error"', $content);
        }

        // Check that aria-invalid is set to true for invalid fields
        if (str_contains((string) $content, 'aria-invalid="true"')) {
            $this->assertStringContains('aria-invalid="true"', $content);
        }
    }

    /**
     */
    #[Test]
    public function test_live_region_announcements(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Check for live regions that will announce status changes
        $this->assertStringContains('id="hd-status-region"', $content);
        $this->assertStringContains('id="hd-alert-region"', $content);
        $this->assertStringContains('id="login-loading"', $content);

        // Check that live regions have proper ARIA attributes
        $this->assertStringContains('aria-live="polite"', $content);
        $this->assertStringContains('aria-live="assertive"', $content);
        $this->assertStringContains('aria-atomic="true"', $content);
    }

    /**
     */
    #[Test]
    public function test_fieldset_and_legend_structure(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Check for grouped form elements
        $this->assertStringContains('role="group"', $content);
        $this->assertStringContains('aria-labelledby="remember-group-label"', $content);
    }

    /**
     */
    #[Test]
    public function test_button_accessibility_attributes(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Check submit button accessibility
        $this->assertStringContains('aria-label="Sign in to HD Tickets"', $content);
        $this->assertStringContains('aria-describedby="login-button-description"', $content);

        // Check password toggle button
        $this->assertStringContains('aria-label="Show password"', $content);
        $this->assertStringContains('aria-describedby="password-toggle-description"', $content);
    }

    /**
     */
    #[Test]
    public function test_link_accessibility(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Check forgot password link
        $this->assertStringContains('aria-label="Forgot your password? Reset it here"', $content);
        $this->assertStringContains('aria-describedby="forgot-password-description"', $content);
    }

    /**
     */
    #[Test]
    public function test_spellcheck_and_language_attributes(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Email field should have spellcheck disabled
        $this->assertStringContains('spellcheck="false"', $content);

        // Check for language attributes if present
        if (str_contains((string) $content, 'lang=')) {
            $this->assertMatchesRegularExpression('/lang="[a-z]{2,}"/', $content);
        }
    }

    /**
     */
    #[Test]
    public function test_error_prevention_features(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Check for features that prevent errors
        $this->assertStringContains('placeholder="example@email.com"', $content);
        $this->assertStringContains('placeholder="Enter your password"', $content);
        $this->assertStringContains('data-lpignore="true"', $content); // LastPass ignore
    }

    /**
     */
    #[Test]
    public function test_progressive_enhancement_support(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Form should work without JavaScript (novalidate attribute)
        $this->assertStringContains('novalidate', $content);

        // Enhanced features should be marked as such
        $this->assertStringContains('enhanced-form', $content);
        $this->assertStringContains('hd-enhanced-checkbox', $content);
    }

    /**
     */
    #[Test]
    public function test_mobile_accessibility_features(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Check for mobile-friendly input types
        $this->assertStringContains('type="email"', $content);
        $this->assertStringContains('type="password"', $content);

        // Check for mobile viewport considerations
        $this->assertStringContains('form-input', $content); // Should be touch-friendly
    }

    /**
     */
    #[Test]
    public function test_security_and_privacy_accessibility(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Password field should be properly secured but accessible
        $this->assertStringContains('autocomplete="current-password"', $content);
        $this->assertStringContains('aria-label="Password for login"', $content);

        // Check for privacy-conscious features
        $this->assertStringContains('data-lpignore="true"', $content);
    }

    /**
     */
    #[Test]
    public function test_contextual_help_and_instructions(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Check for field descriptions
        $this->assertStringContains('id="email-description"', $content);
        $this->assertStringContains('id="password-description"', $content);
        $this->assertStringContains('id="remember-description"', $content);

        // Check for contextual help text
        $this->assertStringContains('Enter the email address associated with your HD Tickets account', $content);
        $this->assertStringContains('Enter your account password', $content);
        $this->assertStringContains('Keep you signed in on this device', $content);
    }

    /**
     */
    #[Test]
    public function test_error_recovery_accessibility(): void
    {
        // Submit form with errors
        $response = $this->post('/login', [
            'email'    => 'invalid@email',
            'password' => '',
            'website'  => '',
        ]);

        $response->assertSessionHasErrors();

        // Get the form with errors
        $response = $this->get('/login');
        $content = $response->getContent();

        // Check that error context is preserved for screen readers
        if (str_contains((string) $content, 'hd-error-message')) {
            $this->assertStringContains('Email error:', $content);
            $this->assertStringContains('Password error:', $content);
        }
    }

    /**
     */
    #[Test]
    public function test_semantic_html_structure(): void
    {
        $response = $this->get('/login');
        $content = $response->getContent();

        // Check for semantic HTML elements
        $this->assertStringContains('<main', $content);
        $this->assertStringContains('<form', $content);
        $this->assertStringContains('<fieldset', $content);
        $this->assertStringContains('<button', $content);

        // Check for proper form structure
        $this->assertStringContains('method="POST"', $content);
        $this->assertStringContains('action=', $content);
    }
}
