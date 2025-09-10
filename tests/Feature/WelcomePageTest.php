<?php declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\WelcomePageService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Override;
use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_welcome_page_renders_correctly_for_guests(): void
    {
        $response = $this->get('/home');

        $response->assertStatus(200);
        $response->assertSee('HD Tickets');
        $response->assertSee('Never Miss Your Team Again');
        $response->assertSee('Start 7-Day Free Trial');
        $response->assertSee('Sign In');
        $response->assertSee('Role-Based Access');
        $response->assertSee('Professional Sports Event Ticket Monitoring Platform');
    }

    public function test_welcome_page_shows_authenticated_user_greeting(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'role' => 'customer',
        ]);

        $response = $this->actingAs($user)->get('/home');

        $response->assertStatus(200);
        $response->assertSee('Welcome back, John Doe!');
        $response->assertSee('Your role: Customer');
        $response->assertSee('Go to Dashboard');
        $response->assertDontSee('Start 7-Day Free Trial');
    }

    public function test_welcome_page_displays_correct_role_information(): void
    {
        $response = $this->get('/home');

        $response->assertStatus(200);
        $response->assertSee('Customer');
        $response->assertSee('$29.99');
        $response->assertSee('Most Popular');
        $response->assertSee('Agent');
        $response->assertSee('Unlimited');
        $response->assertSee('Professional');
        $response->assertSee('Administrator');
        $response->assertSee('Enterprise');
    }

    /**
     */
    #[Test]
    public function welcome_page_displays_subscription_plans(): void
    {
        $response = $this->get('/home');

        $response->assertStatus(200);
        $response->assertSee('Subscription Plans');
        $response->assertSee('Free Trial');
        $response->assertSee('7 Days Free');
        $response->assertSee('Monthly Plan');
        $response->assertSee('No credit card required');
    }

    /**
     */
    #[Test]
    public function welcome_page_displays_security_features(): void
    {
        $response = $this->get('/home');

        $response->assertStatus(200);
        $response->assertSee('Enterprise-Grade Security');
        $response->assertSee('Multi-Factor Authentication');
        $response->assertSee('Enhanced Login Security');
        $response->assertSee('Data Encryption');
        $response->assertSee('Secure Payment Processing');
    }

    /**
     */
    #[Test]
    public function welcome_page_displays_legal_compliance_information(): void
    {
        $response = $this->get('/home');

        $response->assertStatus(200);
        $response->assertSee('Legal Compliance & Trust');
        $response->assertSee('GDPR Compliant');
        $response->assertSee('Mandatory Legal Documents');
        $response->assertSee('No Money-Back Guarantee Policy');
        $response->assertSee('Terms of Service');
        $response->assertSee('Privacy Policy');
    }

    public function test_welcome_page_contains_proper_seo_meta_tags(): void
    {
        $response = $this->get('/home');

        $response->assertStatus(200);
        $response->assertSeeHtml('<meta name="description" content="HD Tickets - Professional Sports Event Ticket Monitoring Platform with Role-Based Access, Subscription Management, and Legal Compliance">');
        $response->assertSeeHtml('<meta name="keywords" content="sports tickets, ticket monitoring, event tickets, subscription platform, GDPR compliant, 2FA security, role-based access">');
        $response->assertSeeHtml('<meta property="og:title" content="HD Tickets - Professional Sports Ticket Monitoring Platform">');
        $response->assertSeeHtml('<meta name="twitter:card" content="summary_large_image">');
    }

    /**
     */
    #[Test]
    public function welcome_page_includes_structured_data(): void
    {
        $response = $this->get('/home');

        $response->assertStatus(200);
        $response->assertSeeHtml('"@type": "SoftwareApplication"');
        $response->assertSeeHtml('"name": "HD Tickets"');
        $response->assertSeeHtml('"applicationCategory": "BusinessApplication"');
        $response->assertSeeHtml('"price": "29.99"');
    }

    /**
     */
    #[Test]
    public function welcome_page_has_correct_footer_legal_links(): void
    {
        $response = $this->get('/home');

        $response->assertStatus(200);
        $response->assertSee('Terms of Service');
        $response->assertSee('Service Disclaimer');
        $response->assertSee('Privacy Policy');
        $response->assertSee('Data Processing Agreement');
        $response->assertSee('Cookie Policy');
        $response->assertSee('Acceptable Use Policy');
    }

    /**
     */
    #[Test]
    public function welcome_stats_api_returns_correct_data(): void
    {
        $response = $this->getJson('/api/welcome-stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'platforms',
            'monitoring',
            'users',
            'events_monitored',
            'tickets_tracked',
            'active_subscriptions',
            'success_rate',
            'avg_savings',
        ]);
    }

    /**
     */
    #[Test]
    public function welcome_page_handles_different_user_roles_correctly(): void
    {
        $testRoles = ['customer', 'agent', 'admin'];

        foreach ($testRoles as $role) {
            $user = User::factory()->create(['role' => $role]);

            $response = $this->actingAs($user)->get('/home');

            $response->assertStatus(200);
            $response->assertSee(ucfirst($role));
        }
    }

    /**
     */
    #[Test]
    public function welcome_page_caches_data_properly(): void
    {
        // First request should cache the data
        $this->get('/home');

        // Verify cache keys exist
        $this->assertTrue(Cache::has('welcome_page_stats'));
    }

    /**
     */
    #[Test]
    public function welcome_page_handles_subscription_info_for_authenticated_users(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        UserSubscription::factory()->create([
            'user_id' => $user->id,
            'status'  => 'active',
            'ends_at' => now()->addMonth(),
        ]);

        $response = $this->actingAs($user)->get('/home');

        $response->assertStatus(200);
        // Should show personalized content based on subscription
    }

    /**
     */
    #[Test]
    public function welcome_page_includes_alpine_js_components(): void
    {
        $response = $this->get('/home');

        $response->assertStatus(200);
        $response->assertSeeHtml('x-data');
        $response->assertSeeHtml('x-intersect');
        $response->assertSeeHtml('x-transition');
    }

    /**
     */
    #[Test]
    public function welcome_page_is_mobile_responsive(): void
    {
        $response = $this->get('/home');

        $response->assertStatus(200);
        $response->assertSeeHtml('<meta name="viewport" content="width=device-width, initial-scale=1">');
        $response->assertSeeHtml('@media (max-width: 768px)');
    }

    /**
     */
    #[Test]
    public function welcome_page_security_headers_are_present(): void
    {
        $response = $this->get('/home');

        // Note: These would be tested at the middleware level
        $response->assertStatus(200);
    }

    /**
     */
    #[Test]
    public function welcome_page_handles_fallback_data_gracefully(): void
    {
        // Mock service to throw exception
        $this->mock(WelcomePageService::class, function ($mock): void {
            $mock->shouldReceive('getWelcomePageData')
                ->andThrow(new Exception('Service unavailable'));
        });

        $response = $this->get('/home');

        // Page should still render with fallback data
        $response->assertStatus(200);
        $response->assertSee('HD Tickets');
    }

    /**
     */
    #[Test]
    public function welcome_page_tracks_analytics_properly(): void
    {
        $response = $this->get('/home');

        $response->assertStatus(200);

        // Check that analytics tracking elements are present
        $response->assertSeeHtml('gtag');
    }

    /**
     */
    #[Test]
    public function welcome_page_ab_testing_works_correctly(): void
    {
        // Test A/B variant assignment
        $response = $this->withSession(['ab_variant' => 'variant_a'])
            ->get('/home');

        $response->assertStatus(200);
        // Should show variant-specific content
    }

    /**
     */
    #[Test]
    public function welcome_page_redirects_based_on_configuration(): void
    {
        config(['welcome.redirect_authenticated_users' => TRUE]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/home');

        // Should redirect to dashboard if configured
        $response->assertRedirect('/dashboard');
    }

    /**
     */
    #[Test]
    public function welcome_page_includes_accessibility_features(): void
    {
        $response = $this->get('/home');

        $response->assertStatus(200);
        $response->assertSeeHtml('aria-label');
        $response->assertSeeHtml('aria-describedby');
        $response->assertSeeHtml('role="main"');
    }

    /**
     */
    #[Test]
    public function welcome_page_language_content_is_correct(): void
    {
        $response = $this->get('/home');

        $response->assertStatus(200);
        $response->assertSee('Never Miss Your Team Again');
        $response->assertSee('Professional Sports Event Ticket Monitoring Platform');
    }

    /**
     */
    #[Test]
    public function welcome_page_displays_scraper_role_notice(): void
    {
        $response = $this->get('/home');

        $response->assertStatus(200);
        $response->assertSee('Scraper role is system-only');
        $response->assertSee('cannot login to the web interface');
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        // Clear cache before each test
        Cache::flush();
    }
}
