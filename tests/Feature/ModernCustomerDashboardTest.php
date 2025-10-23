<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PaymentPlan;
use App\Models\ScrapedTicket;
use App\Models\Subscription;
use App\Models\TicketAlert;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModernCustomerDashboardTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private User $customer;

    private User $adminUser;

    private User $agentUser;

    private int $sportsEventId;

    #[Test]
    public function customer_can_access_modern_dashboard(): void
    {
        $response = $this->actingAs($this->customer)
            ->get('/dashboard/customer');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.customer-modern');
        $response->assertViewHas([
            'user',
            'subscription_status',
            'stats',
            'statistics',
            'recent_tickets',
            'initial_tickets_page',
            'alerts',
            'active_alerts',
            'recommendations',
            'market_insights',
            'feature_flags',
        ]);

        // Aliases mirror check
        $viewStats = $response->viewData('statistics');
        $viewStatsAlias = $response->viewData('stats');
        $this->assertEquals($viewStats, $viewStatsAlias);
        $viewAlerts = $response->viewData('active_alerts');
        $viewAlertsAlias = $response->viewData('alerts');
        $this->assertEquals($viewAlerts->toArray(), $viewAlertsAlias->toArray());
    }

    #[Test]
    public function non_customer_cannot_access_customer_dashboard(): void
    {
        $response = $this->actingAs($this->agentUser)
            ->get('/dashboard/customer');

        $response->assertStatus(403);
    }

    #[Test]
    public function guest_user_redirected_to_login(): void
    {
        $response = $this->get('/dashboard/customer');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function dashboard_stats_ajax_endpoint_returns_correct_data(): void
    {
        // Create test scraped tickets for the customer
        ScrapedTicket::factory()->count(5)->create([
            'is_available' => TRUE,
            'status'       => 'active',
            'min_price'    => 100.00,
            'max_price'    => 120.00,
            'created_at'   => now()->subDays(1),
        ]);

        ScrapedTicket::factory()->count(3)->create([
            'is_available' => TRUE,
            'status'       => 'active',
            'min_price'    => 150.00,
            'max_price'    => 170.00,
            'created_at'   => now(),
        ]);

        // Create alerts
        TicketAlert::factory()->count(4)->create([
            'user_id'         => $this->customer->id,
            'status'          => 'active',
            'sports_event_id' => $this->sportsEventId,
        ]);

        TicketAlert::factory()->count(2)->create([
            'user_id'         => $this->customer->id,
            'status'          => 'triggered',
            'sports_event_id' => $this->sportsEventId,
            'triggered_at'    => now()->subHours(1),
        ]);

        $response = $this->actingAs($this->customer)
            ->getJson('/ajax/customer-dashboard/stats');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => TRUE,
        ]);

        $data = $response->json('data');

        $this->assertEquals(8, $data['available_tickets']);
        $this->assertEquals(3, $data['new_today']);
        $this->assertEquals(4, $data['active_alerts']);
        $this->assertEquals(2, $data['price_alerts_triggered']);
        $this->assertArrayHasKey('total_savings', $data);
    }

    #[Test]
    public function dashboard_tickets_ajax_endpoint_returns_paginated_results(): void
    {
        // Create 25 scraped tickets for pagination test
        ScrapedTicket::factory()->count(25)->create([
            'is_available' => TRUE,
            'status'       => 'active',
        ]);

        $response = $this->actingAs($this->customer)
            ->getJson('/ajax/customer-dashboard/tickets?page=1&limit=10');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => TRUE,
        ]);

        $data = $response->json('data');

        $this->assertCount(10, $data['tickets']);
        $this->assertEquals(1, $data['pagination']['current_page']);
        $this->assertEquals(3, $data['pagination']['last_page']); // 25 tickets / 10 per page = 3 pages
        $this->assertEquals(25, $data['pagination']['total']);
    }

    #[Test]
    public function dashboard_alerts_ajax_endpoint_returns_user_alerts(): void
    {
        // Create alerts for this customer
        $customerAlerts = TicketAlert::factory()->count(3)->create([
            'user_id'         => $this->customer->id,
            'status'          => 'active',
            'sports_event_id' => $this->sportsEventId,
        ]);

        // Create alerts for another user (should not appear)
        $otherUser = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        TicketAlert::factory()->count(2)->create([
            'user_id'         => $otherUser->id,
            'status'          => 'active',
            'sports_event_id' => $this->sportsEventId,
        ]);

        $response = $this->actingAs($this->customer)
            ->getJson('/ajax/customer-dashboard/alerts');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => TRUE,
        ]);

        $data = $response->json('data');

        $this->assertCount(3, $data);

        // Verify all alerts belong to the customer
        foreach ($data as $alert) {
            $this->assertEquals($this->customer->id, $alert['user_id']);
        }
    }

    #[Test]
    public function dashboard_recommendations_endpoint_returns_personalized_data(): void
    {
        // Create user preferences using preference service
        UserPreference::setValue(
            $this->customer->id,
            'tickets',
            'preferred_events',
            ['Basketball', 'Football'],
            'json',
        );
        UserPreference::setValue(
            $this->customer->id,
            'tickets',
            'max_price_range',
            ['min' => 100, 'max' => 500],
            'json',
        );

        // Create some scraped tickets matching preferences
        ScrapedTicket::factory()->count(3)->create([
            'event_type'   => 'Basketball',
            'min_price'    => 250.00,
            'max_price'    => 300.00,
            'is_available' => TRUE,
            'status'       => 'active',
        ]);

        $response = $this->actingAs($this->customer)
            ->getJson('/ajax/customer-dashboard/recommendations');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => TRUE,
        ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    #[Test]
    public function dashboard_market_insights_endpoint_returns_analytics_data(): void
    {
        $response = $this->actingAs($this->customer)
            ->getJson('/ajax/customer-dashboard/market-insights');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => TRUE,
        ]);

        $data = $response->json('data');

        $this->assertArrayHasKey('price_trends', $data);
        $this->assertArrayHasKey('platform_performance', $data);
        $this->assertArrayHasKey('demand_analysis', $data);
        $this->assertArrayHasKey('popular_categories', $data);
        $this->assertArrayHasKey('seasonal_trends', $data);
        $this->assertArrayHasKey('recommendation_score', $data);
        $this->assertArrayHasKey('market_summary', $data);
    }

    #[Test]
    public function dashboard_caches_data_correctly(): void
    {
        // First request should hit the database
        $response1 = $this->actingAs($this->customer)
            ->getJson('/ajax/customer-dashboard/stats');

        $response1->assertStatus(200);

        // Second request within cache time should return same data
        $response2 = $this->actingAs($this->customer)
            ->getJson('/ajax/customer-dashboard/stats');

        $response2->assertStatus(200);

        // Data should be identical (cached)
        $this->assertEquals(
            $response1->json('data'),
            $response2->json('data'),
        );
    }

    #[Test]
    public function subscription_status_is_correctly_determined(): void
    {
        $response = $this->actingAs($this->customer)
            ->get('/dashboard/customer');

        $response->assertStatus(200);

        $subscriptionStatus = $response->viewData('subscription_status');

        $this->assertTrue($subscriptionStatus['has_active_subscription']);
        $this->assertFalse($subscriptionStatus['is_trial']);
        $this->assertNull($subscriptionStatus['trial_days_remaining']);
    }

    #[Test]
    public function trial_user_subscription_status_is_correctly_determined(): void
    {
        // Create a trial user
        $trialUser = User::factory()->create([
            'role'              => User::ROLE_CUSTOMER,
            'email'             => 'trial@example.com',
            'email_verified_at' => now(),
        ]);

        $paymentPlan = PaymentPlan::first();

        Subscription::create([
            'user_id'                => $trialUser->id,
            'payment_plan_id'        => $paymentPlan->id,
            'plan_name'              => 'test-plan',
            'price'                  => 19.99,
            'currency'               => 'USD',
            'stripe_subscription_id' => 'sub_trial_123',
            'status'                 => 'trialing',
            'trial_ends_at'          => now()->addDays(7),
            'current_period_start'   => now(),
            'current_period_end'     => now()->addMonth(),
        ]);

        $response = $this->actingAs($trialUser)
            ->get('/dashboard/customer');

        $response->assertStatus(200);

        $subscriptionStatus = $response->viewData('subscription_status');

        $this->assertTrue($subscriptionStatus['is_trial']);
        $this->assertEquals(7, $subscriptionStatus['trial_days_remaining']);
    }

    #[Test]
    public function ajax_endpoints_require_authentication(): void
    {
        $endpoints = [
            '/ajax/customer-dashboard/stats',
            '/ajax/customer-dashboard/tickets',
            '/ajax/customer-dashboard/alerts',
            '/ajax/customer-dashboard/recommendations',
            '/ajax/customer-dashboard/market-insights',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $response->assertStatus(401);
        }
    }

    #[Test]
    public function ajax_endpoints_require_customer_role(): void
    {
        $endpoints = [
            '/ajax/customer-dashboard/stats',
            '/ajax/customer-dashboard/tickets',
            '/ajax/customer-dashboard/alerts',
            '/ajax/customer-dashboard/recommendations',
            '/ajax/customer-dashboard/market-insights',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->actingAs($this->agentUser)
                ->getJson($endpoint);
            $response->assertStatus(403);
        }
    }

    #[Test]
    public function dashboard_handles_error_states_gracefully(): void
    {
        // Test with a customer that has no data
        $emptyCustomer = User::factory()->create([
            'role'  => User::ROLE_CUSTOMER,
            'email' => 'empty@example.com',
        ]);

        $response = $this->actingAs($emptyCustomer)
            ->get('/dashboard/customer');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.customer-modern');

        // Should still provide default data structure
        $stats = $response->viewData('stats');
        $this->assertIsArray($stats);
        $this->assertEquals(0, $stats['available_tickets']);
        $this->assertEquals(0, $stats['active_alerts']);
    }

    #[Test]
    public function dashboard_view_contains_required_data_attributes(): void
    {
        $response = $this->actingAs($this->customer)
            ->get('/dashboard/customer');

        $response->assertStatus(200);
        $response->assertSee('data-stats', FALSE);
        $response->assertSee('data-tickets', FALSE);
        $response->assertSee('x-data="modernCustomerDashboard()"', FALSE);
        $response->assertSee('csrf-token', FALSE);
    }

    #[Test]
    public function ajax_endpoints_require_ajax_header(): void
    {
        $this->actingAs($this->customer);

        $endpoints = [
            '/ajax/customer-dashboard/stats',
            '/ajax/customer-dashboard/tickets',
            '/ajax/customer-dashboard/alerts',
            '/ajax/customer-dashboard/recommendations',
            '/ajax/customer-dashboard/market-insights',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->get($endpoint); // no X-Requested-With header
            $response->assertStatus(403);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Create test customer
        $this->customer = User::factory()->create([
            'role'              => User::ROLE_CUSTOMER,
            'email'             => 'test.customer@example.com',
            'email_verified_at' => now(),
        ]);

        // Create admin user for comparison
        $this->adminUser = User::factory()->create([
            'role'  => User::ROLE_ADMIN,
            'email' => 'admin@example.com',
        ]);

        // Create agent (non-customer, non-admin)
        $this->agentUser = User::factory()->create([
            'role'              => User::ROLE_AGENT,
            'email'             => 'agent@example.com',
            'email_verified_at' => now(),
        ]);

        // Seed required sports events for FK constraints on ticket_alerts
        $this->seed(\Database\Seeders\SportsEventsSeeder::class);
        $this->sportsEventId = (int) DB::table('sports_events')->value('id');

        // Create payment plan and subscription
        $paymentPlan = PaymentPlan::create([
            'name'             => 'Test Plan',
            'slug'             => 'test-plan',
            'description'      => 'Test payment plan',
            'price'            => 19.99,
            'currency'         => 'USD',
            'billing_interval' => 'monthly',
            'stripe_price_id'  => 'price_test_monthly',
            'features'         => json_encode(['ticket_alerts' => 50]),
            'is_active'        => TRUE,
        ]);

        Subscription::create([
            'user_id'                => $this->customer->id,
            'payment_plan_id'        => $paymentPlan->id,
            'plan_name'              => 'test-plan',
            'price'                  => 19.99,
            'currency'               => 'USD',
            'stripe_subscription_id' => 'sub_test_123',
            'status'                 => 'active',
            'current_period_start'   => now()->subDays(10),
            'current_period_end'     => now()->addDays(20),
        ]);
    }
}
