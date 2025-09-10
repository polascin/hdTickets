<?php declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use function count;

class RecaptchaService
{
    private string $secretKey;

    private string $siteKey;

    private float $minimumScore;

    private bool $enabled;

    public function __construct()
    {
        $this->secretKey = config('services.recaptcha.secret_key', '');
        $this->siteKey = config('services.recaptcha.site_key', '');
        $this->minimumScore = (float) config('services.recaptcha.minimum_score', 0.5);
        $this->enabled = config('services.recaptcha.enabled', FALSE) && ($this->secretKey !== '' && $this->secretKey !== '0');
    }

    /**
     * Verify reCAPTCHA token and return score
     */
    public function verify(string $token, string $action = 'login', ?string $remoteIp = NULL): array
    {
        if (! $this->enabled) {
            return [
                'success'       => TRUE,
                'score'         => 1.0,
                'action'        => $action,
                'challenge_ts'  => now()->toISOString(),
                'hostname'      => request()->getHost(),
                'bypass_reason' => 'recaptcha_disabled',
            ];
        }

        if ($token === '' || $token === '0') {
            return [
                'success'     => FALSE,
                'score'       => 0.0,
                'error-codes' => ['missing-input-response'],
                'action'      => $action,
                'reason'      => 'No token provided',
            ];
        }

        // Check cache for recent verifications to prevent replay attacks
        $cacheKey = "recaptcha:token:{$token}";
        if (Cache::has($cacheKey)) {
            return [
                'success'     => FALSE,
                'score'       => 0.0,
                'error-codes' => ['token-already-used'],
                'action'      => $action,
                'reason'      => 'Token already verified',
            ];
        }

        try {
            $response = Http::timeout(5)
                ->asForm()
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret'   => $this->secretKey,
                    'response' => $token,
                    'remoteip' => $remoteIp ?: request()->ip(),
                ]);

            if (! $response->successful()) {
                Log::warning('reCAPTCHA API request failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return [
                    'success'     => FALSE,
                    'score'       => 0.0,
                    'error-codes' => ['api-error'],
                    'action'      => $action,
                    'reason'      => 'API request failed',
                ];
            }

            $data = $response->json();

            // Cache successful token to prevent reuse
            if ($data['success'] ?? FALSE) {
                Cache::put($cacheKey, TRUE, now()->addMinutes(5));
            }

            // Validate action matches
            if (isset($data['action']) && $data['action'] !== $action) {
                Log::warning('reCAPTCHA action mismatch', [
                    'expected' => $action,
                    'received' => $data['action'],
                ]);

                $data['success'] = FALSE;
                $data['error-codes'] = array_merge($data['error-codes'] ?? [], ['action-mismatch']);
            }

            // Log verification for monitoring
            $this->logVerification($data, $action, request()->ip());

            return $data;
        } catch (Exception $e) {
            Log::error('reCAPTCHA verification failed', [
                'error'  => $e->getMessage(),
                'action' => $action,
                'ip'     => request()->ip(),
            ]);

            return [
                'success'     => FALSE,
                'score'       => 0.0,
                'error-codes' => ['verification-failed'],
                'action'      => $action,
                'reason'      => 'Exception during verification',
            ];
        }
    }

    /**
     * Check if verification passes the minimum score threshold
     */
    public function passes(array $verificationResult): bool
    {
        if (! ($verificationResult['success'] ?? FALSE)) {
            return FALSE;
        }

        $score = $verificationResult['score'] ?? 0.0;

        return $score >= $this->minimumScore;
    }

    /**
     * Get the site key for frontend integration
     */
    public function getSiteKey(): string
    {
        return $this->siteKey;
    }

    /**
     * Check if reCAPTCHA is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get minimum score threshold
     */
    public function getMinimumScore(): float
    {
        return $this->minimumScore;
    }

    /**
     * Determine if request should be challenged based on risk factors
     */
    public function shouldChallenge(Request $request): bool
    {
        if (! $this->enabled) {
            return FALSE;
        }

        $riskScore = $this->calculateRiskScore($request);

        // Challenge if risk score is high
        return $riskScore >= 0.7;
    }

    /**
     * Get verification metrics for analytics
     */
    public function getMetrics(int $days = 7): array
    {
        $metrics = [
            'total_verifications'      => 0,
            'successful_verifications' => 0,
            'failed_verifications'     => 0,
            'success_rate'             => 0.0,
            'average_score'            => 0.0,
            'daily_breakdown'          => [],
            'action_breakdown'         => [],
            'score_distribution'       => [
                'high'   => 0, // 0.7 - 1.0
                'medium' => 0, // 0.3 - 0.69
                'low'    => 0, // 0.0 - 0.29
            ],
        ];

        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');

            for ($hour = 0; $hour < 24; $hour++) {
                $metricsKey = "recaptcha_metrics:{$date}:{$hour}";
                $hourlyMetrics = Cache::get($metricsKey, []);

                if (! empty($hourlyMetrics)) {
                    $metrics['total_verifications'] += $hourlyMetrics['total'] ?? 0;
                    $metrics['successful_verifications'] += $hourlyMetrics['successful'] ?? 0;
                    $metrics['failed_verifications'] += $hourlyMetrics['failed'] ?? 0;

                    // Accumulate daily breakdown
                    if (! isset($metrics['daily_breakdown'][$date])) {
                        $metrics['daily_breakdown'][$date] = [
                            'total'      => 0,
                            'successful' => 0,
                            'failed'     => 0,
                        ];
                    }

                    $metrics['daily_breakdown'][$date]['total'] += $hourlyMetrics['total'] ?? 0;
                    $metrics['daily_breakdown'][$date]['successful'] += $hourlyMetrics['successful'] ?? 0;
                    $metrics['daily_breakdown'][$date]['failed'] += $hourlyMetrics['failed'] ?? 0;

                    // Accumulate action breakdown
                    foreach ($hourlyMetrics['actions'] ?? [] as $action => $count) {
                        $metrics['action_breakdown'][$action] = ($metrics['action_breakdown'][$action] ?? 0) + $count;
                    }

                    // Process scores
                    foreach ($hourlyMetrics['scores'] ?? [] as $score) {
                        if ($score >= 0.7) {
                            $metrics['score_distribution']['high']++;
                        } elseif ($score >= 0.3) {
                            $metrics['score_distribution']['medium']++;
                        } else {
                            $metrics['score_distribution']['low']++;
                        }
                    }
                }
            }
        }

        // Calculate success rate
        if ($metrics['total_verifications'] > 0) {
            $metrics['success_rate'] = $metrics['successful_verifications'] / $metrics['total_verifications'];
        }

        return $metrics;
    }

    /**
     * Calculate risk score based on various factors
     */
    private function calculateRiskScore(Request $request): float
    {
        $riskFactors = [];
        $ip = $request->ip();

        // Check for recent failed attempts
        $failedAttemptsKey = "failed_attempts:{$ip}";
        $failedAttempts = Cache::get($failedAttemptsKey, 0);
        $riskFactors['failed_attempts'] = min($failedAttempts / 10, 1.0);

        // Check for suspicious user agent
        $userAgent = $request->userAgent() ?: '';
        $suspiciousPatterns = ['bot', 'crawler', 'scraper', 'spider', 'headless'];
        $suspiciousUA = FALSE;

        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== FALSE) {
                $suspiciousUA = TRUE;

                break;
            }
        }
        $riskFactors['suspicious_ua'] = $suspiciousUA ? 1.0 : 0.0;

        // Check for rapid requests
        $requestCountKey = "request_count:{$ip}";
        $requestCount = Cache::get($requestCountKey, 0);
        Cache::put($requestCountKey, $requestCount + 1, now()->addMinutes(1));
        $riskFactors['rapid_requests'] = min($requestCount / 20, 1.0);

        // Check time of day (higher risk during off-hours)
        $hour = now()->hour;
        $isOffHours = $hour < 6 || $hour > 22;
        $riskFactors['off_hours'] = $isOffHours ? 0.3 : 0.0;

        // Check for missing common headers
        $missingHeaders = 0;
        $commonHeaders = ['accept-language', 'accept-encoding', 'accept'];

        foreach ($commonHeaders as $header) {
            if (! $request->hasHeader($header)) {
                $missingHeaders++;
            }
        }
        $riskFactors['missing_headers'] = $missingHeaders / count($commonHeaders);

        // Calculate weighted average
        $weights = [
            'failed_attempts' => 0.4,
            'suspicious_ua'   => 0.3,
            'rapid_requests'  => 0.2,
            'off_hours'       => 0.05,
            'missing_headers' => 0.05,
        ];

        $totalScore = 0.0;
        foreach ($riskFactors as $factor => $score) {
            $totalScore += $score * ($weights[$factor] ?? 0.0);
        }

        return min($totalScore, 1.0);
    }

    /**
     * Log verification result for monitoring
     */
    private function logVerification(array $result, string $action, string $ip): void
    {
        $logData = [
            'action'       => $action,
            'success'      => $result['success'] ?? FALSE,
            'score'        => $result['score'] ?? NULL,
            'ip'           => $ip,
            'hostname'     => $result['hostname'] ?? NULL,
            'challenge_ts' => $result['challenge_ts'] ?? NULL,
        ];

        if (isset($result['error-codes'])) {
            $logData['error_codes'] = $result['error-codes'];
        }

        if ($result['success'] ?? FALSE) {
            Log::info('reCAPTCHA verification successful', $logData);
        } else {
            Log::warning('reCAPTCHA verification failed', $logData);
        }

        // Store metrics for analytics
        $this->storeMetrics($result, $action);
    }

    /**
     * Store metrics for analytics dashboard
     */
    private function storeMetrics(array $result, string $action): void
    {
        $date = now()->format('Y-m-d');
        $hour = now()->format('H');

        $metricsKey = "recaptcha_metrics:{$date}:{$hour}";
        $metrics = Cache::get($metricsKey, [
            'total'      => 0,
            'successful' => 0,
            'failed'     => 0,
            'actions'    => [],
            'scores'     => [],
        ]);

        $metrics['total']++;

        if ($result['success'] ?? FALSE) {
            $metrics['successful']++;
        } else {
            $metrics['failed']++;
        }

        $metrics['actions'][$action] = ($metrics['actions'][$action] ?? 0) + 1;

        if (isset($result['score'])) {
            $metrics['scores'][] = $result['score'];
        }

        Cache::put($metricsKey, $metrics, now()->addDays(7));
    }
}
