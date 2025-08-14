<?php declare(strict_types=1);

/**
 * HD Tickets API Configuration
 *
 * @version 2025.07.v4.0
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Ticketmaster API Configuration
    |--------------------------------------------------------------------------
    | Ticketmaster Discovery API provides access to event data
    | Sign up at: https://developer.ticketmaster.com/
    */
    'ticketmaster' => [
        'enabled'    => env('TICKETMASTER_ENABLED', FALSE),
        'api_key'    => env('TICKETMASTER_API_KEY'),
        'base_url'   => 'https://app.ticketmaster.com/discovery/v2',
        'timeout'    => 30,
        'rate_limit' => [
            'requests_per_second' => 5,
            'requests_per_day'    => 5000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SeatGeek API Configuration
    |--------------------------------------------------------------------------
    | SeatGeek Open Platform provides event and venue data
    | Sign up at: https://platform.seatgeek.com/
    */
    'seatgeek' => [
        'enabled'       => env('SEATGEEK_ENABLED', FALSE),
        'client_id'     => env('SEATGEEK_CLIENT_ID'),
        'client_secret' => env('SEATGEEK_CLIENT_SECRET'),
        'base_url'      => 'https://api.seatgeek.com/2',
        'timeout'       => 30,
        'rate_limit'    => [
            'requests_per_second' => 10,
            'requests_per_hour'   => 1000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Eventbrite API Configuration
    |--------------------------------------------------------------------------
    | Eventbrite API provides access to event data
    | Sign up at: https://www.eventbrite.com/platform/
    */
    'eventbrite' => [
        'enabled'    => env('EVENTBRITE_ENABLED', FALSE),
        'api_key'    => env('EVENTBRITE_API_KEY'),
        'base_url'   => 'https://www.eventbriteapi.com/v3',
        'timeout'    => 30,
        'rate_limit' => [
            'requests_per_second' => 1000,
            'requests_per_hour'   => 50000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | StubHub API Configuration (Partner Program Required)
    |--------------------------------------------------------------------------
    | StubHub Partner API provides resale ticket data
    | Apply at: https://stubhub.com/partners
    */
    'stubhub' => [
        'enabled'         => env('STUBHUB_ENABLED', FALSE),
        'api_key'         => env('STUBHUB_API_KEY'),
        'app_token'       => env('STUBHUB_APP_TOKEN'),
        'consumer_key'    => env('STUBHUB_CONSUMER_KEY'),
        'consumer_secret' => env('STUBHUB_CONSUMER_SECRET'),
        'base_url'        => 'https://api.stubhub.com',
        'sandbox_url'     => 'https://api.stubhubsandbox.com',
        'timeout'         => 30,
        'sandbox'         => env('STUBHUB_SANDBOX', TRUE),
        'rate_limit'      => [
            'requests_per_second'    => 10,
            'requests_per_hour'      => 5000,
            'requests_per_day'       => 50000,
            'delay_between_requests' => 0.1, // seconds
        ],
        'scraping' => [
            'enabled'     => env('STUBHUB_SCRAPING_ENABLED', FALSE),
            'user_agents' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            ],
            'delay_range' => [2, 5], // Random delay between requests
            'timeout'     => 45,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Bandsintown API Configuration
    |--------------------------------------------------------------------------
    | Bandsintown API provides music event data
    | Register at: https://manager.bandsintown.com/support/api
    */
    'bandsintown' => [
        'enabled'    => env('BANDSINTOWN_ENABLED', FALSE),
        'app_id'     => env('BANDSINTOWN_APP_ID'),
        'base_url'   => 'https://rest.bandsintown.com',
        'timeout'    => 30,
        'rate_limit' => [
            'requests_per_second' => 1,
            'requests_per_day'    => 2000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Viagogo Configuration (Web Scraping)
    |--------------------------------------------------------------------------
    | Viagogo ticket marketplace - web scraping implementation
    | Global platform with multi-currency support
    */
    'viagogo' => [
        'enabled'    => env('VIAGOGO_ENABLED', FALSE),
        'base_url'   => 'https://www.viagogo.com',
        'timeout'    => 30,
        'rate_limit' => [
            'requests_per_second'    => 2, // Be respectful with scraping
            'requests_per_hour'      => 500,
            'requests_per_day'       => 5000,
            'delay_between_requests' => 0.5, // seconds
        ],
        'scraping' => [
            'user_agents' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ],
            'delay_range'    => [3, 8], // Random delay between requests (seconds)
            'timeout'        => 45,
            'max_retries'    => 3,
            'backoff_factor' => 2, // Exponential backoff
            'proxy_rotation' => env('VIAGOGO_PROXY_ROTATION', FALSE),
            'headers'        => [
                'Accept-Language'           => 'en-US,en;q=0.9',
                'Accept-Encoding'           => 'gzip, deflate, br',
                'DNT'                       => '1',
                'Upgrade-Insecure-Requests' => '1',
            ],
        ],
        'currencies' => ['USD', 'EUR', 'GBP', 'CAD', 'AUD'], // Supported currencies
        'regions'    => ['us', 'uk', 'ca', 'au', 'de', 'fr'], // Supported regions
    ],

    /*
    |--------------------------------------------------------------------------
    | TickPick Configuration (Web Scraping)
    |--------------------------------------------------------------------------
    | TickPick no-fee ticket marketplace - web scraping implementation
    | Specializes in transparent, no-fee pricing
    */
    'tickpick' => [
        'enabled'    => env('TICKPICK_ENABLED', FALSE),
        'api_key'    => env('TICKPICK_API_KEY'), // If API becomes available
        'base_url'   => 'https://www.tickpick.com',
        'api_url'    => 'https://api.tickpick.com', // Potential future API endpoint
        'timeout'    => 30,
        'rate_limit' => [
            'requests_per_second'    => 3,
            'requests_per_hour'      => 1000,
            'requests_per_day'       => 10000,
            'delay_between_requests' => 0.33, // seconds
        ],
        'scraping' => [
            'user_agents' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ],
            'delay_range'         => [2, 6], // Random delay between requests (seconds)
            'timeout'             => 40,
            'max_retries'         => 2,
            'javascript_required' => TRUE, // TickPick heavily uses JS
            'headers'             => [
                'Accept'          => 'application/json, text/plain, */*',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Referer'         => 'https://www.tickpick.com/',
            ],
        ],
        'features' => [
            'no_fees'             => TRUE, // TickPick's main selling point
            'best_deal_guarantee' => TRUE,
            'instant_download'    => TRUE,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Manchester United Official App Configuration (Web Scraping)
    |--------------------------------------------------------------------------
    | Manchester United official website and mobile app - web scraping implementation
    | Covers Manchester United home matches and official ticket sales
    */
    'manchester_united' => [
        'enabled'        => env('MANCHESTER_UNITED_ENABLED', FALSE),
        'base_url'       => 'https://www.manutd.com',
        'mobile_app_url' => 'https://www.manutd.com/en/tickets',
        'timeout'        => 30,
        'rate_limit'     => [
            'requests_per_second'    => 2, // Be respectful with official club website
            'requests_per_hour'      => 200,
            'requests_per_day'       => 2000,
            'delay_between_requests' => 0.5, // seconds
        ],
        'scraping' => [
            'user_agents' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1', // Mobile user agent
            ],
            'delay_range' => [2, 6], // Random delay between requests (seconds)
            'timeout'     => 35,
            'max_retries' => 2,
            'headers'     => [
                'Accept-Language'           => 'en-GB,en;q=0.9,en-US;q=0.8', // British English preference
                'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'DNT'                       => '1',
                'Connection'                => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
            ],
        ],
        'localization' => [
            'default_language'    => 'en-GB', // British English
            'supported_languages' => ['en-GB', 'en-US'], // English variants
            'currency'            => 'GBP',
            'timezone'            => 'Europe/London',
        ],
        'venue_info' => [
            'primary_venue' => 'Old Trafford',
            'capacity'      => 74879,
            'city'          => 'Manchester',
            'country'       => 'United Kingdom',
            'address'       => 'Sir Matt Busby Way, Old Trafford, Manchester M16 0RA, UK',
        ],
        'event_types' => [
            'premier_league'   => TRUE,
            'champions_league' => TRUE,
            'europa_league'    => TRUE,
            'fa_cup'           => TRUE,
            'carabao_cup'      => TRUE,
            'friendly_matches' => TRUE,
            'women_matches'    => FALSE, // Separate ticketing system
            'youth_matches'    => FALSE, // Usually different pricing/availability
        ],
        'ticket_categories' => [
            'season_tickets'  => FALSE, // Not available for general purchase
            'members_tickets' => TRUE,
            'general_sale'    => TRUE,
            'hospitality'     => TRUE,
            'away_tickets'    => FALSE, // Not sold through MUFC website
        ],
        'mobile_app' => [
            'enabled'                => TRUE,
            'app_specific_endpoints' => [
                'fixtures'    => '/api/fixtures',
                'tickets'     => '/api/tickets',
                'memberships' => '/api/memberships',
            ],
            'requires_authentication'     => TRUE,
            'supports_push_notifications' => TRUE,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */
    'default_timeout' => 30,
    'cache_ttl'       => 3600, // 1 hour
    'retry_attempts'  => 3,
    'retry_delay'     => 1, // seconds

    /*
    |--------------------------------------------------------------------------
    | User Rotation Settings
    |--------------------------------------------------------------------------
    | Configuration for rotating users across scraping operations
    | to distribute load and avoid detection
    */
    'user_rotation' => [
        'enabled'                => env('USER_ROTATION_ENABLED', TRUE),
        'pool_size'              => env('USER_ROTATION_POOL_SIZE', 500),
        'cache_ttl'              => env('USER_ROTATION_CACHE_TTL', 3600),
        'refresh_interval'       => env('USER_ROTATION_REFRESH_INTERVAL', 1800),
        'activity_tracking'      => env('USER_ROTATION_ACTIVITY_TRACKING', TRUE),
        'activity_history_limit' => env('USER_ROTATION_ACTIVITY_HISTORY_LIMIT', 10),
        'platform_specific'      => [
            'stubhub' => [
                'priority_users'   => ['agent', 'premium'],
                'exclude_patterns' => [],
            ],
            'ticketmaster' => [
                'priority_users'   => ['agent', 'premium'],
                'exclude_patterns' => [],
            ],
            'viagogo' => [
                'priority_users'   => ['agent', 'premium'],
                'exclude_patterns' => [],
            ],
            'seatgeek' => [
                'priority_users'   => ['agent', 'premium'],
                'exclude_patterns' => [],
            ],
            'tickpick' => [
                'priority_users'   => ['customer', 'premium'],
                'exclude_patterns' => [],
            ],
            'manchester_united' => [
                'priority_users'   => ['customer', 'premium', 'agent'],
                'exclude_patterns' => [],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Enhanced Scraping Configuration
    |--------------------------------------------------------------------------
    | Global scraping settings and anti-detection measures
    */
    'scraping' => [
        'enabled'             => env('SCRAPING_ENABLED', TRUE),
        'user_agent_rotation' => env('SCRAPING_USER_AGENT_ROTATION', TRUE),
        'default_timeout'     => env('SCRAPING_DEFAULT_TIMEOUT', 30),
        'default_delay'       => env('SCRAPING_DEFAULT_DELAY', 2),
        'max_retries'         => env('SCRAPING_MAX_RETRIES', 3),
        'cache_enabled'       => env('SCRAPING_CACHE_ENABLED', TRUE),
        'cache_ttl'           => env('SCRAPING_CACHE_TTL', 300),
        'log_level'           => env('SCRAPING_LOG_LEVEL', 'info'),
        'log_channel'         => env('SCRAPING_LOG_CHANNEL', 'ticket_apis'),
        'proxy'               => [
            'enabled'  => env('SCRAPING_PROXY_ENABLED', FALSE),
            'host'     => env('SCRAPING_PROXY_HOST'),
            'port'     => env('SCRAPING_PROXY_PORT'),
            'username' => env('SCRAPING_PROXY_USERNAME'),
            'password' => env('SCRAPING_PROXY_PASSWORD'),
            'rotation' => env('SCRAPING_PROXY_ROTATION', FALSE),
        ],
        'rate_limit' => [
            'enabled'           => env('SCRAPING_RATE_LIMIT_ENABLED', TRUE),
            'global_rate_limit' => env('SCRAPING_GLOBAL_RATE_LIMIT', 100),
        ],
        'anti_detection' => [
            'random_delays'        => TRUE,
            'user_agent_rotation'  => TRUE,
            'session_management'   => TRUE,
            'referrer_spoofing'    => TRUE,
            'cookie_persistence'   => TRUE,
            'javascript_execution' => env('SCRAPING_JAVASCRIPT_ENABLED', FALSE),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Platform Integration Management
    |--------------------------------------------------------------------------
    | Settings for managing multiple platform integrations
    */
    'platform_integration' => [
        'enabled_platforms' => [
            'stubhub'           => env('STUBHUB_ENABLED', FALSE),
            'ticketmaster'      => env('TICKETMASTER_ENABLED', FALSE),
            'viagogo'           => env('VIAGOGO_ENABLED', FALSE),
            'seatgeek'          => env('SEATGEEK_ENABLED', FALSE),
            'tickpick'          => env('TICKPICK_ENABLED', FALSE),
            'manchester_united' => env('MANCHESTER_UNITED_ENABLED', FALSE),
        ],
        'priority_order' => [
            'high_priority'   => ['ticketmaster', 'stubhub'],
            'medium_priority' => ['seatgeek', 'viagogo', 'manchester_united'],
            'low_priority'    => ['tickpick'],
        ],
        'fallback_enabled'    => env('PLATFORM_FALLBACK_ENABLED', TRUE),
        'parallel_processing' => env('PLATFORM_PARALLEL_PROCESSING', TRUE),
        'load_balancing'      => [
            'enabled'   => env('PLATFORM_LOAD_BALANCING', TRUE),
            'algorithm' => env('PLATFORM_LOAD_ALGORITHM', 'round_robin'), // round_robin, weighted, least_connections
            'weights'   => [
                'stubhub'           => 30,
                'ticketmaster'      => 25,
                'viagogo'           => 20,
                'seatgeek'          => 15,
                'manchester_united' => 12,
                'tickpick'          => 10,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Quality & Validation
    |--------------------------------------------------------------------------
    | Settings for ensuring data quality across platforms
    */
    'data_quality' => [
        'validation_enabled' => env('DATA_VALIDATION_ENABLED', TRUE),
        'price_validation'   => [
            'enabled'             => TRUE,
            'min_price'           => 1.00,
            'max_price'           => 50000.00,
            'currency_validation' => TRUE,
        ],
        'event_validation' => [
            'enabled'            => TRUE,
            'required_fields'    => ['name', 'date', 'venue'],
            'date_validation'    => TRUE,
            'future_events_only' => TRUE,
        ],
        'duplicate_detection' => [
            'enabled'              => TRUE,
            'similarity_threshold' => 0.85,
            'matching_fields'      => ['name', 'date', 'venue'],
        ],
        'normalization' => [
            'enabled'              => TRUE,
            'venue_mapping'        => TRUE,
            'event_categorization' => TRUE,
            'date_standardization' => TRUE,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring & Performance
    |--------------------------------------------------------------------------
    | Monitoring and performance tracking configuration
    */
    'monitoring' => [
        'enabled'            => env('TICKET_API_MONITORING_ENABLED', TRUE),
        'metrics_collection' => [
            'response_times'      => TRUE,
            'success_rates'       => TRUE,
            'error_rates'         => TRUE,
            'cache_hit_rates'     => TRUE,
            'user_rotation_stats' => TRUE,
        ],
        'alerts' => [
            'enabled'    => env('MONITORING_ALERTS_ENABLED', TRUE),
            'channels'   => ['log', 'email'], // log, email, slack, webhook
            'thresholds' => [
                'error_rate'    => 0.1, // 10%
                'response_time' => 10000, // 10 seconds
                'success_rate'  => 0.8, // 80%
            ],
        ],
        'performance_tracking' => [
            'enabled'                           => TRUE,
            'track_scraping_efficiency'         => TRUE,
            'track_platform_reliability'        => TRUE,
            'track_user_rotation_effectiveness' => TRUE,
        ],
    ],
];
