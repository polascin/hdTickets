<?php declare(strict_types=1);

use App\Exceptions\TicketPlatformException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Sentry\Integration\EnvironmentIntegration;
use Sentry\Integration\FrameContextifierIntegration;
use Sentry\Integration\RequestIntegration;
use Sentry\Integration\TransactionIntegration;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

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

    'enabled' => env('SENTRY_ENABLED', FALSE),

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

    'capture_unhandled_rejections' => TRUE,

    'capture_silenced_errors' => FALSE,

    'max_breadcrumbs' => 50,

    'attach_stacktrace' => TRUE,

    'context_lines' => 5,

    /*
    |--------------------------------------------------------------------------
    | Breadcrumbs Configuration
    |--------------------------------------------------------------------------
    */

    'breadcrumbs' => [
        'logs'                 => TRUE,
        'cache'                => TRUE,
        'livewire'             => TRUE,
        'sql_queries'          => TRUE,
        'sql_bindings'         => TRUE,
        'sql_transactions'     => TRUE,
        'queue_info'           => TRUE,
        'command_info'         => TRUE,
        'http_client_requests' => TRUE,
    ],

    /*
    |--------------------------------------------------------------------------
    | Integrations Configuration
    |--------------------------------------------------------------------------
    */

    'integrations' => [
        RequestIntegration::class,
        TransactionIntegration::class,
        FrameContextifierIntegration::class,
        EnvironmentIntegration::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignored Exceptions
    |--------------------------------------------------------------------------
    */

    'ignore_exceptions' => [
        AuthenticationException::class,
        AuthorizationException::class,
        ModelNotFoundException::class,
        'Illuminate\Http\Exception\NotFoundHttpException',
        'Illuminate\Http\Exception\HttpResponseException',
        TokenMismatchException::class,
        ValidationException::class,
        NotFoundHttpException::class,
        MethodNotAllowedHttpException::class,
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

    'before_send' => NULL,

    /*
    |--------------------------------------------------------------------------
    | Before Send Transaction Callback
    |--------------------------------------------------------------------------
    | Note: Closures have been disabled to allow config caching.
    | Custom transaction processing should be handled in a service provider.
    */

    'before_send_transaction' => NULL,

    /*
    |--------------------------------------------------------------------------
    | Tags Configuration
    |--------------------------------------------------------------------------
    */

    'tags' => [
        'php_version'   => PHP_VERSION,
        'server_name'   => gethostname(),
        'deployment_id' => env('DEPLOYMENT_ID', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | User Context
    |--------------------------------------------------------------------------
    */

    'send_default_pii' => FALSE,

    'user_context' => [
        'id'         => TRUE,
        'username'   => TRUE,
        'email'      => FALSE, // Don't send email for privacy
        'ip_address' => FALSE, // Don't send IP for privacy
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Context for Ticket System
    |--------------------------------------------------------------------------
    */

    'custom_context' => [
        'ticket_scraping' => [
            'active_scrapers'  => TRUE,
            'last_scrape_time' => TRUE,
            'platform_health'  => TRUE,
        ],
        'user_activity' => [
            'current_alerts'      => TRUE,
            'subscription_status' => TRUE,
            'last_login'          => TRUE,
        ],
        'system_metrics' => [
            'memory_usage'       => TRUE,
            'cpu_usage'          => TRUE,
            'active_connections' => TRUE,
            'queue_depth'        => TRUE,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sampling Configuration
    |--------------------------------------------------------------------------
    */

    'sampling' => [
        'error_sample_rate'       => 1.0, // Capture all errors
        'transaction_sample_rate' => 0.1, // Sample 10% of transactions
        'profiling_sample_rate'   => 0.01, // Profile 1% of transactions
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Fingerprinting
    |--------------------------------------------------------------------------
    */

    'fingerprinting_rules' => [
        // Group database connection errors
        '{{ error.type }}' => [
            QueryException::class,
            'PDOException',
        ],

        // Group scraping-related errors
        '{{ error.type }}:{{ tags.platform }}' => [
            TicketPlatformException::class,
            RequestException::class,
        ],

        // Group rate limiting errors
        '{{ error.type }}:rate_limit' => [
            TooManyRequestsHttpException::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    */

    'performance' => [
        'auto_finishing_transactions' => TRUE,
        'continue_from_headers'       => TRUE,
        'trace_propagation_targets'   => [
            'hdtickets.polascin.net',
            'api.hdtickets.polascin.net',
        ],
        'idle_timeout'  => 30,
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
