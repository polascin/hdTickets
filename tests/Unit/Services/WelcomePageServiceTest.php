<?php declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\Attributes\Test;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\WelcomePageService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Override;
use ReflectionClass;
use Tests\TestCase;

class WelcomePageServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WelcomePageService $service;

    /**
     */
    #[Test]
    public function it_returns_complete_welcome_page_data(): void
    {
        $options = [
            'include_stats'      => TRUE,
            'include_pricing'    => TRUE,
            'include_features'   => TRUE,
            'include_legal_docs' => TRUE,
        ];

        $data = $this->service->getWelcomePageData($options);

        $this->assertArrayHasKey('stats', $data);
        $this->assertArrayHasKey('pricing', $data);
        $this->assertArrayHasKey('features', $data);
        $this->assertArrayHasKey('legal_docs', $data);
        $this->assertArrayHasKey('roles', $data);
        $this->assertArrayHasKey('security_features', $data);
    }

    /**
     */
    #[Test]
    public function it_can_exclude_specific_data_sections(): void
    {
        $options = [
            'include_stats'      => FALSE,
            'include_pricing'    => FALSE,
            'include_features'   => TRUE,
            'include_legal_docs' => TRUE,
        ];

        $data = $this->service->getWelcomePageData($options);

        $this->assertArrayNotHasKey('stats', $data);
        $this->assertArrayNotHasKey('pricing', $data);
        $this->assertArrayHasKey('features', $data);
        $this->assertArrayHasKey('legal_docs', $data);
    }

    /**
     */
    #[Test]
    public function it_returns_statistics_with_correct_structure(): void
    {
        $stats = $this->service->getStatistics();

        $expectedKeys = [
            'platforms', 'monitoring', 'users', 'events_monitored',
            'tickets_tracked', 'active_subscriptions', 'success_rate', 'avg_savings',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $stats);
        }

        $this->assertEquals('50+', $stats['platforms']);
        $this->assertEquals('24/7', $stats['monitoring']);
    }

    /**
     */
    #[Test]
    public function it_caches_statistics_properly(): void
    {
        // First call should cache the data
        $stats1 = $this->service->getStatistics();

        // Verify cache exists
        $this->assertTrue(Cache::has('welcome_page_stats'));

        // Second call should return cached data
        $stats2 = $this->service->getStatistics();

        $this->assertEquals($stats1, $stats2);
    }

    /**
     */
    #[Test]
    public function it_returns_pricing_information_with_defaults(): void
    {
        $pricing = $this->service->getPricingInformation();

        $expectedKeys = [
            'monthly_price', 'yearly_price', 'free_trial_days',
            'default_ticket_limit', 'currency', 'processing_fee_rate',
            'service_fee', 'agent_unlimited', 'no_money_back_guarantee',
            'service_provided_as_is',
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

    /**
     */
    #[Test]
    public function it_returns_features_list_with_correct_categories(): void
    {
        $features = $this->service->getFeaturesList();

        $expectedCategories = [
            'role_based_access', 'subscription_system', 'legal_compliance',
            'enhanced_security', 'monitoring_automation',
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

    /**
     */
    #[Test]
    public function it_returns_legal_documents_information(): void
    {
        $legalDocs = $this->service->getLegalDocuments();

        $expectedDocs = [
            'terms_of_service', 'service_disclaimer', 'privacy_policy',
            'data_processing_agreement', 'cookie_policy', 'acceptable_use_policy',
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

    /**
     */
    #[Test]
    public function it_returns_role_information_for_all_roles(): void
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

    /**
     */
    #[Test]
    public function it_returns_security_features_information(): void
    {
        $securityFeatures = $this->service->getSecurityFeatures();

        $expectedFeatures = [
            'multi_factor_auth', 'enhanced_login', 'data_encryption', 'payment_security',
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

    /**
     */
    #[Test]
    public function it_tracks_page_views_properly(): void
    {
        $viewData = [
            'user_id'    => 1,
            'ip'         => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'timestamp'  => now(),
        ];

        $this->service->trackPageView($viewData);

        // Verify data is cached for batch processing
        $cacheKey = 'page_views_' . date('Y-m-d-H');
        $this->assertTrue(Cache::has($cacheKey));

        $cachedViews = Cache::get($cacheKey);
        $this->assertIsArray($cachedViews);
        $this->assertCount(1, $cachedViews);
    }

    /**
     */
    #[Test]
    public function it_applies_ab_test_variants_correctly(): void
    {
        $data = ['pricing' => ['monthly_price' => 29.99]];

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('applyABTestVariant');

        $result = $method->invoke($this->service, $data, 'variant_a');

        $this->assertArrayHasKey('highlight_yearly', $result['pricing']);
        $this->assertTrue($result['pricing']['highlight_yearly']);
        $this->assertEquals(17, $result['pricing']['yearly_discount_percentage']);
    }

    /**
     */
    #[Test]
    public function it_returns_fallback_stats_on_exception(): void
    {
        // Mock DB to throw exception
        DB::shouldReceive('table')->andThrow(new Exception('Database error'));

        $stats = $this->service->getStatistics();

    // Per-metric fallbacks (users count uses direct model count and is unaffected)
    $this->assertEquals('0+', $stats['users']);
    $this->assertEquals('1M+', $stats['events_monitored']);
    $this->assertEquals('5M+', $stats['tickets_tracked']);
    }

    /**
     */
    #[Test]
    public function it_formats_user_count_correctly(): void
    {
        User::factory(1500)->create();

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('getFormattedUserCount');

        $result = $method->invoke($this->service);

        $this->assertEquals('1.5K+', $result);
    }

    /**
     */
    #[Test]
    public function it_gets_user_subscription_info_correctly(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        UserSubscription::factory()->create([
            'user_id' => $user->id,
            'status'  => 'active',
            'ends_at' => now()->addMonth(),
        ]);

        $userInfo = $this->service->getUserSubscriptionInfo($user);

        $expectedKeys = [
            'has_active_subscription', 'is_in_trial', 'trial_ends_at',
            'subscription_ends_at', 'monthly_ticket_usage', 'ticket_limit',
            'remaining_tickets', 'subscription_status', 'can_purchase_tickets',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $userInfo);
        }
    }

    /**
     */
    #[Test]
    public function it_handles_exceptions_gracefully_in_user_subscription_info(): void
    {
        // Create a partial mock that will throw when calling hasActiveSubscription
        $user = User::factory()->make();
        $userMock = $this->getMockBuilder(get_class($user))
            ->onlyMethods(['hasActiveSubscription'])
            ->getMock();
        $userMock->method('hasActiveSubscription')->willThrowException(new Exception('Error'));

        $userInfo = $this->service->getUserSubscriptionInfo($userMock);
        $this->assertEmpty($userInfo); // Graceful fallback on exception
    }

    /**
     */
    #[Test]
    public function it_caches_pricing_information(): void
    {
        // First call should cache the data
        $pricing1 = $this->service->getPricingInformation();

        // Verify cache exists
        $this->assertTrue(Cache::has('welcome_page_pricing'));

        // Second call should return cached data
        $pricing2 = $this->service->getPricingInformation();

        $this->assertEquals($pricing1, $pricing2);
    }

    /**
     */
    #[Test]
    public function it_caches_features_list(): void
    {
        // First call should cache the data
        $features1 = $this->service->getFeaturesList();

        // Verify cache exists
        $this->assertTrue(Cache::has('welcome_page_features'));

        // Second call should return cached data
        $features2 = $this->service->getFeaturesList();

        $this->assertEquals($features1, $features2);
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new WelcomePageService();
        Cache::flush();
    }
}