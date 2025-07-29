<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * CAPTCHA Service for automated CAPTCHA solving
 * 
 * Supports multiple CAPTCHA solving services:
 * - 2captcha.com
 * - anti-captcha.com
 * - captchasolver.com
 * - deathbycaptcha.eu
 */
class CaptchaService
{
    protected $client;
    protected $config;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => config('services.captcha.timeout', 120),
            'verify' => false, // For development - enable SSL verification in production
        ]);

        $this->config = config('services.captcha', []);
    }

    /**
     * Solve reCAPTCHA v2
     *
     * @param string $siteKey
     * @param string $pageUrl
     * @param array $options
     * @return string|null Solved CAPTCHA token
     */
    public function solveRecaptchaV2(string $siteKey, string $pageUrl, array $options = []): ?string
    {
        $service = $this->config['service'] ?? '2captcha';

        switch ($service) {
            case '2captcha':
                return $this->solve2CaptchaRecaptchaV2($siteKey, $pageUrl, $options);
            case 'anticaptcha':
                return $this->solveAntiCaptchaRecaptchaV2($siteKey, $pageUrl, $options);
            case 'captchasolver':
                return $this->solveCaptchaSolverRecaptchaV2($siteKey, $pageUrl, $options);
            case 'deathbycaptcha':
                return $this->solveDeathByCaptchaRecaptchaV2($siteKey, $pageUrl, $options);
            default:
                Log::error('Unsupported CAPTCHA service', ['service' => $service]);
                return null;
        }
    }

    /**
     * Solve reCAPTCHA v3
     *
     * @param string $siteKey
     * @param string $pageUrl
     * @param string $action
     * @param float $minScore
     * @return string|null
     */
    public function solveRecaptchaV3(string $siteKey, string $pageUrl, string $action = 'verify', float $minScore = 0.3): ?string
    {
        $service = $this->config['service'] ?? '2captcha';

        switch ($service) {
            case '2captcha':
                return $this->solve2CaptchaRecaptchaV3($siteKey, $pageUrl, $action, $minScore);
            case 'anticaptcha':
                return $this->solveAntiCaptchaRecaptchaV3($siteKey, $pageUrl, $action, $minScore);
            default:
                Log::error('reCAPTCHA v3 not supported for service', ['service' => $service]);
                return null;
        }
    }

    /**
     * Solve image CAPTCHA
     *
     * @param string $imageBase64
     * @param array $options
     * @return string|null
     */
    public function solveImageCaptcha(string $imageBase64, array $options = []): ?string
    {
        $service = $this->config['service'] ?? '2captcha';

        switch ($service) {
            case '2captcha':
                return $this->solve2CaptchaImage($imageBase64, $options);
            case 'anticaptcha':
                return $this->solveAntiCaptchaImage($imageBase64, $options);
            case 'deathbycaptcha':
                return $this->solveDeathByCaptchaImage($imageBase64, $options);
            default:
                Log::error('Image CAPTCHA not supported for service', ['service' => $service]);
                return null;
        }
    }

    /**
     * 2captcha.com reCAPTCHA v2 solver
     */
    protected function solve2CaptchaRecaptchaV2(string $siteKey, string $pageUrl, array $options = []): ?string
    {
        $apiKey = config('services.captcha.2captcha.api_key');
        if (!$apiKey) {
            Log::error('2captcha API key not configured');
            return null;
        }

        try {
            // Submit CAPTCHA
            $response = $this->client->post('http://2captcha.com/in.php', [
                'form_params' => [
                    'key' => $apiKey,
                    'method' => 'userrecaptcha',
                    'googlekey' => $siteKey,
                    'pageurl' => $pageUrl,
                    'json' => 1,
                    'soft_id' => config('services.captcha.2captcha.soft_id', ''),
                    'invisible' => $options['invisible'] ?? 0,
                    'enterprise' => $options['enterprise'] ?? 0,
                ]
            ]);

            $submitResult = json_decode($response->getBody()->getContents(), true);

            if ($submitResult['status'] !== 1) {
                Log::error('2captcha submit failed', ['error' => $submitResult['error_text'] ?? 'Unknown error']);
                return null;
            }

            $captchaId = $submitResult['request'];

            // Poll for result
            return $this->poll2CaptchaResult($captchaId, $apiKey);

        } catch (RequestException $e) {
            Log::error('2captcha request failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * 2captcha.com reCAPTCHA v3 solver
     */
    protected function solve2CaptchaRecaptchaV3(string $siteKey, string $pageUrl, string $action, float $minScore): ?string
    {
        $apiKey = config('services.captcha.2captcha.api_key');
        if (!$apiKey) {
            Log::error('2captcha API key not configured');
            return null;
        }

        try {
            // Submit CAPTCHA
            $response = $this->client->post('http://2captcha.com/in.php', [
                'form_params' => [
                    'key' => $apiKey,
                    'method' => 'userrecaptcha',
                    'googlekey' => $siteKey,
                    'pageurl' => $pageUrl,
                    'version' => 'v3',
                    'action' => $action,
                    'min_score' => $minScore,
                    'json' => 1,
                    'soft_id' => config('services.captcha.2captcha.soft_id', ''),
                ]
            ]);

            $submitResult = json_decode($response->getBody()->getContents(), true);

            if ($submitResult['status'] !== 1) {
                Log::error('2captcha v3 submit failed', ['error' => $submitResult['error_text'] ?? 'Unknown error']);
                return null;
            }

            $captchaId = $submitResult['request'];

            // Poll for result
            return $this->poll2CaptchaResult($captchaId, $apiKey);

        } catch (RequestException $e) {
            Log::error('2captcha v3 request failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * 2captcha.com image CAPTCHA solver
     */
    protected function solve2CaptchaImage(string $imageBase64, array $options = []): ?string
    {
        $apiKey = config('services.captcha.2captcha.api_key');
        if (!$apiKey) {
            Log::error('2captcha API key not configured');
            return null;
        }

        try {
            // Submit CAPTCHA
            $response = $this->client->post('http://2captcha.com/in.php', [
                'form_params' => [
                    'key' => $apiKey,
                    'method' => 'base64',
                    'body' => $imageBase64,
                    'json' => 1,
                    'soft_id' => config('services.captcha.2captcha.soft_id', ''),
                    'numeric' => $options['numeric'] ?? 0,
                    'min_len' => $options['min_len'] ?? 0,
                    'max_len' => $options['max_len'] ?? 0,
                    'phrase' => $options['phrase'] ?? 0,
                    'case_sensitive' => $options['case_sensitive'] ?? 0,
                    'calc' => $options['calc'] ?? 0,
                    'lang' => $options['lang'] ?? 'en',
                ]
            ]);

            $submitResult = json_decode($response->getBody()->getContents(), true);

            if ($submitResult['status'] !== 1) {
                Log::error('2captcha image submit failed', ['error' => $submitResult['error_text'] ?? 'Unknown error']);
                return null;
            }

            $captchaId = $submitResult['request'];

            // Poll for result
            return $this->poll2CaptchaResult($captchaId, $apiKey);

        } catch (RequestException $e) {
            Log::error('2captcha image request failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Poll 2captcha for result
     */
    protected function poll2CaptchaResult(string $captchaId, string $apiKey): ?string
    {
        $timeout = config('services.captcha.timeout', 120);
        $pollingInterval = config('services.captcha.polling_interval', 5);
        $startTime = time();

        while ((time() - $startTime) < $timeout) {
            sleep($pollingInterval);

            try {
                $response = $this->client->get('http://2captcha.com/res.php', [
                    'query' => [
                        'key' => $apiKey,
                        'action' => 'get',
                        'id' => $captchaId,
                        'json' => 1,
                    ]
                ]);

                $result = json_decode($response->getBody()->getContents(), true);

                if ($result['status'] === 1) {
                    Log::info('2captcha solved successfully', ['captcha_id' => $captchaId]);
                    return $result['request'];
                }

                if ($result['error_text'] !== 'CAPCHA_NOT_READY') {
                    Log::error('2captcha error', ['error' => $result['error_text']]);
                    return null;
                }

            } catch (RequestException $e) {
                Log::error('2captcha polling failed', ['error' => $e->getMessage()]);
                return null;
            }
        }

        Log::error('2captcha timeout', ['captcha_id' => $captchaId, 'timeout' => $timeout]);
        return null;
    }

    /**
     * Anti-Captcha.com reCAPTCHA v2 solver
     */
    protected function solveAntiCaptchaRecaptchaV2(string $siteKey, string $pageUrl, array $options = []): ?string
    {
        $apiKey = config('services.captcha.anticaptcha.api_key');
        if (!$apiKey) {
            Log::error('Anti-Captcha API key not configured');
            return null;
        }

        try {
            // Create task
            $response = $this->client->post('https://api.anti-captcha.com/createTask', [
                'json' => [
                    'clientKey' => $apiKey,
                    'task' => [
                        'type' => $options['invisible'] ?? false ? 'NoCaptchaTaskProxyless' : 'NoCaptchaTaskProxyless',
                        'websiteURL' => $pageUrl,
                        'websiteKey' => $siteKey,
                        'isInvisible' => $options['invisible'] ?? false,
                    ],
                    'softId' => 0,
                ]
            ]);

            $createResult = json_decode($response->getBody()->getContents(), true);

            if ($createResult['errorId'] !== 0) {
                Log::error('Anti-Captcha create task failed', ['error' => $createResult['errorDescription']]);
                return null;
            }

            $taskId = $createResult['taskId'];

            // Poll for result
            return $this->pollAntiCaptchaResult($taskId, $apiKey);

        } catch (RequestException $e) {
            Log::error('Anti-Captcha request failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Poll Anti-Captcha for result
     */
    protected function pollAntiCaptchaResult(int $taskId, string $apiKey): ?string
    {
        $timeout = config('services.captcha.timeout', 120);
        $pollingInterval = config('services.captcha.polling_interval', 5);
        $startTime = time();

        while ((time() - $startTime) < $timeout) {
            sleep($pollingInterval);

            try {
                $response = $this->client->post('https://api.anti-captcha.com/getTaskResult', [
                    'json' => [
                        'clientKey' => $apiKey,
                        'taskId' => $taskId,
                    ]
                ]);

                $result = json_decode($response->getBody()->getContents(), true);

                if ($result['errorId'] !== 0) {
                    Log::error('Anti-Captcha error', ['error' => $result['errorDescription']]);
                    return null;
                }

                if ($result['status'] === 'ready') {
                    Log::info('Anti-Captcha solved successfully', ['task_id' => $taskId]);
                    return $result['solution']['gRecaptchaResponse'];
                }

            } catch (RequestException $e) {
                Log::error('Anti-Captcha polling failed', ['error' => $e->getMessage()]);
                return null;
            }
        }

        Log::error('Anti-Captcha timeout', ['task_id' => $taskId, 'timeout' => $timeout]);
        return null;
    }

    /**
     * Get account balance for current service
     */
    public function getBalance(): ?float
    {
        $service = $this->config['service'] ?? '2captcha';

        switch ($service) {
            case '2captcha':
                return $this->get2CaptchaBalance();
            case 'anticaptcha':
                return $this->getAntiCaptchaBalance();
            default:
                return null;
        }
    }

    /**
     * Get 2captcha balance
     */
    protected function get2CaptchaBalance(): ?float
    {
        $apiKey = config('services.captcha.2captcha.api_key');
        if (!$apiKey) {
            return null;
        }

        try {
            $response = $this->client->get('http://2captcha.com/res.php', [
                'query' => [
                    'key' => $apiKey,
                    'action' => 'getbalance',
                ]
            ]);

            $balance = floatval($response->getBody()->getContents());
            return $balance > 0 ? $balance : null;

        } catch (RequestException $e) {
            Log::error('2captcha balance check failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get Anti-Captcha balance
     */
    protected function getAntiCaptchaBalance(): ?float
    {
        $apiKey = config('services.captcha.anticaptcha.api_key');
        if (!$apiKey) {
            return null;
        }

        try {
            $response = $this->client->post('https://api.anti-captcha.com/getBalance', [
                'json' => [
                    'clientKey' => $apiKey,
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if ($result['errorId'] !== 0) {
                Log::error('Anti-Captcha balance check failed', ['error' => $result['errorDescription']]);
                return null;
            }

            return $result['balance'];

        } catch (RequestException $e) {
            Log::error('Anti-Captcha balance check failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Report bad CAPTCHA solution
     */
    public function reportBad(string $captchaId): bool
    {
        $service = $this->config['service'] ?? '2captcha';

        switch ($service) {
            case '2captcha':
                return $this->report2CaptchaBad($captchaId);
            case 'anticaptcha':
                return $this->reportAntiCaptchaBad($captchaId);
            default:
                return false;
        }
    }

    /**
     * Report bad 2captcha solution
     */
    protected function report2CaptchaBad(string $captchaId): bool
    {
        $apiKey = config('services.captcha.2captcha.api_key');
        if (!$apiKey) {
            return false;
        }

        try {
            $response = $this->client->get('http://2captcha.com/res.php', [
                'query' => [
                    'key' => $apiKey,
                    'action' => 'reportbad',
                    'id' => $captchaId,
                ]
            ]);

            return $response->getBody()->getContents() === 'OK_REPORT_RECORDED';

        } catch (RequestException $e) {
            Log::error('2captcha report bad failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Check if CAPTCHA service is enabled and configured
     */
    public function isEnabled(): bool
    {
        return config('services.captcha.enabled', false) && 
               !empty(config('services.captcha.service'));
    }

    /**
     * Get service statistics
     */
    public function getStats(): array
    {
        $cacheKey = 'captcha_stats_' . date('Y-m-d');
        
        return Cache::remember($cacheKey, 3600, function () {
            return [
                'service' => $this->config['service'] ?? 'none',
                'enabled' => $this->isEnabled(),
                'balance' => $this->getBalance(),
                'solved_today' => Cache::get('captcha_solved_today', 0),
                'failed_today' => Cache::get('captcha_failed_today', 0),
                'total_cost_today' => Cache::get('captcha_cost_today', 0.0),
            ];
        });
    }

    /**
     * Increment daily statistics
     */
    public function incrementStats(string $type, float $cost = 0.0): void
    {
        $today = date('Y-m-d');
        $key = "captcha_{$type}_today";
        
        Cache::increment($key);
        Cache::put($key, Cache::get($key, 0), now()->endOfDay());

        if ($cost > 0) {
            $costKey = 'captcha_cost_today';
            $currentCost = Cache::get($costKey, 0.0);
            Cache::put($costKey, $currentCost + $cost, now()->endOfDay());
        }
    }

    // Placeholder methods for other services
    protected function solveAntiCaptchaRecaptchaV3(string $siteKey, string $pageUrl, string $action, float $minScore): ?string { return null; }
    protected function solveCaptchaSolverRecaptchaV2(string $siteKey, string $pageUrl, array $options): ?string { return null; }
    protected function solveDeathByCaptchaRecaptchaV2(string $siteKey, string $pageUrl, array $options): ?string { return null; }
    protected function solveAntiCaptchaImage(string $imageBase64, array $options): ?string { return null; }
    protected function solveDeathByCaptchaImage(string $imageBase64, array $options): ?string { return null; }
    protected function reportAntiCaptchaBad(string $captchaId): bool { return false; }
}
