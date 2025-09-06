<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the HD Tickets analytics
    | system including predictive models, anomaly detection, export settings,
    | and data processing parameters.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | General Analytics Settings
    |--------------------------------------------------------------------------
    */
    'enabled' => env('ANALYTICS_ENABLED', true),
    'cache_ttl' => env('ANALYTICS_CACHE_TTL', 300), // 5 minutes in seconds
    'default_lookback_days' => env('ANALYTICS_DEFAULT_LOOKBACK_DAYS', 30),
    'max_data_points' => env('ANALYTICS_MAX_DATA_POINTS', 10000),

    /*
    |--------------------------------------------------------------------------
    | Predictive Analytics Configuration
    |--------------------------------------------------------------------------
    */
    'predictive' => [
        'enabled' => env('PREDICTIVE_ANALYTICS_ENABLED', true),
        'price_prediction_window' => 30, // days
        'demand_forecast_horizon' => 90, // days
        'confidence_threshold' => 0.75,
        'min_historical_data_points' => 50,
        'model_refresh_interval' => 24, // hours
        'prediction_cache_ttl' => 900, // 15 minutes

        // Machine learning model settings
        'models' => [
            'price_prediction' => [
                'algorithm' => 'linear_regression',
                'features' => [
                    'historical_prices',
                    'venue_capacity',
                    'event_popularity',
                    'seasonal_patterns',
                    'platform_trends',
                ],
                'validation_split' => 0.2,
                'cross_validation_folds' => 5,
            ],
            'demand_forecasting' => [
                'algorithm' => 'time_series',
                'features' => [
                    'historical_demand',
                    'event_category',
                    'venue_location',
                    'price_points',
                    'external_factors',
                ],
                'seasonality' => true,
                'trend_analysis' => true,
            ],
        ],

        // Accuracy thresholds
        'accuracy_thresholds' => [
            'price_prediction' => [
                'mae_threshold' => 15.0, // Mean Absolute Error
                'mape_threshold' => 10.0, // Mean Absolute Percentage Error
                'r_squared_min' => 0.70, // Coefficient of determination
            ],
            'demand_forecasting' => [
                'mae_threshold' => 20.0,
                'mape_threshold' => 15.0,
                'r_squared_min' => 0.65,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Anomaly Detection Configuration
    |--------------------------------------------------------------------------
    */
    'anomaly_detection' => [
        'enabled' => env('ANOMALY_DETECTION_ENABLED', true),
        'price_anomaly_threshold' => 3.0, // Standard deviations
        'volume_anomaly_threshold' => 2.5,
        'velocity_anomaly_threshold' => 2.0,
        'lookback_period' => 30, // days
        'min_data_points' => 20,
        'confidence_level' => 0.95,

        // Detection algorithms
        'algorithms' => [
            'statistical' => [
                'enabled' => true,
                'z_score_threshold' => 3.0,
                'iqr_multiplier' => 1.5,
            ],
            'isolation_forest' => [
                'enabled' => true,
                'contamination' => 0.1,
                'n_estimators' => 100,
            ],
            'time_series' => [
                'enabled' => true,
                'seasonal_decomposition' => true,
                'trend_detection' => true,
            ],
        ],

        // Alert configurations
        'alerts' => [
            'enabled' => true,
            'channels' => ['email', 'slack', 'database'],
            'severity_levels' => [
                'critical' => [
                    'z_score_min' => 4.0,
                    'notification_delay' => 0, // immediate
                    'escalation_time' => 300, // 5 minutes
                ],
                'high' => [
                    'z_score_min' => 3.0,
                    'notification_delay' => 60, // 1 minute
                    'escalation_time' => 900, // 15 minutes
                ],
                'medium' => [
                    'z_score_min' => 2.0,
                    'notification_delay' => 300, // 5 minutes
                    'escalation_time' => 3600, // 1 hour
                ],
                'low' => [
                    'z_score_min' => 1.5,
                    'notification_delay' => 900, // 15 minutes
                    'escalation_time' => 7200, // 2 hours
                ],
            ],
        ],

        // Real-time monitoring
        'realtime' => [
            'enabled' => true,
            'check_interval' => 300, // 5 minutes
            'price_spike_threshold' => 50.0, // percentage
            'volume_surge_threshold' => 200.0, // percentage
            'platform_downtime_threshold' => 600, // 10 minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Export Configuration
    |--------------------------------------------------------------------------
    */
    'export' => [
        'enabled' => env('ANALYTICS_EXPORT_ENABLED', true),
        'storage_disk' => env('ANALYTICS_EXPORT_DISK', 'local'),
        'export_path' => 'analytics/exports',
        'retention_days' => 30,
        'max_rows_per_export' => 50000,
        'chunk_size' => 1000,

        // Supported formats
        'formats' => [
            'csv' => [
                'enabled' => true,
                'max_file_size' => '50M',
                'delimiter' => ',',
                'enclosure' => '"',
                'escape' => '\\',
            ],
            'xlsx' => [
                'enabled' => true,
                'max_file_size' => '100M',
                'max_sheets' => 10,
                'include_charts' => false,
            ],
            'pdf' => [
                'enabled' => true,
                'max_file_size' => '25M',
                'page_size' => 'A4',
                'orientation' => 'portrait',
                'include_images' => true,
            ],
            'json' => [
                'enabled' => true,
                'max_file_size' => '75M',
                'pretty_print' => true,
                'include_metadata' => true,
            ],
        ],

        // Automated exports
        'scheduled_exports' => [
            'enabled' => env('SCHEDULED_EXPORTS_ENABLED', true),
            'daily_report' => [
                'enabled' => true,
                'time' => '06:00',
                'format' => 'pdf',
                'recipients' => env('DAILY_REPORT_RECIPIENTS', ''),
            ],
            'weekly_report' => [
                'enabled' => true,
                'day' => 'monday',
                'time' => '08:00',
                'format' => 'xlsx',
                'recipients' => env('WEEKLY_REPORT_RECIPIENTS', ''),
            ],
            'monthly_report' => [
                'enabled' => true,
                'day' => 1,
                'time' => '09:00',
                'format' => 'pdf',
                'recipients' => env('MONTHLY_REPORT_RECIPIENTS', ''),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Configuration
    |--------------------------------------------------------------------------
    */
    'dashboard' => [
        'refresh_interval' => 30, // seconds
        'auto_refresh' => true,
        'max_chart_points' => 500,
        'chart_animations' => true,

        // Default chart configurations
        'charts' => [
            'price_trends' => [
                'type' => 'line',
                'time_range' => 30, // days
                'aggregation' => 'daily',
                'colors' => ['#3498db', '#e74c3c', '#2ecc71', '#f39c12'],
            ],
            'platform_performance' => [
                'type' => 'bar',
                'sort_by' => 'total_tickets',
                'show_percentages' => true,
                'colors' => ['#9b59b6', '#1abc9c', '#34495e', '#e67e22'],
            ],
            'event_popularity' => [
                'type' => 'doughnut',
                'max_categories' => 8,
                'show_legends' => true,
                'colors' => ['#ff6384', '#36a2eb', '#cc65fe', '#ffce56'],
            ],
        ],

        // Widget settings
        'widgets' => [
            'overview_metrics' => [
                'enabled' => true,
                'update_interval' => 60, // seconds
                'show_trends' => true,
                'trend_period' => 7, // days
            ],
            'real_time_alerts' => [
                'enabled' => true,
                'max_alerts' => 10,
                'auto_dismiss' => 300, // 5 minutes
                'severity_colors' => [
                    'critical' => '#e74c3c',
                    'high' => '#f39c12',
                    'medium' => '#f1c40f',
                    'low' => '#3498db',
                ],
            ],
            'top_events' => [
                'enabled' => true,
                'limit' => 10,
                'sort_by' => 'ticket_count',
                'time_range' => 7, // days
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Sources Configuration
    |--------------------------------------------------------------------------
    */
    'data_sources' => [
        'tickets' => [
            'table' => 'tickets',
            'model' => 'App\\Domain\\Ticket\\Models\\Ticket',
            'relationships' => ['sportsEvent', 'platform'],
            'required_fields' => ['price', 'source_platform', 'sports_event_id'],
        ],
        'sports_events' => [
            'table' => 'sports_events',
            'model' => 'App\\Domain\\Event\\Models\\SportsEvent',
            'relationships' => ['tickets', 'venue'],
            'required_fields' => ['name', 'category', 'event_date'],
        ],
        'users' => [
            'table' => 'users',
            'model' => 'App\\Models\\User',
            'relationships' => ['purchases'],
            'required_fields' => ['role', 'created_at'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Optimization
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'use_database_indexes' => true,
        'enable_query_caching' => true,
        'cache_warming' => [
            'enabled' => true,
            'schedule' => '0 */4 * * *', // Every 4 hours
            'preload_data' => [
                'overview_metrics',
                'platform_performance',
                'trending_events',
            ],
        ],

        // Database optimization
        'database' => [
            'use_read_replicas' => env('ANALYTICS_USE_READ_REPLICAS', false),
            'query_timeout' => 30, // seconds
            'max_concurrent_queries' => 10,
            'optimize_aggregations' => true,
        ],

        // Memory management
        'memory' => [
            'max_memory_usage' => '512M',
            'chunk_processing' => true,
            'gc_probability' => 1,
            'gc_divisor' => 100,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'security' => [
        'rate_limiting' => [
            'requests_per_minute' => 60,
            'burst_limit'         => 100,
        ],
        'data_masking'       => env('ANALYTICS_DATA_MASKING', FALSE),
        'audit_logging'      => env('ANALYTICS_AUDIT_LOGGING', TRUE),
        'ip_whitelisting'    => env('ANALYTICS_IP_WHITELISTING', FALSE),
        'encryption_at_rest' => env('ANALYTICS_ENCRYPTION_AT_REST', TRUE),
    ],

    /*
    |--------------------------------------------------------------------------
    | Visualization Settings
    |--------------------------------------------------------------------------
    */
    'visualization' => [
        'color_schemes' => [
            'default'             => ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd'],
            'colorblind_friendly' => ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd'],
            'high_contrast'       => ['#000000', '#ffffff', '#ff0000', '#00ff00', '#0000ff'],
        ],
        'chart_libraries' => [
            'primary'  => 'chart.js',
            'fallback' => 'd3.js',
        ],
        'responsive_breakpoints' => [
            'mobile'  => 768,
            'tablet'  => 1024,
            'desktop' => 1200,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Integration Settings
    |--------------------------------------------------------------------------
    */
    'integrations' => [
        'external_apis' => [
            'enabled'     => env('ANALYTICS_EXTERNAL_APIS', FALSE),
            'rate_limits' => [
                'requests_per_hour' => 1000,
                'burst_limit'       => 50,
            ],
        ],
        'webhooks' => [
            'enabled'        => env('ANALYTICS_WEBHOOKS_ENABLED', FALSE),
            'retry_attempts' => 3,
            'timeout'        => 30,
        ],
        'third_party_tools' => [
            'google_analytics' => env('ANALYTICS_GA_INTEGRATION', FALSE),
            'mixpanel'         => env('ANALYTICS_MIXPANEL_INTEGRATION', FALSE),
            'amplitude'        => env('ANALYTICS_AMPLITUDE_INTEGRATION', FALSE),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Dashboard Configuration
    |--------------------------------------------------------------------------
    */
    'default_dashboard' => [
        'layout'           => 'grid',
        'columns'          => 3,
        'theme'            => 'light',
        'auto_refresh'     => TRUE,
        'refresh_interval' => 300,
        'widgets'          => [
            'price_trends',
            'demand_patterns',
            'success_rates',
            'platform_comparison',
            'real_time_metrics',
        ],
        'filters' => [
            'time_range' => '30d',
            'platforms'  => [],
            'categories' => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */
    'queues' => [
        'analytics_processing' => env('ANALYTICS_QUEUE', 'analytics'),
        'export_generation'    => env('ANALYTICS_EXPORT_QUEUE', 'exports'),
        'ml_training'          => env('ANALYTICS_ML_QUEUE', 'ml-training'),
        'data_cleanup'         => env('ANALYTICS_CLEANUP_QUEUE', 'cleanup'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring and Logging
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'enabled'            => env('ANALYTICS_MONITORING_ENABLED', TRUE),
        'metrics_collection' => [
            'query_performance' => TRUE,
            'user_interactions' => TRUE,
            'error_tracking'    => TRUE,
            'resource_usage'    => TRUE,
        ],
        'log_channels' => [
            'analytics'   => env('ANALYTICS_LOG_CHANNEL', 'stack'),
            'performance' => env('ANALYTICS_PERFORMANCE_LOG_CHANNEL', 'stack'),
            'errors'      => env('ANALYTICS_ERROR_LOG_CHANNEL', 'stack'),
        ],
    ],
];
