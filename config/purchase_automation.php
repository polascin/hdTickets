<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Automated Purchase System Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains all the configurable settings for the intelligent
    | automated purchase system including decision algorithms, thresholds,
    | and optimization parameters.
    |
    */

    'enabled' => env('PURCHASE_AUTOMATION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Decision Algorithm Settings
    |--------------------------------------------------------------------------
    */
    'decision_algorithm' => [
        'weights' => [
            'price_score' => 0.25,
            'demand_score' => 0.20,
            'platform_score' => 0.20,
            'timing_score' => 0.15,
            'user_preference_score' => 0.10,
            'success_probability' => 0.10,
        ],
        
        'thresholds' => [
            'auto_purchase_min_score' => 80,
            'recommendation_min_score' => 50,
            'high_confidence_threshold' => 85,
            'low_confidence_threshold' => 40,
        ],
        
        'risk_factors' => [
            'max_price_variance' => 0.3,  // 30% price variance
            'min_success_probability' => 0.7,  // 70% success rate
            'max_processing_time' => 300,  // 5 minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Platform Price Comparison
    |--------------------------------------------------------------------------
    */
    'price_comparison' => [
        'cache_duration' => 300,  // 5 minutes
        'max_price_difference' => 0.5,  // 50% max difference
        'platform_reliability_weight' => 0.3,
        'value_score_calculation' => [
            'price_weight' => 0.4,
            'reliability_weight' => 0.3,
            'success_rate_weight' => 0.2,
            'processing_time_weight' => 0.1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Platform-Specific Settings
    |--------------------------------------------------------------------------
    */
    'platforms' => [
        'ticketmaster' => [
            'base_fees_percentage' => 0.15,
            'processing_timeout' => 180,
            'max_retries' => 3,
            'retry_delay' => 30,
            'reliability_multiplier' => 1.0,
        ],
        'stubhub' => [
            'base_fees_percentage' => 0.18,
            'processing_timeout' => 240,
            'max_retries' => 2,
            'retry_delay' => 45,
            'reliability_multiplier' => 0.9,
        ],
        'viagogo' => [
            'base_fees_percentage' => 0.20,
            'processing_timeout' => 300,
            'max_retries' => 2,
            'retry_delay' => 60,
            'reliability_multiplier' => 0.8,
        ],
        'seatgeek' => [
            'base_fees_percentage' => 0.16,
            'processing_timeout' => 200,
            'max_retries' => 3,
            'retry_delay' => 30,
            'reliability_multiplier' => 0.95,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Automated Checkout Flow
    |--------------------------------------------------------------------------
    */
    'checkout' => [
        'max_concurrent_purchases' => 10,
        'purchase_timeout' => 600,  // 10 minutes
        'confirmation_required' => false,
        'auto_retry_on_failure' => true,
        'max_auto_retries' => 2,
        'retry_backoff_multiplier' => 2,
        
        'validation_rules' => [
            'max_price_per_ticket' => 5000,
            'max_quantity_per_purchase' => 8,
            'min_time_before_event' => 60,  // 1 hour
        ],
        
        'user_preferences' => [
            'respect_budget_limits' => true,
            'honor_platform_preferences' => true,
            'apply_section_filters' => true,
            'check_quantity_requirements' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Success Tracking & Optimization
    |--------------------------------------------------------------------------
    */
    'tracking' => [
        'store_detailed_logs' => true,
        'track_user_behavior' => true,
        'analyze_failure_patterns' => true,
        'ml_model_updates' => true,
        
        'metrics' => [
            'success_rate_target' => 0.85,  // 85%
            'avg_processing_time_target' => 120,  // 2 minutes
            'user_satisfaction_target' => 0.9,  // 90%
            'cost_efficiency_target' => 0.8,  // 80%
        ],
        
        'optimization_frequency' => [
            'parameter_adjustment' => '1 hour',
            'ml_model_retraining' => '6 hours',
            'strategy_evaluation' => '24 hours',
            'performance_reporting' => '7 days',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Machine Learning Configuration
    |--------------------------------------------------------------------------
    */
    'machine_learning' => [
        'enabled' => env('ML_PURCHASE_OPTIMIZATION', true),
        
        'models' => [
            'price_prediction' => [
                'enabled' => true,
                'accuracy_threshold' => 0.8,
                'retrain_interval' => '24 hours',
                'features' => [
                    'historical_prices',
                    'demand_indicators',
                    'platform_performance',
                    'event_metadata',
                    'seasonal_factors',
                ],
            ],
            
            'success_probability' => [
                'enabled' => true,
                'accuracy_threshold' => 0.85,
                'retrain_interval' => '12 hours',
                'features' => [
                    'platform_history',
                    'user_preferences',
                    'ticket_characteristics',
                    'timing_factors',
                    'market_conditions',
                ],
            ],
            
            'demand_forecasting' => [
                'enabled' => true,
                'accuracy_threshold' => 0.75,
                'retrain_interval' => '6 hours',
                'features' => [
                    'historical_demand',
                    'event_popularity',
                    'social_media_sentiment',
                    'competitor_pricing',
                    'external_factors',
                ],
            ],
        ],
        
        'training' => [
            'batch_size' => 1000,
            'learning_rate' => 0.001,
            'epochs' => 100,
            'validation_split' => 0.2,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Safety & Risk Management
    |--------------------------------------------------------------------------
    */
    'safety' => [
        'circuit_breaker' => [
            'failure_threshold' => 10,  // failures before breaking
            'recovery_timeout' => 300,  // 5 minutes
            'half_open_max_calls' => 3,
        ],
        
        'rate_limiting' => [
            'max_purchases_per_user_per_hour' => 5,
            'max_purchases_per_platform_per_minute' => 20,
            'max_total_purchases_per_minute' => 50,
        ],
        
        'fraud_detection' => [
            'enabled' => true,
            'max_price_anomaly_threshold' => 2.0,  // 2 standard deviations
            'suspicious_pattern_detection' => true,
            'automated_blocking' => false,  // Require manual review
        ],
        
        'financial_limits' => [
            'max_daily_spend_per_user' => 10000,
            'max_single_purchase' => 5000,
            'require_approval_above' => 2000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'purchase_success' => [
            'enabled' => true,
            'channels' => ['email', 'slack', 'discord'],
            'immediate' => true,
        ],
        
        'purchase_failure' => [
            'enabled' => true,
            'channels' => ['email', 'slack'],
            'retry_notifications' => true,
        ],
        
        'optimization_alerts' => [
            'enabled' => true,
            'channels' => ['slack'],
            'performance_degradation' => true,
            'anomaly_detection' => true,
        ],
        
        'financial_alerts' => [
            'enabled' => true,
            'channels' => ['email', 'slack'],
            'high_value_purchases' => true,
            'budget_thresholds' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Optimization
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'caching' => [
            'decision_cache_duration' => 300,  // 5 minutes
            'platform_stats_cache_duration' => 3600,  // 1 hour
            'price_comparison_cache_duration' => 180,  // 3 minutes
        ],
        
        'database' => [
            'connection_pool_size' => 10,
            'query_timeout' => 30,
            'bulk_insert_batch_size' => 500,
        ],
        
        'queue_processing' => [
            'high_priority_workers' => 3,
            'normal_priority_workers' => 5,
            'low_priority_workers' => 2,
            'max_job_timeout' => 600,  // 10 minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Development & Testing
    |--------------------------------------------------------------------------
    */
    'development' => [
        'simulate_purchases' => env('SIMULATE_PURCHASES', false),
        'mock_platform_responses' => env('MOCK_PLATFORM_RESPONSES', false),
        'debug_logging' => env('DEBUG_PURCHASE_AUTOMATION', false),
        'test_mode_override' => env('PURCHASE_TEST_MODE', false),
        
        'simulation_settings' => [
            'success_rate' => 0.75,
            'avg_processing_time' => 120,
            'price_variance' => 0.1,
        ],
    ],
];
