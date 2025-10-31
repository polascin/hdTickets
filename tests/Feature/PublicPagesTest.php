<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\get;
use function Pest\Laravel\actingAs;

uses(Tests\TestCase::class, RefreshDatabase::class);

describe('Public Marketing Pages', function (): void {
    test('home page returns successful response', function (): void {
        $response = get(route('public.home'));

        $response->assertStatus(200);
        $response->assertSee('Never Miss Your');
        $response->assertSee('Favourite Sports Events');
    });

    test('home page contains navigation items', function (): void {
        $response = get(route('public.home'));

        $response->assertStatus(200);
        $response->assertSee('Pricing');
        $response->assertSee('Coverage');
        $response->assertSee('FAQs');
        $response->assertSee('Browse Tickets');
        $response->assertSee('Sign In');
        $response->assertSee('Get Started');
    });

    test('home page contains hero search form', function (): void {
        $response = get(route('public.home'));

        $response->assertStatus(200);
        $response->assertSee('Search for teams, events, or venues...');
        $response->assertSee('action');
    });

    test('pricing page returns successful response', function (): void {
        $response = get(route('public.pricing'));

        $response->assertStatus(200);
        $response->assertSee('Simple, Transparent Pricing');
    });

    test('pricing page displays all pricing tiers', function (): void {
        $response = get(route('public.pricing'));

        $response->assertStatus(200);
        $response->assertSee('Free');
        $response->assertSee('Standard');
        $response->assertSee('Pro');
        $response->assertSee('£9.99');
        $response->assertSee('£24.99');
    });

    test('pricing page contains FAQ accordion', function (): void {
        $response = get(route('public.pricing'));

        $response->assertStatus(200);
        $response->assertSee('Pricing FAQs');
        $response->assertSee('How does the free trial work?');
    });

    test('coverage page returns successful response', function (): void {
        $response = get(route('public.coverage'));

        $response->assertStatus(200);
        $response->assertSee('Comprehensive Coverage');
    });

    test('coverage page displays sport categories', function (): void {
        $response = get(route('public.coverage'));

        $response->assertStatus(200);
        $response->assertSee('Football');
        $response->assertSee('Premier League');
        $response->assertSee('Champions League');
        $response->assertSee('Rugby');
        $response->assertSee('Cricket');
    });

    test('coverage page displays monitored platforms', function (): void {
        $response = get(route('public.coverage'));

        $response->assertStatus(200);
        $response->assertSee('Monitored Platforms');
        $response->assertSee('Ticketmaster');
        $response->assertSee('StubHub');
    });

    test('faqs page returns successful response', function (): void {
        $response = get(route('public.faqs'));

        $response->assertStatus(200);
        $response->assertSee('Frequently Asked Questions');
    });

    test('faqs page contains question categories', function (): void {
        $response = get(route('public.faqs'));

        $response->assertStatus(200);
        $response->assertSee('General');
        $response->assertSee('Pricing & Plans');
        $response->assertSee('Alerts & Notifications');
        $response->assertSee('Technical Questions');
    });

    test('faqs page has accessible accordion markup', function (): void {
        $response = get(route('public.faqs'));

        $response->assertStatus(200);
        $response->assertSee('aria-expanded');
        $response->assertSee('aria-controls');
    });
});

describe('SEO and Meta Tags', function (): void {
    test('home page has proper meta tags', function (): void {
        $response = get(route('public.home'));

        $response->assertStatus(200);
        $response->assertSee('<meta property="og:type"', false);
        $response->assertSee('<meta property="og:title"', false);
        $response->assertSee('<meta name="description"', false);
        $response->assertSee('application/ld+json', false);
    });

    test('pricing page has proper meta tags', function (): void {
        $response = get(route('public.pricing'));

        $response->assertStatus(200);
        $response->assertSee('<meta name="description"', false);
        $response->assertSee('<link rel="canonical"', false);
    });

    test('all public pages have JSON-LD structured data', function (): void {
        $routes = ['public.home', 'public.pricing', 'public.coverage', 'public.faqs'];

        foreach ($routes as $route) {
            $response = get(route($route));
            $response->assertStatus(200);
            $response->assertSee('application/ld+json', false);
            $response->assertSee('WebApplication', false);
        }
    });
});

describe('Accessibility Features', function (): void {
    test('home page has skip to main content link', function (): void {
        $response = get(route('public.home'));

        $response->assertStatus(200);
        $response->assertSee('Skip to main content');
        $response->assertSee('#main-content', false);
    });

    test('all pages have proper landmark regions', function (): void {
        $routes = ['public.home', 'public.pricing', 'public.coverage', 'public.faqs'];

        foreach ($routes as $route) {
            $response = get(route($route));
            $response->assertStatus(200);
            $response->assertSee('role="banner"', false);
            $response->assertSee('role="main"', false);
            $response->assertSee('role="contentinfo"', false);
        }
    });

    test('navigation is keyboard accessible', function (): void {
        $response = get(route('public.home'));

        $response->assertStatus(200);
        $response->assertSee('aria-expanded', false);
        $response->assertSee('aria-label', false);
    });
});

describe('User Authentication Flow', function (): void {
    test('guest users can access all public pages', function (): void {
        $routes = ['public.home', 'public.pricing', 'public.coverage', 'public.faqs'];

        foreach ($routes as $route) {
            $response = get(route($route));
            $response->assertStatus(200);
        }
    });

    test('authenticated users can still access public pages', function (): void {
        $user = User::factory()->create();

        $routes = ['public.home', 'public.pricing', 'public.coverage', 'public.faqs'];

        foreach ($routes as $route) {
            $response = actingAs($user)->get(route($route));
            $response->assertStatus(200);
        }
    });

    test('navigation shows appropriate CTAs for guests', function (): void {
        $response = get(route('public.home'));

        $response->assertStatus(200);
        $response->assertSee('Sign In');
        $response->assertSee('Get Started');
    });

    test('navigation shows dashboard link for authenticated users', function (): void {
        $user = User::factory()->create();

        $response = actingAs($user)->get(route('public.home'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    });
});

describe('Performance and Caching', function (): void {
    test('home page stats are cached', function (): void {
        // First request should populate cache
        $response1 = $this->get(route('public.home'));
        $response1->assertStatus(200);

        // Second request should use cache
        $response2 = $this->get(route('public.home'));
        $response2->assertStatus(200);

        // Both responses should be identical for cached content
        expect($response1->getContent())->toBe($response2->getContent());
    });
});

describe('British English Compliance', function (): void {
    test('pages use British English spelling', function (): void {
        $routes = ['public.home', 'public.pricing', 'public.coverage', 'public.faqs'];

        foreach ($routes as $route) {
            $response = get(route($route));
            $response->assertStatus(200);
            
            // Check for British spelling
            $response->assertSee('favourite', false);
            $response->assertDontSee('favorite', false);
        }
    });

    test('pricing uses GBP currency', function (): void {
        $response = get(route('public.pricing'));

        $response->assertStatus(200);
        $response->assertSee('£9.99');
        $response->assertSee('£24.99');
    });
});
