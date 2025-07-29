<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Advanced Analytics Dashboard Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the HDTickets Advanced Analytics Dashboard
    | including caching, data retention, and performance optimization.
    |
    */

    'enabled' => env('ANALYTICS_DASHBOARD_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'ttl' => env('ANALYTICS_CACHE_TTL', 3600), // 1 hour
        'prefix' => 'analytics',
        'store' => env('ANALYTICS_CACHE_STORE', 'redis'),
        'tags' => ['analytics', 'dashboard'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Retention
    |--------------------------------------------------------------------------
    */
    'data_retention' => [
        'price_history_days' => env('ANALYTICS_PRICE_HISTORY_DAYS', 365),
        'demand_data_days' => env('ANALYTICS_DEMAND_DATA_DAYS', 180),
        'user_activity_days' => env('ANALYTICS_USER_ACTIVITY_DAYS', 90),
        'performance_metrics_days' => env('ANALYTICS_PERFORMANCE_METRICS_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Widgets
    |--------------------------------------------------------------------------
    */
    'widgets' => [
        'price_trends' => [
            'enabled' => true,
            'refresh_interval' => 300, // 5 minutes
            'max_data_points' => 1000,
            'chart_types' => ['line', 'area', 'candlestick'],
        ],
        'demand_patterns' => [
            'enabled' => true,
            'refresh_interval' => 600, // 10 minutes
            'ml_predictions' => env('ANALYTICS_ML_ENABLED', true),
            'chart_types' => ['heatmap', 'bar', 'scatter'],
        ],
        'success_rates' => [
            'enabled' => true,
            'refresh_interval' => 300,
            'optimization_suggestions' => true,
            'chart_types' => ['gauge', 'bar', 'trend'],
        ],
        'platform_comparison' => [
            'enabled' => true,
            'refresh_interval' => 900, // 15 minutes
            'max_platforms' => 10,
            'chart_types' => ['radar', 'bar', 'table'],
        ],
        'real_time_metrics' => [
            'enabled' => true,
            'refresh_interval' => 30, // 30 seconds
            'chart_types' => ['gauge', 'sparkline', 'number'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Optimization
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'pagination_size' => env('ANALYTICS_PAGINATION_SIZE', 100),
        'max_export_records' => env('ANALYTICS_MAX_EXPORT_RECORDS', 50000),
        'query_timeout' => env('ANALYTICS_QUERY_TIMEOUT', 30),
        'background_processing' => env('ANALYTICS_BACKGROUND_PROCESSING', true),
        'compression' => env('ANALYTICS_COMPRESSION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Configuration
    |--------------------------------------------------------------------------
    */
    'export' => [
        'formats' => ['json', 'csv', 'xlsx', 'pdf'],
        'max_file_size' => env('ANALYTICS_MAX_FILE_SIZE', '50MB'),
        'temp_storage_days' => env('ANALYTICS_TEMP_STORAGE_DAYS', 7),
        'compression' => true,
        'password_protection' => env('ANALYTICS_EXPORT_PASSWORD_PROTECTION', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Machine Learning Configuration
    |--------------------------------------------------------------------------
    */
    'ml' => [
        'enabled' => env('ANALYTICS_ML_ENABLED', true),
        'prediction_confidence_threshold' => 0.7,
        'model_retraining_interval' => env('ML_RETRAINING_INTERVAL', 'weekly'),
        'feature_selection' => [
            'price_history_weight' => 0.3,
            'demand_patterns_weight' => 0.25,
            'user_behavior_weight' => 0.2,
            'platform_reliability_weight' => 0.15,
            'seasonal_factors_weight' => 0.1,
        ],
        'algorithms' => [
            'price_prediction' => 'linear_regression',
            'demand_forecasting' => 'random_forest',
            'availability_prediction' => 'neural_network',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Thresholds
    |--------------------------------------------------------------------------
    */
    'alerts' => [
        'price_volatility_threshold' => 20, // percentage
        'demand_spike_threshold' => 150, // percentage increase
        'system_performance_threshold' => [
            'response_time' => 2000, // milliseconds
            'error_rate' => 5, // percentage
            'memory_usage' => 80, // percentage
        ],
        'data_quality_threshold' => 90, // percentage
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'security' => [
        'rate_limiting' => [
            'requests_per_minute' => 60,
            'burst_limit' => 100,
        ],
        'data_masking' => env('ANALYTICS_DATA_MASKING', false),
        'audit_logging' => env('ANALYTICS_AUDIT_LOGGING', true),
        'ip_whitelisting' => env('ANALYTICS_IP_WHITELISTING', false),
        'encryption_at_rest' => env('ANALYTICS_ENCRYPTION_AT_REST', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Visualization Settings
    |--------------------------------------------------------------------------
    */
    'visualization' => [
        'color_schemes' => [
            'default' => ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd'],
            'colorblind_friendly' => ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd'],
            'high_contrast' => ['#000000', '#ffffff', '#ff0000', '#00ff00', '#0000ff'],
        ],
        'chart_libraries' => [
            'primary' => 'chart.js',
            'fallback' => 'd3.js',
        ],
        'responsive_breakpoints' => [
            'mobile' => 768,
            'tablet' => 1024,
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
            'enabled' => env('ANALYTICS_EXTERNAL_APIS', false),
            'rate_limits' => [
                'requests_per_hour' => 1000,
                'burst_limit' => 50,
            ],
        ],
        'webhooks' => [
            'enabled' => env('ANALYTICS_WEBHOOKS_ENABLED', false),
            'retry_attempts' => 3,
            'timeout' => 30,
        ],
        'third_party_tools' => [
            'google_analytics' => env('ANALYTICS_GA_INTEGRATION', false),
            'mixpanel' => env('ANALYTICS_MIXPANEL_INTEGRATION', false),
            'amplitude' => env('ANALYTICS_AMPLITUDE_INTEGRATION', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Dashboard Configuration
    |--------------------------------------------------------------------------
    */
    'default_dashboard' => [
        'layout' => 'grid',
        'columns' => 3,
        'theme' => 'light',
        'auto_refresh' => true,
        'refresh_interval' => 300,
        'widgets' => [
            'price_trends',
            'demand_patterns',
            'success_rates',
            'platform_comparison',
            'real_time_metrics',
        ],
        'filters' => [
            'time_range' => '30d',
            'platforms' => [],
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
        'export_generation' => env('ANALYTICS_EXPORT_QUEUE', 'exports'),
        'ml_training' => env('ANALYTICS_ML_QUEUE', 'ml-training'),
        'data_cleanup' => env('ANALYTICS_CLEANUP_QUEUE', 'cleanup'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring and Logging
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'enabled' => env('ANALYTICS_MONITORING_ENABLED', true),
        'metrics_collection' => [
            'query_performance' => true,
            'user_interactions' => true,
            'error_tracking' => true,
            'resource_usage' => true,
        ],
        'log_channels' => [
            'analytics' => env('ANALYTICS_LOG_CHANNEL', 'stack'),
            'performance' => env('ANALYTICS_PERFORMANCE_LOG_CHANNEL', 'stack'),
            'errors' => env('ANALYTICS_ERROR_LOG_CHANNEL', 'stack'),
        ],
    ],
];
