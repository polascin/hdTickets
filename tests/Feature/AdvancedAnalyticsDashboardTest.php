<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ScrapedTicket;
use App\Models\TicketPriceHistory;
use App\Models\AnalyticsDashboard;
use App\Services\AdvancedAnalyticsDashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class AdvancedAnalyticsDashboardTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $analyticsDashboard;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->analyticsDashboard = new AdvancedAnalyticsDashboard();
        
        // Create test data
        $this->createTestData();
    }

    /** @test */
    public function it_can_get_price_trend_analysis()
    {
        $filters = [
            'start_date' => Carbon::now()->subDays(30),
            'end_date' => Carbon::now(),
            'platforms' => ['ticketmaster', 'stubhub']
        ];

        $analysis = $this->analyticsDashboard->getPriceTrendAnalysis($filters);

        $this->assertArrayHasKey('overview', $analysis);
        $this->assertArrayHasKey('daily_trends', $analysis);
        $this->assertArrayHasKey('platform_comparison', $analysis);
        $this->assertArrayHasKey('volatility_analysis', $analysis);
        $this->assertArrayHasKey('prediction_insights', $analysis);
        $this->assertArrayHasKey('anomaly_detection', $analysis);
        $this->assertArrayHasKey('recommendations', $analysis);
    }

    /** @test */
    public function it_can_get_demand_pattern_analysis()
    {
        $filters = [
            'start_date' => Carbon::now()->subDays(30),
            'end_date' => Carbon::now()
        ];

        $analysis = $this->analyticsDashboard->getDemandPatternAnalysis($filters);

        $this->assertArrayHasKey('demand_overview', $analysis);
        $this->assertArrayHasKey('temporal_patterns', $analysis);
        $this->assertArrayHasKey('event_type_analysis', $analysis);
        $this->assertArrayHasKey('geographic_patterns', $analysis);
        $this->assertArrayHasKey('seasonal_trends', $analysis);
        $this->assertArrayHasKey('prediction_model', $analysis);
        $this->assertArrayHasKey('market_saturation', $analysis);
        $this->assertArrayHasKey('recommendations', $analysis);
    }

    /** @test */
    public function it_can_get_success_rate_optimization()
    {
        $filters = [
            'start_date' => Carbon::now()->subDays(30),
            'end_date' => Carbon::now()
        ];

        $optimization = $this->analyticsDashboard->getSuccessRateOptimization($filters);

        $this->assertArrayHasKey('current_performance', $optimization);
        $this->assertArrayHasKey('channel_optimization', $optimization);
        $this->assertArrayHasKey('timing_optimization', $optimization);
        $this->assertArrayHasKey('content_optimization', $optimization);
        $this->assertArrayHasKey('user_segmentation', $optimization);
        $this->assertArrayHasKey('a_b_test_suggestions', $optimization);
        $this->assertArrayHasKey('predictive_scoring', $optimization);
        $this->assertArrayHasKey('improvement_roadmap', $optimization);
        $this->assertArrayHasKey('roi_analysis', $optimization);
    }

    /** @test */
    public function it_can_get_platform_performance_comparison()
    {
        $filters = [
            'start_date' => Carbon::now()->subDays(30),
            'end_date' => Carbon::now()
        ];

        $comparison = $this->analyticsDashboard->getPlatformPerformanceComparison($filters);

        $this->assertArrayHasKey('platform_rankings', $comparison);
        $this->assertArrayHasKey('performance_metrics', $comparison);
        $this->assertArrayHasKey('reliability_analysis', $comparison);
        $this->assertArrayHasKey('user_preference_analysis', $comparison);
        $this->assertArrayHasKey('market_share_analysis', $comparison);
        $this->assertArrayHasKey('competitive_analysis', $comparison);
        $this->assertArrayHasKey('trend_analysis', $comparison);
        $this->assertArrayHasKey('recommendations', $comparison);
    }

    /** @test */
    public function it_can_get_real_time_dashboard_metrics()
    {
        $metrics = $this->analyticsDashboard->getRealTimeDashboardMetrics();

        $this->assertArrayHasKey('live_metrics', $metrics);
        $this->assertArrayHasKey('system_health', $metrics);
        $this->assertArrayHasKey('active_alerts', $metrics);
        $this->assertArrayHasKey('user_activity', $metrics);
        $this->assertArrayHasKey('performance_indicators', $metrics);
        $this->assertArrayHasKey('alerts_summary', $metrics);
    }

    /** @test */
    public function price_trend_analysis_api_endpoint_works()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/analytics/price-trends', [
            'start_date' => Carbon::now()->subDays(30)->toDateString(),
            'end_date' => Carbon::now()->toDateString(),
            'platforms' => ['ticketmaster']
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'overview',
                        'daily_trends',
                        'platform_comparison',
                        'volatility_analysis',
                        'prediction_insights',
                        'anomaly_detection',
                        'recommendations'
                    ],
                    'metadata'
                ]);
    }

    /** @test */
    public function demand_pattern_analysis_api_endpoint_works()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/analytics/demand-patterns', [
            'start_date' => Carbon::now()->subDays(30)->toDateString(),
            'end_date' => Carbon::now()->toDateString()
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'insights',
                    'metadata'
                ]);
    }

    /** @test */
    public function success_rate_optimization_api_endpoint_works()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/analytics/success-optimization');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'projections',
                    'action_items',
                    'metadata'
                ]);
    }

    /** @test */
    public function platform_comparison_api_endpoint_works()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/analytics/platform-comparison', [
            'comparison_type' => 'summary'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'summary',
                    'metadata'
                ]);
    }

    /** @test */
    public function real_time_metrics_api_endpoint_works()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/analytics/real-time-metrics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'status',
                    'alerts',
                    'metadata'
                ]);
    }

    /** @test */
    public function custom_dashboard_api_endpoint_works()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/analytics/custom-dashboard', [
            'widgets' => ['price_trends', 'demand_patterns'],
            'time_range' => '7d',
            'auto_refresh' => true
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'configuration',
                    'metadata'
                ]);
    }

    /** @test */
    public function export_analytics_data_endpoint_works()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/analytics/export/price_trends', [
            'format' => 'json',
            'start_date' => Carbon::now()->subDays(7)->toDateString(),
            'end_date' => Carbon::now()->toDateString()
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'export_info'
                ]);
    }

    /** @test */
    public function analytics_dashboard_model_works()
    {
        $dashboard = AnalyticsDashboard::createDefaultForUser($this->user->id);

        $this->assertInstanceOf(AnalyticsDashboard::class, $dashboard);
        $this->assertEquals($this->user->id, $dashboard->user_id);
        $this->assertTrue($dashboard->is_default);
        $this->assertEquals('Default Dashboard', $dashboard->name);
    }

    /** @test */
    public function dashboard_can_be_shared_with_users()
    {
        $dashboard = AnalyticsDashboard::createDefaultForUser($this->user->id);
        $otherUser = User::factory()->create();

        $dashboard->shareWith([$otherUser->id]);

        $this->assertContains($otherUser->id, $dashboard->fresh()->shared_with);
        $this->assertTrue($dashboard->canAccess($otherUser->id));
    }

    /** @test */
    public function dashboard_access_control_works()
    {
        $dashboard = AnalyticsDashboard::createDefaultForUser($this->user->id);
        $otherUser = User::factory()->create();

        // Owner can access
        $this->assertTrue($dashboard->canAccess($this->user->id));

        // Other user cannot access private dashboard
        $this->assertFalse($dashboard->canAccess($otherUser->id));

        // Make dashboard public
        $dashboard->update(['is_public' => true]);
        $this->assertTrue($dashboard->canAccess($otherUser->id));
    }

    /** @test */
    public function analytics_data_export_validation_works()
    {
        $this->actingAs($this->user);

        // Test invalid export type
        $response = $this->getJson('/api/analytics/export/invalid_type');
        $response->assertStatus(422);

        // Test invalid date range
        $response = $this->getJson('/api/analytics/export/price_trends', [
            'start_date' => Carbon::now()->toDateString(),
            'end_date' => Carbon::now()->subDays(7)->toDateString()
        ]);
        $response->assertStatus(422);
    }

    /** @test */
    public function unauthenticated_requests_are_rejected()
    {
        $response = $this->getJson('/api/analytics/price-trends');
        $response->assertStatus(401);

        $response = $this->getJson('/api/analytics/demand-patterns');
        $response->assertStatus(401);

        $response = $this->getJson('/api/analytics/success-optimization');
        $response->assertStatus(401);
    }

    private function createTestData()
    {
        // Create test tickets
        $tickets = ScrapedTicket::factory()->count(50)->create([
            'platform' => $this->faker->randomElement(['ticketmaster', 'stubhub', 'seatgeek']),
            'price' => $this->faker->randomFloat(2, 50, 500),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now')
        ]);

        // Create price history
        foreach ($tickets as $ticket) {
            TicketPriceHistory::factory()->count(5)->create([
                'ticket_id' => $ticket->id,
                'price' => $this->faker->randomFloat(2, $ticket->price * 0.8, $ticket->price * 1.2),
                'recorded_at' => $this->faker->dateTimeBetween($ticket->created_at, 'now')
            ]);
        }
    }
}
