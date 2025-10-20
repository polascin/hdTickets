<?php declare(strict_types=1);

namespace Tests;

use App\Models\Ticket;
use App\Models\TicketSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Override;
use Tests\Factories\TestDataFactory;
use Throwable;

use function get_class;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;
    use WithFaker;

    protected TestDataFactory $testDataFactory;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        // Configure test environment first
        $this->configureTestEnvironment();

        // Ensure migrations are run (RefreshDatabase may not always run migrations)
        $this->artisan('migrate', ['--force' => true]);

        // Initialize test data factory
        $this->testDataFactory = new TestDataFactory();
    }

    #[Override]
    protected function tearDown(): void
    {
        // Clean up test resources
        $this->cleanupTestResources();

        parent::tearDown();
    }

    /**
     * Configure test environment settings
     */
    protected function configureTestEnvironment(): void
    {
        // Disable external API calls during testing
        config(['services.external_apis_enabled' => FALSE]);

        // Configure test-specific database settings
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);

        // Configure test-specific settings
        config(['app.debug' => TRUE]);
        config(['mail.default' => 'array']);
        config(['queue.default' => 'sync']);
        // Ensure default free access is disabled in tests unless explicitly set in a test
        config(['subscription.free_access_days' => 0]);

        // Clear caches
        Cache::flush();
    }

    /**
     * Clean up test resources
     */
    protected function cleanupTestResources(): void
    {
        // Clear any lingering cache data
        Cache::flush();

        // Clear queued jobs (sync driver has no purge method); for other drivers dispatch events as needed
        try {
            if (method_exists(Queue::getFacadeRoot(), 'clear')) {
                Queue::clear();
            }
        } catch (Throwable $e) {
            // Ignore cleanup errors
        }
    }

    /**
     * Create a test user with specified role
     */
    protected function createTestUser(array $attributes = [], string $role = 'customer'): User
    {
        return $this->testDataFactory->createUser($attributes, $role);
    }

    /**
     * Create test ticket with specified attributes
     */
    protected function createTestTicket(array $attributes = []): Ticket
    {
        return $this->testDataFactory->createTicket($attributes);
    }

    /**
     * Create test ticket source
     */
    protected function createTestTicketSource(array $attributes = []): TicketSource
    {
        return $this->testDataFactory->createTicketSource($attributes);
    }

    /**
     * Mock external service dependencies
     */
    protected function mockExternalServices(): void
    {
        // Mock payment gateways
        $this->mockStripeService();
        $this->mockPayPalService();

        // Mock notification services
        $this->mockTwilioService();
        $this->mockSlackService();

        // Mock scraping services
        $this->mockScrapingServices();
    }

    /**
     * Mock Stripe payment service
     */
    protected function mockStripeService(): void
    {
        $mock = Mockery::mock('alias:Stripe\PaymentIntent');
        $mock->shouldReceive('create')->andReturn((object) [
            'id'       => 'pi_test_' . $this->faker->uuid,
            'status'   => 'succeeded',
            'amount'   => 2999,
            'currency' => 'usd',
        ]);
    }

    /**
     * Mock PayPal service
     */
    protected function mockPayPalService(): void
    {
        // PayPal mock implementation
    }

    /**
     * Mock Twilio SMS service
     */
    protected function mockTwilioService(): void
    {
        // Twilio mock implementation
    }

    /**
     * Mock Slack notification service
     */
    protected function mockSlackService(): void
    {
        // Slack mock implementation
    }

    /**
     * Mock scraping services
     */
    protected function mockScrapingServices(): void
    {
        // Scraping services mock implementation
    }

    /**
     * Assert database has ticket with specific criteria
     */
    protected function assertDatabaseHasTicket(array $criteria): void
    {
        $this->assertDatabaseHas('tickets', $criteria);
    }

    /**
     * Assert user received specific notification
     *
     * @param mixed $user
     */
    protected function assertUserReceivedNotification($user, string $notificationType): void
    {
        $this->assertDatabaseHas('notifications', [
            'notifiable_id'   => $user->id,
            'notifiable_type' => get_class($user),
            'type'            => $notificationType,
        ]);
    }

    /**
     * Assert queue has job of specific type
     */
    protected function assertQueueHasJob(string $jobClass): void
    {
        Queue::assertPushed($jobClass);
    }

    /**
     * Create test database transaction
     */
    protected function withDatabaseTransaction(callable $callback)
    {
        return DB::transaction($callback);
    }

    /**
     * Simulate time passage for testing scheduled events
     */
    protected function travelToFuture(int $minutes): void
    {
        $this->travel($minutes)->minutes();
    }

    /**
     * Create test API headers
     */
    protected function getApiHeaders(?User $user = NULL): array
    {
        $headers = [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($user instanceof User) {
            $token = $user->createToken('test-token')->plainTextToken;
            $headers['Authorization'] = "Bearer {$token}";
        }

        return $headers;
    }

    /**
     * Assert API response has correct structure
     *
     * @param mixed $response
     */
    protected function assertApiResponse($response, int $statusCode = 200, array $structure = []): void
    {
        $response->assertStatus($statusCode);

        if ($structure !== []) {
            $response->assertJsonStructure($structure);
        }
    }

    /**
     * Create test performance metrics
     */
    protected function measurePerformance(callable $callback): array
    {
        $startTime = microtime(TRUE);
        $startMemory = memory_get_usage();

        $result = $callback();

        $endTime = microtime(TRUE);
        $endMemory = memory_get_usage();

        return [
            'result'         => $result,
            'execution_time' => $endTime - $startTime,
            'memory_used'    => $endMemory - $startMemory,
            'peak_memory'    => memory_get_peak_usage(),
        ];
    }
}
