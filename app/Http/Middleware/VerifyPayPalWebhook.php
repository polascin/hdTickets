<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\PayPal\PayPalService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class VerifyPayPalWebhook
{
    /**
     * PayPal's known IP ranges for webhook requests
     * These should be updated periodically from PayPal's documentation
     */
    private const PAYPAL_IP_RANGES = [
        // PayPal Sandbox IPs
        '173.0.80.0/20',
        '173.0.88.0/21',
        '173.0.96.0/20',

        // PayPal Production IPs
        '64.4.240.0/21',
        '64.4.248.0/21',
        '66.211.168.0/22',
        '173.0.80.0/20',
        '173.0.88.0/21',
        '173.0.96.0/20',
        '199.19.80.0/22',
        '199.19.84.0/22',

        // Additional PayPal IP ranges
        '207.38.80.0/20',
        '207.38.96.0/20',
        '216.113.160.0/19',
        '216.113.192.0/20',
    ];

    public function __construct(
        private PayPalService $paypalService
    ) {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): HttpResponse
    {
        // Skip verification in local development if configured
        if (app()->environment('local') && config('paypal.skip_webhook_verification', FALSE)) {
            Log::warning('PayPal webhook verification skipped in development environment');

            return $next($request);
        }

        // Verify IP address is from PayPal
        if (!$this->isValidPayPalIp($request->ip())) {
            Log::warning('PayPal webhook request from invalid IP', [
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'headers'    => $request->headers->all(),
            ]);

            return response('Forbidden - Invalid source IP', 403);
        }

        // Verify required PayPal headers are present
        $requiredHeaders = [
            'PAYPAL-TRANSMISSION-ID',
            'PAYPAL-CERT-ID',
            'PAYPAL-AUTH-ALGO',
            'PAYPAL-TRANSMISSION-SIG',
            'PAYPAL-TRANSMISSION-TIME',
        ];

        foreach ($requiredHeaders as $header) {
            if (!$request->hasHeader($header)) {
                Log::warning('PayPal webhook missing required header', [
                    'missing_header' => $header,
                    'ip'             => $request->ip(),
                ]);

                return response('Bad Request - Missing PayPal headers', 400);
            }
        }

        // Verify webhook signature
        if (!$this->verifyWebhookSignature($request)) {
            Log::error('PayPal webhook signature verification failed', [
                'ip'      => $request->ip(),
                'headers' => $this->extractPayPalHeaders($request),
            ]);

            return response('Unauthorized - Invalid signature', 401);
        }

        // Check for replay attacks (webhook timestamp should be recent)
        if (!$this->isRecentTimestamp($request->header('PAYPAL-TRANSMISSION-TIME'))) {
            Log::warning('PayPal webhook timestamp too old - possible replay attack', [
                'timestamp' => $request->header('PAYPAL-TRANSMISSION-TIME'),
                'ip'        => $request->ip(),
            ]);

            return response('Bad Request - Timestamp too old', 400);
        }

        // Add security metadata to request for logging
        $request->merge([
            '_paypal_security_verified' => TRUE,
            '_paypal_transmission_id'   => $request->header('PAYPAL-TRANSMISSION-ID'),
            '_paypal_verified_ip'       => $request->ip(),
        ]);

        Log::info('PayPal webhook security verification passed', [
            'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
            'ip'              => $request->ip(),
            'event_type'      => $this->extractEventType($request),
        ]);

        return $next($request);
    }

    /**
     * Verify the IP address is from PayPal
     */
    private function isValidPayPalIp(string $ip): bool
    {
        // Skip IP validation in local development
        if (app()->environment('local')) {
            return TRUE;
        }

        foreach (self::PAYPAL_IP_RANGES as $range) {
            if ($this->ipInRange($ip, $range)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Check if IP is in CIDR range
     */
    private function ipInRange(string $ip, string $range): bool
    {
        if (strpos($range, '/') === FALSE) {
            // Single IP comparison
            return $ip === $range;
        }

        list($subnet, $bits) = explode('/', $range);

        if ($bits === NULL) {
            $bits = 32;
        }

        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;

        return ($ip & $mask) === $subnet;
    }

    /**
     * Verify PayPal webhook signature
     */
    private function verifyWebhookSignature(Request $request): bool
    {
        try {
            $headers = $this->extractPayPalHeaders($request);
            $body = $request->getContent();
            $webhookId = config('services.paypal.webhook_id');

            if (!$webhookId) {
                Log::error('PayPal webhook ID not configured');

                return FALSE;
            }

            return $this->paypalService->verifyWebhookSignature($headers, $body, $webhookId);
        } catch (\Exception $e) {
            Log::error('PayPal webhook signature verification error', [
                'error' => $e->getMessage(),
                'ip'    => $request->ip(),
            ]);

            return FALSE;
        }
    }

    /**
     * Extract PayPal headers from request
     */
    private function extractPayPalHeaders(Request $request): array
    {
        return [
            'PAYPAL-TRANSMISSION-ID'   => $request->header('PAYPAL-TRANSMISSION-ID'),
            'PAYPAL-CERT-ID'           => $request->header('PAYPAL-CERT-ID'),
            'PAYPAL-AUTH-ALGO'         => $request->header('PAYPAL-AUTH-ALGO'),
            'PAYPAL-TRANSMISSION-SIG'  => $request->header('PAYPAL-TRANSMISSION-SIG'),
            'PAYPAL-TRANSMISSION-TIME' => $request->header('PAYPAL-TRANSMISSION-TIME'),
        ];
    }

    /**
     * Check if timestamp is recent (within 5 minutes)
     */
    private function isRecentTimestamp(?string $timestamp): bool
    {
        if (!$timestamp) {
            return FALSE;
        }

        $webhookTime = (int) $timestamp;
        $currentTime = time();
        $maxAge = 300; // 5 minutes

        return abs($currentTime - $webhookTime) <= $maxAge;
    }

    /**
     * Extract event type from request payload for logging
     */
    private function extractEventType(Request $request): ?string
    {
        try {
            $payload = json_decode($request->getContent(), TRUE);

            return $payload['event_type'] ?? NULL;
        } catch (\Exception) {
            return NULL;
        }
    }
}
