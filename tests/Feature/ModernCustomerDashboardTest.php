<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PaymentPlan;
use App\Models\Subscription;
use App\Models\Ticket;
use App\Models\TicketAlert;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ModernCustomerDashboardTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private User $customer;

    private User $adminUser;

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
            'stripe_subscription_id' => 'sub_test_123',
            'status'                 => 'active',
            'current_period_start'   => now()->subDays(10),
            'current_period_end'     => now()->addDays(20),
        ]);
    }

    /** @test */
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
            'recent_tickets',
            'alerts',
            'recommendations',
        ]);
    }

    /** @test */
    public function non_customer_cannot_access_customer_dashboard(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/dashboard/customer');

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_user_redirected_to_login(): void
    {
        $response = $this->get('/dashboard/customer');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function dashboard_stats_ajax_endpoint_returns_correct_data(): void
    {
        // Create test tickets for the customer
        Ticket::factory()->count(5)->create([
            'requester_id' => $this->customer->id,
            'is_available' => TRUE,
            'price'        => 100.00,
            'created_at'   => now()->subHours(2),
        ]);

        Ticket::factory()->count(3)->create([
            'requester_id' => $this->customer->id,
            'is_available' => TRUE,
            'price'        => 150.00,
            'created_at'   => now(),
        ]);

        // Create alerts
        TicketAlert::factory()->count(4)->create([
            'user_id'   => $this->customer->id,
            'is_active' => TRUE,
        ]);

        TicketAlert::factory()->count(2)->create([
            'user_id'      => $this->customer->id,
            'is_active'    => TRUE,
            'triggered_at' => now()->subHours(1),
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

    /** @test */
    public function dashboard_tickets_ajax_endpoint_returns_paginated_results(): void
    {
        // Create 25 tickets for pagination test
        Ticket::factory()->count(25)->create([
            'requester_id' => $this->customer->id,
            'is_available' => TRUE,
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

    /** @test */
    public function dashboard_alerts_ajax_endpoint_returns_user_alerts(): void
    {
        // Create alerts for this customer
        $customerAlerts = TicketAlert::factory()->count(3)->create([
            'user_id'   => $this->customer->id,
            'is_active' => TRUE,
        ]);

        // Create alerts for another user (should not appear)
        $otherUser = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        TicketAlert::factory()->count(2)->create([
            'user_id'   => $otherUser->id,
            'is_active' => TRUE,
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

    /** @test */
    public function dashboard_recommendations_endpoint_returns_personalized_data(): void
    {
        // Create user preferences
        UserPreference::create([
            'user_id'     => $this->customer->id,
            'preferences' => json_encode([
                'tickets' => [
                    'preferred_events' => ['Basketball', 'Football'],
                    'max_price_range'  => ['min' => 100, 'max' => 500],
                ],
            ]),
        ]);

        // Create some tickets matching preferences
        Ticket::factory()->count(3)->create([
            'event_type'   => 'Basketball',
            'price'        => 250.00,
            'is_available' => TRUE,
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

    /** @test */
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

    /** @test */
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
            $response2->json('data')
        );
    }

    /** @test */
    public function subscription_status_is_correctly_determined(): void
    {
        $response = $this->actingAs($this->customer)
            ->get('/dashboard/customer');

        $response->assertStatus(200);

        $subscriptionStatus = $response->viewData('subscription_status');

        $this->assertEquals('active', $subscriptionStatus['status']);
        $this->assertTrue($subscriptionStatus['has_active_subscription']);
        $this->assertFalse($subscriptionStatus['is_trial']);
        $this->assertNull($subscriptionStatus['trial_days_remaining']);
    }

    /** @test */
    public function trial_user_subscription_status_is_correctly_determined(): void
    {
        // Create a trial user
        $trialUser = User::factory()->create([
            'role'  => User::ROLE_CUSTOMER,
            'email' => 'trial@example.com',
        ]);

        $paymentPlan = PaymentPlan::first();

        Subscription::create([
            'user_id'                => $trialUser->id,
            'payment_plan_id'        => $paymentPlan->id,
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

        $this->assertEquals('trialing', $subscriptionStatus['status']);
        $this->assertTrue($subscriptionStatus['has_active_subscription']);
        $this->assertTrue($subscriptionStatus['is_trial']);
        $this->assertEquals(7, $subscriptionStatus['trial_days_remaining']);
    }

    /** @test */
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

    /** @test */
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
            $response = $this->actingAs($this->adminUser)
                ->getJson($endpoint);
            $response->assertStatus(403);
        }
    }

    /** @test */
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

    /** @test */
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
}
