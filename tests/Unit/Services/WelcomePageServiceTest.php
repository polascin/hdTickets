<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\UserSubscription;
use App\Services\WelcomePageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WelcomePageServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WelcomePageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new WelcomePageService();
        Cache::flush();
    }

    /** @test */
    public function it_returns_complete_welcome_page_data()
    {
        $options = [
            'include_stats' => true,
            'include_pricing' => true,
            'include_features' => true,
            'include_legal_docs' => true
        ];

        $data = $this->service->getWelcomePageData($options);

        $this->assertArrayHasKey('stats', $data);
        $this->assertArrayHasKey('pricing', $data);
        $this->assertArrayHasKey('features', $data);
        $this->assertArrayHasKey('legal_docs', $data);
        $this->assertArrayHasKey('roles', $data);
        $this->assertArrayHasKey('security_features', $data);
    }

    /** @test */
    public function it_can_exclude_specific_data_sections()
    {
        $options = [
            'include_stats' => false,
            'include_pricing' => false,
            'include_features' => true,
            'include_legal_docs' => true
        ];

        $data = $this->service->getWelcomePageData($options);

        $this->assertArrayNotHasKey('stats', $data);
        $this->assertArrayNotHasKey('pricing', $data);
        $this->assertArrayHasKey('features', $data);
        $this->assertArrayHasKey('legal_docs', $data);
    }

    /** @test */
    public function it_returns_statistics_with_correct_structure()
    {
        $stats = $this->service->getStatistics();

        $expectedKeys = [
            'platforms', 'monitoring', 'users', 'events_monitored',
            'tickets_tracked', 'active_subscriptions', 'success_rate', 'avg_savings'
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $stats);
        }

        $this->assertEquals('50+', $stats['platforms']);
        $this->assertEquals('24/7', $stats['monitoring']);
    }

    /** @test */
    public function it_caches_statistics_properly()
    {
        // First call should cache the data
        $stats1 = $this->service->getStatistics();
        
        // Verify cache exists
        $this->assertTrue(Cache::has('welcome_page_stats'));
        
        // Second call should return cached data
        $stats2 = $this->service->getStatistics();
        
        $this->assertEquals($stats1, $stats2);
    }

    /** @test */
    public function it_returns_pricing_information_with_defaults()
    {
        $pricing = $this->service->getPricingInformation();

        $expectedKeys = [
            'monthly_price', 'yearly_price', 'free_trial_days', 
            'default_ticket_limit', 'currency', 'processing_fee_rate',
            'service_fee', 'agent_unlimited', 'no_money_back_guarantee',
            'service_provided_as_is'
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $pricing);
        }

        $this->assertEquals(29.99, $pricing['monthly_price']);
        $this->assertEquals(7, $pricing['free_trial_days']);
        $this->assertEquals('USD', $pricing['currency']);
        $this->assertTrue($pricing['no_money_back_guarantee']);
        $this->assertTrue($pricing['service_provided_as_is']);
    }

    /** @test */
    public function it_returns_features_list_with_correct_categories()
    {
        $features = $this->service->getFeaturesList();

        $expectedCategories = [
            'role_based_access', 'subscription_system', 'legal_compliance',
            'enhanced_security', 'monitoring_automation'
        ];

        foreach ($expectedCategories as $category) {
            $this->assertArrayHasKey($category, $features);
            $this->assertArrayHasKey('title', $features[$category]);
            $this->assertArrayHasKey('description', $features[$category]);
            $this->assertArrayHasKey('icon', $features[$category]);
            $this->assertArrayHasKey('features', $features[$category]);
            $this->assertIsArray($features[$category]['features']);
        }
    }

    /** @test */
    public function it_returns_legal_documents_information()
    {
        $legalDocs = $this->service->getLegalDocuments();

        $expectedDocs = [
            'terms_of_service', 'service_disclaimer', 'privacy_policy',
            'data_processing_agreement', 'cookie_policy', 'acceptable_use_policy'
        ];

        foreach ($expectedDocs as $doc) {
            $this->assertArrayHasKey($doc, $legalDocs);
            $this->assertArrayHasKey('title', $legalDocs[$doc]);
            $this->assertArrayHasKey('url', $legalDocs[$doc]);
            $this->assertArrayHasKey('description', $legalDocs[$doc]);
            $this->assertArrayHasKey('required', $legalDocs[$doc]);
            $this->assertTrue($legalDocs[$doc]['required']);
        }
    }

    /** @test */
    public function it_returns_role_information_for_all_roles()
    {
        $roles = $this->service->getRoleInformation();

        $expectedRoles = ['customer', 'agent', 'admin', 'scraper'];

        foreach ($expectedRoles as $role) {
            $this->assertArrayHasKey($role, $roles);
            
            if ($role !== 'scraper') {
                $this->assertArrayHasKey('name', $roles[$role]);
                $this->assertArrayHasKey('icon', $roles[$role]);
                $this->assertArrayHasKey('price', $roles[$role]);
                $this->assertArrayHasKey('features', $roles[$role]);
                $this->assertIsArray($roles[$role]['features']);
            } else {
                // Scraper role has different structure
                $this->assertArrayHasKey('name', $roles[$role]);
                $this->assertArrayHasKey('features', $roles[$role]);
            }
        }
    }

    /** @test */
    public function it_returns_security_features_information()
    {
        $securityFeatures = $this->service->getSecurityFeatures();

        $expectedFeatures = [
            'multi_factor_auth', 'enhanced_login', 'data_encryption', 'payment_security'
        ];

        foreach ($expectedFeatures as $feature) {
            $this->assertArrayHasKey($feature, $securityFeatures);
            $this->assertArrayHasKey('title', $securityFeatures[$feature]);
            $this->assertArrayHasKey('description', $securityFeatures[$feature]);
            $this->assertArrayHasKey('icon', $securityFeatures[$feature]);
            $this->assertArrayHasKey('features', $securityFeatures[$feature]);
            $this->assertIsArray($securityFeatures[$feature]['features']);
        }
    }

    /** @test */
    public function it_tracks_page_views_properly()
    {
        $viewData = [
            'user_id' => 1,
            'ip' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'timestamp' => now()
        ];

        $this->service->trackPageView($viewData);

        // Verify data is cached for batch processing
        $cacheKey = 'page_views_' . date('Y-m-d-H');
        $this->assertTrue(Cache::has($cacheKey));
        
        $cachedViews = Cache::get($cacheKey);
        $this->assertIsArray($cachedViews);
        $this->assertCount(1, $cachedViews);
    }

    /** @test */
    public function it_applies_ab_test_variants_correctly()
    {
        $data = ['pricing' => ['monthly_price' => 29.99]];

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('applyABTestVariant');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, $data, 'variant_a');

        $this->assertArrayHasKey('highlight_yearly', $result['pricing']);
        $this->assertTrue($result['pricing']['highlight_yearly']);
        $this->assertEquals(17, $result['pricing']['yearly_discount_percentage']);
    }

    /** @test */
    public function it_returns_fallback_stats_on_exception()
    {
        // Mock DB to throw exception
        DB::shouldReceive('table')->andThrow(new \Exception('Database error'));

        $stats = $this->service->getStatistics();

        // Should return fallback stats
        $this->assertEquals('15K+', $stats['users']);
        $this->assertEquals('1M+', $stats['events_monitored']);
        $this->assertEquals('5M+', $stats['tickets_tracked']);
    }

    /** @test */
    public function it_formats_user_count_correctly()
    {
        User::factory(1500)->create();

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getFormattedUserCount');
        $method->setAccessible(true);

        $result = $method->invoke($this->service);

        $this->assertEquals('1.5K+', $result);
    }

    /** @test */
    public function it_gets_user_subscription_info_correctly()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $subscription = UserSubscription::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'ends_at' => now()->addMonth()
        ]);

        // Mock user methods that might not exist
        $user = $this->getMockBuilder(User::class)
                     ->onlyMethods(['hasActiveSubscription', 'isInTrialPeriod', 'getMonthlyTicketUsage', 'getTicketLimit', 'getRemainingTickets', 'canPurchaseTickets'])
                     ->getMock();

        $user->method('hasActiveSubscription')->willReturn(true);
        $user->method('isInTrialPeriod')->willReturn(false);
        $user->method('getMonthlyTicketUsage')->willReturn(25);
        $user->method('getTicketLimit')->willReturn(100);
        $user->method('getRemainingTickets')->willReturn(75);
        $user->method('canPurchaseTickets')->willReturn(true);

        $userInfo = $this->service->getUserSubscriptionInfo($user);

        $expectedKeys = [
            'has_active_subscription', 'is_in_trial', 'trial_ends_at',
            'subscription_ends_at', 'monthly_ticket_usage', 'ticket_limit',
            'remaining_tickets', 'subscription_status', 'can_purchase_tickets'
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $userInfo);
        }
    }

    /** @test */
    public function it_handles_exceptions_gracefully_in_user_subscription_info()
    {
        $user = $this->getMockBuilder(User::class)
                     ->onlyMethods(['hasActiveSubscription'])
                     ->getMock();

        $user->method('hasActiveSubscription')->willThrowException(new \Exception('Error'));

        $userInfo = $this->service->getUserSubscriptionInfo($user);

        $this->assertEmpty($userInfo);
    }

    /** @test */
    public function it_caches_pricing_information()
    {
        // First call should cache the data
        $pricing1 = $this->service->getPricingInformation();
        
        // Verify cache exists
        $this->assertTrue(Cache::has('welcome_page_pricing'));
        
        // Second call should return cached data
        $pricing2 = $this->service->getPricingInformation();
        
        $this->assertEquals($pricing1, $pricing2);
    }

    /** @test */
    public function it_caches_features_list()
    {
        // First call should cache the data
        $features1 = $this->service->getFeaturesList();
        
        // Verify cache exists
        $this->assertTrue(Cache::has('welcome_page_features'));
        
        // Second call should return cached data
        $features2 = $this->service->getFeaturesList();
        
        $this->assertEquals($features1, $features2);
    }
}
