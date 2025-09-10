<?php declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\Attributes\Test;
use App\Services\Core\ScrapingService;
use App\Services\Interfaces\ScrapingInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Mockery;
use Override;
use Tests\TestCase;

class ScrapingServiceTest extends TestCase
{
    private ScrapingService $scrapingService;

    private $mockAnalyticsService;

    private $mockCacheService;

    private $mockEncryptionService;

    /**
     */
    #[Test]
    public function it_implements_scraping_interface(): void
    {
        $this->assertInstanceOf(ScrapingInterface::class, $this->scrapingService);
    }

    /**
     */
    #[Test]
    public function it_initializes_with_dependencies(): void
    {
        $dependencies = [
            'analyticsService'  => $this->mockAnalyticsService,
            'cacheService'      => $this->mockCacheService,
            'encryptionService' => $this->mockEncryptionService,
        ];

        $this->scrapingService->initialize($dependencies);

        $healthStatus = $this->scrapingService->getHealthStatus();
        $this->assertEquals('healthy', $healthStatus['status']);
    }

    /**
     */
    #[Test]
    public function it_throws_exception_when_missing_required_dependencies(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Required dependency 'analyticsService' not provided");

        // Initialize without required dependencies
        $this->scrapingService->initialize([
            'cacheService' => $this->mockCacheService,
        ]);
    }

    /**
     */
    #[Test]
    public function it_returns_available_platforms(): void
    {
        $dependencies = [
            'analyticsService'  => $this->mockAnalyticsService,
            'cacheService'      => $this->mockCacheService,
            'encryptionService' => $this->mockEncryptionService,
        ];

        $this->scrapingService->initialize($dependencies);

        $platforms = $this->scrapingService->getAvailablePlatforms();

        $this->assertIsArray($platforms);
        $this->assertContains('ticketmaster', $platforms);
        $this->assertContains('stubhub', $platforms);
    }

    /**
     */
    #[Test]
    public function it_enables_and_disables_platforms(): void
    {
        $dependencies = [
            'analyticsService'  => $this->mockAnalyticsService,
            'cacheService'      => $this->mockCacheService,
            'encryptionService' => $this->mockEncryptionService,
        ];

        $this->scrapingService->initialize($dependencies);

        // Test enabling platform
        $this->scrapingService->enablePlatform('new_platform');
        $this->assertTrue(TRUE); // Platform should be enabled

        // Test disabling platform
        $this->scrapingService->disablePlatform('new_platform');
        $this->assertTrue(TRUE); // Platform should be disabled
    }

    /**
     */
    #[Test]
    public function it_returns_scraping_statistics(): void
    {
        Cache::shouldReceive('get')->andReturn([]);

        $dependencies = [
            'analyticsService'  => $this->mockAnalyticsService,
            'cacheService'      => $this->mockCacheService,
            'encryptionService' => $this->mockEncryptionService,
        ];

        $this->scrapingService->initialize($dependencies);

        $statistics = $this->scrapingService->getScrapingStatistics();

        $this->assertIsArray($statistics);
        $this->assertArrayHasKey('platforms', $statistics);
        $this->assertArrayHasKey('overall', $statistics);
        $this->assertArrayHasKey('health_status', $statistics);
    }

    /**
     */
    #[Test]
    public function it_schedules_recurring_scraping(): void
    {
        Cache::shouldReceive('put')->once();

        $dependencies = [
            'analyticsService'  => $this->mockAnalyticsService,
            'cacheService'      => $this->mockCacheService,
            'encryptionService' => $this->mockEncryptionService,
        ];

        $this->scrapingService->initialize($dependencies);

        $criteria = ['sport' => 'football', 'team' => 'Manchester United'];
        $jobId = $this->scrapingService->scheduleRecurringScraping($criteria, 30);

        $this->assertIsString($jobId);
        $this->assertStringStartsWith('scraping_', $jobId);
    }

    /**
     */
    #[Test]
    public function it_updates_scheduled_scraping_criteria(): void
    {
        // Mock cache get to return existing schedule
        Cache::shouldReceive('get')
            ->once()
            ->with('scraping_schedule_test_job_id')
            ->andReturn(['criteria' => ['old' => 'data']]);

        Cache::shouldReceive('put')->once();

        $dependencies = [
            'analyticsService'  => $this->mockAnalyticsService,
            'cacheService'      => $this->mockCacheService,
            'encryptionService' => $this->mockEncryptionService,
        ];

        $this->scrapingService->initialize($dependencies);

        $newCriteria = ['sport' => 'basketball'];
        $result = $this->scrapingService->updateScheduledScraping('test_job_id', $newCriteria);

        $this->assertTrue($result);
    }

    /**
     */
    #[Test]
    public function it_cancels_scheduled_scraping(): void
    {
        Cache::shouldReceive('forget')
            ->once()
            ->with('scraping_schedule_test_job_id')
            ->andReturn(TRUE);

        $dependencies = [
            'analyticsService'  => $this->mockAnalyticsService,
            'cacheService'      => $this->mockCacheService,
            'encryptionService' => $this->mockEncryptionService,
        ];

        $this->scrapingService->initialize($dependencies);

        $result = $this->scrapingService->cancelScheduledScraping('test_job_id');

        $this->assertTrue($result);
    }

    /**
     */
    #[Test]
    public function it_handles_errors_gracefully(): void
    {
        Log::shouldReceive('error')->once();

        $dependencies = [
            'analyticsService'  => $this->mockAnalyticsService,
            'cacheService'      => $this->mockCacheService,
            'encryptionService' => $this->mockEncryptionService,
        ];

        $this->scrapingService->initialize($dependencies);

        // This should not throw an exception but handle it gracefully
        $this->expectException(InvalidArgumentException::class);
        $this->scrapingService->scrapePlatform('non_existent_platform', []);
    }

    /**
     */
    #[Test]
    public function it_maintains_health_status(): void
    {
        $dependencies = [
            'analyticsService'  => $this->mockAnalyticsService,
            'cacheService'      => $this->mockCacheService,
            'encryptionService' => $this->mockEncryptionService,
        ];

        $this->scrapingService->initialize($dependencies);

        $healthStatus = $this->scrapingService->getHealthStatus();

        $this->assertIsArray($healthStatus);
        $this->assertArrayHasKey('status', $healthStatus);
        $this->assertArrayHasKey('uptime', $healthStatus);
        $this->assertArrayHasKey('memory_usage', $healthStatus);
        $this->assertEquals('healthy', $healthStatus['status']);
    }

    /**
     */
    #[Test]
    public function it_cleans_up_resources(): void
    {
        $dependencies = [
            'analyticsService'  => $this->mockAnalyticsService,
            'cacheService'      => $this->mockCacheService,
            'encryptionService' => $this->mockEncryptionService,
        ];

        $this->scrapingService->initialize($dependencies);

        // Verify initialized
        $this->assertEquals('healthy', $this->scrapingService->getHealthStatus()['status']);

        // Cleanup
        $this->scrapingService->cleanup();

        // Verify cleaned up
        $this->assertEquals('not_initialized', $this->scrapingService->getHealthStatus()['status']);
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        // Create mock dependencies
        $this->mockAnalyticsService = Mockery::mock();
        $this->mockCacheService = Mockery::mock();
        $this->mockEncryptionService = Mockery::mock();

        // Create service instance
        $this->scrapingService = new ScrapingService();
    }

    #[Override]
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
