<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enhanced Alert System Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the enhanced alert system
    | including ML predictions, escalation rules, and notification channels.
    |
    */

    'enhanced_alerts' => [
        'enabled' => env('ENHANCED_ALERTS_ENABLED', true),
        'ml_predictions' => env('ML_PREDICTIONS_ENABLED', true),
        'escalation' => env('ALERT_ESCALATION_ENABLED', true),
        'debug_mode' => env('ALERT_DEBUG_MODE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Machine Learning Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the ML-based ticket prediction system
    |
    */

    'ml' => [
        'model_version' => env('ML_MODEL_VERSION', '1.0'),
        'confidence_threshold' => env('ML_CONFIDENCE_THRESHOLD', 0.7),
        'cache_predictions' => env('ML_CACHE_PREDICTIONS', true),
        'cache_ttl' => env('ML_CACHE_TTL', 300), // 5 minutes
        'fallback_enabled' => env('ML_FALLBACK_ENABLED', true),
        'features' => [
            'price_analysis' => true,
            'demand_prediction' => true,
            'availability_forecasting' => true,
            'seasonal_adjustments' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Prioritization
    |--------------------------------------------------------------------------
    |
    | Configuration for smart alert prioritization
    |
    */

    'prioritization' => [
        'factors' => [
            'price_weight' => 0.25,
            'time_weight' => 0.20,
            'availability_weight' => 0.20,
            'user_preference_weight' => 0.15,
            'demand_weight' => 0.10,
            'platform_reliability_weight' => 0.10,
        ],
        'thresholds' => [
            'critical' => 0.8,
            'high' => 0.6,
            'medium' => 0.4,
            'normal' => 0.2,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Escalation Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for alert escalation and retry mechanisms
    |
    */

    'escalation' => [
        'enabled' => env('ESCALATION_ENABLED', true),
        'max_escalations_per_hour' => env('MAX_ESCALATIONS_PER_HOUR', 10),
        'user_activity_timeout' => env('USER_ACTIVITY_TIMEOUT', 15), // minutes
        'strategies' => [
            'critical' => [
                'initial_delay' => 2, // minutes
                'max_attempts' => 5,
                'retry_base_delay' => 3,
                'retry_max_delay' => 15,
                'retry_multiplier' => 1.5,
            ],
            'high' => [
                'initial_delay' => 5,
                'max_attempts' => 3,
                'retry_base_delay' => 5,
                'retry_max_delay' => 30,
                'retry_multiplier' => 2,
            ],
        ],
        'channels' => [
            'critical' => ['sms', 'phone', 'slack_urgent', 'discord_ping'],
            'high' => ['sms', 'slack', 'discord'],
            'medium' => ['push', 'slack'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    |
    | Configuration for external notification channels
    |
    */

    'channels' => [
        'slack' => [
            'enabled' => env('SLACK_NOTIFICATIONS_ENABLED', false),
            'webhook_url' => env('SLACK_WEBHOOK_URL'),
            'bot_token' => env('SLACK_BOT_TOKEN'),
            'default_channel' => env('SLACK_DEFAULT_CHANNEL', '#ticket-alerts'),
            'timeout' => env('SLACK_TIMEOUT', 10),
            'retry_attempts' => env('SLACK_RETRY_ATTEMPTS', 3),
        ],

        'discord' => [
            'enabled' => env('DISCORD_NOTIFICATIONS_ENABLED', false),
            'webhook_url' => env('DISCORD_WEBHOOK_URL'),
            'bot_token' => env('DISCORD_BOT_TOKEN'),
            'application_id' => env('DISCORD_APPLICATION_ID'),
            'timeout' => env('DISCORD_TIMEOUT', 10),
            'retry_attempts' => env('DISCORD_RETRY_ATTEMPTS', 3),
        ],

        'telegram' => [
            'enabled' => env('TELEGRAM_NOTIFICATIONS_ENABLED', false),
            'bot_token' => env('TELEGRAM_BOT_TOKEN'),
            'default_chat_id' => env('TELEGRAM_DEFAULT_CHAT_ID'),
            'timeout' => env('TELEGRAM_TIMEOUT', 10),
            'retry_attempts' => env('TELEGRAM_RETRY_ATTEMPTS', 3),
        ],

        'webhook' => [
            'enabled' => env('WEBHOOK_NOTIFICATIONS_ENABLED', false),
            'default_url' => env('WEBHOOK_DEFAULT_URL'),
            'timeout' => env('WEBHOOK_TIMEOUT', 10),
            'max_retries' => env('WEBHOOK_MAX_RETRIES', 3),
            'retry_delay' => env('WEBHOOK_RETRY_DELAY', 1),
            'verify_ssl' => env('WEBHOOK_VERIFY_SSL', true),
            'signature_header' => env('WEBHOOK_SIGNATURE_HEADER', 'X-HDTickets-Signature'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Queue settings for different priority levels
    |
    */

    'queues' => [
        'alerts' => [
            'critical' => env('QUEUE_ALERTS_CRITICAL', 'alerts-critical'),
            'high' => env('QUEUE_ALERTS_HIGH', 'alerts-high'),
            'medium' => env('QUEUE_ALERTS_MEDIUM', 'alerts-medium'),
            'default' => env('QUEUE_ALERTS_DEFAULT', 'alerts-default'),
        ],
        'notifications' => [
            'critical' => env('QUEUE_NOTIFICATIONS_CRITICAL', 'notifications-critical'),
            'high' => env('QUEUE_NOTIFICATIONS_HIGH', 'notifications-high'),
            'medium' => env('QUEUE_NOTIFICATIONS_MEDIUM', 'notifications-medium'),
            'default' => env('QUEUE_NOTIFICATIONS_DEFAULT', 'notifications-default'),
        ],
        'escalations' => env('QUEUE_ESCALATIONS', 'escalations'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting configuration for notifications
    |
    */

    'rate_limiting' => [
        'enabled' => env('NOTIFICATION_RATE_LIMITING_ENABLED', true),
        'per_user' => [
            'critical' => '10,60', // 10 per minute
            'high' => '20,60',     // 20 per minute
            'medium' => '30,60',   // 30 per minute
            'normal' => '60,60',   // 60 per minute
        ],
        'per_channel' => [
            'slack' => '100,60',
            'discord' => '100,60',
            'telegram' => '50,60',
            'webhook' => '200,60',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics and Monitoring
    |--------------------------------------------------------------------------
    |
    | Configuration for alert analytics and monitoring
    |
    */

    'analytics' => [
        'enabled' => env('ALERT_ANALYTICS_ENABLED', true),
        'track_user_engagement' => env('TRACK_USER_ENGAGEMENT', true),
        'track_channel_performance' => env('TRACK_CHANNEL_PERFORMANCE', true),
        'track_ml_accuracy' => env('TRACK_ML_ACCURACY', true),
        'retention_days' => env('ANALYTICS_RETENTION_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    |
    | Security settings for notifications
    |
    */

    'security' => [
        'encrypt_sensitive_data' => env('ENCRYPT_NOTIFICATION_DATA', true),
        'webhook_ip_whitelist' => env('WEBHOOK_IP_WHITELIST', ''),
        'require_signature_verification' => env('REQUIRE_WEBHOOK_SIGNATURE', false),
        'max_payload_size' => env('MAX_NOTIFICATION_PAYLOAD_SIZE', 1048576), // 1MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance
    |--------------------------------------------------------------------------
    |
    | Performance optimization settings
    |
    */

    'performance' => [
        'cache_user_preferences' => env('CACHE_USER_PREFERENCES', true),
        'cache_ttl' => env('NOTIFICATION_CACHE_TTL', 3600),
        'batch_notifications' => env('BATCH_NOTIFICATIONS', true),
        'batch_size' => env('NOTIFICATION_BATCH_SIZE', 50),
        'async_processing' => env('ASYNC_NOTIFICATION_PROCESSING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Configuration
    |--------------------------------------------------------------------------
    |
    | Fallback settings when primary channels fail
    |
    */

    'fallbacks' => [
        'enabled' => env('NOTIFICATION_FALLBACKS_ENABLED', true),
        'fallback_chain' => [
            'slack' => ['discord', 'webhook'],
            'discord' => ['slack', 'telegram'],
            'telegram' => ['slack', 'webhook'],
            'webhook' => ['mail'],
        ],
        'fallback_delay' => env('FALLBACK_DELAY_SECONDS', 30),
    ],

];
