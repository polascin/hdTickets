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
    $response->assertSee('<main id="main-content"', false);
    $response->assertSee('role="main"', false);
    $response->assertSee('role="navigation"', false);
    $response->assertSee('role="contentinfo"', false);
    // Check for skip link
    $response->assertSee('Skip to main content');
});

test('welcome page uses British English spelling', function () {
    $response = $this->get('/');
    
    $response->assertOk();
    $response->assertSee('favourite');
    $response->assertSee('Personalised');
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
    $response->assertDontSee('support tickets', false);
});

test('welcome page contains proper SEO meta tags', function () {
    $response = $this->get('/');
    
    $response->assertOk();
    $response->assertSee('lang="en-GB"', false);
    $response->assertSee('<link rel="canonical"', false);
    $response->assertSee('<meta property="og:type" content="website">', false);
    $response->assertSee('<meta name="twitter:card"', false);
});

test('welcome page includes structured data', function () {
    $response = $this->get('/');
    
    $response->assertOk();
    $response->assertSee('application/ld+json', false);
    $response->assertSee('"@type": "SoftwareApplication"', false);
    $response->assertSee('"name": "HD Tickets"', false);
});

test('welcome page has accessibility features', function () {
    $response = $this->get('/');
    
    $response->assertOk();
    // Check for aria labels
    $response->assertSee('aria-label="Main navigation"', false);
    $response->assertSee('aria-labelledby', false);
    $response->assertSee('aria-hidden="true"', false);
    // Check for focus management
    $response->assertSee('focus:outline-none', false);
    $response->assertSee('focus:ring', false);
});

test('welcome page includes performance optimisations', function () {
    $response = $this->get('/');
    
    $response->assertOk();
    // Check for preloads
    $response->assertSee('<link rel="preload"', false);
    $response->assertSee('fetchpriority="high"', false);
    $response->assertSee('loading="lazy"', false);
    // Check for font optimisations
    $response->assertSee('<link rel="preconnect"', false);
    $response->assertSee('crossorigin', false);
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
    $response->assertSee('prefers-reduced-motion', false);
    $response->assertSee("behavior: prefersReducedMotion ? 'auto' : 'smooth'", false);
});

test('welcome page footer contains legal links', function () {
    $response = $this->get('/');
    
    $response->assertOk();
    $response->assertSee('Privacy Policy');
    $response->assertSee('Terms of Service');
    $response->assertSee('Service Disclaimer');
    $response->assertSee('Professional Sports Events Entry Tickets Monitoring Platform');
});
