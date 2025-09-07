<?php declare(strict_types=1);

namespace App\Services\Email;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * IMAP Connection Service
 *
 * Manages IMAP connections for HD Tickets sports events monitoring system.
 * Handles connections to multiple email providers for monitoring ticket
 * availability notifications.
 */
class ImapConnectionService
{
    private array $connections = [];

    private array $config;

    public function __construct()
    {
        $this->config = Config::get('imap');
    }

    /**
     * Get IMAP connection for specified provider
     *
     * @param  string|null      $connection Connection name (gmail, outlook, yahoo, custom)
     * @return resource         IMAP connection resource
     * @throws RuntimeException
     */
    public function getConnection(?string $connection = NULL)
    {
        $connection = $connection ?? $this->config['default'];

        if (!isset($this->config['connections'][$connection])) {
            throw new RuntimeException("IMAP connection '{$connection}' not configured");
        }

        // Return cached connection if available
        if (isset($this->connections[$connection]) && $this->isConnectionActive($this->connections[$connection])) {
            return $this->connections[$connection];
        }

        // Create new connection
        $this->connections[$connection] = $this->createConnection($connection);

        return $this->connections[$connection];
    }

    /**
     * Create new IMAP connection
     *
     * @param  string           $connection Connection name
     * @return resource         IMAP connection resource
     * @throws RuntimeException
     */
    private function createConnection(string $connection)
    {
        $config = $this->config['connections'][$connection];

        if (empty($config['username']) || empty($config['password'])) {
            throw new RuntimeException("IMAP credentials not configured for '{$connection}'");
        }

        // Build connection string
        $connectionString = $this->buildConnectionString($config);

        $retryCount = $config['retry_count'] ?? 3;
        $retryDelay = $config['retry_delay'] ?? 5;

        for ($attempt = 1; $attempt <= $retryCount; $attempt++) {
            try {
                // Log connection attempt
                if ($this->config['logging']['log_connections']) {
                    Log::channel($this->config['logging']['channel'])
                        ->info("Attempting IMAP connection to {$connection} (attempt {$attempt}/{$retryCount})", [
                            'connection' => $connection,
                            'host'       => $config['host'],
                            'port'       => $config['port'],
                            'username'   => $config['username'],
                        ]);
                }

                // Attempt connection
                $imapConnection = @imap_open(
                    $connectionString,
                    $config['username'],
                    $config['password'],
                    0,
                    1,
                    ['DISABLE_AUTHENTICATOR' => 'GSSAPI']
                );

                if ($imapConnection === FALSE) {
                    $error = imap_last_error();

                    throw new RuntimeException("IMAP connection failed: {$error}");
                }

                // Set timeout
                if (isset($config['timeout'])) {
                    imap_timeout(IMAP_READTIMEOUT, $config['timeout']);
                    imap_timeout(IMAP_OPENTIMEOUT, $config['timeout']);
                    imap_timeout(IMAP_CLOSETIMEOUT, $config['timeout']);
                }

                // Log successful connection
                if ($this->config['logging']['log_connections']) {
                    Log::channel($this->config['logging']['channel'])
                        ->info('IMAP connection established successfully', [
                            'connection' => $connection,
                            'attempt'    => $attempt,
                        ]);
                }

                // Cache connection info
                $this->cacheConnectionInfo($connection, $imapConnection);

                return $imapConnection;
            } catch (Exception $e) {
                // Log connection failure
                Log::channel($this->config['logging']['channel'])
                    ->error("IMAP connection attempt {$attempt} failed", [
                        'connection'   => $connection,
                        'error'        => $e->getMessage(),
                        'attempt'      => $attempt,
                        'max_attempts' => $retryCount,
                    ]);

                // If not the last attempt, wait before retrying
                if ($attempt < $retryCount) {
                    sleep($retryDelay);
                } else {
                    throw new RuntimeException(
                        "Failed to establish IMAP connection to '{$connection}' after {$retryCount} attempts: " . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        }
    }

    /**
     * Build IMAP connection string
     *
     * @param  array  $config Connection configuration
     * @return string IMAP connection string
     */
    private function buildConnectionString(array $config): string
    {
        $host = $config['host'];
        $port = $config['port'];
        $protocol = $config['protocol'] ?? 'imap';
        $encryption = $config['encryption'] ?? 'ssl';
        $validateCert = $config['validate_cert'] ?? TRUE;

        $connectionString = "{{$host}:{$port}/{$protocol}";

        if ($encryption) {
            $connectionString .= "/{$encryption}";
        }

        if (!$validateCert) {
            $connectionString .= '/novalidate-cert';
        }

        // Security settings
        $security = $this->config['security'] ?? [];

        if (!($security['verify_peer'] ?? TRUE)) {
            $connectionString .= '/novalidate-cert';
        }

        if ($security['allow_self_signed'] ?? FALSE) {
            $connectionString .= '/self-signed';
        }

        $connectionString .= '}';

        return $connectionString;
    }

    /**
     * Check if IMAP connection is still active
     *
     * @param  resource $connection IMAP connection resource
     * @return bool
     */
    private function isConnectionActive($connection): bool
    {
        if (!is_resource($connection)) {
            return FALSE;
        }

        try {
            // Try to ping the connection
            $result = @imap_ping($connection);

            return $result !== FALSE;
        } catch (Exception $e) {
            return FALSE;
        }
    }

    /**
     * Get list of available mailboxes
     *
     * @param  string|null $connection Connection name
     * @return array       List of mailboxes
     */
    public function getMailboxes(?string $connection = NULL): array
    {
        $imapConnection = $this->getConnection($connection);
        $connectionConfig = $this->config['connections'][$connection ?? $this->config['default']];

        // Build server reference for listing mailboxes
        $serverString = $this->buildConnectionString($connectionConfig);

        try {
            $mailboxes = @imap_list($imapConnection, $serverString, '*');

            if ($mailboxes === FALSE) {
                Log::channel($this->config['logging']['channel'])
                    ->warning('Failed to retrieve mailbox list', [
                        'connection' => $connection ?? $this->config['default'],
                        'error'      => imap_last_error(),
                    ]);

                return [];
            }

            // Clean mailbox names
            $cleanMailboxes = [];
            foreach ($mailboxes as $mailbox) {
                // Extract mailbox name from full path
                $parts = explode('}', $mailbox, 2);
                $cleanMailboxes[] = isset($parts[1]) ? $parts[1] : $mailbox;
            }

            return $cleanMailboxes;
        } catch (Exception $e) {
            Log::channel($this->config['logging']['channel'])
                ->error('Error retrieving mailboxes', [
                    'connection' => $connection ?? $this->config['default'],
                    'error'      => $e->getMessage(),
                ]);

            return [];
        }
    }

    /**
     * Select mailbox for IMAP operations
     *
     * @param  resource $connection IMAP connection
     * @param  string   $mailbox    Mailbox name
     * @return bool     Success status
     */
    public function selectMailbox($connection, string $mailbox): bool
    {
        try {
            $result = @imap_reopen($connection, $this->getMailboxPath($connection, $mailbox));

            if ($result === FALSE) {
                $error = imap_last_error();
                Log::channel($this->config['logging']['channel'])
                    ->error('Failed to select mailbox', [
                        'mailbox' => $mailbox,
                        'error'   => $error,
                    ]);

                return FALSE;
            }

            return TRUE;
        } catch (Exception $e) {
            Log::channel($this->config['logging']['channel'])
                ->error('Error selecting mailbox', [
                    'mailbox' => $mailbox,
                    'error'   => $e->getMessage(),
                ]);

            return FALSE;
        }
    }

    /**
     * Get full mailbox path for IMAP operations
     *
     * @param  resource $connection IMAP connection
     * @param  string   $mailbox    Mailbox name
     * @return string   Full mailbox path
     */
    private function getMailboxPath($connection, string $mailbox): string
    {
        $connectionInfo = $this->getConnectionInfo($connection);

        return $connectionInfo['server'] . $mailbox;
    }

    /**
     * Get connection information
     *
     * @param  resource $connection IMAP connection
     * @return array    Connection information
     */
    private function getConnectionInfo($connection): array
    {
        $cacheKey = 'imap_connection_info_' . spl_object_hash($connection);

        return Cache::remember($cacheKey, $this->config['cache']['connection_ttl'] ?? 30, function () use ($connection) {
            $check = @imap_check($connection);

            if ($check === FALSE) {
                return [
                    'server'   => '',
                    'mailbox'  => '',
                    'messages' => 0,
                ];
            }

            return [
                'server'   => isset($check->Mailbox) ? substr($check->Mailbox, 0, strrpos($check->Mailbox, '}') + 1) : '',
                'mailbox'  => isset($check->Mailbox) ? substr($check->Mailbox, strrpos($check->Mailbox, '}') + 1) : '',
                'messages' => $check->Nmsgs ?? 0,
                'recent'   => $check->Recent ?? 0,
            ];
        });
    }

    /**
     * Cache connection information
     *
     * @param string   $connection     Connection name
     * @param resource $imapConnection IMAP connection
     */
    private function cacheConnectionInfo(string $connection, $imapConnection): void
    {
        if (!$this->config['cache']['enabled']) {
            return;
        }

        $cacheKey = $this->config['cache']['prefix'] . "_connection_{$connection}";
        $ttl = $this->config['cache']['connection_ttl'] ?? 30;

        Cache::put($cacheKey, [
            'connection'     => $connection,
            'established_at' => now(),
            'resource_id'    => spl_object_hash($imapConnection),
        ], $ttl * 60);
    }

    /**
     * Close IMAP connection
     *
     * @param  string|null $connection Connection name
     * @return bool        Success status
     */
    public function closeConnection(?string $connection = NULL): bool
    {
        $connection = $connection ?? $this->config['default'];

        if (!isset($this->connections[$connection])) {
            return TRUE;
        }

        try {
            $result = @imap_close($this->connections[$connection]);
            unset($this->connections[$connection]);

            if ($this->config['logging']['log_connections']) {
                Log::channel($this->config['logging']['channel'])
                    ->info('IMAP connection closed', [
                        'connection' => $connection,
                    ]);
            }

            return $result !== FALSE;
        } catch (Exception $e) {
            Log::channel($this->config['logging']['channel'])
                ->error('Error closing IMAP connection', [
                    'connection' => $connection,
                    'error'      => $e->getMessage(),
                ]);

            return FALSE;
        }
    }

    /**
     * Close all IMAP connections
     *
     * @return bool Success status
     */
    public function closeAllConnections(): bool
    {
        $success = TRUE;

        foreach (array_keys($this->connections) as $connection) {
            if (!$this->closeConnection($connection)) {
                $success = FALSE;
            }
        }

        return $success;
    }

    /**
     * Test IMAP connection
     *
     * @param  string|null $connection Connection name
     * @return array       Test result with status and details
     */
    public function testConnection(?string $connection = NULL): array
    {
        $connection = $connection ?? $this->config['default'];

        try {
            $startTime = microtime(TRUE);
            $imapConnection = $this->getConnection($connection);
            $connectionTime = microtime(TRUE) - $startTime;

            // Test basic operations
            $mailboxes = $this->getMailboxes($connection);
            $connectionInfo = $this->getConnectionInfo($imapConnection);

            return [
                'success'         => TRUE,
                'connection'      => $connection,
                'connection_time' => round($connectionTime, 3),
                'mailboxes_count' => count($mailboxes),
                'messages_count'  => $connectionInfo['messages'] ?? 0,
                'recent_count'    => $connectionInfo['recent'] ?? 0,
                'mailboxes'       => array_slice($mailboxes, 0, 10), // First 10 mailboxes
                'tested_at'       => now()->toISOString(),
            ];
        } catch (Exception $e) {
            return [
                'success'    => FALSE,
                'connection' => $connection,
                'error'      => $e->getMessage(),
                'tested_at'  => now()->toISOString(),
            ];
        }
    }

    /**
     * Get connection statistics
     *
     * @return array Connection statistics
     */
    public function getConnectionStats(): array
    {
        $stats = [
            'active_connections'     => count($this->connections),
            'configured_connections' => count($this->config['connections']),
            'default_connection'     => $this->config['default'],
            'connections'            => [],
        ];

        foreach ($this->connections as $name => $connection) {
            $stats['connections'][$name] = [
                'active'      => $this->isConnectionActive($connection),
                'resource_id' => spl_object_hash($connection),
            ];
        }

        return $stats;
    }

    /**
     * Cleanup inactive connections
     */
    public function cleanupConnections(): void
    {
        foreach ($this->connections as $name => $connection) {
            if (!$this->isConnectionActive($connection)) {
                unset($this->connections[$name]);

                Log::channel($this->config['logging']['channel'])
                    ->info('Removed inactive IMAP connection', [
                        'connection' => $name,
                    ]);
            }
        }
    }

    /**
     * Destructor - close all connections
     */
    public function __destruct()
    {
        $this->closeAllConnections();
    }
}
