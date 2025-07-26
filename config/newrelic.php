<?php

return [

    /*
    |--------------------------------------------------------------------------
    | New Relic APM Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for New Relic Application Performance Monitoring
    | for the Sports Event Ticket Monitoring System
    |
    */

    'enabled' => env('NEW_RELIC_ENABLED', false),

    'app_name' => env('NEW_RELIC_APP_NAME', 'HDTickets-Production'),

    'license_key' => env('NEW_RELIC_LICENSE_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Transaction Tracing
    |--------------------------------------------------------------------------
    */

    'transaction_tracer' => [
        'enabled' => true,
        'threshold' => 'apdex_f', // Trace transactions slower than Apdex frustrated
        'detail' => 1,
        'slow_sql' => true,
        'stack_trace_threshold' => 0.5,
        'explain_enabled' => true,
        'explain_threshold' => 0.5,
        'record_sql' => 'obfuscated',
        'custom_instrumentation_editor' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Collection
    |--------------------------------------------------------------------------
    */

    'error_collector' => [
        'enabled' => true,
        'record_database_errors' => true,
        'prioritize_api_errors' => true,
        'ignore_errors' => [
            'Illuminate\Http\Exception\HttpResponseException',
            'Illuminate\Http\Exception\NotFoundHttpException',
            'Illuminate\Auth\AuthenticationException',
            'Illuminate\Validation\ValidationException',
        ],
        'ignore_status_codes' => [
            '401', '403', '404', '405', '422'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Browser Monitoring (Real User Monitoring)
    |--------------------------------------------------------------------------
    */

    'browser_monitoring' => [
        'auto_instrument' => true,
        'capture_attributes' => true,
        'include_response_headers' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Logging
    |--------------------------------------------------------------------------
    */

    'application_logging' => [
        'enabled' => true,
        'forwarding' => [
            'enabled' => true,
            'max_samples_stored' => 10000,
        ],
        'metrics' => [
            'enabled' => true,
        ],
        'local_decorating' => [
            'enabled' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Attributes
    |--------------------------------------------------------------------------
    */

    'custom_attributes' => [
        'environment' => env('APP_ENV'),
        'version' => '2025.7.3',
        'deployment_id' => env('DEPLOYMENT_ID', ''),
        'server_name' => gethostname(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Events & Metrics
    |--------------------------------------------------------------------------
    */

    'custom_events' => [
        'ticket_scraping_events' => true,
        'purchase_attempts' => true,
        'api_response_times' => true,
        'user_activities' => true,
        'alert_notifications' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Distributed Tracing
    |--------------------------------------------------------------------------
    */

    'distributed_tracing' => [
        'enabled' => true,
        'exclude_newrelic_header' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    */

    'datastore_tracer' => [
        'database_name_reporting' => [
            'enabled' => true,
        ],
        'instance_reporting' => [
            'enabled' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Thresholds
    |--------------------------------------------------------------------------
    */

    'performance' => [
        'apdex_t' => 0.5, // 500ms target response time
        'slow_query_threshold' => 1.0, // 1 second
        'very_slow_query_threshold' => 5.0, // 5 seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Instrumentation for Ticket System
    |--------------------------------------------------------------------------
    */

    'custom_instrumentation' => [
        'ticket_scraping' => [
            'classes' => [
                'App\Services\TicketScrapingService',
                'App\Services\TicketmasterScraper',
                'App\Services\MultiPlatformManager',
            ],
            'methods' => [
                'scrapeTickets',
                'processTicketData',
                'aggregateResults',
                'sendAlerts',
            ],
        ],
        'api_clients' => [
            'classes' => [
                'App\Services\TicketApis\*Client',
            ],
            'methods' => [
                'makeRequest',
                'processResponse',
                'handleErrors',
            ],
        ],
        'performance_critical' => [
            'classes' => [
                'App\Services\PerformanceCacheService',
                'App\Services\PlatformCachingService',
                'App\Services\RealTimeMonitoringService',
            ],
        ],
    ],

];
