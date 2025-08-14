<?php declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Route Caching Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file handles settings for route caching in production.
    | When routes are cached, they are compiled into a single file for better
    | performance. However, certain middleware and closures require special
    | consideration when caching routes.
    |
    */

    /**
     * Enable route caching in production
     * Set to true when deploying to production environments
     */
    'enabled' => env('ROUTE_CACHE_ENABLED', FALSE),

    /**
     * Routes that should not be cached
     * These routes contain closures or dynamic middleware that cannot be serialized
     */
    'excluded_routes' => [
        // Admin platform performance route with closure
        'admin.platform-performance',
        // Admin activities recent route with closure
        'admin.activities.recent',
        // Any other routes that use closures
    ],

    /**
     * Middleware that requires special handling during route caching
     * These middleware should be defined as aliases in the Kernel
     */
    'middleware_considerations' => [
        'role' => [
            'class'     => 'App\\Http\\Middleware\\RoleMiddleware',
            'cacheable' => TRUE,
            'note'      => 'Role middleware is fully cacheable as it uses string parameters',
        ],
        'admin' => [
            'class'     => 'App\\Http\\Middleware\\AdminMiddleware',
            'cacheable' => TRUE,
            'note'      => 'Admin middleware with permissions is cacheable',
        ],
        'agent' => [
            'class'     => 'App\\Http\\Middleware\\AgentMiddleware',
            'cacheable' => TRUE,
            'note'      => 'Agent middleware is cacheable',
        ],
        'scraper' => [
            'class'     => 'App\\Http\\Middleware\\ScraperMiddleware',
            'cacheable' => TRUE,
            'note'      => 'Scraper middleware is cacheable',
        ],
        'customer' => [
            'class'     => 'App\\Http\\Middleware\\CustomerMiddleware',
            'cacheable' => TRUE,
            'note'      => 'Customer middleware is cacheable',
        ],
    ],

    /**
     * Pre-cache warming configuration
     * Routes to pre-warm when clearing cache
     */
    'warm_routes' => [
        // Critical application routes
        'home',
        'login',
        'dashboard',
        'dashboard.customer',
        'dashboard.agent',
        'dashboard.scraper',
        'admin.dashboard',
        // Health check routes
        'health.index',
        'health.database',
        'health.redis',
    ],

    /**
     * Cache validation settings
     * Ensure route cache integrity
     */
    'validation' => [
        'check_middleware_registration' => TRUE,
        'verify_controller_existence'   => TRUE,
        'validate_route_parameters'     => TRUE,
    ],
];
