# HD Tickets Monitoring Setup Guide

## Overview

This guide covers the comprehensive monitoring setup for the HD Tickets sports events entry tickets monitoring system, specifically focusing on authentication security, performance tracking, and error monitoring for production environments.

## Prerequisites

- **System**: Ubuntu 24.04 LTS with Apache2, PHP 8.4, MySQL/MariaDB 10.4
- **Laravel Framework**: Current version with Horizon queue management  
- **Redis**: For caching and rate limiting
- **Log Storage**: Adequate disk space for log retention

## Authentication & Security Monitoring

### Failed Login Attempt Tracking

#### Database Schema
The system uses the `login_history` table to track all authentication attempts:

```sql
-- Login history tracking table
CREATE TABLE login_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    success BOOLEAN NOT NULL DEFAULT FALSE,
    failure_reason VARCHAR(255) NULL,
    rate_limited BOOLEAN DEFAULT FALSE,
    location_data JSON NULL,
    session_id VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email_created (email, created_at),
    INDEX idx_ip_created (ip_address, created_at),
    INDEX idx_success_created (success, created_at),
    INDEX idx_user_id_created (user_id, created_at)
);
```

#### Login Monitoring Service
Located in: `app/Services/Security/SecurityMonitoringService.php`

```php
<?php

namespace App\Services\Security;

use App\Models\LoginHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SecurityMonitoringService
{
    /**
     * Track failed login attempt
     */
    public function trackFailedLogin(string $email, string $reason, array $context = []): void
    {
        $attempt = LoginHistory::create([
            'user_id' => null,
            'email' => $email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'success' => false,
            'failure_reason' => $reason,
            'rate_limited' => $context['rate_limited'] ?? false,
            'location_data' => $this->getLocationData(request()->ip()),
            'session_id' => session()->getId(),
        ]);

        // Log security event
        Log::channel('security')->warning('Failed login attempt', [
            'attempt_id' => $attempt->id,
            'email' => $email,
            'ip' => request()->ip(),
            'reason' => $reason,
            'user_agent' => request()->userAgent(),
            'rate_limited' => $context['rate_limited'] ?? false,
        ]);

        // Check for suspicious patterns
        $this->analyzeFailurePatterns($email, request()->ip());
    }

    /**
     * Track successful login
     */
    public function trackSuccessfulLogin(int $userId, string $email): void
    {
        LoginHistory::create([
            'user_id' => $userId,
            'email' => $email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'success' => true,
            'location_data' => $this->getLocationData(request()->ip()),
            'session_id' => session()->getId(),
        ]);

        Log::channel('security')->info('Successful login', [
            'user_id' => $userId,
            'email' => $email,
            'ip' => request()->ip(),
        ]);
    }

    /**
     * Analyze failure patterns for suspicious activity
     */
    private function analyzeFailurePatterns(string $email, string $ip): void
    {
        $recentFailures = LoginHistory::where('success', false)
            ->where(function ($query) use ($email, $ip) {
                $query->where('email', $email)
                      ->orWhere('ip_address', $ip);
            })
            ->where('created_at', '>', Carbon::now()->subHours(1))
            ->count();

        if ($recentFailures >= 10) {
            $this->alertSecurityTeam('High failure rate detected', [
                'email' => $email,
                'ip' => $ip,
                'failure_count' => $recentFailures,
                'time_window' => '1 hour',
            ]);
        }
    }

    /**
     * Send security alerts
     */
    private function alertSecurityTeam(string $message, array $context): void
    {
        Log::channel('security')->critical($message, $context);
        
        // TODO: Implement notification channels (Slack, Email)
        // Notification::route('slack', config('notifications.slack.security'))
        //     ->notify(new SecurityAlert($message, $context));
    }

    /**
     * Get location data from IP (optional enhancement)
     */
    private function getLocationData(string $ip): ?array
    {
        // Basic implementation - can be enhanced with GeoIP service
        return [
            'ip' => $ip,
            'timestamp' => now()->toISOString(),
        ];
    }
}
```

#### Authentication Request Handler Integration
In `app/Http/Requests/Auth/LoginRequest.php`:

```php
<?php

namespace App\Http\Requests\Auth;

use App\Services\Security\SecurityMonitoringService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private SecurityMonitoringService $securityMonitor;

    public function __construct(SecurityMonitoringService $securityMonitor)
    {
        parent::__construct();
        $this->securityMonitor = $securityMonitor;
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (!Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            // Track failed attempt
            $this->securityMonitor->trackFailedLogin(
                $this->input('email'),
                'Invalid credentials',
                ['rate_limited' => false]
            );

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Track successful login
        $user = Auth::user();
        $this->securityMonitor->trackSuccessfulLogin(
            $user->id,
            $user->email
        );

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        // Track rate limited attempt
        $this->securityMonitor->trackFailedLogin(
            $this->input('email'),
            'Rate limited',
            ['rate_limited' => true]
        );

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
            'rate_limit_seconds' => $seconds,
        ]);
    }
}
```

### Security Monitoring Dashboard

Create monitoring queries for security analysis:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Models\LoginHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecurityDashboardController extends Controller
{
    /**
     * Get failed login statistics
     */
    public function getFailedLoginStats(Request $request): array
    {
        $timeRange = $request->input('range', '24h');
        $startTime = $this->getStartTime($timeRange);

        return [
            'total_attempts' => LoginHistory::where('created_at', '>', $startTime)->count(),
            'failed_attempts' => LoginHistory::where('created_at', '>', $startTime)
                ->where('success', false)->count(),
            'success_rate' => $this->calculateSuccessRate($startTime),
            'top_failure_ips' => $this->getTopFailureIPs($startTime),
            'failure_reasons' => $this->getFailureReasons($startTime),
            'hourly_trends' => $this->getHourlyTrends($startTime),
        ];
    }

    private function getTopFailureIPs(Carbon $startTime): array
    {
        return LoginHistory::select('ip_address', DB::raw('COUNT(*) as attempts'))
            ->where('created_at', '>', $startTime)
            ->where('success', false)
            ->groupBy('ip_address')
            ->orderByDesc('attempts')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function getFailureReasons(Carbon $startTime): array
    {
        return LoginHistory::select('failure_reason', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>', $startTime)
            ->where('success', false)
            ->whereNotNull('failure_reason')
            ->groupBy('failure_reason')
            ->orderByDesc('count')
            ->get()
            ->toArray();
    }
}
```

## Performance Monitoring

### Client-Side Performance Tracking

#### Performance Monitor JavaScript Enhancement
The existing `public/js/performanceMonitor.js` includes login-specific monitoring:

```javascript
class LoginPerformanceMonitor extends HDTicketsPerformanceMonitor {
    constructor() {
        super();
        this.loginMetrics = {
            formRenderTime: 0,
            firstInteraction: 0,
            submissionTime: 0,
            authResponseTime: 0,
            redirectTime: 0
        };
        
        this.setupLoginMonitoring();
    }

    setupLoginMonitoring() {
        // Track form load time
        this.measureFormRenderTime();
        
        // Track user interaction patterns
        this.trackUserInteractions();
        
        // Monitor form submission performance
        this.monitorFormSubmission();
        
        // Track authentication response times
        this.trackAuthenticationFlow();
    }

    measureFormRenderTime() {
        const formLoadStart = performance.mark('login-form-start');
        
        document.addEventListener('DOMContentLoaded', () => {
            const formLoadEnd = performance.mark('login-form-end');
            this.loginMetrics.formRenderTime = performance.measure(
                'login-form-render',
                'login-form-start',
                'login-form-end'
            ).duration;
            
            this.sendMetric('login_form_render_time', this.loginMetrics.formRenderTime);
        });
    }

    trackUserInteractions() {
        const form = document.getElementById('login-form');
        if (!form) return;

        let firstInteraction = null;
        const trackableEvents = ['input', 'focus', 'click'];

        trackableEvents.forEach(eventType => {
            form.addEventListener(eventType, (e) => {
                if (!firstInteraction) {
                    firstInteraction = performance.now();
                    this.loginMetrics.firstInteraction = firstInteraction;
                    this.sendMetric('login_first_interaction', firstInteraction);
                }
            }, { once: true });
        });
    }

    monitorFormSubmission() {
        const form = document.getElementById('login-form');
        if (!form) return;

        form.addEventListener('submit', (e) => {
            const submissionStart = performance.now();
            this.loginMetrics.submissionTime = submissionStart;
            
            // Track form validation time
            const validationStart = performance.mark('form-validation-start');
            
            setTimeout(() => {
                const validationEnd = performance.mark('form-validation-end');
                const validationTime = performance.measure(
                    'form-validation',
                    'form-validation-start',
                    'form-validation-end'
                ).duration;
                
                this.sendMetric('login_validation_time', validationTime);
            }, 0);
        });
    }

    trackAuthenticationFlow() {
        // Monitor network request timing
        const originalFetch = window.fetch;
        window.fetch = (...args) => {
            const start = performance.now();
            
            return originalFetch.apply(this, args)
                .then(response => {
                    const end = performance.now();
                    const duration = end - start;
                    
                    if (args[0].includes('/login')) {
                        this.loginMetrics.authResponseTime = duration;
                        this.sendMetric('login_auth_response_time', duration);
                    }
                    
                    return response;
                });
        };
    }

    sendMetric(name, value, tags = {}) {
        const metric = {
            name: name,
            value: value,
            timestamp: Date.now(),
            url: window.location.href,
            userAgent: navigator.userAgent,
            tags: {
                ...tags,
                page_type: 'login',
                performance_category: 'authentication'
            }
        };

        // Send to analytics endpoint
        fetch('/api/analytics/performance', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify(metric)
        }).catch(err => {
            console.warn('Failed to send performance metric:', err);
        });
    }
}

// Initialize login-specific monitoring
if (document.getElementById('login-form')) {
    new LoginPerformanceMonitor();
}
```

#### Server-Side Performance Monitoring
Enhanced `app/Logging/PerformanceLogger.php` for authentication metrics:

```php
<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerformanceLogger
{
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof \Monolog\Handler\StreamHandler) {
                $handler->pushProcessor([$this, 'addAuthenticationMetrics']);
                $handler->pushProcessor([$this, 'addDatabaseMetrics']);
                $handler->pushProcessor([$this, 'addSystemMetrics']);
            }
        }
    }

    public function addAuthenticationMetrics(array $record): array
    {
        if (request()->is('login') || request()->is('api/auth/*')) {
            $record['extra']['auth_context'] = [
                'route' => request()->route()?->getName(),
                'method' => request()->method(),
                'is_authenticated' => Auth::check(),
                'user_id' => Auth::id(),
                'session_lifetime' => config('session.lifetime'),
                'rate_limit_key' => $this->getRateLimitKey(),
            ];
        }

        return $record;
    }

    public function addDatabaseMetrics(array $record): array
    {
        $record['extra']['database'] = [
            'query_count' => DB::getQueryLog() ? count(DB::getQueryLog()) : 0,
            'connection_name' => config('database.default'),
            'slow_queries' => $this->getSlowQueryCount(),
        ];

        return $record;
    }

    public function addSystemMetrics(array $record): array
    {
        $record['extra']['system'] = [
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'memory_current_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'execution_time_ms' => $this->getExecutionTime(),
            'cpu_usage' => $this->getCpuUsage(),
        ];

        return $record;
    }

    private function getRateLimitKey(): ?string
    {
        if (request()->has('email')) {
            return 'login:' . request()->input('email') . '|' . request()->ip();
        }
        return null;
    }

    private function getSlowQueryCount(): int
    {
        // Implementation depends on query logging setup
        return 0; // Placeholder
    }

    private function getExecutionTime(): float
    {
        if (defined('LARAVEL_START')) {
            return round((microtime(true) - LARAVEL_START) * 1000, 2);
        }
        return 0.0;
    }

    private function getCpuUsage(): float
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return round(($load[0] ?? 0) * 100, 2);
        }
        return 0.0;
    }
}
```

### Performance Monitoring Dashboard

API endpoint for performance metrics collection:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PerformanceMetricsController extends Controller
{
    /**
     * Collect performance metrics from client
     */
    public function store(Request $request)
    {
        $metrics = $request->validate([
            'name' => 'required|string|max:100',
            'value' => 'required|numeric',
            'timestamp' => 'required|integer',
            'url' => 'required|url',
            'userAgent' => 'nullable|string',
            'tags' => 'array',
            'tags.*' => 'string|max:100',
        ]);

        // Log performance metric
        Log::channel('performance')->info('Client performance metric', [
            'metric_name' => $metrics['name'],
            'value' => $metrics['value'],
            'timestamp' => $metrics['timestamp'],
            'url' => $metrics['url'],
            'user_agent' => $metrics['userAgent'],
            'tags' => $metrics['tags'] ?? [],
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'ip_address' => $request->ip(),
        ]);

        // Store in cache for real-time monitoring
        $cacheKey = 'performance_metrics:' . $metrics['name'] . ':' . date('Y-m-d-H');
        $cached = Cache::get($cacheKey, []);
        $cached[] = [
            'value' => $metrics['value'],
            'timestamp' => $metrics['timestamp'],
        ];
        
        Cache::put($cacheKey, $cached, now()->addHours(2));

        return response()->json(['status' => 'success']);
    }

    /**
     * Get performance statistics
     */
    public function index(Request $request)
    {
        $metricName = $request->input('metric', 'login_form_render_time');
        $hours = $request->input('hours', 24);
        
        $stats = $this->getPerformanceStats($metricName, $hours);
        
        return response()->json($stats);
    }

    private function getPerformanceStats(string $metricName, int $hours): array
    {
        $stats = ['values' => [], 'hourly_avg' => []];
        
        for ($i = 0; $i < $hours; $i++) {
            $hour = Carbon::now()->subHours($i)->format('Y-m-d-H');
            $cacheKey = "performance_metrics:{$metricName}:{$hour}";
            $hourlyData = Cache::get($cacheKey, []);
            
            if (!empty($hourlyData)) {
                $values = array_column($hourlyData, 'value');
                $stats['hourly_avg'][$hour] = [
                    'avg' => round(array_sum($values) / count($values), 2),
                    'min' => min($values),
                    'max' => max($values),
                    'count' => count($values),
                ];
                $stats['values'] = array_merge($stats['values'], $values);
            }
        }

        if (!empty($stats['values'])) {
            $stats['summary'] = [
                'avg' => round(array_sum($stats['values']) / count($stats['values']), 2),
                'min' => min($stats['values']),
                'max' => max($stats['values']),
                'p50' => $this->percentile($stats['values'], 50),
                'p95' => $this->percentile($stats['values'], 95),
                'p99' => $this->percentile($stats['values'], 99),
                'total_samples' => count($stats['values']),
            ];
        }

        return $stats;
    }

    private function percentile(array $values, int $percentile): float
    {
        sort($values);
        $index = ceil($percentile / 100 * count($values)) - 1;
        return $values[$index] ?? 0;
    }
}
```

## JavaScript Error Monitoring

### Error Tracking Setup

Enhanced error tracking in `public/js/auth-security.js`:

```javascript
class ErrorTracker {
    constructor() {
        this.errorQueue = [];
        this.maxQueueSize = 50;
        this.flushInterval = 30000; // 30 seconds
        
        this.setupGlobalErrorHandling();
        this.setupUnhandledPromiseRejection();
        this.setupFormErrorTracking();
        this.startPeriodicFlush();
    }

    setupGlobalErrorHandling() {
        window.addEventListener('error', (event) => {
            this.captureError({
                type: 'javascript_error',
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                stack: event.error?.stack,
                timestamp: Date.now(),
                url: window.location.href,
                userAgent: navigator.userAgent,
                context: this.getPageContext(),
            });
        });
    }

    setupUnhandledPromiseRejection() {
        window.addEventListener('unhandledrejection', (event) => {
            this.captureError({
                type: 'promise_rejection',
                message: event.reason?.message || 'Unhandled promise rejection',
                stack: event.reason?.stack,
                timestamp: Date.now(),
                url: window.location.href,
                context: this.getPageContext(),
            });
        });
    }

    setupFormErrorTracking() {
        // Track form validation errors
        document.addEventListener('invalid', (event) => {
            this.captureError({
                type: 'form_validation_error',
                field: event.target.name,
                value: event.target.value ? '[REDACTED]' : '',
                validity: event.target.validity,
                message: event.target.validationMessage,
                timestamp: Date.now(),
                context: this.getPageContext(),
            }, false); // Don't send immediately for form errors
        });

        // Track AJAX errors
        const originalFetch = window.fetch;
        window.fetch = (...args) => {
            return originalFetch.apply(this, args)
                .catch(error => {
                    this.captureError({
                        type: 'network_error',
                        message: error.message,
                        url: args[0],
                        method: args[1]?.method || 'GET',
                        timestamp: Date.now(),
                        context: this.getPageContext(),
                    });
                    throw error; // Re-throw to maintain normal error handling
                });
        };
    }

    captureError(errorData, immediate = true) {
        // Add to queue
        this.errorQueue.push(errorData);
        
        // Limit queue size
        if (this.errorQueue.length > this.maxQueueSize) {
            this.errorQueue.shift();
        }

        // Send immediately for critical errors
        if (immediate && this.isCriticalError(errorData)) {
            this.flushErrors();
        }
    }

    isCriticalError(errorData) {
        const criticalTypes = ['javascript_error', 'network_error'];
        const criticalMessages = ['Failed to fetch', 'Network Error', 'TypeError'];
        
        return criticalTypes.includes(errorData.type) ||
               criticalMessages.some(msg => errorData.message?.includes(msg));
    }

    getPageContext() {
        return {
            page_type: document.getElementById('login-form') ? 'login' : 'unknown',
            form_errors: document.querySelectorAll('.hd-error-message').length,
            authenticated: document.querySelector('[data-authenticated]')?.dataset.authenticated === 'true',
            session_id: document.querySelector('meta[name="session-id"]')?.content,
        };
    }

    startPeriodicFlush() {
        setInterval(() => {
            if (this.errorQueue.length > 0) {
                this.flushErrors();
            }
        }, this.flushInterval);
    }

    flushErrors() {
        if (this.errorQueue.length === 0) return;

        const errors = [...this.errorQueue];
        this.errorQueue = [];

        fetch('/api/errors/batch', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken || '',
            },
            body: JSON.stringify({ errors }),
        }).catch(err => {
            console.warn('Failed to send error reports:', err);
            // Re-add errors to queue if sending failed
            this.errorQueue = errors.concat(this.errorQueue).slice(0, this.maxQueueSize);
        });
    }
}

// Initialize error tracking
document.addEventListener('DOMContentLoaded', () => {
    new ErrorTracker();
});
```

### Error Reporting API

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ErrorReportingController extends Controller
{
    /**
     * Batch error reporting endpoint
     */
    public function batchReport(Request $request)
    {
        $data = $request->validate([
            'errors' => 'required|array|max:50',
            'errors.*.type' => 'required|string|in:javascript_error,promise_rejection,form_validation_error,network_error',
            'errors.*.message' => 'required|string|max:1000',
            'errors.*.timestamp' => 'required|integer',
            'errors.*.url' => 'nullable|url',
            'errors.*.context' => 'nullable|array',
        ]);

        foreach ($data['errors'] as $error) {
            $this->logClientError($error, $request);
        }

        return response()->json(['status' => 'success', 'received' => count($data['errors'])]);
    }

    private function logClientError(array $error, Request $request): void
    {
        $logData = [
            'error_id' => Str::uuid(),
            'type' => $error['type'],
            'message' => $error['message'],
            'timestamp' => $error['timestamp'],
            'url' => $error['url'] ?? $request->url(),
            'context' => $error['context'] ?? [],
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        // Determine log level based on error type
        $level = $this->getLogLevel($error['type']);
        
        Log::channel('javascript')->$level('Client-side error reported', $logData);

        // Alert for critical errors
        if ($level === 'error') {
            $this->alertOnCriticalError($logData);
        }
    }

    private function getLogLevel(string $type): string
    {
        return match ($type) {
            'javascript_error', 'promise_rejection' => 'error',
            'network_error' => 'warning',
            'form_validation_error' => 'info',
            default => 'debug',
        };
    }

    private function alertOnCriticalError(array $errorData): void
    {
        // Count recent similar errors
        $recentCount = $this->countRecentSimilarErrors($errorData);
        
        if ($recentCount > 5) { // Threshold for alerting
            Log::channel('security')->critical('High frequency client error detected', [
                'error_pattern' => $errorData['message'],
                'recent_count' => $recentCount,
                'time_window' => '10 minutes',
                'affected_users' => $this->getAffectedUserCount($errorData),
            ]);
        }
    }

    private function countRecentSimilarErrors(array $errorData): int
    {
        // This would typically query a dedicated error tracking store
        // For now, return a placeholder
        return 1;
    }

    private function getAffectedUserCount(array $errorData): int
    {
        // This would typically query unique users affected by this error
        return 1;
    }
}
```

## Logging Configuration

### Enhanced Logging Channels
Update `config/logging.php`:

```php
<?php

return [
    'default' => env('LOG_CHANNEL', 'stack'),

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'performance', 'security'],
            'ignore_exceptions' => false,
        ],

        'security' => [
            'driver' => 'daily',
            'path' => storage_path('logs/security.log'),
            'level' => 'info',
            'days' => 90, // Keep security logs longer
            'formatter' => \App\Logging\SecurityFormatter::class,
        ],

        'performance' => [
            'driver' => 'daily',
            'path' => storage_path('logs/performance.log'),
            'level' => 'info',
            'days' => 30,
            'tap' => [\App\Logging\PerformanceLogger::class],
        ],

        'javascript' => [
            'driver' => 'daily',
            'path' => storage_path('logs/javascript.log'),
            'level' => 'debug',
            'days' => 14,
            'formatter' => \App\Logging\JavaScriptFormatter::class,
        ],

        'auth_audit' => [
            'driver' => 'daily',
            'path' => storage_path('logs/auth-audit.log'),
            'level' => 'info',
            'days' => 365, // Keep authentication audit logs for compliance
            'formatter' => \App\Logging\AuditFormatter::class,
        ],
    ],
];
```

### Custom Log Formatters

Create `app/Logging/SecurityFormatter.php`:

```php
<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;

class SecurityFormatter extends LineFormatter
{
    public function format(array $record): string
    {
        $output = sprintf(
            "[%s] %s.%s: %s %s %s\n",
            $record['datetime']->format('Y-m-d H:i:s'),
            $record['channel'],
            $record['level_name'],
            $record['message'],
            $this->formatContext($record['context']),
            $this->formatExtra($record['extra'])
        );

        return $output;
    }

    protected function formatContext(array $context): string
    {
        if (empty($context)) {
            return '';
        }

        // Redact sensitive information
        $context = $this->redactSensitiveData($context);
        
        return json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function redactSensitiveData(array $data): array
    {
        $sensitiveKeys = ['password', 'token', 'secret', 'key', 'auth'];
        
        array_walk_recursive($data, function (&$value, $key) use ($sensitiveKeys) {
            if (is_string($key) && in_array(strtolower($key), $sensitiveKeys)) {
                $value = '[REDACTED]';
            }
        });

        return $data;
    }
}
```

## Health Check Endpoints

### Authentication System Health

Add health check routes in `routes/health.php`:

```php
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\LoginHistory;
use Carbon\Carbon;

Route::get('/health/authentication', function (Request $request) {
    $checks = [
        'login_rate' => $this->checkLoginRate(),
        'failed_attempts' => $this->checkFailedAttempts(),
        'rate_limiting' => $this->checkRateLimiting(),
        'session_store' => $this->checkSessionStore(),
    ];

    $overallHealth = collect($checks)->every(fn($check) => $check['status'] === 'healthy');

    return response()->json([
        'status' => $overallHealth ? 'healthy' : 'degraded',
        'timestamp' => now()->toISOString(),
        'checks' => $checks,
    ], $overallHealth ? 200 : 503);
});

// Helper functions would be in a dedicated health check service
function checkLoginRate(): array
{
    $recentLogins = LoginHistory::where('created_at', '>', Carbon::now()->subMinutes(5))
        ->count();
    
    return [
        'status' => $recentLogins < 1000 ? 'healthy' : 'warning',
        'recent_logins_5min' => $recentLogins,
        'threshold' => 1000,
    ];
}

function checkFailedAttempts(): array
{
    $failureRate = LoginHistory::where('created_at', '>', Carbon::now()->subMinutes(10))
        ->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failed,
            (SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as failure_rate
        ')
        ->first();
    
    $rate = $failureRate->failure_rate ?? 0;
    
    return [
        'status' => $rate < 25 ? 'healthy' : ($rate < 50 ? 'warning' : 'critical'),
        'failure_rate_percent' => round($rate, 2),
        'total_attempts' => $failureRate->total ?? 0,
        'failed_attempts' => $failureRate->failed ?? 0,
    ];
}
```

## Monitoring Dashboards

### Grafana Dashboard Configuration
Example dashboard configuration for monitoring HD Tickets authentication:

```json
{
  "dashboard": {
    "title": "HD Tickets Authentication Monitoring",
    "panels": [
      {
        "title": "Login Success Rate",
        "type": "stat",
        "targets": [
          {
            "expr": "rate(login_attempts_total{status=\"success\"}[5m]) / rate(login_attempts_total[5m]) * 100"
          }
        ]
      },
      {
        "title": "Failed Login Attempts",
        "type": "graph",
        "targets": [
          {
            "expr": "rate(login_attempts_total{status=\"failed\"}[1m])"
          }
        ]
      },
      {
        "title": "Login Performance",
        "type": "graph",
        "targets": [
          {
            "expr": "histogram_quantile(0.95, rate(login_response_time_seconds_bucket[5m]))"
          }
        ]
      },
      {
        "title": "Top Failure IPs",
        "type": "table",
        "targets": [
          {
            "expr": "topk(10, sum by (ip) (rate(login_attempts_total{status=\"failed\"}[1h])))"
          }
        ]
      }
    ]
  }
}
```

## Alerting Rules

### Prometheus Alert Rules
Example alerting rules in `prometheus.yml`:

```yaml
groups:
  - name: hd_tickets_auth
    rules:
      - alert: HighLoginFailureRate
        expr: rate(login_attempts_total{status="failed"}[5m]) / rate(login_attempts_total[5m]) > 0.3
        for: 2m
        labels:
          severity: warning
          service: hd_tickets
          component: authentication
        annotations:
          summary: "High login failure rate detected"
          description: "Login failure rate is {{ $value | humanizePercentage }} over the last 5 minutes"

      - alert: LoginPerformanceDegraded
        expr: histogram_quantile(0.95, rate(login_response_time_seconds_bucket[5m])) > 5
        for: 3m
        labels:
          severity: warning
          service: hd_tickets
          component: authentication
        annotations:
          summary: "Login performance degraded"
          description: "95th percentile login response time is {{ $value }}s"

      - alert: SuspiciousLoginActivity
        expr: increase(login_attempts_total{status="failed"}[1h]) by (ip) > 100
        for: 0m
        labels:
          severity: critical
          service: hd_tickets
          component: security
        annotations:
          summary: "Suspicious login activity detected"
          description: "IP {{ $labels.ip }} has {{ $value }} failed login attempts in the last hour"
```

## Maintenance Procedures

### Daily Monitoring Tasks

Create `app/Console/Commands/DailySecurityReport.php`:

```php
<?php

namespace App\Console\Commands;

use App\Models\LoginHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DailySecurityReport extends Command
{
    protected $signature = 'security:daily-report';
    protected $description = 'Generate daily security monitoring report';

    public function handle()
    {
        $yesterday = Carbon::yesterday();
        
        $stats = [
            'date' => $yesterday->toDateString(),
            'total_login_attempts' => $this->getTotalLoginAttempts($yesterday),
            'successful_logins' => $this->getSuccessfulLogins($yesterday),
            'failed_logins' => $this->getFailedLogins($yesterday),
            'unique_users' => $this->getUniqueUsers($yesterday),
            'top_failure_ips' => $this->getTopFailureIPs($yesterday),
            'failure_reasons' => $this->getFailureReasons($yesterday),
            'performance_metrics' => $this->getPerformanceMetrics($yesterday),
        ];

        // Log the daily report
        Log::channel('security')->info('Daily security report', $stats);

        // Output to console
        $this->info('Daily Security Report for ' . $yesterday->toDateString());
        $this->table(['Metric', 'Value'], [
            ['Total Login Attempts', $stats['total_login_attempts']],
            ['Successful Logins', $stats['successful_logins']],
            ['Failed Logins', $stats['failed_logins']],
            ['Success Rate', round(($stats['successful_logins'] / max($stats['total_login_attempts'], 1)) * 100, 2) . '%'],
            ['Unique Users', $stats['unique_users']],
        ]);

        if (!empty($stats['top_failure_ips'])) {
            $this->info('Top Failure IPs:');
            $this->table(['IP Address', 'Failed Attempts'], $stats['top_failure_ips']);
        }

        return 0;
    }

    private function getTotalLoginAttempts(Carbon $date): int
    {
        return LoginHistory::whereDate('created_at', $date)->count();
    }

    private function getSuccessfulLogins(Carbon $date): int
    {
        return LoginHistory::whereDate('created_at', $date)
            ->where('success', true)
            ->count();
    }

    private function getFailedLogins(Carbon $date): int
    {
        return LoginHistory::whereDate('created_at', $date)
            ->where('success', false)
            ->count();
    }

    private function getUniqueUsers(Carbon $date): int
    {
        return LoginHistory::whereDate('created_at', $date)
            ->where('success', true)
            ->distinct('user_id')
            ->count();
    }

    private function getTopFailureIPs(Carbon $date): array
    {
        return LoginHistory::select('ip_address', DB::raw('COUNT(*) as attempts'))
            ->whereDate('created_at', $date)
            ->where('success', false)
            ->groupBy('ip_address')
            ->orderByDesc('attempts')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [$item->ip_address, $item->attempts];
            })
            ->toArray();
    }

    private function getFailureReasons(Carbon $date): array
    {
        return LoginHistory::select('failure_reason', DB::raw('COUNT(*) as count'))
            ->whereDate('created_at', $date)
            ->where('success', false)
            ->whereNotNull('failure_reason')
            ->groupBy('failure_reason')
            ->get()
            ->pluck('count', 'failure_reason')
            ->toArray();
    }

    private function getPerformanceMetrics(Carbon $date): array
    {
        // This would integrate with your performance monitoring system
        return [
            'avg_response_time' => 0, // Placeholder
            'error_rate' => 0, // Placeholder
        ];
    }
}
```

### Schedule the Daily Report
Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('security:daily-report')
             ->dailyAt('08:00')
             ->emailOutputOnFailure('admin@hdtickets.com');
}
```

## Production Deployment Checklist

### Pre-Deployment
- [ ] Verify log rotation configuration is in place
- [ ] Test all monitoring endpoints respond correctly
- [ ] Confirm database indexes are created for login_history table
- [ ] Validate Redis is configured for rate limiting and caching
- [ ] Test error reporting endpoints
- [ ] Verify log file permissions and disk space

### Post-Deployment
- [ ] Monitor login attempt patterns for first 24 hours
- [ ] Verify performance metrics are being collected
- [ ] Test alerting rules trigger correctly
- [ ] Confirm daily security reports are generated
- [ ] Validate JavaScript error reporting is working
- [ ] Check health check endpoints return expected responses

### Ongoing Maintenance
- [ ] Weekly review of security logs
- [ ] Monthly performance trend analysis
- [ ] Quarterly security monitoring system updates
- [ ] Annual review and update of alerting thresholds

This comprehensive monitoring setup provides complete visibility into the authentication system's security posture, performance characteristics, and error patterns for the HD Tickets sports events entry tickets monitoring system.
