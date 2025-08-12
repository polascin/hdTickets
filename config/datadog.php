<?php declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Datadog APM Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Datadog Application Performance Monitoring
    | for the Sports Event Ticket Monitoring System
    |
    */

    'enabled' => env('DATADOG_ENABLED', FALSE),

    'api_key' => env('DATADOG_API_KEY', ''),

    'app_key' => env('DATADOG_APP_KEY', ''),

    'site' => env('DATADOG_SITE', 'datadoghq.eu'), // EU site for GDPR compliance

    /*
    |--------------------------------------------------------------------------
    | Service Configuration
    |--------------------------------------------------------------------------
    */

    'service' => [
        'name'        => 'hdtickets',
        'version'     => '2025.7.3',
        'environment' => env('APP_ENV', 'production'),
    ],

    /*
    |--------------------------------------------------------------------------
    | APM Configuration
    |--------------------------------------------------------------------------
    */

    'apm' => [
        'enabled'      => TRUE,
        'service_name' => 'hdtickets-web',
        'version'      => '2025.7.3',
        'env'          => env('APP_ENV', 'production'),

        // Distributed tracing
        'distributed_tracing' => TRUE,
        'priority_sampling'   => TRUE,
        'trace_sample_rate'   => 1.0,

        // Performance monitoring
        'profiling_enabled'               => TRUE,
        'resource_names_as_service_names' => FALSE,

        // Database tracing
        'trace_laravel_view'  => TRUE,
        'trace_laravel_queue' => TRUE,
        'trace_eloquent'      => TRUE,
        'trace_cache'         => TRUE,
        'trace_redis'         => TRUE,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    */

    'logs' => [
        'enabled'         => TRUE,
        'level'           => 'info',
        'inject_trace_id' => TRUE,
        'channels'        => [
            'single',
            'daily',
            'slack',
            'syslog',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Metrics Configuration
    |--------------------------------------------------------------------------
    */

    'metrics' => [
        'enabled'        => TRUE,
        'namespace'      => 'hdtickets',
        'custom_metrics' => [
            'ticket_scraping_duration' => [
                'type' => 'histogram',
                'tags' => ['platform', 'event_type'],
            ],
            'api_response_time' => [
                'type' => 'histogram',
                'tags' => ['endpoint', 'method', 'status_code'],
            ],
            'ticket_alerts_sent' => [
                'type' => 'count',
                'tags' => ['alert_type', 'platform'],
            ],
            'purchase_attempts' => [
                'type' => 'count',
                'tags' => ['platform', 'success', 'user_type'],
            ],
            'scraping_success_rate' => [
                'type' => 'gauge',
                'tags' => ['platform'],
            ],
            'active_users' => [
                'type' => 'gauge',
                'tags' => ['user_type'],
            ],
            'cache_hit_rate' => [
                'type' => 'gauge',
                'tags' => ['cache_type'],
            ],
            'queue_depth' => [
                'type' => 'gauge',
                'tags' => ['queue_name'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Tracking
    |--------------------------------------------------------------------------
    */

    'error_tracking' => [
        'enabled'           => TRUE,
        'ignore_exceptions' => [
            'Illuminate\Http\Exception\NotFoundHttpException',
            'Illuminate\Auth\AuthenticationException',
            'Illuminate\Validation\ValidationException',
        ],
        'ignore_status_codes' => [401, 403, 404, 422],
    ],

    /*
    |--------------------------------------------------------------------------
    | Real User Monitoring (RUM)
    |--------------------------------------------------------------------------
    */

    'rum' => [
        'enabled'                    => TRUE,
        'application_id'             => env('DATADOG_RUM_APPLICATION_ID', ''),
        'client_token'               => env('DATADOG_RUM_CLIENT_TOKEN', ''),
        'session_sample_rate'        => 100,
        'session_replay_sample_rate' => 20,
        'track_user_interactions'    => TRUE,
        'track_resources'            => TRUE,
        'track_long_tasks'           => TRUE,
    ],

    /*
    |--------------------------------------------------------------------------
    | Synthetics Monitoring
    |--------------------------------------------------------------------------
    */

    'synthetics' => [
        'enabled' => TRUE,
        'tests'   => [
            'api_health_check' => [
                'url'        => env('APP_URL') . '/health',
                'method'     => 'GET',
                'frequency'  => 300, // 5 minutes
                'locations'  => ['aws:eu-central-1'],
                'assertions' => [
                    ['type' => 'statusCode', 'operator' => 'is', 'value' => 200],
                    ['type' => 'responseTime', 'operator' => 'lessThan', 'value' => 1000],
                ],
            ],
            'login_functionality' => [
                'url'          => env('APP_URL') . '/login',
                'method'       => 'GET',
                'frequency'    => 600, // 10 minutes
                'locations'    => ['aws:eu-central-1'],
                'browser_test' => TRUE,
            ],
            'ticket_search' => [
                'url'       => env('APP_URL') . '/api/tickets/search',
                'method'    => 'POST',
                'frequency' => 900, // 15 minutes
                'locations' => ['aws:eu-central-1'],
                'headers'   => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Infrastructure Monitoring
    |--------------------------------------------------------------------------
    */

    'infrastructure' => [
        'enabled'                => TRUE,
        'agent_version'          => '7.x',
        'collect_ec2_tags'       => TRUE,
        'collect_custom_metrics' => TRUE,
        'integrations'           => [
            'mysql' => [
                'enabled'            => TRUE,
                'performance_schema' => TRUE,
            ],
            'redis' => [
                'enabled'       => TRUE,
                'command_stats' => TRUE,
            ],
            'nginx' => [
                'enabled'    => TRUE,
                'status_url' => 'http://localhost/nginx_status',
            ],
            'php_fpm' => [
                'enabled'    => TRUE,
                'status_url' => 'http://localhost/fpm-status',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alerting Configuration
    |--------------------------------------------------------------------------
    */

    'alerts' => [
        'channels' => [
            'slack'     => env('DATADOG_SLACK_WEBHOOK', ''),
            'email'     => env('DATADOG_ALERT_EMAIL', 'alerts@hdtickets.polascin.net'),
            'pagerduty' => env('DATADOG_PAGERDUTY_KEY', ''),
        ],
        'thresholds' => [
            'error_rate'        => 5, // 5% error rate threshold
            'response_time_p95' => 2000, // 2 seconds
            'response_time_p99' => 5000, // 5 seconds
            'memory_usage'      => 80, // 80% memory usage
            'cpu_usage'         => 85, // 85% CPU usage
            'disk_usage'        => 90, // 90% disk usage
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Tags
    |--------------------------------------------------------------------------
    */

    'tags' => [
        'service'     => 'hdtickets',
        'version'     => '2025.7.3',
        'environment' => env('APP_ENV', 'production'),
        'team'        => 'platform',
        'cost_center' => 'engineering',
        'region'      => 'eu-central-1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    */

    'security' => [
        'obfuscate_sql_values'         => TRUE,
        'obfuscate_memcache_keys'      => TRUE,
        'obfuscate_redis_commands'     => TRUE,
        'obfuscate_http_query_strings' => [
            'password',
            'token',
            'api_key',
            'secret',
            'credit_card',
        ],
    ],
];
