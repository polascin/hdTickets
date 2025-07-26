<?php

/**
 * HD Tickets Platform Configuration
 * @author Lubomir Polascin (Ľubomír Polaščín) aka Walter Csoelle
 * @version 2025.07.v4.0
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Platform Display Order Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines the consistent ordering for all platform
    | dropdowns and selections throughout the application. This order should
    | be maintained across all UI components to improve user experience.
    |
    */

    'display_order' => [
        'ticketmaster' => [
            'key' => 'ticketmaster',
            'name' => 'Ticketmaster',
            'display_name' => 'Ticketmaster',
            'order' => 1,
        ],
        'stubhub' => [
            'key' => 'stubhub',
            'name' => 'StubHub',
            'display_name' => 'StubHub',
            'order' => 2,
        ],
        'viagogo' => [
            'key' => 'viagogo',
            'name' => 'Viagogo',
            'display_name' => 'Viagogo',
            'order' => 3,
        ],
        'seatgeek' => [
            'key' => 'seatgeek',
            'name' => 'SeatGeek',
            'display_name' => 'SeatGeek',
            'order' => 4,
        ],
        'tickpick' => [
            'key' => 'tickpick',
            'name' => 'TickPick',
            'display_name' => 'TickPick',
            'order' => 5,
        ],
        'eventbrite' => [
            'key' => 'eventbrite',
            'name' => 'Eventbrite',
            'display_name' => 'Eventbrite',
            'order' => 6,
        ],
        'bandsintown' => [
            'key' => 'bandsintown',
            'name' => 'Bandsintown',
            'display_name' => 'Bandsintown',
            'order' => 7,
        ],
        'manchester_united' => [
            'key' => 'manchester_united',
            'name' => 'Manchester United Official App',
            'display_name' => 'Manchester United Official App',
            'order' => 8,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Platform Sorting Helper
    |--------------------------------------------------------------------------
    |
    | Note: Closures cannot be cached, so this has been moved to a helper class.
    | Use App\Helpers\PlatformHelper::getSortedPlatforms() instead.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Platform Keys in Order
    |--------------------------------------------------------------------------
    |
    | Simple array of platform keys in the correct order for quick access.
    |
    */
    'ordered_keys' => [
        'ticketmaster',
        'stubhub',
        'viagogo',
        'seatgeek',
        'tickpick',
        'eventbrite',
        'bandsintown',
        'manchester_united',
    ],
];
