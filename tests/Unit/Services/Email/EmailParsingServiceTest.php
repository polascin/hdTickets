<?php declare(strict_types=1);

namespace Tests\Unit\Services\Email;

use PHPUnit\Framework\Attributes\Test;
use App\Services\Email\EmailParsingService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function function_exists;

/**
 * Email Parsing Service Tests
 *
 * Unit tests for the EmailParsingService class in the HD Tickets
 * sports events monitoring system.
 */
class EmailParsingServiceTest extends TestCase
{
    private EmailParsingService $service;

    /**
     */
    #[Test]
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(EmailParsingService::class, $this->service);
    }

    /**
     */
    #[Test]
    public function it_detects_sport_categories_correctly(): void
    {
        // Use reflection to access private method
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('detectSportCategory');

        // Test various sport categories
        $this->assertEquals('football', $method->invoke($this->service, 'NFL Cowboys vs Patriots'));
        $this->assertEquals('basketball', $method->invoke($this->service, 'Lakers vs Warriors NBA Finals'));
        $this->assertEquals('baseball', $method->invoke($this->service, 'Yankees vs Red Sox MLB'));
        $this->assertEquals('hockey', $method->invoke($this->service, 'Rangers vs Bruins NHL'));
        $this->assertEquals('soccer', $method->invoke($this->service, 'Manchester United FC'));
        $this->assertEquals('tennis', $method->invoke($this->service, 'Wimbledon Tennis Championship'));
        $this->assertEquals('unknown', $method->invoke($this->service, 'Random Event Name'));
    }

    /**
     */
    #[Test]
    public function it_parses_ticketmaster_emails(): void
    {
        $headers = [
            'subject' => 'Cowboys vs Patriots Tickets Available',
            'from'    => (object) ['mailbox' => 'noreply', 'host' => 'ticketmaster.com'],
        ];

        $body = "Event: Dallas Cowboys vs New England Patriots\n" .
                "Venue: AT&T Stadium\n" .
                "Date: December 15, 2024\n" .
                "Price: Starting at $89.50\n" .
                '100 tickets available';

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('parseTicketmasterEmail');

        $result = $method->invoke($this->service, $headers, $body);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('sports_events', $result);
        $this->assertArrayHasKey('tickets', $result);

        // Should extract the event
        $this->assertNotEmpty($result['sports_events']);
        $this->assertEquals('Dallas Cowboys vs New England Patriots', $result['sports_events'][0]['name']);
        $this->assertEquals('ticketmaster', $result['sports_events'][0]['source_platform']);
        $this->assertEquals('football', $result['sports_events'][0]['category']);
    }

    /**
     */
    #[Test]
    public function it_parses_stubhub_emails(): void
    {
        $headers = [
            'subject' => 'Price Drop for Lakers vs Warriors',
            'from'    => (object) ['mailbox' => 'noreply', 'host' => 'stubhub.com'],
        ];

        $body = "Great news regarding Lakers vs Warriors at Staples Center on January 20, 2024\n" .
                'Section 100, Row A tickets dropped to $250 each';

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('parseStubhubEmail');

        $result = $method->invoke($this->service, $headers, $body);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('sports_events', $result);
        $this->assertArrayHasKey('tickets', $result);

        // Should extract tickets with pricing
        $this->assertNotEmpty($result['tickets']);
        $this->assertEquals(250.0, $result['tickets'][0]['price']);
        $this->assertEquals('stubhub', $result['tickets'][0]['source_platform']);
    }

    /**
     */
    #[Test]
    public function it_parses_generic_sports_emails(): void
    {
        $headers = [
            'subject' => 'Yankees vs Red Sox Game Tonight',
            'from'    => (object) ['mailbox' => 'info', 'host' => 'sportstickets.com'],
        ];

        $body = "Don't miss Yankees vs Red Sox tonight!\n" .
                "Tickets starting at $45.00\n" .
                'Premium seats available for $125.50';

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('parseGenericEmail');

        $result = $method->invoke($this->service, $headers, $body);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('sports_events', $result);
        $this->assertArrayHasKey('tickets', $result);

        // Should detect baseball event
        $this->assertNotEmpty($result['sports_events']);
        $this->assertEquals('baseball', $result['sports_events'][0]['category']);

        // Should extract multiple ticket prices
        $this->assertCount(2, $result['tickets']);
    }

    /**
     */
    #[Test]
    public function it_validates_parsed_data(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('validateParsedData');

        $parsedData = [
            'sports_events' => [
                ['name' => 'Valid Event Name', 'category' => 'football'],
                ['name' => '', 'category' => 'basketball'], // Should be filtered out
                ['name' => 'OK', 'category' => 'hockey'], // Too short, should be filtered out
            ],
            'tickets' => [
                ['price' => 50.0],
                ['price' => -10.0], // Invalid price, should be filtered out
                ['price' => 100000.0], // Too expensive, should be filtered out
                ['price' => 'invalid'], // Non-numeric, should be filtered out
            ],
        ];

        $result = $method->invoke($this->service, $parsedData);

        // Should only keep valid sports events
        $this->assertCount(1, $result['sports_events']);
        $this->assertEquals('Valid Event Name', $result['sports_events'][0]['name']);

        // Should only keep valid tickets
        $this->assertCount(1, $result['tickets']);
        $this->assertEquals(50.0, $result['tickets'][0]['price']);
    }

    /**
     */
    #[Test]
    public function it_extracts_email_metadata(): void
    {
        $headers = [
            'subject'    => 'Test Sports Event',
            'from'       => (object) ['mailbox' => 'test', 'host' => 'example.com'],
            'message_id' => '<test123@example.com>',
            'size'       => 1024,
            'date'       => '2024-01-15 10:30:00',
        ];

        $body = "This email contains ticket information for a sports event.\n" .
                "Visit https://example.com/tickets for more details.\n" .
                'The game is at the stadium tonight.';

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('extractMetadata');

        $result = $method->invoke($this->service, $headers, $body);

        $this->assertIsArray($result);
        $this->assertEquals('Test Sports Event', $result['subject']);
        $this->assertEquals('test@example.com', $result['from_email']);
        $this->assertEquals('<test123@example.com>', $result['message_id']);
        $this->assertEquals(1024, $result['size']);

        // Should extract URLs
        $this->assertArrayHasKey('urls', $result);
        $this->assertContains('https://example.com/tickets', $result['urls']);

        // Should count keywords
        $this->assertArrayHasKey('keyword_frequency', $result);
        $this->assertEquals(2, $result['keyword_frequency']['ticket']); // "ticket" and "tickets"
        $this->assertEquals(2, $result['keyword_frequency']['sports']);
        $this->assertEquals(1, $result['keyword_frequency']['stadium']);
    }

    /**
     */
    #[Test]
    public function it_parses_event_dates(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('parseEventDate');

        // Test various date formats
        $this->assertEquals('2024-01-15', $method->invoke($this->service, 'Jan 15, 2024'));
        $this->assertEquals('2024-01-15', $method->invoke($this->service, 'January 15, 2024'));
        $this->assertEquals('2024-01-15', $method->invoke($this->service, '2024-01-15'));
        $this->assertNull($method->invoke($this->service, 'invalid date'));
        $this->assertNull($method->invoke($this->service, NULL));
    }

    /**
     */
    #[Test]
    public function it_gets_parsing_statistics(): void
    {
        $stats = $this->service->getParsingStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('supported_platforms', $stats);
        $this->assertArrayHasKey('sport_categories', $stats);

        $this->assertContains('ticketmaster', $stats['supported_platforms']);
        $this->assertContains('stubhub', $stats['supported_platforms']);
        $this->assertContains('football', $stats['sport_categories']);
        $this->assertContains('basketball', $stats['sport_categories']);
    }

    /**
     */
    #[Test]
    public function it_parses_complete_email_data(): void
    {
        $emailData = [
            'uid'        => 123,
            'connection' => 'gmail',
            'platform'   => 'ticketmaster',
            'headers'    => [
                'subject' => 'Cowboys vs Patriots - Tickets Available',
                'from'    => (object) ['mailbox' => 'noreply', 'host' => 'ticketmaster.com'],
            ],
            'body' => "Event: Dallas Cowboys vs New England Patriots\n" .
                     "Venue: AT&T Stadium\n" .
                     'Price: $75.00 each',
        ];

        $result = $this->service->parseEmailContent($emailData);

        $this->assertIsArray($result);
        $this->assertEquals('ticketmaster', $result['platform']);
        $this->assertEquals(123, $result['email_uid']);
        $this->assertEquals('gmail', $result['connection']);
        $this->assertArrayHasKey('sports_events', $result);
        $this->assertArrayHasKey('tickets', $result);
        $this->assertArrayHasKey('metadata', $result);

        // Should have processed sports events and tickets
        $this->assertNotEmpty($result['sports_events']);
        $this->assertNotEmpty($result['tickets']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Mock config function
        if (! function_exists('config')) {
            function config($key = NULL): array
            {
                return [
                    'logging' => [
                        'channel' => 'imap',
                    ],
                ];
            }
        }

        $this->service = new EmailParsingService();
    }
}
