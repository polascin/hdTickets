<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ticketmaster API Configuration
    |--------------------------------------------------------------------------
    | Ticketmaster Discovery API provides access to event data
    | Sign up at: https://developer.ticketmaster.com/
    */
    'ticketmaster' => [
        'enabled' => env('TICKETMASTER_ENABLED', false),
        'api_key' => env('TICKETMASTER_API_KEY'),
        'base_url' => 'https://app.ticketmaster.com/discovery/v2',
        'timeout' => 30,
        'rate_limit' => [
            'requests_per_second' => 5,
            'requests_per_day' => 5000,
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
        'enabled' => env('SEATGEEK_ENABLED', false),
        'client_id' => env('SEATGEEK_CLIENT_ID'),
        'client_secret' => env('SEATGEEK_CLIENT_SECRET'),
        'base_url' => 'https://api.seatgeek.com/2',
        'timeout' => 30,
        'rate_limit' => [
            'requests_per_second' => 10,
            'requests_per_hour' => 1000,
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
        'enabled' => env('EVENTBRITE_ENABLED', false),
        'api_key' => env('EVENTBRITE_API_KEY'),
        'base_url' => 'https://www.eventbriteapi.com/v3',
        'timeout' => 30,
        'rate_limit' => [
            'requests_per_second' => 1000,
            'requests_per_hour' => 50000,
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
        'enabled' => env('STUBHUB_ENABLED', false),
        'api_key' => env('STUBHUB_API_KEY'),
        'app_token' => env('STUBHUB_APP_TOKEN'),
        'consumer_key' => env('STUBHUB_CONSUMER_KEY'),
        'consumer_secret' => env('STUBHUB_CONSUMER_SECRET'),
        'base_url' => 'https://api.stubhub.com',
        'sandbox_url' => 'https://api.stubhubsandbox.com',
        'timeout' => 30,
        'sandbox' => env('STUBHUB_SANDBOX', true),
        'rate_limit' => [
            'requests_per_second' => 10,
            'requests_per_hour' => 5000,
            'requests_per_day' => 50000,
            'delay_between_requests' => 0.1, // seconds
        ],
        'scraping' => [
            'enabled' => env('STUBHUB_SCRAPING_ENABLED', false),
            'user_agents' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            ],
            'delay_range' => [2, 5], // Random delay between requests
            'timeout' => 45,
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
        'enabled' => env('BANDSINTOWN_ENABLED', false),
        'app_id' => env('BANDSINTOWN_APP_ID'),
        'base_url' => 'https://rest.bandsintown.com',
        'timeout' => 30,
        'rate_limit' => [
            'requests_per_second' => 1,
            'requests_per_day' => 2000,
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
        'enabled' => env('VIAGOGO_ENABLED', false),
        'base_url' => 'https://www.viagogo.com',
        'timeout' => 30,
        'rate_limit' => [
            'requests_per_second' => 2, // Be respectful with scraping
            'requests_per_hour' => 500,
            'requests_per_day' => 5000,
            'delay_between_requests' => 0.5, // seconds
        ],
        'scraping' => [
            'user_agents' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ],
            'delay_range' => [3, 8], // Random delay between requests (seconds)
            'timeout' => 45,
            'max_retries' => 3,
            'backoff_factor' => 2, // Exponential backoff
            'proxy_rotation' => env('VIAGOGO_PROXY_ROTATION', false),
            'headers' => [
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept-Encoding' => 'gzip, deflate, br',
                'DNT' => '1',
                'Upgrade-Insecure-Requests' => '1',
            ],
        ],
        'currencies' => ['USD', 'EUR', 'GBP', 'CAD', 'AUD'], // Supported currencies
        'regions' => ['us', 'uk', 'ca', 'au', 'de', 'fr'], // Supported regions
    ],

    /*
    |--------------------------------------------------------------------------
    | TickPick Configuration (Web Scraping)
    |--------------------------------------------------------------------------
    | TickPick no-fee ticket marketplace - web scraping implementation
    | Specializes in transparent, no-fee pricing
    */
    'tickpick' => [
        'enabled' => env('TICKPICK_ENABLED', false),
        'api_key' => env('TICKPICK_API_KEY'), // If API becomes available
        'base_url' => 'https://www.tickpick.com',
        'api_url' => 'https://api.tickpick.com', // Potential future API endpoint
        'timeout' => 30,
        'rate_limit' => [
            'requests_per_second' => 3,
            'requests_per_hour' => 1000,
            'requests_per_day' => 10000,
            'delay_between_requests' => 0.33, // seconds
        ],
        'scraping' => [
            'user_agents' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ],
            'delay_range' => [2, 6], // Random delay between requests (seconds)
            'timeout' => 40,
            'max_retries' => 2,
            'javascript_required' => true, // TickPick heavily uses JS
            'headers' => [
                'Accept' => 'application/json, text/plain, */*',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Referer' => 'https://www.tickpick.com/',
            ],
        ],
        'features' => [
            'no_fees' => true, // TickPick's main selling point
            'best_deal_guarantee' => true,
            'instant_download' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | FunZone Configuration (Web Scraping)
    |--------------------------------------------------------------------------
    | FunZone Slovak ticket platform - web scraping implementation
    | Covers events in Slovakia and surrounding regions
    */
    'funzone' => [
        'enabled' => env('FUNZONE_ENABLED', false),
        'base_url' => 'https://www.funzone.sk',
        'alternate_url' => 'https://www.funzone.com', // International version
        'timeout' => 30,
        'rate_limit' => [
            'requests_per_second' => 5,
            'requests_per_hour' => 2000,
            'requests_per_day' => 15000,
            'delay_between_requests' => 0.2, // seconds
        ],
        'scraping' => [
            'user_agents' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ],
            'delay_range' => [1, 4], // Random delay between requests (seconds)
            'timeout' => 35,
            'max_retries' => 2,
            'headers' => [
                'Accept-Language' => 'sk-SK,sk;q=0.9,en;q=0.8', // Slovak preference
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ],
        ],
        'localization' => [
            'default_language' => 'sk', // Slovak
            'supported_languages' => ['sk', 'en', 'cs'], // Slovak, English, Czech
            'currency' => 'EUR',
            'timezone' => 'Europe/Bratislava',
        ],
        'regions' => [
            'primary' => 'Slovakia',
            'secondary' => ['Czech Republic', 'Austria', 'Hungary'],
        ],
        'event_types' => [
            'concerts' => true,
            'theater' => true,
            'sports' => true,
            'festivals' => true,
            'family' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */
    'default_timeout' => 30,
    'cache_ttl' => 3600, // 1 hour
    'retry_attempts' => 3,
    'retry_delay' => 1, // seconds
];
