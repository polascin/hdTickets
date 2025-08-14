<?php declare(strict_types=1);

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

    'enabled' => env('NEW_RELIC_ENABLED', FALSE),

    'app_name' => env('NEW_RELIC_APP_NAME', 'HDTickets-Production'),

    'license_key' => env('NEW_RELIC_LICENSE_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Transaction Tracing
    |--------------------------------------------------------------------------
    */

    'transaction_tracer' => [
        'enabled'                       => TRUE,
        'threshold'                     => 'apdex_f', // Trace transactions slower than Apdex frustrated
        'detail'                        => 1,
        'slow_sql'                      => TRUE,
        'stack_trace_threshold'         => 0.5,
        'explain_enabled'               => TRUE,
        'explain_threshold'             => 0.5,
        'record_sql'                    => 'obfuscated',
        'custom_instrumentation_editor' => TRUE,
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Collection
    |--------------------------------------------------------------------------
    */

    'error_collector' => [
        'enabled'                => TRUE,
        'record_database_errors' => TRUE,
        'prioritize_api_errors'  => TRUE,
        'ignore_errors'          => [
            'Illuminate\Http\Exception\HttpResponseException',
            'Illuminate\Http\Exception\NotFoundHttpException',
            'Illuminate\Auth\AuthenticationException',
            'Illuminate\Validation\ValidationException',
        ],
        'ignore_status_codes' => [
            '401', '403', '404', '405', '422',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Browser Monitoring (Real User Monitoring)
    |--------------------------------------------------------------------------
    */

    'browser_monitoring' => [
        'auto_instrument'          => TRUE,
        'capture_attributes'       => TRUE,
        'include_response_headers' => TRUE,
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Logging
    |--------------------------------------------------------------------------
    */

    'application_logging' => [
        'enabled'    => TRUE,
        'forwarding' => [
            'enabled'            => TRUE,
            'max_samples_stored' => 10000,
        ],
        'metrics' => [
            'enabled' => TRUE,
        ],
        'local_decorating' => [
            'enabled' => TRUE,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Attributes
    |--------------------------------------------------------------------------
    */

    'custom_attributes' => [
        'environment'   => env('APP_ENV'),
        'version'       => '2025.7.3',
        'deployment_id' => env('DEPLOYMENT_ID', ''),
        'server_name'   => gethostname(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Events & Metrics
    |--------------------------------------------------------------------------
    */

    'custom_events' => [
        'ticket_scraping_events' => TRUE,
        'purchase_attempts'      => TRUE,
        'api_response_times'     => TRUE,
        'user_activities'        => TRUE,
        'alert_notifications'    => TRUE,
    ],

    /*
    |--------------------------------------------------------------------------
    | Distributed Tracing
    |--------------------------------------------------------------------------
    */

    'distributed_tracing' => [
        'enabled'                 => TRUE,
        'exclude_newrelic_header' => FALSE,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    */

    'datastore_tracer' => [
        'database_name_reporting' => [
            'enabled' => TRUE,
        ],
        'instance_reporting' => [
            'enabled' => TRUE,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Thresholds
    |--------------------------------------------------------------------------
    */

    'performance' => [
        'apdex_t'                   => 0.5, // 500ms target response time
        'slow_query_threshold'      => 1.0, // 1 second
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
