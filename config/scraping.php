<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled Scraper Plugins
    |--------------------------------------------------------------------------
    |
    | List of scraper plugins that should be automatically loaded and enabled.
    | Plugin names should match the class names without the "Plugin" suffix.
    |
    */

    'enabled_plugins' => [
        'ticketmaster',
        'stubhub', 
        'seatgeek',
        'viagogo',
        'tickpick',
        'eventbrite',
        'bandsintown',
        'axs',
        'manchester_united',
        // UK Sports Platforms - Tier 1 & 2
        'wimbledon',
        'liverpoolfc',
        'wembleystadium',
        'ticketekuk',
        'arsenalfc',
        'twickenham',
        'lordscricket',
        // UK Sports Platforms - Tier 3
        'seeticketsuk',
        'chelseafc',
        'tottenham',
        'englandcricket',
        'silverstonef1',
        'celticfc',
        // European Football Clubs
        'manchester_city',
        'real_madrid',
        'barcelona',
        'atletico_madrid', 
        'bayern_munich',
        'borussia_dortmund',
        'juventus',
        'ac_milan',
        'inter_milan',
        'psg',
        'newcastle_united',
        // European Ticketing Platforms
        'eventim',
        'fnac_spectacles',
        'vivaticket',
        'entradas',
        // Add more plugins as needed
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugin-Specific Configurations
    |--------------------------------------------------------------------------
    |
    | Configuration settings for individual scraper plugins.
    |
    */

    'plugins' => [
        
        'seatgeek' => [
            'base_url' => env('SEATGEEK_BASE_URL', 'https://seatgeek.com'),
            'rate_limit_seconds' => 2,
            'timeout' => 30,
            'max_retries' => 3,
            'user_agent' => env('SCRAPER_USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'),
        ],

        'stubhub' => [
            'base_url' => env('STUBHUB_BASE_URL', 'https://www.stubhub.com'),
            'rate_limit_seconds' => 2,
            'timeout' => 30,
            'max_retries' => 3,
        ],

        'viagogo' => [
            'base_url' => env('VIAGOGO_BASE_URL', 'https://www.viagogo.com'),
            'rate_limit_seconds' => 3,
            'timeout' => 30,
            'max_retries' => 2,
        ],

        'tickpick' => [
            'base_url' => env('TICKPICK_BASE_URL', 'https://www.tickpick.com'),
            'rate_limit_seconds' => 2,
            'timeout' => 25,
            'max_retries' => 3,
        ],

        'manchester_united' => [
            'base_url' => env('MANUTD_BASE_URL', 'https://www.manutd.com'),
            'rate_limit_seconds' => 3,
            'timeout' => 30,
            'max_retries' => 2,
            'fixtures_endpoint' => '/fixtures',
            'tickets_endpoint' => '/tickets-and-hospitality',
        ],

        'ticketmaster' => [
            'api_key' => env('TICKETMASTER_API_KEY'),
            'base_url' => env('TICKETMASTER_BASE_URL', 'https://app.ticketmaster.com/discovery/v2/'),
            'rate_limit_seconds' => 1,
            'timeout' => 30,
            'max_retries' => 3,
        ],

        'eventbrite' => [
            'base_url' => env('EVENTBRITE_BASE_URL', 'https://www.eventbrite.com'),
            'rate_limit_seconds' => 2,
            'timeout' => 30,
            'max_retries' => 3,
        ],

        'bandsintown' => [
            'base_url' => env('BANDSINTOWN_BASE_URL', 'https://www.bandsintown.com'),
            'rate_limit_seconds' => 2,
            'timeout' => 30,
            'max_retries' => 3,
        ],

        'axs' => [
            'base_url' => env('AXS_BASE_URL', 'https://www.axs.com'),
            'rate_limit_seconds' => 2,
            'timeout' => 30,
            'max_retries' => 3,
            'search_endpoint' => '/api/events/search',
            'event_details_endpoint' => '/api/events/',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global Scraper Settings
    |--------------------------------------------------------------------------
    |
    | Global configuration that applies to all scrapers.
    |
    */

    'global' => [
        
        // Default timeout for HTTP requests (in seconds)
        'default_timeout' => 30,
        
        // Default rate limiting (seconds between requests)
        'default_rate_limit' => 2,
        
        // Maximum number of results per scraping session
        'max_results_per_plugin' => 100,
        
        // Enable/disable proxy rotation
        'use_proxy_rotation' => env('SCRAPER_USE_PROXIES', false),
        
        // Enable/disable caching of scraper results
        'cache_results' => env('SCRAPER_CACHE_RESULTS', true),
        
        // Cache TTL in minutes
        'cache_ttl_minutes' => env('SCRAPER_CACHE_TTL', 60),
        
        // Maximum concurrent scrapers
        'max_concurrent_scrapers' => 3,
        
        // Enable detailed logging
        'debug_logging' => env('SCRAPER_DEBUG', false),
        
        // User agents pool for rotation
        'user_agents' => [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring & Health Check Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for monitoring scraper health and performance.
    |
    */

    'monitoring' => [
        
        // Enable health monitoring
        'enabled' => true,
        
        // Minimum success rate threshold for "healthy" status
        'healthy_success_rate_threshold' => 70,
        
        // Maximum allowed errors per hour before marking as unhealthy
        'max_errors_per_hour' => 10,
        
        // Enable performance metrics collection
        'collect_performance_metrics' => true,
        
        // Enable error reporting
        'error_reporting' => [
            'enabled' => env('SCRAPER_ERROR_REPORTING', true),
            'channels' => ['log', 'database'], // Available: log, database, email, slack
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Anti-Detection Measures
    |--------------------------------------------------------------------------
    |
    | Settings to help avoid detection by target websites.
    |
    */

    'anti_detection' => [
        
        // Randomize delays between requests
        'randomize_delays' => true,
        
        // Random delay range (in seconds)
        'delay_range' => [1, 3],
        
        // Rotate user agents
        'rotate_user_agents' => true,
        
        // Add random headers
        'random_headers' => true,
        
        // Simulate human-like behavior
        'human_simulation' => [
            'enabled' => true,
            'mouse_movements' => false, // Only applicable for browser-based scraping
            'random_clicks' => false,   // Only applicable for browser-based scraping
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Processing Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for processing and formatting scraped data.
    |
    */

    'data_processing' => [
        
        // Enable data validation
        'validate_data' => true,
        
        // Enable data normalization
        'normalize_data' => true,
        
        // Enable duplicate detection
        'detect_duplicates' => true,
        
        // Required fields for valid ticket data
        'required_fields' => [
            'event_name',
            'platform',
            'scraped_at',
        ],
        
        // Optional fields that enhance data quality
        'preferred_fields' => [
            'venue',
            'date',
            'price_min',
            'price_max', 
            'url',
            'availability_status',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for storing scraped data.
    |
    */

    'storage' => [
        
        // Enable automatic storage of scraped data
        'auto_store' => env('SCRAPER_AUTO_STORE', true),
        
        // Storage backend (database, file, s3, etc.)
        'backend' => env('SCRAPER_STORAGE_BACKEND', 'database'),
        
        // Database table for storing scraped data
        'table' => 'ticket_sources',
        
        // Enable data archiving for old results
        'archive_old_data' => true,
        
        // Archive data older than X days
        'archive_after_days' => 30,
    ],

];
