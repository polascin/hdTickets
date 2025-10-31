<?php declare(strict_types=1);

namespace App\Services\NotificationChannels;

use App\Models\User;
use App\Models\UserNotificationSettings;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use function in_array;
use function is_array;
use function strlen;

class WebhookNotificationChannel
{
    protected $defaultWebhookUrl;

    protected $timeout;

    public function __construct()
    {
        $this->defaultWebhookUrl = config('services.webhook.default_url');
        $this->timeout = config('services.webhook.timeout', 10);
    }

    /**
     * Send notification via webhook
     */
    /**
     * Send
     */
    public function send(User $user, array $alertData): bool
    {
        try {
            $webhookSettings = $this->getUserWebhookSettings($user);

            if (!$webhookSettings || !$webhookSettings->is_enabled) {
                Log::info('Webhook notifications disabled for user', ['user_id' => $user->id]);

                return FALSE;
            }

            $webhookUrl = $webhookSettings->webhook_url ?? $this->defaultWebhookUrl;

            if (!$webhookUrl) {
                Log::warning('No webhook URL configured for user', ['user_id' => $user->id]);

                return FALSE;
            }

            $payload = $this->buildWebhookPayload($alertData, $user);

            return $this->sendWebhook($webhookUrl, $payload, $webhookSettings);
        } catch (Exception $e) {
            Log::error('Failed to send webhook notification', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return FALSE;
        }
    }

    /**
     * Test webhook connection
     */
    /**
     * TestConnection
     */
    public function testConnection(User $user): array
    {
        try {
            $testPayload = [
                'event'     => 'webhook_test',
                'version'   => '1.0',
                'timestamp' => now()->toISOString(),
                'user'      => [
                    'id'    => $user->id,
                    'email' => $user->email,
                    'name'  => $user->name,
                ],
                'test_data' => [
                    'message'    => 'This is a test webhook from HDTickets',
                    'event_name' => 'Test Event',
                    'price'      => 99.99,
                    'quantity'   => 2,
                ],
            ];

            $webhookSettings = $this->getUserWebhookSettings($user);

            if (!$webhookSettings || !$webhookSettings->webhook_url) {
                return [
                    'success' => FALSE,
                    'message' => 'No webhook URL configured',
                ];
            }

            $success = $this->sendWebhook($webhookSettings->webhook_url, $testPayload, $webhookSettings);

            return [
                'success' => $success,
                'message' => $success ? 'Webhook test sent successfully' : 'Failed to send webhook test',
            ];
        } catch (Exception $e) {
            return [
                'success' => FALSE,
                'message' => 'Webhook test failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate webhook URL format
     */
    /**
     * ValidateWebhookUrl
     */
    public function validateWebhookUrl(string $url): bool
    {
        // Basic URL validation
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return FALSE;
        }

        // Must be HTTP or HTTPS
        $parsed = parse_url($url);
        if (!in_array($parsed['scheme'] ?? '', ['http', 'https'], TRUE)) {
            return FALSE;
        }

        // Block local/private IPs for security
        $host = $parsed['host'] ?? '';

        return !(filter_var($host, FILTER_VALIDATE_IP) && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE));
    }

    /**
     * Send webhook with different HTTP methods
     */
    /**
     * SendWithMethod
     */
    public function sendWithMethod(User $user, array $alertData, string $method = 'POST'): bool
    {
        $webhookSettings = $this->getUserWebhookSettings($user);

        if (!$webhookSettings || !$webhookSettings->webhook_url) {
            return FALSE;
        }

        $payload = $this->buildWebhookPayload($alertData, $user);
        $headers = $this->buildHeaders($webhookSettings);

        try {
            $http = Http::withHeaders($headers)->timeout($this->timeout);

            switch (strtoupper($method)) {
                case 'POST':
                    $response = $http->post($webhookSettings->webhook_url, $payload);

                    break;
                case 'PUT':
                    $response = $http->put($webhookSettings->webhook_url, $payload);

                    break;
                case 'PATCH':
                    $response = $http->patch($webhookSettings->webhook_url, $payload);

                    break;
                default:
                    return FALSE;
            }

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Webhook request with method failed', [
                'method' => $method,
                'url'    => $webhookSettings->webhook_url,
                'error'  => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Get webhook delivery status
     */
    /**
     * Get  delivery status
     */
    public function getDeliveryStatus(string $webhookId): ?array
    {
        // This would integrate with a webhook delivery tracking system
        return Cache::get("webhook_delivery:{$webhookId}");
    }

    /**
     * Build webhook payload in standardized format
     */
    /**
     * BuildWebhookPayload
     */
    protected function buildWebhookPayload(array $alertData, User $user): array
    {
        $ticket = $alertData['ticket'];
        $alert = $alertData['alert'];

        return [
            'event'     => 'ticket_alert',
            'version'   => '1.0',
            'timestamp' => now()->toISOString(),
            'user'      => [
                'id'    => $user->id,
                'email' => $user->email,
                'name'  => $user->name,
            ],
            'alert' => [
                'id'             => $alert['id'],
                'priority'       => $alertData['priority'] ?? 2,
                'priority_label' => $alertData['priority_label'] ?? 'Normal',
            ],
            'ticket' => [
                'id'         => $ticket['id'],
                'event_name' => $ticket['event_name'],
                'price'      => $ticket['price'],
                'quantity'   => $ticket['quantity'],
                'platform'   => $ticket['platform'],
                'venue'      => $ticket['venue'] ?? NULL,
                'event_date' => $ticket['event_date'] ?? NULL,
                'url'        => $ticket['url'] ?? NULL,
            ],
            'prediction' => $alertData['prediction'] ?? NULL,
            'context'    => $alertData['context'] ?? NULL,
            'escalation' => $alertData['escalation'] ?? NULL,
            'actions'    => $alertData['actions'] ?? NULL,
            'metadata'   => $alertData['metadata'] ?? NULL,
        ];
    }

    /**
     * Send webhook with retry logic
     *
     * @param mixed $settings
     */
    /**
     * SendWebhook
     *
     * @param mixed $settings
     */
    protected function sendWebhook(string $url, array $payload, array $settings): bool
    {
        $headers = $this->buildHeaders($settings);
        $maxRetries = $settings->max_retries ?? 3;
        $retryDelay = $settings->retry_delay ?? 1;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Http::withHeaders($headers)
                    ->timeout($this->timeout)
                    ->post($url, $payload);

                if ($response->successful()) {
                    Log::info('Webhook notification sent successfully', [
                        'url'     => $url,
                        'attempt' => $attempt,
                        'status'  => $response->status(),
                    ]);

                    return TRUE;
                }

                // Check if we should retry based on status code
                if (!$this->shouldRetry($response->status()) || $attempt === $maxRetries) {
                    Log::error('Webhook notification failed', [
                        'url'      => $url,
                        'attempt'  => $attempt,
                        'status'   => $response->status(),
                        'response' => $response->body(),
                    ]);

                    return FALSE;
                }

                // Wait before retry
                sleep($retryDelay * $attempt);
            } catch (Exception $e) {
                Log::error('Webhook request exception', [
                    'url'     => $url,
                    'attempt' => $attempt,
                    'error'   => $e->getMessage(),
                ]);

                if ($attempt === $maxRetries) {
                    return FALSE;
                }

                sleep($retryDelay * $attempt);
            }
        }

        return FALSE;
    }

    /**
     * Build headers for webhook request
     *
     * @param mixed $settings
     */
    /**
     * BuildHeaders
     */
    protected function buildHeaders(array $settings): array
    {
        $headers = [
            'Content-Type'          => 'application/json',
            'User-Agent'            => 'HDTickets-Webhook/1.0',
            'X-HDTickets-Timestamp' => time(),
            'X-HDTickets-Version'   => '1.0',
        ];

        // Add authentication headers if configured
        if ($settings->auth_type === 'bearer' && $settings->auth_token) {
            $headers['Authorization'] = 'Bearer ' . $settings->auth_token;
        } elseif ($settings->auth_type === 'api_key' && $settings->api_key) {
            $headers['X-API-Key'] = $settings->api_key;
        } elseif ($settings->auth_type === 'basic' && $settings->basic_username && $settings->basic_password) {
            $headers['Authorization'] = 'Basic ' . base64_encode($settings->basic_username . ':' . $settings->basic_password);
        }

        // Add signature if secret is configured
        if ($settings->webhook_secret) {
            $headers['X-HDTickets-Signature'] = $this->generateSignature($settings->webhook_secret);
        }

        // Add custom headers
        if ($settings->custom_headers) {
            $customHeaders = json_decode($settings->custom_headers, TRUE);
            if (is_array($customHeaders)) {
                $headers = array_merge($headers, $customHeaders);
            }
        }

        return $headers;
    }

    /**
     * Generate webhook signature for verification
     */
    /**
     * GenerateSignature
     */
    protected function generateSignature(string $secret, string $payload = ''): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Determine if request should be retried based on status code
     */
    /**
     * ShouldRetry
     */
    protected function shouldRetry(int $statusCode): bool
    {
        // Retry on server errors and rate limiting
        return in_array($statusCode, [429, 500, 502, 503, 504, 520, 521, 522, 523, 524], TRUE);
    }

    /**
     * Get user's webhook settings
     */
    protected function getUserWebhookSettings(User $user)
    {
        return Cache::remember("webhook_settings:{$user->id}", 3600, fn () => UserNotificationSettings::where('user_id', $user->id)
            ->where('channel', 'webhook')
            ->first());
    }

    /**
     * Store webhook delivery attempt
     */
    /**
     * StoreDeliveryAttempt
     */
    protected function storeDeliveryAttempt(string $url, array $payload, bool $success, ?string $error = NULL): void
    {
        // Store delivery attempt for debugging and monitoring
        Cache::put('webhook_delivery:' . md5($url . time()), [
            'url'          => $url,
            'payload_size' => strlen(json_encode($payload)),
            'success'      => $success,
            'error'        => $error,
            'timestamp'    => now()->toISOString(),
        ], 86400); // 24 hours
    }
}
