<?php

namespace App\Services\Email;

use App\Domain\Email\Models\EmailMessage;
use App\Domain\Event\Models\SportsEvent;
use App\Domain\Ticket\Models\Ticket;
use App\Jobs\ProcessSportsEventEmailJob;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use RuntimeException;

/**
 * Email Monitoring Service
 * 
 * Monitors email inboxes for sports event ticket notifications from various
 * platforms and processes them for the HD Tickets system.
 */
class EmailMonitoringService
{
    private ImapConnectionService $connectionService;
    private EmailParsingService $parsingService;
    private array $config;
    private array $platformPatterns;

    public function __construct(
        ImapConnectionService $connectionService,
        EmailParsingService $parsingService
    ) {
        $this->connectionService = $connectionService;
        $this->parsingService = $parsingService;
        $this->config = Config::get('imap');
        $this->platformPatterns = $this->config['platform_patterns'] ?? [];
    }

    /**
     * Monitor all configured email connections
     * 
     * @return array Monitoring results
     */
    public function monitorAll(): array
    {
        $results = [
            'processed_connections' => 0,
            'total_emails_found' => 0,
            'total_emails_processed' => 0,
            'total_sports_events_identified' => 0,
            'connections' => [],
            'errors' => [],
            'started_at' => now()->toISOString(),
        ];

        $connections = array_keys($this->config['connections'] ?? []);

        foreach ($connections as $connection) {
            try {
                $connectionResult = $this->monitorConnection($connection);
                $results['connections'][$connection] = $connectionResult;
                $results['processed_connections']++;
                $results['total_emails_found'] += $connectionResult['emails_found'];
                $results['total_emails_processed'] += $connectionResult['emails_processed'];
                $results['total_sports_events_identified'] += $connectionResult['sports_events_identified'];

            } catch (Exception $e) {
                $error = [
                    'connection' => $connection,
                    'error' => $e->getMessage(),
                    'occurred_at' => now()->toISOString(),
                ];
                
                $results['errors'][] = $error;
                $results['connections'][$connection] = ['error' => $error];

                Log::channel($this->config['logging']['channel'])
                    ->error("Failed to monitor connection", $error);
            }
        }

        $results['completed_at'] = now()->toISOString();
        $results['duration'] = Carbon::parse($results['started_at'])->diffInSeconds($results['completed_at']);

        return $results;
    }

    /**
     * Monitor specific email connection
     * 
     * @param string $connection Connection name
     * @return array Connection monitoring results
     */
    public function monitorConnection(string $connection): array
    {
        $result = [
            'connection' => $connection,
            'emails_found' => 0,
            'emails_processed' => 0,
            'sports_events_identified' => 0,
            'mailboxes_checked' => [],
            'errors' => [],
            'started_at' => now()->toISOString(),
        ];

        try {
            // Get IMAP connection
            $imapConnection = $this->connectionService->getConnection($connection);
            
            // Get mailboxes to monitor
            $mailboxes = $this->config['monitoring']['mailboxes'] ?? ['INBOX'];
            
            foreach ($mailboxes as $mailbox) {
                try {
                    $mailboxResult = $this->monitorMailbox($imapConnection, $mailbox, $connection);
                    $result['mailboxes_checked'][$mailbox] = $mailboxResult;
                    $result['emails_found'] += $mailboxResult['emails_found'];
                    $result['emails_processed'] += $mailboxResult['emails_processed'];
                    $result['sports_events_identified'] += $mailboxResult['sports_events_identified'];

                } catch (Exception $e) {
                    $error = [
                        'mailbox' => $mailbox,
                        'error' => $e->getMessage(),
                        'occurred_at' => now()->toISOString(),
                    ];
                    
                    $result['errors'][] = $error;
                    $result['mailboxes_checked'][$mailbox] = ['error' => $error];

                    Log::channel($this->config['logging']['channel'])
                        ->error("Failed to monitor mailbox", [
                            'connection' => $connection,
                            'mailbox' => $mailbox,
                            'error' => $e->getMessage(),
                        ]);
                }
            }

        } catch (Exception $e) {
            throw new RuntimeException("Failed to monitor connection '{$connection}': " . $e->getMessage(), 0, $e);
        }

        $result['completed_at'] = now()->toISOString();
        $result['duration'] = Carbon::parse($result['started_at'])->diffInSeconds($result['completed_at']);

        return $result;
    }

    /**
     * Monitor specific mailbox for sports event emails
     * 
     * @param resource $imapConnection IMAP connection
     * @param string $mailbox Mailbox name
     * @param string $connection Connection name
     * @return array Mailbox monitoring results
     */
    private function monitorMailbox($imapConnection, string $mailbox, string $connection): array
    {
        $result = [
            'mailbox' => $mailbox,
            'emails_found' => 0,
            'emails_processed' => 0,
            'sports_events_identified' => 0,
            'started_at' => now()->toISOString(),
        ];

        // Select mailbox
        if (!$this->connectionService->selectMailbox($imapConnection, $mailbox)) {
            throw new RuntimeException("Failed to select mailbox '{$mailbox}'");
        }

        // Get unread emails within the configured age limit
        $emails = $this->getUnreadEmails($imapConnection);
        $result['emails_found'] = count($emails);

        if (empty($emails)) {
            $result['completed_at'] = now()->toISOString();
            return $result;
        }

        // Process emails in batches
        $batchSize = $this->config['monitoring']['batch_size'] ?? 50;
        $emailBatches = array_chunk($emails, $batchSize);

        foreach ($emailBatches as $batch) {
            $batchResult = $this->processEmailBatch($imapConnection, $batch, $connection);
            $result['emails_processed'] += $batchResult['processed'];
            $result['sports_events_identified'] += $batchResult['sports_events_identified'];
        }

        $result['completed_at'] = now()->toISOString();

        return $result;
    }

    /**
     * Get unread emails from mailbox
     * 
     * @param resource $imapConnection IMAP connection
     * @return array Email UIDs
     */
    private function getUnreadEmails($imapConnection): array
    {
        try {
            // Calculate date filter based on max age
            $maxAgeDays = $this->config['monitoring']['max_age_days'] ?? 7;
            $sinceDate = Carbon::now()->subDays($maxAgeDays)->format('d-M-Y');

            // Search for unread emails within the age limit
            $searchCriteria = "UNSEEN SINCE \"{$sinceDate}\"";
            $emails = @imap_search($imapConnection, $searchCriteria, SE_UID);

            if ($emails === false) {
                $error = imap_last_error();
                if ($error && strpos($error, 'SEARCH completed') === false) {
                    Log::channel($this->config['logging']['channel'])
                        ->warning("IMAP search returned no results or error", [
                            'criteria' => $searchCriteria,
                            'error' => $error,
                        ]);
                }
                return [];
            }

            return $emails;

        } catch (Exception $e) {
            Log::channel($this->config['logging']['channel'])
                ->error("Error searching for emails", [
                    'error' => $e->getMessage(),
                ]);
            return [];
        }
    }

    /**
     * Process batch of emails
     * 
     * @param resource $imapConnection IMAP connection
     * @param array $emailUids Email UIDs
     * @param string $connection Connection name
     * @return array Batch processing results
     */
    private function processEmailBatch($imapConnection, array $emailUids, string $connection): array
    {
        $result = [
            'processed' => 0,
            'sports_events_identified' => 0,
            'errors' => [],
        ];

        foreach ($emailUids as $uid) {
            try {
                $emailProcessed = $this->processEmail($imapConnection, $uid, $connection);
                
                if ($emailProcessed['processed']) {
                    $result['processed']++;
                }

                if ($emailProcessed['sports_event_identified']) {
                    $result['sports_events_identified']++;
                }

            } catch (Exception $e) {
                $result['errors'][] = [
                    'uid' => $uid,
                    'error' => $e->getMessage(),
                ];

                Log::channel($this->config['logging']['channel'])
                    ->error("Failed to process email", [
                        'connection' => $connection,
                        'uid' => $uid,
                        'error' => $e->getMessage(),
                    ]);
            }
        }

        return $result;
    }

    /**
     * Process individual email
     * 
     * @param resource $imapConnection IMAP connection
     * @param int $uid Email UID
     * @param string $connection Connection name
     * @return array Email processing result
     */
    private function processEmail($imapConnection, int $uid, string $connection): array
    {
        $result = [
            'processed' => false,
            'sports_event_identified' => false,
            'platform' => null,
        ];

        // Check if email was already processed
        if ($this->isEmailProcessed($uid, $connection)) {
            return $result;
        }

        // Get email headers
        $headers = $this->getEmailHeaders($imapConnection, $uid);
        if (!$headers) {
            return $result;
        }

        // Check if email is from a sports event platform
        $platform = $this->identifyPlatform($headers);
        if (!$platform) {
            $this->markEmailAsProcessed($uid, $connection);
            return $result;
        }

        $result['platform'] = $platform;

        // Check if email contains sports event content
        if (!$this->containsSportsEventContent($imapConnection, $uid, $platform)) {
            $this->markEmailAsProcessed($uid, $connection);
            $result['processed'] = true;
            return $result;
        }

        // Queue email for detailed processing
        $this->queueEmailForProcessing($imapConnection, $uid, $connection, $platform, $headers);

        // Mark email as read if configured
        if ($this->config['monitoring']['mark_as_read'] ?? true) {
            $this->markEmailAsRead($imapConnection, $uid);
        }

        // Mark as processed
        $this->markEmailAsProcessed($uid, $connection);

        $result['processed'] = true;
        $result['sports_event_identified'] = true;

        return $result;
    }

    /**
     * Check if email was already processed
     * 
     * @param int $uid Email UID
     * @param string $connection Connection name
     * @return bool
     */
    private function isEmailProcessed(int $uid, string $connection): bool
    {
        if (!$this->config['cache']['enabled']) {
            return false;
        }

        $cacheKey = $this->config['cache']['prefix'] . "_processed_{$connection}_{$uid}";
        return Cache::has($cacheKey);
    }

    /**
     * Mark email as processed
     * 
     * @param int $uid Email UID
     * @param string $connection Connection name
     */
    private function markEmailAsProcessed(int $uid, string $connection): void
    {
        if (!$this->config['cache']['enabled']) {
            return;
        }

        $cacheKey = $this->config['cache']['prefix'] . "_processed_{$connection}_{$uid}";
        $ttl = $this->config['cache']['processed_ttl'] ?? 24;
        
        Cache::put($cacheKey, true, $ttl * 60 * 60); // Convert hours to seconds
    }

    /**
     * Get email headers
     * 
     * @param resource $imapConnection IMAP connection
     * @param int $uid Email UID
     * @return array|null Email headers
     */
    private function getEmailHeaders($imapConnection, int $uid): ?array
    {
        try {
            $headerInfo = @imap_headerinfo($imapConnection, $uid, 0, 0, 0, UID: true);
            
            if (!$headerInfo) {
                return null;
            }

            return [
                'from' => $headerInfo->from[0] ?? null,
                'to' => $headerInfo->to[0] ?? null,
                'subject' => $headerInfo->subject ?? '',
                'date' => $headerInfo->date ?? '',
                'message_id' => $headerInfo->message_id ?? '',
                'size' => $headerInfo->Size ?? 0,
            ];

        } catch (Exception $e) {
            Log::channel($this->config['logging']['channel'])
                ->error("Error getting email headers", [
                    'uid' => $uid,
                    'error' => $e->getMessage(),
                ]);
            return null;
        }
    }

    /**
     * Identify platform from email headers
     * 
     * @param array $headers Email headers
     * @return string|null Platform identifier
     */
    private function identifyPlatform(array $headers): ?string
    {
        $fromEmail = $headers['from']->mailbox ?? '' . '@' . ($headers['from']->host ?? '');
        $subject = strtolower($headers['subject'] ?? '');

        foreach ($this->platformPatterns as $platform => $patterns) {
            // Check sender patterns
            foreach ($patterns['from_patterns'] ?? [] as $pattern) {
                if (fnmatch($pattern, $fromEmail)) {
                    return $platform;
                }
            }

            // Check subject keywords
            foreach ($patterns['subject_keywords'] ?? [] as $keyword) {
                if (strpos($subject, strtolower($keyword)) !== false) {
                    return $platform;
                }
            }
        }

        return null;
    }

    /**
     * Check if email contains sports event content
     * 
     * @param resource $imapConnection IMAP connection
     * @param int $uid Email UID
     * @param string $platform Platform identifier
     * @return bool
     */
    private function containsSportsEventContent($imapConnection, int $uid, string $platform): bool
    {
        try {
            // Get email body
            $body = $this->getEmailBody($imapConnection, $uid);
            if (!$body) {
                return false;
            }

            $bodyLower = strtolower($body);
            $platformPatterns = $this->platformPatterns[$platform] ?? [];

            // Check for sports event keywords
            $keywords = array_merge(
                $platformPatterns['body_keywords'] ?? [],
                $this->platformPatterns['generic']['body_keywords'] ?? []
            );

            $keywordMatches = 0;
            foreach ($keywords as $keyword) {
                if (strpos($bodyLower, strtolower($keyword)) !== false) {
                    $keywordMatches++;
                }
            }

            // Require at least 2 keyword matches to be considered sports event content
            return $keywordMatches >= 2;

        } catch (Exception $e) {
            Log::channel($this->config['logging']['channel'])
                ->error("Error checking sports event content", [
                    'uid' => $uid,
                    'platform' => $platform,
                    'error' => $e->getMessage(),
                ]);
            return false;
        }
    }

    /**
     * Get email body content
     * 
     * @param resource $imapConnection IMAP connection
     * @param int $uid Email UID
     * @return string|null Email body
     */
    private function getEmailBody($imapConnection, int $uid): ?string
    {
        try {
            $structure = @imap_fetchstructure($imapConnection, $uid, FT_UID);
            if (!$structure) {
                return null;
            }

            // Get body
            $body = @imap_fetchbody($imapConnection, $uid, 1, FT_UID);
            if (!$body) {
                return null;
            }

            // Handle encoding
            if ($structure->encoding === 3) { // Base64
                $body = base64_decode($body);
            } elseif ($structure->encoding === 4) { // Quoted-printable
                $body = quoted_printable_decode($body);
            }

            return $body;

        } catch (Exception $e) {
            Log::channel($this->config['logging']['channel'])
                ->error("Error getting email body", [
                    'uid' => $uid,
                    'error' => $e->getMessage(),
                ]);
            return null;
        }
    }

    /**
     * Queue email for detailed processing
     * 
     * @param resource $imapConnection IMAP connection
     * @param int $uid Email UID
     * @param string $connection Connection name
     * @param string $platform Platform identifier
     * @param array $headers Email headers
     */
    private function queueEmailForProcessing($imapConnection, int $uid, string $connection, string $platform, array $headers): void
    {
        if (!$this->config['queue']['enabled']) {
            return;
        }

        try {
            // Get full email content
            $emailData = [
                'uid' => $uid,
                'connection' => $connection,
                'platform' => $platform,
                'headers' => $headers,
                'body' => $this->getEmailBody($imapConnection, $uid),
                'processed_at' => now()->toISOString(),
            ];

            // Dispatch job
            $queueName = $this->config['queue']['name'] ?? 'email-processing';
            ProcessSportsEventEmailJob::dispatch($emailData)->onQueue($queueName);

            if ($this->config['logging']['log_processed']) {
                Log::channel($this->config['logging']['channel'])
                    ->info("Email queued for processing", [
                        'uid' => $uid,
                        'connection' => $connection,
                        'platform' => $platform,
                        'queue' => $queueName,
                    ]);
            }

        } catch (Exception $e) {
            Log::channel($this->config['logging']['channel'])
                ->error("Failed to queue email for processing", [
                    'uid' => $uid,
                    'connection' => $connection,
                    'platform' => $platform,
                    'error' => $e->getMessage(),
                ]);
        }
    }

    /**
     * Mark email as read
     * 
     * @param resource $imapConnection IMAP connection
     * @param int $uid Email UID
     */
    private function markEmailAsRead($imapConnection, int $uid): void
    {
        try {
            @imap_setflag_full($imapConnection, (string)$uid, '\\Seen', ST_UID);
        } catch (Exception $e) {
            Log::channel($this->config['logging']['channel'])
                ->warning("Failed to mark email as read", [
                    'uid' => $uid,
                    'error' => $e->getMessage(),
                ]);
        }
    }

    /**
     * Get monitoring statistics
     * 
     * @return array Monitoring statistics
     */
    public function getMonitoringStats(): array
    {
        $stats = [
            'total_connections' => count($this->config['connections'] ?? []),
            'active_connections' => 0,
            'total_mailboxes' => count($this->config['monitoring']['mailboxes'] ?? []),
            'platform_patterns' => count($this->platformPatterns),
            'platforms' => array_keys($this->platformPatterns),
        ];

        // Count active connections
        foreach (array_keys($this->config['connections'] ?? []) as $connection) {
            try {
                $testResult = $this->connectionService->testConnection($connection);
                if ($testResult['success']) {
                    $stats['active_connections']++;
                }
            } catch (Exception $e) {
                // Connection not available
            }
        }

        return $stats;
    }

    /**
     * Clear processed emails cache
     * 
     * @param string|null $connection Specific connection or all
     */
    public function clearProcessedCache(?string $connection = null): void
    {
        if (!$this->config['cache']['enabled']) {
            return;
        }

        $prefix = $this->config['cache']['prefix'] . '_processed';
        
        if ($connection) {
            $pattern = "{$prefix}_{$connection}_*";
        } else {
            $pattern = "{$prefix}_*";
        }

        // This would need a custom implementation based on your cache driver
        // For Redis, you could use KEYS pattern and DEL commands
        // For other drivers, you might need to track keys differently
    }
}
