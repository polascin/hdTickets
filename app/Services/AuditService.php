<?php declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use function strlen;

class AuditService
{
    /**
     * Log PayPal transaction event
     */
    public function logPayPalTransaction(
        string $action,
        array $data,
        ?User $user = NULL,
        string $level = 'info',
    ): void {
        $this->logEvent('paypal_transaction', $action, $data, $user, $level);
    }

    /**
     * Log PayPal webhook event
     */
    public function logPayPalWebhook(
        string $eventType,
        array $payload,
        array $headers,
        bool $verified = FALSE,
        ?string $error = NULL,
    ): void {
        $data = [
            'event_type'   => $eventType,
            'webhook_id'   => $headers['PAYPAL-TRANSMISSION-ID'] ?? NULL,
            'verified'     => $verified,
            'payload_size' => strlen(json_encode($payload)),
            'headers'      => [
                'transmission_id'   => $headers['PAYPAL-TRANSMISSION-ID'] ?? NULL,
                'cert_id'           => $headers['PAYPAL-CERT-ID'] ?? NULL,
                'auth_algo'         => $headers['PAYPAL-AUTH-ALGO'] ?? NULL,
                'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'] ?? NULL,
            ],
            'error' => $error,
        ];

        $level = $error ? 'error' : ($verified ? 'info' : 'warning');
        $this->logEvent('paypal_webhook', $eventType, $data, NULL, $level);
    }

    /**
     * Log PayPal payment attempt
     */
    public function logPaymentAttempt(
        User $user,
        string $paymentMethod,
        float $amount,
        string $currency,
        bool $success,
        ?string $orderId = NULL,
        ?string $error = NULL,
    ): void {
        $data = [
            'payment_method' => $paymentMethod,
            'amount'         => $amount,
            'currency'       => $currency,
            'success'        => $success,
            'order_id'       => $orderId,
            'error'          => $error,
            'user_agent'     => request()?->userAgent(),
            'ip_address'     => request()?->ip(),
        ];

        $level = $success ? 'info' : 'warning';
        $action = $success ? 'payment_completed' : 'payment_failed';

        $this->logEvent('payment_attempt', $action, $data, $user, $level);
    }

    /**
     * Log subscription lifecycle event
     */
    public function logSubscriptionEvent(
        User $user,
        string $action,
        array $subscriptionData,
        bool $success = TRUE,
        ?string $error = NULL,
    ): void {
        $data = array_merge($subscriptionData, [
            'success'    => $success,
            'error'      => $error,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);

        $level = $success ? 'info' : 'error';
        $this->logEvent('subscription', $action, $data, $user, $level);
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(
        string $event,
        array $data,
        string $level = 'warning',
        ?User $user = NULL,
    ): void {
        $securityData = array_merge($data, [
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'timestamp'  => now()->toISOString(),
        ]);

        $this->logEvent('security', $event, $securityData, $user, $level);
    }

    /**
     * Log failed payment attempt with fraud detection data
     */
    public function logFailedPayment(
        ?User $user,
        string $paymentMethod,
        float $amount,
        string $error,
        array $fraudData = [],
    ): void {
        $request = request();
        $data = [
            'payment_method'   => $paymentMethod,
            'amount'           => $amount,
            'error'            => $error,
            'ip_address'       => $request?->ip(),
            'user_agent'       => $request?->userAgent(),
            'fraud_indicators' => $fraudData,
            'session_id'       => session()->getId(),
            'referer'          => $request?->header('referer'),
        ];

        // Check for suspicious patterns
        if ($this->isSuspiciousActivity($data)) {
            $data['flagged_as_suspicious'] = TRUE;
            $level = 'error';
        } else {
            $level = 'warning';
        }

        $this->logEvent('payment_failure', 'failed_payment', $data, $user, $level);
    }

    /**
     * Log idempotency check
     */
    public function logIdempotencyCheck(
        string $key,
        bool $duplicate,
        array $context = [],
    ): void {
        $data = [
            'idempotency_key' => $key,
            'is_duplicate'    => $duplicate,
            'context'         => $context,
            'ip_address'      => request()?->ip(),
        ];

        $level = $duplicate ? 'warning' : 'debug';
        $action = $duplicate ? 'duplicate_request' : 'new_request';

        $this->logEvent('idempotency', $action, $data, NULL, $level);
    }

    /**
     * Log rate limiting event
     */
    public function logRateLimit(
        string $endpoint,
        int $attempts,
        int $maxAttempts,
        ?User $user = NULL,
    ): void {
        $data = [
            'endpoint'     => $endpoint,
            'attempts'     => $attempts,
            'max_attempts' => $maxAttempts,
            'exceeded'     => $attempts >= $maxAttempts,
            'ip_address'   => request()?->ip(),
            'user_agent'   => request()?->userAgent(),
        ];

        $level = $attempts >= $maxAttempts ? 'warning' : 'info';
        $action = $attempts >= $maxAttempts ? 'rate_limit_exceeded' : 'rate_limit_check';

        $this->logEvent('rate_limit', $action, $data, $user, $level);
    }

    /**
     * Get audit statistics for dashboard
     */
    public function getPayPalAuditStats(int $days = 30): array
    {
        $since = now()->subDays($days);

        return [
            'total_transactions' => AuditLog::where('event', 'LIKE', 'paypal_transaction.%')
                ->where('created_at', '>', $since)
                ->count(),

            'successful_payments' => AuditLog::where('event', 'paypal_transaction.payment_completed')
                ->where('created_at', '>', $since)
                ->count(),

            'failed_payments' => AuditLog::where('event', 'LIKE', 'payment_failure.%')
                ->where('created_at', '>', $since)
                ->count(),

            'webhook_events' => AuditLog::where('event', 'LIKE', 'paypal_webhook.%')
                ->where('created_at', '>', $since)
                ->count(),

            'security_events' => AuditLog::where('event', 'LIKE', 'security.%')
                ->where('created_at', '>', $since)
                ->count(),

            'suspicious_activities' => AuditLog::where('tags', 'LIKE', '%suspicious%')
                ->where('created_at', '>', $since)
                ->count(),
        ];
    }

    /**
     * Core event logging method
     */
    private function logEvent(
        string $category,
        string $action,
        array $data,
        ?User $user = NULL,
        string $level = 'info',
    ): void {
        try {
            // Log to Laravel log
            Log::channel('audit')->$level("Audit: {$category}.{$action}", [
                'category'  => $category,
                'action'    => $action,
                'user_id'   => $user?->id,
                'data'      => $data,
                'timestamp' => now()->toISOString(),
            ]);

            // Store in database audit log
            AuditLog::create([
                'user_id'        => $user?->id ?? Auth::id(),
                'event'          => "{$category}.{$action}",
                'auditable_type' => $this->determineAuditableType($category),
                'auditable_id'   => $this->determineAuditableId($category, $data),
                'old_values'     => $data['old_values'] ?? NULL,
                'new_values'     => $data['new_values'] ?? NULL,
                'url'            => request()?->fullUrl(),
                'ip_address'     => request()?->ip(),
                'user_agent'     => request()?->userAgent(),
                'tags'           => $this->generateTags($category, $action, $data),
                'properties'     => $data,
            ]);
        } catch (Exception $e) {
            // Fallback logging if audit log fails
            Log::error('Failed to write audit log', [
                'error'         => $e->getMessage(),
                'category'      => $category,
                'action'        => $action,
                'original_data' => $data,
            ]);
        }
    }

    /**
     * Determine auditable type based on category
     */
    private function determineAuditableType(string $category): ?string
    {
        return match ($category) {
            'paypal_transaction', 'payment_attempt', 'payment_failure' => 'App\\Models\\PurchaseAttempt',
            'subscription'   => 'App\\Models\\UserSubscription',
            'paypal_webhook' => 'App\\Models\\WebhookEvent',
            'security'       => 'App\\Models\\SecurityEvent',
            default          => NULL,
        };
    }

    /**
     * Determine auditable ID from data
     */
    private function determineAuditableId(string $category, array $data): ?int
    {
        return match ($category) {
            'paypal_transaction', 'payment_attempt' => $data['purchase_attempt_id'] ?? NULL,
            'subscription' => $data['subscription_id'] ?? NULL,
            default        => NULL,
        };
    }

    /**
     * Generate tags for easier searching
     */
    private function generateTags(string $category, string $action, array $data): array
    {
        $tags = [$category, $action];

        // Add payment method tags
        if (isset($data['payment_method'])) {
            $tags[] = 'payment:' . $data['payment_method'];
        }

        // Add status tags
        if (isset($data['success'])) {
            $tags[] = $data['success'] ? 'success' : 'failure';
        }

        // Add amount range tags for payments
        if (isset($data['amount'])) {
            $amount = (float) $data['amount'];
            if ($amount < 10) {
                $tags[] = 'amount:small';
            } elseif ($amount < 100) {
                $tags[] = 'amount:medium';
            } else {
                $tags[] = 'amount:large';
            }
        }

        // Add security flags
        if (isset($data['flagged_as_suspicious'])) {
            $tags[] = 'suspicious';
        }

        return array_unique($tags);
    }

    /**
     * Check for suspicious activity patterns
     */
    private function isSuspiciousActivity(array $data): bool
    {
        $suspiciousIndicators = 0;

        // Multiple rapid failures from same IP
        if (isset($data['ip_address'])) {
            $recentFailures = AuditLog::where('event', 'LIKE', 'payment_failure.%')
                ->where('ip_address', $data['ip_address'])
                ->where('created_at', '>', now()->subMinutes(15))
                ->count();

            if ($recentFailures > 3) {
                $suspiciousIndicators++;
            }
        }

        // Unusual user agent
        if (isset($data['user_agent'])) {
            if (empty($data['user_agent'])
                || strlen($data['user_agent']) < 10
                || preg_match('/bot|crawler|scanner|wget|curl/i', $data['user_agent'])) {
                $suspiciousIndicators++;
            }
        }

        // High amount with failure
        if (isset($data['amount']) && $data['amount'] > 1000) {
            $suspiciousIndicators++;
        }

        // Missing referer on payment page
        if (isset($data['referer']) && empty($data['referer'])) {
            $suspiciousIndicators++;
        }

        return $suspiciousIndicators >= 2;
    }
}
