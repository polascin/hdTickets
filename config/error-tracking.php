<?php declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Error Tracking Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file controls error tracking and monitoring
    | for the HD Tickets sports events monitoring system in production.
    |
    */

    'enabled' => env('ERROR_TRACKING_ENABLED', TRUE),

    /*
    |--------------------------------------------------------------------------
    | Ignition Configuration for Production
    |--------------------------------------------------------------------------
    |
    | Laravel Ignition should be disabled in production for security reasons.
    | We'll use custom error handlers instead.
    |
    */

    'ignition' => [
        'enabled_in_production'      => env('IGNITION_ENABLED_PRODUCTION', FALSE),
        'share_button_enabled'       => FALSE,
        'register_commands'          => FALSE,
        'ignored_solution_providers' => [
            Spatie\Ignition\Solutions\SolutionProviders\BadMethodCallSolutionProvider::class,
            Spatie\Ignition\Solutions\SolutionProviders\MergeConflictSolutionProvider::class,
            Spatie\Ignition\Solutions\SolutionProviders\UndefinedPropertySolutionProvider::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Levels to Track
    |--------------------------------------------------------------------------
    |
    | Define which error levels should be tracked and reported
    |
    */

    'track_levels' => [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning', // Only in staging/development
    ],

    /*
    |--------------------------------------------------------------------------
    | Sensitive Data Filtering
    |--------------------------------------------------------------------------
    |
    | Define which data should be filtered from error reports
    |
    */

    'sensitive_keys' => [
        'password',
        'token',
        'secret',
        'key',
        'api_key',
        'auth',
        'authorization',
        'cookie',
        'session',
        'csrf_token',
        '_token',
        'credit_card',
        'cc_number',
        'ssn',
        'social_security',
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Context Collection
    |--------------------------------------------------------------------------
    |
    | Configure what contextual data to collect with errors
    |
    */

    'context' => [
        'user'        => TRUE,
        'request'     => TRUE,
        'session'     => FALSE, // Disable session data for security
        'environment' => [
            'APP_ENV',
            'APP_VERSION',
            'PHP_VERSION',
            'LARAVEL_VERSION',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Configure performance monitoring thresholds
    |
    */

    'performance' => [
        'slow_query_threshold'   => env('SLOW_QUERY_THRESHOLD', 1000), // milliseconds
        'slow_request_threshold' => env('SLOW_REQUEST_THRESHOLD', 5000), // milliseconds
        'memory_threshold'       => env('MEMORY_THRESHOLD', 128), // MB
        'cpu_threshold'          => env('CPU_THRESHOLD', 80), // percentage
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Error Handlers
    |--------------------------------------------------------------------------
    |
    | Register custom error handlers for different error types
    |
    */

    'handlers' => [
        'database_errors' => App\Exceptions\DatabaseErrorHandler::class,
        'api_errors'      => App\Exceptions\ApiErrorHandler::class,
        'scraping_errors' => App\Exceptions\ScrapingErrorHandler::class,
        'payment_errors'  => App\Exceptions\PaymentErrorHandler::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    |
    | Configure how errors should be reported
    |
    */

    'notifications' => [
        'slack' => [
            'enabled'     => env('SLACK_ERROR_NOTIFICATIONS', FALSE),
            'webhook_url' => env('SLACK_WEBHOOK_URL'),
            'channel'     => env('SLACK_ERROR_CHANNEL', '#alerts'),
            'levels'      => ['emergency', 'alert', 'critical'],
        ],

        'email' => [
            'enabled'    => env('EMAIL_ERROR_NOTIFICATIONS', TRUE),
            'recipients' => [
                env('ADMIN_EMAIL', 'admin@hdtickets.local'),
            ],
            'levels' => ['emergency', 'alert', 'critical'],
        ],

        'database' => [
            'enabled' => TRUE,
            'table'   => 'error_logs',
            'levels'  => ['emergency', 'alert', 'critical', 'error'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Prevent error spam by rate limiting notifications
    |
    */

    'rate_limiting' => [
        'enabled'              => TRUE,
        'max_errors_per_hour'  => 50,
        'max_duplicate_errors' => 5,
        'duplicate_timeout'    => 300, // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Health Check Integration
    |--------------------------------------------------------------------------
    |
    | Configure integration with health check endpoints
    |
    */

    'health_checks' => [
        'error_rate_threshold'     => 10, // errors per minute
        'alert_on_threshold'       => TRUE,
        'include_in_health_status' => TRUE,
    ],
];
