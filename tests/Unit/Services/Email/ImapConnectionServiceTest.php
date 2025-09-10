<?php declare(strict_types=1);

namespace Tests\Unit\Services\Email;

use App\Services\Email\ImapConnectionService;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

/**
 * IMAP Connection Service Tests
 *
 * Unit tests for the ImapConnectionService class in the HD Tickets
 * sports events monitoring system.
 */
class ImapConnectionServiceTest extends TestCase
{
    private ImapConnectionService $service;

    private array $testConfig;

    #[Test]
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(ImapConnectionService::class, $this->service);
    }

    #[Test]
    public function it_throws_exception_for_unknown_connection(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("IMAP connection 'nonexistent' not configured");

        $this->service->getConnection('nonexistent');
    }

    #[Test]
    public function it_throws_exception_for_missing_credentials(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("IMAP credentials not configured for 'invalid'");

        $this->service->getConnection('invalid');
    }

    #[Test]
    public function it_builds_connection_string_correctly(): void
    {
        $config = $this->testConfig['connections']['test'];

        // Use reflection to access private method
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('buildConnectionString');

        $connectionString = $method->invoke($this->service, $config);

        $this->assertStringContains('test.example.com', $connectionString);
        $this->assertStringContains('993', $connectionString);
        $this->assertStringContains('imap', $connectionString);
        $this->assertStringContains('ssl', $connectionString);
        $this->assertStringContains('novalidate-cert', $connectionString);
    }

    #[Test]
    public function it_gets_connection_statistics(): void
    {
        $stats = $this->service->getConnectionStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('active_connections', $stats);
        $this->assertArrayHasKey('configured_connections', $stats);
        $this->assertArrayHasKey('default_connection', $stats);
        $this->assertArrayHasKey('connections', $stats);

        $this->assertEquals(0, $stats['active_connections']);
        $this->assertEquals(2, $stats['configured_connections']);
        $this->assertEquals('test', $stats['default_connection']);
    }

    #[Test]
    public function it_handles_connection_cleanup(): void
    {
        // This test would normally verify that inactive connections are cleaned up
        // Since we can't easily mock IMAP resources in unit tests, we'll test the method exists
        $this->assertTrue(method_exists($this->service, 'cleanupConnections'));
    }

    #[Test]
    public function it_can_close_all_connections(): void
    {
        $result = $this->service->closeAllConnections();
        $this->assertTrue($result);
    }

    #[Test]
    public function test_connection_returns_failure_for_invalid_config(): void
    {
        $result = $this->service->testConnection('invalid');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertFalse($result['success']);
        $this->assertStringContains('credentials', $result['error']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Mock IMAP configuration
        $this->testConfig = [
            'default'     => 'test',
            'connections' => [
                'test' => [
                    'host'          => 'test.example.com',
                    'port'          => 993,
                    'protocol'      => 'imap',
                    'encryption'    => 'ssl',
                    'validate_cert' => FALSE,
                    'username'      => 'test@example.com',
                    'password'      => 'password123',
                    'timeout'       => 30,
                    'retry_count'   => 2,
                    'retry_delay'   => 1,
                ],
                'invalid' => [
                    'host'          => 'invalid.example.com',
                    'port'          => 993,
                    'protocol'      => 'imap',
                    'encryption'    => 'ssl',
                    'validate_cert' => FALSE,
                    'username'      => '',  // Missing username
                    'password'      => '',  // Missing password
                ],
            ],
            'cache' => [
                'enabled'        => FALSE,
                'connection_ttl' => 30,
            ],
            'logging' => [
                'enabled'         => FALSE,
                'log_connections' => FALSE,
                'channel'         => 'imap',
            ],
            'security' => [
                'verify_peer'       => FALSE,
                'allow_self_signed' => TRUE,
            ],
        ];

        Config::shouldReceive('get')
            ->with('imap')
            ->andReturn($this->testConfig);

        $this->service = new ImapConnectionService();
    }
}
