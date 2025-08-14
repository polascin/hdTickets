<?php declare(strict_types=1);

/**
 * HD Tickets Deployment Configuration
 * Sports Events Entry Tickets Monitoring System
 *
 * Centralized configuration for all deployment environments
 * with validation, hot-reload, and environment-specific settings
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Environment Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for different deployment environments
    | (development, staging, production, blue, green)
    |
    */

    'environments' => [
        'development' => [
            'app_url'        => env('APP_URL', 'http://localhost'),
            'debug'          => TRUE,
            'log_level'      => 'debug',
            'cache_driver'   => 'array',
            'session_driver' => 'file',
            'queue_driver'   => 'sync',
            'mail_driver'    => 'log',

            // Sports ticket monitoring specific settings
            'scraping' => [
                'enabled'             => TRUE,
                'interval'            => 30, // seconds
                'timeout'             => 30,
                'retry_attempts'      => 3,
                'concurrent_requests' => 5,
                'user_agents'         => [
                    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                ],
            ],

            'ticket_platforms' => [
                'ticketmaster' => [
                    'enabled'    => TRUE,
                    'api_key'    => env('TICKETMASTER_API_KEY'),
                    'rate_limit' => 200, // requests per minute
                    'endpoints'  => [
                        'events' => 'https://app.ticketmaster.com/discovery/v2/events',
                        'venues' => 'https://app.ticketmaster.com/discovery/v2/venues',
                    ],
                ],
                'stubhub' => [
                    'enabled'    => TRUE,
                    'api_key'    => env('STUBHUB_API_KEY'),
                    'rate_limit' => 100,
                    'endpoints'  => [
                        'inventory' => 'https://api.stubhub.com/search/inventory/v2',
                    ],
                ],
            ],

            'alerts' => [
                'enabled'                     => TRUE,
                'channels'                    => ['email', 'slack'],
                'price_drop_threshold'        => 10, // percentage
                'availability_check_interval' => 300, // seconds
            ],
        ],

        'staging' => [
            'app_url'        => env('STAGING_APP_URL', 'https://staging.hdtickets.local'),
            'debug'          => FALSE,
            'log_level'      => 'info',
            'cache_driver'   => 'redis',
            'session_driver' => 'redis',
            'queue_driver'   => 'redis',
            'mail_driver'    => 'smtp',

            'scraping' => [
                'enabled'             => TRUE,
                'interval'            => 60,
                'timeout'             => 45,
                'retry_attempts'      => 5,
                'concurrent_requests' => 10,
                'user_agents'         => [
                    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
                ],
            ],

            'ticket_platforms' => [
                'ticketmaster' => [
                    'enabled'    => TRUE,
                    'api_key'    => env('TICKETMASTER_API_KEY'),
                    'rate_limit' => 300,
                    'endpoints'  => [
                        'events' => 'https://app.ticketmaster.com/discovery/v2/events',
                        'venues' => 'https://app.ticketmaster.com/discovery/v2/venues',
                    ],
                ],
                'stubhub' => [
                    'enabled'    => TRUE,
                    'api_key'    => env('STUBHUB_API_KEY'),
                    'rate_limit' => 200,
                    'endpoints'  => [
                        'inventory' => 'https://api.stubhub.com/search/inventory/v2',
                    ],
                ],
                'viagogo' => [
                    'enabled'    => TRUE,
                    'api_key'    => env('VIAGOGO_API_KEY'),
                    'rate_limit' => 150,
                ],
            ],

            'alerts' => [
                'enabled'                     => TRUE,
                'channels'                    => ['email', 'slack', 'sms'],
                'price_drop_threshold'        => 5,
                'availability_check_interval' => 180,
            ],
        ],

        'production' => [
            'app_url'        => env('APP_URL', 'https://hdtickets.local'),
            'debug'          => FALSE,
            'log_level'      => 'warning',
            'cache_driver'   => 'redis',
            'session_driver' => 'redis',
            'queue_driver'   => 'redis',
            'mail_driver'    => 'smtp',

            'scraping' => [
                'enabled'             => TRUE,
                'interval'            => 30,
                'timeout'             => 60,
                'retry_attempts'      => 5,
                'concurrent_requests' => 20,
                'user_agents'         => [
                    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
                    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:89.0) Gecko/20100101 Firefox/89.0',
                ],
            ],

            'ticket_platforms' => [
                'ticketmaster' => [
                    'enabled'    => TRUE,
                    'api_key'    => env('TICKETMASTER_API_KEY'),
                    'rate_limit' => 500,
                    'endpoints'  => [
                        'events'      => 'https://app.ticketmaster.com/discovery/v2/events',
                        'venues'      => 'https://app.ticketmaster.com/discovery/v2/venues',
                        'attractions' => 'https://app.ticketmaster.com/discovery/v2/attractions',
                    ],
                ],
                'stubhub' => [
                    'enabled'    => TRUE,
                    'api_key'    => env('STUBHUB_API_KEY'),
                    'rate_limit' => 400,
                    'endpoints'  => [
                        'inventory' => 'https://api.stubhub.com/search/inventory/v2',
                        'events'    => 'https://api.stubhub.com/catalog/events/v3',
                    ],
                ],
                'viagogo' => [
                    'enabled'    => TRUE,
                    'api_key'    => env('VIAGOGO_API_KEY'),
                    'rate_limit' => 300,
                    'endpoints'  => [
                        'events'   => 'https://api.viagogo.net/v2/events',
                        'listings' => 'https://api.viagogo.net/v2/listings',
                    ],
                ],
                'tickpick' => [
                    'enabled'    => TRUE,
                    'api_key'    => env('TICKPICK_API_KEY'),
                    'rate_limit' => 250,
                ],
            ],

            'alerts' => [
                'enabled'                     => TRUE,
                'channels'                    => ['email', 'slack', 'sms', 'push'],
                'price_drop_threshold'        => 3,
                'availability_check_interval' => 120,
            ],
        ],

        'blue' => [
            'extends'          => 'production',
            'app_url'          => 'http://127.0.0.1:8080',
            'deployment_color' => 'blue',
        ],

        'green' => [
            'extends'          => 'production',
            'app_url'          => 'http://127.0.0.1:9080',
            'deployment_color' => 'green',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration per Environment
    |--------------------------------------------------------------------------
    */

    'database' => [
        'development' => [
            'connection' => 'mysql',
            'host'       => env('DB_HOST', '127.0.0.1'),
            'port'       => env('DB_PORT', '3306'),
            'database'   => 'hdtickets_dev',
            'username'   => env('DB_USERNAME', 'hdtickets_user'),
            'password'   => env('DB_PASSWORD'),
            'charset'    => 'utf8mb4',
            'collation'  => 'utf8mb4_unicode_ci',
            'strict'     => FALSE,
            'engine'     => 'InnoDB',
            'options'    => [
                PDO::ATTR_STRINGIFY_FETCHES => FALSE,
                PDO::ATTR_EMULATE_PREPARES  => FALSE,
            ],
        ],

        'staging' => [
            'connection' => 'mysql',
            'host'       => env('STAGING_DB_HOST', '127.0.0.1'),
            'port'       => env('STAGING_DB_PORT', '3306'),
            'database'   => 'hdtickets_staging',
            'username'   => env('STAGING_DB_USERNAME'),
            'password'   => env('STAGING_DB_PASSWORD'),
            'charset'    => 'utf8mb4',
            'collation'  => 'utf8mb4_unicode_ci',
            'strict'     => TRUE,
            'engine'     => 'InnoDB',
        ],

        'production' => [
            'connection' => 'mysql',
            'host'       => env('DB_HOST'),
            'port'       => env('DB_PORT', '3306'),
            'database'   => env('DB_DATABASE', 'hdtickets'),
            'username'   => env('DB_USERNAME'),
            'password'   => env('DB_PASSWORD'),
            'charset'    => 'utf8mb4',
            'collation'  => 'utf8mb4_unicode_ci',
            'strict'     => TRUE,
            'engine'     => 'InnoDB',
            'options'    => [
                PDO::ATTR_STRINGIFY_FETCHES => FALSE,
                PDO::ATTR_EMULATE_PREPARES  => FALSE,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring & Performance Configuration
    |--------------------------------------------------------------------------
    */

    'monitoring' => [
        'enabled' => env('MONITORING_ENABLED', TRUE),

        'application' => [
            'health_check_interval' => 60, // seconds
            'performance_metrics'   => TRUE,
            'error_tracking'        => TRUE,
            'business_metrics'      => TRUE,
        ],

        'infrastructure' => [
            'server_monitoring'   => TRUE,
            'database_monitoring' => TRUE,
            'cache_monitoring'    => TRUE,
            'queue_monitoring'    => TRUE,
        ],

        'sports_events' => [
            'track_ticket_price_changes' => TRUE,
            'track_availability_changes' => TRUE,
            'track_scraping_performance' => TRUE,
            'track_alert_effectiveness'  => TRUE,
        ],

        'alerting' => [
            'critical_errors'         => TRUE,
            'performance_degradation' => TRUE,
            'high_error_rates'        => TRUE,
            'service_downtime'        => TRUE,
            'unusual_ticket_activity' => TRUE,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    */

    'security' => [
        'encryption' => [
            'cipher'            => 'AES-256-CBC',
            'key_rotation_days' => 90,
        ],

        'api_rate_limiting' => [
            'enabled'             => TRUE,
            'requests_per_minute' => env('API_RATE_LIMIT', 60),
            'requests_per_hour'   => env('API_RATE_LIMIT_HOUR', 1000),
        ],

        'csrf' => [
            'enabled'        => TRUE,
            'token_lifetime' => 7200, // seconds
        ],

        'cors' => [
            'allowed_origins' => [
                'https://hdtickets.local',
                'https://app.hdtickets.local',
            ],
            'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        ],

        'content_security_policy' => [
            'enabled'     => TRUE,
            'script_src'  => ["'self'", "'unsafe-inline'"],
            'style_src'   => ["'self'", "'unsafe-inline'"],
            'img_src'     => ["'self'", 'data:', 'https:'],
            'connect_src' => ["'self'"],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'sports_events' => [
            'ttl'  => 3600, // 1 hour
            'tags' => ['sports_events', 'events'],
        ],

        'ticket_prices' => [
            'ttl'  => 300, // 5 minutes
            'tags' => ['prices', 'tickets'],
        ],

        'availability' => [
            'ttl'  => 180, // 3 minutes
            'tags' => ['availability', 'tickets'],
        ],

        'user_alerts' => [
            'ttl'  => 1800, // 30 minutes
            'tags' => ['alerts', 'users'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */

    'queues' => [
        'scraping' => [
            'connection'  => 'redis',
            'queue'       => 'scraping',
            'retry_after' => 300,
            'max_tries'   => 3,
        ],

        'notifications' => [
            'connection'  => 'redis',
            'queue'       => 'notifications',
            'retry_after' => 60,
            'max_tries'   => 5,
        ],

        'data_processing' => [
            'connection'  => 'redis',
            'queue'       => 'data-processing',
            'retry_after' => 600,
            'max_tries'   => 3,
        ],

        'exports' => [
            'connection'  => 'redis',
            'queue'       => 'exports',
            'retry_after' => 1800,
            'max_tries'   => 2,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */

    'features' => [
        'new_scraping_engine'          => env('FEATURE_NEW_SCRAPING_ENGINE', FALSE),
        'advanced_analytics'           => env('FEATURE_ADVANCED_ANALYTICS', TRUE),
        'mobile_app_api'               => env('FEATURE_MOBILE_APP_API', TRUE),
        'real_time_notifications'      => env('FEATURE_REAL_TIME_NOTIFICATIONS', TRUE),
        'machine_learning_predictions' => env('FEATURE_ML_PREDICTIONS', FALSE),
        'social_sharing'               => env('FEATURE_SOCIAL_SHARING', TRUE),
    ],

    /*
    |--------------------------------------------------------------------------
    | Deployment Settings
    |--------------------------------------------------------------------------
    */

    'deployment' => [
        'strategy'             => 'blue-green',
        'health_check_timeout' => 30,
        'health_check_retries' => 10,
        'rollback_on_failure'  => TRUE,
        'auto_cleanup'         => TRUE,

        'backup' => [
            'enabled'      => TRUE,
            'retain_count' => 10,
            'compress'     => TRUE,
        ],

        'maintenance_mode' => [
            'template'    => 'deployment.maintenance.maintenance',
            'retry_after' => 300,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Hot Reload Configuration
    |--------------------------------------------------------------------------
    */

    'hot_reload' => [
        'enabled'     => env('CONFIG_HOT_RELOAD', FALSE),
        'watch_paths' => [
            config_path(),
            base_path('.env'),
            base_path('.env.local'),
        ],
        'reload_delay'  => 2, // seconds
        'excluded_keys' => [
            'app.key',
            'database.default.password',
        ],
    ],
];
