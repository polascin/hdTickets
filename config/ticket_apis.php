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
        'base_url' => 'https://api.stubhub.com',
        'timeout' => 30,
        'sandbox' => env('STUBHUB_SANDBOX', true),
        'rate_limit' => [
            'requests_per_second' => 10,
            'requests_per_hour' => 5000,
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
    | Default Settings
    |--------------------------------------------------------------------------
    */
    'default_timeout' => 30,
    'cache_ttl' => 3600, // 1 hour
    'retry_attempts' => 3,
    'retry_delay' => 1, // seconds
];
