<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sentry Error Tracking Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Sentry error tracking and performance monitoring
    | for the Sports Event Ticket Monitoring System
    |
    */

    'enabled' => env('SENTRY_ENABLED', false),

    'dsn' => env('SENTRY_LARAVEL_DSN', ''),

    'environment' => env('APP_ENV', 'production'),

    'release' => env('APP_VERSION', 'unknown'),

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    */

    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.0),

    'profiles_sample_rate' => (float) env('SENTRY_PROFILES_SAMPLE_RATE', 0.0),

    /*
    |--------------------------------------------------------------------------
    | Error Capture Configuration
    |--------------------------------------------------------------------------
    */

    'capture_unhandled_rejections' => true,

    'capture_silenced_errors' => false,

    'max_breadcrumbs' => 50,

    'attach_stacktrace' => true,

    'context_lines' => 5,

    /*
    |--------------------------------------------------------------------------
    | Breadcrumbs Configuration
    |--------------------------------------------------------------------------
    */

    'breadcrumbs' => [
        'logs' => true,
        'cache' => true,
        'livewire' => true,
        'sql_queries' => true,
        'sql_bindings' => true,
        'sql_transactions' => true,
        'queue_info' => true,
        'command_info' => true,
        'http_client_requests' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Integrations Configuration
    |--------------------------------------------------------------------------
    */

    'integrations' => [
        Sentry\Integration\RequestIntegration::class,
        Sentry\Integration\TransactionIntegration::class,
        Sentry\Integration\FrameContextifierIntegration::class,
        Sentry\Integration\EnvironmentIntegration::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignored Exceptions
    |--------------------------------------------------------------------------
    */

    'ignore_exceptions' => [
        'Illuminate\Auth\AuthenticationException',
        'Illuminate\Auth\Access\AuthorizationException',
        'Illuminate\Database\Eloquent\ModelNotFoundException',
        'Illuminate\Http\Exception\NotFoundHttpException',
        'Illuminate\Http\Exception\HttpResponseException',
        'Illuminate\Session\TokenMismatchException',
        'Illuminate\Validation\ValidationException',
        'Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
        'Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException',
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignored HTTP Status Codes
    |--------------------------------------------------------------------------
    */

    'ignore_http_codes' => [
        400, 401, 403, 404, 405, 422, 429,
    ],

    /*
    |--------------------------------------------------------------------------
    | Before Send Callback
    |--------------------------------------------------------------------------
    | Note: Closures have been disabled to allow config caching.
    | Custom event processing should be handled in a service provider.
    */

    'before_send' => null,

    /*
    |--------------------------------------------------------------------------
    | Before Send Transaction Callback
    |--------------------------------------------------------------------------
    | Note: Closures have been disabled to allow config caching.
    | Custom transaction processing should be handled in a service provider.
    */

    'before_send_transaction' => null,

    /*
    |--------------------------------------------------------------------------
    | Tags Configuration
    |--------------------------------------------------------------------------
    */

    'tags' => [
        'php_version' => PHP_VERSION,
        'server_name' => gethostname(),
        'deployment_id' => env('DEPLOYMENT_ID', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | User Context
    |--------------------------------------------------------------------------
    */

    'send_default_pii' => false,

    'user_context' => [
        'id' => true,
        'username' => true,
        'email' => false, // Don't send email for privacy
        'ip_address' => false, // Don't send IP for privacy
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Context for Ticket System
    |--------------------------------------------------------------------------
    */

    'custom_context' => [
        'ticket_scraping' => [
            'active_scrapers' => true,
            'last_scrape_time' => true,
            'platform_health' => true,
        ],
        'user_activity' => [
            'current_alerts' => true,
            'subscription_status' => true,
            'last_login' => true,
        ],
        'system_metrics' => [
            'memory_usage' => true,
            'cpu_usage' => true,
            'active_connections' => true,
            'queue_depth' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sampling Configuration
    |--------------------------------------------------------------------------
    */

    'sampling' => [
        'error_sample_rate' => 1.0, // Capture all errors
        'transaction_sample_rate' => 0.1, // Sample 10% of transactions
        'profiling_sample_rate' => 0.01, // Profile 1% of transactions
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Fingerprinting
    |--------------------------------------------------------------------------
    */

    'fingerprinting_rules' => [
        // Group database connection errors
        '{{ error.type }}' => [
            'Illuminate\Database\QueryException',
            'PDOException',
        ],
        
        // Group scraping-related errors
        '{{ error.type }}:{{ tags.platform }}' => [
            'App\Exceptions\TicketPlatformException',
            'GuzzleHttp\Exception\RequestException',
        ],
        
        // Group rate limiting errors
        '{{ error.type }}:rate_limit' => [
            'Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    */

    'performance' => [
        'auto_finishing_transactions' => true,
        'continue_from_headers' => true,
        'trace_propagation_targets' => [
            'hdtickets.polascin.net',
            'api.hdtickets.polascin.net',
        ],
        'idle_timeout' => 30,
        'final_timeout' => 300,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security & Privacy
    |--------------------------------------------------------------------------
    */

    'security' => [
        'scrub_fields' => [
            'password',
            'password_confirmation',
            'secret',
            'api_key',
            'token',
            'credit_card',
            'ssn',
            'auth_token',
            'access_token',
            'refresh_token',
        ],
        'scrub_patterns' => [
            '/\b\d{4}[-\s]?\d{4}[-\s]?\d{4}[-\s]?\d{4}\b/', // Credit card numbers
            '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', // Email addresses
            '/\b\d{3}-\d{2}-\d{4}\b/', // SSN pattern
        ],
    ],

];
