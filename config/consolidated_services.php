<?php declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Consolidated Services Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the HD Tickets consolidated service layer.
    | This includes settings for scraping, monitoring, notifications,
    | purchase automation, and other core services.
    |
    */

    'scraping' => [
        'enabled_platforms' => [
            'ticketmaster', 'stubhub', 'seatgeek', 'viagogo', 'see_tickets',
            'manchester_united', 'arsenal_fc', 'chelsea_fc', 'liverpool_fc',
            'real_madrid', 'barcelona', 'bayern_munich', 'juventus',
        ],
        'rate_limits' => [
            'default'           => 60,        // requests per minute
            'ticketmaster'      => 30,
            'stubhub'           => 45,
            'premium_platforms' => 120,
        ],
        'anti_detection' => [
            'user_agents_rotation' => TRUE,
            'proxy_rotation'       => TRUE,
            'request_delays'       => [
                'min' => 2000,      // milliseconds
                'max' => 5000,
            ],
        ],
        'cache_ttl'      => 300,         // 5 minutes default cache
        'retry_attempts' => 3,
        'timeout'        => 30,             // seconds
    ],

    'monitoring' => [
        'check_intervals' => [
            'high_priority' => 60,   // seconds
            'normal'        => 300,         // 5 minutes
            'low_priority'  => 900,    // 15 minutes
        ],
        'alert_thresholds' => [
            'price_change_percent' => 10,
            'availability_change'  => 5,
            'response_time_ms'     => 5000,
        ],
        'history_retention' => [
            'availability' => 100,   // data points
            'price'        => 100,
            'alerts'       => 1000,
        ],
        'health_check_interval' => 60, // seconds
    ],

    'notifications' => [
        'enabled_channels' => [
            'database', 'broadcast', 'push', 'mail', 'sms',
            'discord', 'slack', 'telegram', 'webhook',
        ],
        'rate_limits' => [
            'email'   => 60,          // per hour per user
            'sms'     => 10,
            'push'    => 300,
            'webhook' => 100,
        ],
        'priorities' => [
            'high'   => ['database', 'broadcast', 'push', 'mail', 'sms'],
            'normal' => ['database', 'broadcast', 'push', 'mail'],
            'low'    => ['database', 'broadcast'],
        ],
        'templates' => [
            'ticket_alert' => 'notifications.ticket_alert',
            'price_update' => 'notifications.price_update',
            'system'       => 'notifications.system',
        ],
        'quiet_hours_default' => [
            'start' => '22:00',
            'end'   => '08:00',
        ],
        'batch_size'     => 1000,       // users per batch
        'retry_attempts' => 3,
    ],

    'purchase_automation' => [
        'decision_timeout' => 5,    // seconds
        'queue_processing' => [
            'batch_size'   => 50,
            'max_attempts' => 3,
            'retry_delay'  => 300,    // seconds
        ],
        'decision_factors' => [
            'price_weight'                => 0.4,
            'availability_weight'         => 0.3,
            'user_preference_weight'      => 0.2,
            'platform_reliability_weight' => 0.1,
        ],
        'confidence_threshold' => 0.7,
        'security'             => [
            'encrypt_decisions'   => TRUE,
            'encrypt_preferences' => TRUE,
            'audit_trail'         => TRUE,
        ],
        'strategies' => [
            'default'  => 'standard',
            'premium'  => 'aggressive',
            'cautious' => 'conservative',
        ],
    ],

    'analytics' => [
        'retention_periods' => [
            'events'        => 2592000,    // 30 days
            'metrics'       => 7776000,   // 90 days
            'user_behavior' => 7776000,
            'performance'   => 2592000,
        ],
        'aggregation_intervals' => [
            'real_time' => 60,      // seconds
            'hourly'    => 3600,
            'daily'     => 86400,
        ],
        'sampling_rates' => [
            'events'      => 1.0,        // 100%
            'performance' => 0.1,   // 10%
            'debug'       => 0.01,         // 1%
        ],
    ],

    'cache' => [
        'default_ttl'          => 300,       // 5 minutes
        'service_health_ttl'   => 60, // 1 minute
        'user_preferences_ttl' => 3600, // 1 hour
        'scraping_results_ttl' => 300,
        'analytics_ttl'        => 1800,    // 30 minutes
        'prefixes'             => [
            'scraping'      => 'scraping:',
            'monitoring'    => 'monitoring:',
            'notifications' => 'notifications:',
            'purchase'      => 'purchase:',
            'analytics'     => 'analytics:',
        ],
    ],

    'queue' => [
        'default_connection' => 'redis',
        'queues'             => [
            'scraping'      => 'scraping',
            'monitoring'    => 'monitoring',
            'notifications' => 'notifications',
            'purchase'      => 'purchase',
            'analytics'     => 'analytics',
        ],
        'retry_attempts' => 3,
        'batch_size'     => 100,
        'timeout'        => 300,            // seconds
    ],

    'security' => [
        'encryption' => [
            'sensitive_fields' => [
                'user_preferences',
                'purchase_decisions',
                'authentication_tokens',
                'payment_details',
                'personal_data',
            ],
            'key_rotation_interval' => 86400 * 30, // 30 days
        ],
        'rate_limiting' => [
            'api_calls'     => 1000,    // per hour
            'scraping'      => 60,       // per minute
            'notifications' => 100,  // per hour
        ],
        'audit_logging' => [
            'enabled'   => TRUE,
            'retention' => 86400 * 90, // 90 days
            'events'    => [
                'service_initialization',
                'purchase_decisions',
                'configuration_changes',
                'security_events',
            ],
        ],
    ],

    'health_monitoring' => [
        'check_interval'  => 60,     // seconds
        'service_timeout' => 10,    // seconds for health checks
        'thresholds'      => [
            'memory_usage_mb'    => 512,
            'response_time_ms'   => 1000,
            'error_rate_percent' => 5,
        ],
        'alerts' => [
            'critical_health_threshold' => 50, // percentage
            'warning_health_threshold'  => 75,
            'notification_channels'     => ['email', 'slack'],
        ],
    ],

    'performance' => [
        'optimization' => [
            'enable_caching'            => TRUE,
            'enable_compression'        => TRUE,
            'enable_connection_pooling' => TRUE,
        ],
        'monitoring' => [
            'track_response_times'   => TRUE,
            'track_memory_usage'     => TRUE,
            'track_database_queries' => TRUE,
        ],
        'limits' => [
            'max_concurrent_scraping'   => 10,
            'max_concurrent_monitoring' => 20,
            'max_queue_size'            => 10000,
        ],
    ],
];
