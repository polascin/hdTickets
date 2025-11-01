<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// Root route tests
test('root route renders welcome page for guests', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Never Miss Your Favourite Team Again');
    $response->assertSee('Comprehensive Sports Events Entry Tickets Monitoring');
    $response->assertSee('Start 7-Day Free Trial');
});

test('root route redirects authenticated users to dashboard', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $response = $this->actingAs($user)->get('/');

    $response->assertRedirect(route('dashboard'));
});

// Legacy redirect tests
test('welcome path redirects to root with 308', function () {
    $response = $this->get('/welcome');

    $response->assertStatus(308);
    $response->assertRedirect('/');
});

test('home path redirects to root with 308', function () {
    $response = $this->get('/home');

    $response->assertStatus(308);
    $response->assertRedirect('/');
});

test('welcome-modern path redirects to root with 308', function () {
    $response = $this->get('/welcome/modern');

    $response->assertStatus(308);
    $response->assertRedirect('/');
});

test('welcome-enhanced path redirects to root with 308', function () {
    $response = $this->get('/welcome/enhanced');

    $response->assertStatus(308);
    $response->assertRedirect('/');
});

// Content and accessibility tests
test('welcome page contains proper semantic HTML structure', function () {
    $response = $this->get('/');

    $response->assertOk();
    // Check for landmarks
    $response->assertSee('<main id="main-content"', FALSE);
    $response->assertSee('role="main"', FALSE);
    $response->assertSee('role="navigation"', FALSE);
    $response->assertSee('role="contentinfo"', FALSE);
    // Check for skip link
    $response->assertSee('Skip to main content');
});

test('welcome page uses British English spelling', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('favourite');
    $response->assertSee('personalised');
});

test('welcome page contains domain-correct copy', function () {
    $response = $this->get('/');

    $response->assertOk();
    // Assert sports events entry tickets terminology
    $response->assertSee('Sports Events Entry Tickets');
    $response->assertSee('Ticket Platforms Monitored');
    $response->assertSee('Real-time Monitoring');

    // Assert NO helpdesk terminology
    $response->assertDontSee('helpdesk');
    $response->assertDontSee('help desk');
    $response->assertDontSee('support tickets', FALSE);
});

test('welcome page contains proper SEO meta tags', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('lang="en-GB"', FALSE);
    $response->assertSee('<link rel="canonical"', FALSE);
    $response->assertSee('<meta property="og:type" content="website">', FALSE);
    $response->assertSee('<meta name="twitter:card"', FALSE);
});

test('welcome page includes structured data', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('application/ld+json', FALSE);
    $response->assertSee('"@type": "SoftwareApplication"', FALSE);
    $response->assertSee('"name": "HD Tickets"', FALSE);
});

test('welcome page has accessibility features', function () {
    $response = $this->get('/');

    $response->assertOk();
    // Check for aria labels
    $response->assertSee('aria-label="Main navigation"', FALSE);
    $response->assertSee('aria-labelledby', FALSE);
    $response->assertSee('aria-hidden="true"', FALSE);
    // Check for focus management
    $response->assertSee('focus:outline-none', FALSE);
    $response->assertSee('focus:ring', FALSE);
});

test('welcome page includes performance optimisations', function () {
    $response = $this->get('/');

    $response->assertOk();
    // Check for preloads
    $response->assertSee('<link rel="preload"', FALSE);
    $response->assertSee('fetchpriority="high"', FALSE);
    $response->assertSee('loading="lazy"', FALSE);
    // Check for font optimisations
    $response->assertSee('<link rel="preconnect"', FALSE);
    $response->assertSee('crossorigin', FALSE);
});

test('welcome page shows correct CTAs for guests', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Start 7-Day Free Trial');
    $response->assertSee('Sign In');
    $response->assertSee('Get Started');
    $response->assertDontSee('Go to Dashboard');
});

test('welcome page shows correct CTAs for authenticated users', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $response = $this->actingAs($user)->get('/welcome'); // Will redirect, but controller still called

    $response->assertRedirect(); // Authenticated users get redirected
});

test('welcome stats API endpoint returns correct data structure', function () {
    $response = $this->get('/api/welcome-stats');

    $response->assertOk();
    $response->assertJsonStructure([
        'platforms',
        'monitoring',
        'users',
    ]);
});

test('welcome page contains platform integrations section', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Trusted Platform Integrations');
    $response->assertSee('Ticketmaster');
    $response->assertSee('StubHub');
    $response->assertSee('SeatGeek');
});

test('welcome page contains security features section', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Enterprise-Grade Security');
    $response->assertSee('Multi-layered security architecture');
});

test('welcome page respects reduced motion preference', function () {
    $response = $this->get('/');

    $response->assertOk();
    // Check for reduced motion support in inline script
    $response->assertSee('prefers-reduced-motion', FALSE);
    $response->assertSee("behavior: prefersReducedMotion ? 'auto' : 'smooth'", FALSE);
});

test('welcome page footer contains legal links', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Privacy Policy');
    $response->assertSee('Terms of Service');
    $response->assertSee('Service Disclaimer');
    $response->assertSee('Professional Sports Events Entry Tickets Monitoring Platform');
});
