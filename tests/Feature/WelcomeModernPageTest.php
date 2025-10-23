<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WelcomeModernPageTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    #[Test]
    public function root_route_renders_modern_welcome_correctly_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Check hero content
        $response->assertSee('Never Miss the Perfect Ticket');
        $response->assertSee('Professional Sports Event Monitoring');
        $response->assertSee('Start 7-Day Free Trial');

        // Check brand logo is present in header
        $response->assertSee('HD Tickets logo', FALSE);
        $response->assertSeeHtml('assets/images/hdTicketsLogo.png');
        $response->assertSeeHtml('width="40"');
        $response->assertSeeHtml('height="40"');

        // Check brand link
        $response->assertSeeHtml('class="welcome-logo"');
        $response->assertSeeHtml('aria-label="HD Tickets home"');

        // Check footer logo
        $response->assertSeeHtml('width="32"');
        $response->assertSeeHtml('height="32"');
        $response->assertSeeHtml('loading="lazy"');

        // Check SEO meta tags
        $response->assertSeeHtml('og:image');
        $response->assertSeeHtml('assets/images/hdTicketsLogo.png');
        $response->assertSeeHtml('twitter:image');

        // Check preload
        $response->assertSeeHtml('rel="preload"');
        $response->assertSeeHtml('as="image"');
        $response->assertSeeHtml('fetchpriority="high"');
    }

    #[Test]
    public function root_route_shows_authenticated_user_dashboard_cta(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'role' => 'customer',
        ]);

        $response = $this->actingAs($user)->get('/');

        // User will be redirected to dashboard for authenticated users
        // If this fails, it means the route logic may have changed
        if ($response->status() === 302) {
            $response->assertRedirect('/dashboard');
        } else {
            $response->assertStatus(200);
            $response->assertSee('Go to Dashboard');
            $response->assertDontSee('Start 7-Day Free Trial');
            $response->assertSee('Dashboard', FALSE); // Check navigation link
        }
    }

    #[Test]
    public function root_route_displays_dynamic_statistics(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Check stats section exists and contains platform data
        $response->assertSee('Platform Statistics');
        $response->assertSee('Platforms Monitored');
        $response->assertSee('Real-time Monitoring');
        $response->assertSee('Active Users');
        $response->assertSee('Average Savings');

        // Check stats have fallback values - note stats come from service
        $response->assertSee('50+');
        $response->assertSee('24/7');
        // Don't check exact user count as it varies by environment
        $response->assertSee('$127'); // avg savings amount
        $response->assertSee('Average Savings');
    }

    #[Test]
    public function root_route_has_comprehensive_seo_meta_tags(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // SEO basics
        $response->assertSeeHtml('<title>HD Tickets - Professional Sports Event Ticket Monitoring Platform</title>');
        $response->assertSeeHtml('<meta name="description" content="HD Tickets - Professional Sports Events Entry Tickets Monitoring, Scraping and Purchase System with Advanced Security, Real-time Monitoring & Automated Purchasing across 50+ Platforms">');
        $response->assertSeeHtml('<meta name="keywords" content="sports tickets, ticket monitoring, event tickets, automated purchasing, real-time alerts, sports events, ticketmaster, stubhub, seatgeek">');

        // Open Graph
        $response->assertSeeHtml('<meta property="og:type" content="website">');
        $response->assertSeeHtml('<meta property="og:title" content="HD Tickets - Professional Sports Event Ticket Monitoring Platform">');
        $response->assertSeeHtml('<meta property="og:site_name" content="HD Tickets">');

        // Twitter Card
        $response->assertSeeHtml('<meta name="twitter:card" content="summary_large_image">');
        $response->assertSeeHtml('<meta name="twitter:title" content="HD Tickets - Professional Sports Event Ticket Monitoring Platform">');
    }

    #[Test]
    public function root_route_includes_structured_data(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSeeHtml('"@type": "SoftwareApplication"');
        $response->assertSeeHtml('"name": "HD Tickets"');
        $response->assertSeeHtml('"applicationCategory": "BusinessApplication"');
        $response->assertSeeHtml('"@type": "AggregateRating"');
        $response->assertSeeHtml('"ratingValue": "4.8"');
    }

    #[Test]
    public function root_route_displays_feature_sections(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Core features
        $response->assertSee('Everything You Need to Score Great Deals');
        $response->assertSee('Smart Monitoring');
        $response->assertSee('Instant Alerts');
        $response->assertSee('Auto Purchase');
        $response->assertSee('Price Analytics');
        $response->assertSee('Secure', FALSE); // HTML encoded as &amp;
        $response->assertSee('Team Management');
    }

    #[Test]
    public function root_route_includes_accessibility_features(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // ARIA labels and roles
        $response->assertSeeHtml('role="navigation"');
        $response->assertSeeHtml('aria-label="Main navigation"');
        $response->assertSeeHtml('role="main"');
        $response->assertSeeHtml('role="contentinfo"');
        $response->assertSeeHtml('aria-labelledby="stats-heading"');
        $response->assertSeeHtml('aria-labelledby="features-heading"');

        // Screen reader content
        $response->assertSeeHtml('<h2 id="stats-heading" class="sr-only">Platform Statistics</h2>');

        // Decorative image properly marked
        $response->assertSeeHtml('aria-hidden="true"');
    }

    #[Test]
    public function root_route_uses_welcome_css_classes(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Check welcome.css classes are being used instead of inline styles
        $response->assertSeeHtml('class="welcome-layout stadium-bg field-pattern"');
        $response->assertSeeHtml('class="welcome-header"');
        $response->assertSeeHtml('class="welcome-nav"');
        $response->assertSeeHtml('class="welcome-logo"');
        $response->assertSeeHtml('class="welcome-hero stadium-lights"');
        $response->assertSeeHtml('class="welcome-stats"');
        $response->assertSeeHtml('class="welcome-features"');
        $response->assertSeeHtml('class="welcome-footer"');

        // Ensure no inline styles are present
        $response->assertDontSee('style="');
    }

    #[Test]
    public function root_route_includes_preloads_partial(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // The preloads partial should render welcome CSS via Vite (with hash)
        $response->assertSeeHtml('welcome-');
        $response->assertSeeHtml('.css');
    }

    #[Test]
    public function root_route_uses_bunny_fonts_instead_of_google_fonts(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSeeHtml('fonts.bunny.net');
        $response->assertDontSee('fonts.googleapis.com');
        $response->assertDontSee('fonts.gstatic.com');
    }

    #[Test]
    public function root_route_has_proper_hero_badge_logo_attributes(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Hero badge should be decorative
        $response->assertSeeHtml('class="welcome-hero-badge-icon"');
        $response->assertSeeHtml('alt=""');
        $response->assertSeeHtml('aria-hidden="true"');
        $response->assertSeeHtml('loading="lazy"');
        $response->assertSeeHtml('width="24"');
        $response->assertSeeHtml('height="24"');
    }

    #[Test]
    public function root_route_handles_platform_integrations_when_available(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Should show platform integrations section when data is available
        $response->assertSee('Trusted Platform Integrations');
    }

    #[Test]
    public function root_route_includes_smooth_scroll_javascript(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Check JavaScript for smooth scrolling and animations
        $response->assertSeeHtml('IntersectionObserver');
        $response->assertSeeHtml('animate-fade-in');
        $response->assertSeeHtml('scrollIntoView');
        $response->assertSeeHtml('behavior: \'smooth\'');
    }

    #[Test]
    public function root_route_british_english_spelling_is_used(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // British spelling should be used
        $response->assertSee('favourite');
        $response->assertSee('personalised');
        $response->assertDontSee('favorite');
        $response->assertDontSee('personalized');
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        // Clear cache before each test
        Cache::flush();
    }
}
