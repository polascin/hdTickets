<?php declare(strict_types=1);

/**
 * HD Tickets Platform Configuration
 *
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
            'key'          => 'ticketmaster',
            'name'         => 'Ticketmaster',
            'display_name' => 'Ticketmaster',
            'order'        => 1,
        ],
        'stubhub' => [
            'key'          => 'stubhub',
            'name'         => 'StubHub',
            'display_name' => 'StubHub',
            'order'        => 2,
        ],
        'viagogo' => [
            'key'          => 'viagogo',
            'name'         => 'Viagogo',
            'display_name' => 'Viagogo',
            'order'        => 3,
        ],
        'seatgeek' => [
            'key'          => 'seatgeek',
            'name'         => 'SeatGeek',
            'display_name' => 'SeatGeek',
            'order'        => 4,
        ],
        'tickpick' => [
            'key'          => 'tickpick',
            'name'         => 'TickPick',
            'display_name' => 'TickPick',
            'order'        => 5,
        ],
        'eventbrite' => [
            'key'          => 'eventbrite',
            'name'         => 'Eventbrite',
            'display_name' => 'Eventbrite',
            'order'        => 6,
        ],
        'bandsintown' => [
            'key'          => 'bandsintown',
            'name'         => 'Bandsintown',
            'display_name' => 'Bandsintown',
            'order'        => 7,
        ],
        'manchester_united' => [
            'key'          => 'manchester_united',
            'name'         => 'Manchester United Official App',
            'display_name' => 'Manchester United Official App',
            'order'        => 8,
        ],
        // European Football Platforms - Spain
        'real_madrid' => [
            'key'          => 'real_madrid',
            'name'         => 'Real Madrid CF',
            'display_name' => 'Real Madrid CF',
            'order'        => 9,
        ],
        'barcelona' => [
            'key'          => 'barcelona',
            'name'         => 'FC Barcelona',
            'display_name' => 'FC Barcelona',
            'order'        => 10,
        ],
        'atletico_madrid' => [
            'key'          => 'atletico_madrid',
            'name'         => 'Atlético Madrid',
            'display_name' => 'Atlético Madrid',
            'order'        => 11,
        ],
        'entradium_spain' => [
            'key'          => 'entradium_spain',
            'name'         => 'Entradium Spain',
            'display_name' => 'Entradium Spain',
            'order'        => 12,
        ],
        // European Football Platforms - Germany
        'bayern_munich' => [
            'key'          => 'bayern_munich',
            'name'         => 'FC Bayern Munich',
            'display_name' => 'FC Bayern Munich',
            'order'        => 13,
        ],
        'borussia_dortmund' => [
            'key'          => 'borussia_dortmund',
            'name'         => 'Borussia Dortmund',
            'display_name' => 'Borussia Dortmund',
            'order'        => 14,
        ],
        'stadionwelt_germany' => [
            'key'          => 'stadionwelt_germany',
            'name'         => 'StadionWelt Germany',
            'display_name' => 'StadionWelt Germany',
            'order'        => 15,
        ],
        // European Football Platforms - Italy
        'juventus' => [
            'key'          => 'juventus',
            'name'         => 'Juventus FC',
            'display_name' => 'Juventus FC',
            'order'        => 16,
        ],
        'ac_milan' => [
            'key'          => 'ac_milan',
            'name'         => 'AC Milan',
            'display_name' => 'AC Milan',
            'order'        => 17,
        ],
        'ticketone_italy' => [
            'key'          => 'ticketone_italy',
            'name'         => 'TicketOne Italy',
            'display_name' => 'TicketOne Italy',
            'order'        => 18,
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
        // European Football - Spain
        'real_madrid',
        'barcelona',
        'atletico_madrid',
        'entradium_spain',
        // European Football - Germany
        'bayern_munich',
        'borussia_dortmund',
        'stadionwelt_germany',
        // European Football - Italy
        'juventus',
        'ac_milan',
        'ticketone_italy',
    ],
];
