<?php declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Component Architecture Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the HD Tickets component
    | architecture system that manages Blade, Alpine.js, and Vue.js components.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Component Registry Settings
    |--------------------------------------------------------------------------
    |
    | These settings control how components are discovered, registered,
    | and managed within the application.
    |
    */
    'registry' => [
        // Enable automatic component discovery
        'auto_discovery' => env('COMPONENT_AUTO_DISCOVERY', TRUE),

        // Component discovery paths
        'discovery_paths' => [
            'blade'  => 'resources/views/components',
            'alpine' => 'resources/js/alpine/components',
            'vue'    => 'resources/js/components',
        ],

        // File extensions for component types
        'extensions' => [
            'blade'  => ['.blade.php'],
            'alpine' => ['.js'],
            'vue'    => ['.vue'],
        ],

        // Component caching
        'cache_enabled'  => env('COMPONENT_CACHE', TRUE),
        'cache_duration' => env('COMPONENT_CACHE_DURATION', 3600), // seconds

        // Component validation
        'validate_on_register' => env('COMPONENT_VALIDATE', TRUE),
        'strict_validation'    => env('COMPONENT_STRICT_VALIDATION', FALSE),
    ],

    /*
    |--------------------------------------------------------------------------
    | Component Communication Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for inter-component communication patterns and validation.
    |
    */
    'communication' => [
        // Enable prop validation
        'validate_props' => env('COMPONENT_VALIDATE_PROPS', TRUE),

        // Enable event validation
        'validate_events' => env('COMPONENT_VALIDATE_EVENTS', TRUE),

        // Communication patterns
        'patterns' => [
            'blade_to_alpine' => [
                'method'  => 'data-attributes',
                'enabled' => TRUE,
            ],
            'blade_to_vue' => [
                'method'  => 'data-props',
                'enabled' => TRUE,
            ],
            'alpine_to_vue' => [
                'method'  => 'custom-events',
                'enabled' => TRUE,
            ],
            'vue_to_alpine' => [
                'method'  => 'dom-events',
                'enabled' => TRUE,
            ],
        ],

        // Event debounce settings
        'event_debounce' => [
            'enabled'       => TRUE,
            'default_delay' => 300, // milliseconds
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Component Lifecycle Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for component lifecycle management including initialization,
    | mounting, updating, and cleanup processes.
    |
    */
    'lifecycle' => [
        // Enable lifecycle management
        'enabled' => env('COMPONENT_LIFECYCLE', TRUE),

        // Performance monitoring
        'performance_monitoring' => [
            'enabled'             => env('COMPONENT_PERFORMANCE_MONITORING', TRUE),
            'track_creation_time' => TRUE,
            'track_mount_time'    => TRUE,
            'track_update_count'  => TRUE,
            'track_memory_usage'  => env('APP_ENV') === 'local',
        ],

        // Error handling
        'error_handling' => [
            'log_errors'               => TRUE,
            'max_errors_per_component' => 10,
            'error_recovery'           => TRUE,
        ],

        // Cleanup settings
        'cleanup' => [
            'auto_cleanup_on_unmount' => TRUE,
            'cleanup_timeout'         => 30000, // milliseconds
            'cleanup_intervals'       => TRUE,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Component Type Boundaries
    |--------------------------------------------------------------------------
    |
    | Define clear boundaries and responsibilities for each component type
    | to maintain proper separation of concerns.
    |
    */
    'boundaries' => [
        'blade' => [
            'responsibilities' => [
                'Server-side HTML generation',
                'SEO-optimized content',
                'Basic form rendering',
                'Static content display',
                'Email templates',
                'PDF generation',
            ],
            'restrictions' => [
                'No complex client-side state management',
                'Limited JavaScript interactions',
                'No real-time updates',
                'No complex data manipulation',
            ],
            'best_practices' => [
                'Keep logic in the controller or service layer',
                'Use slots for content injection',
                'Leverage Blade directives for common patterns',
                'Optimize for server-side rendering performance',
            ],
        ],

        'alpine' => [
            'responsibilities' => [
                'Lightweight client-side interactions',
                'Form validation and enhancement',
                'Modal and dropdown management',
                'Simple state management',
                'DOM manipulation',
                'Progressive enhancement',
            ],
            'restrictions' => [
                'No complex routing',
                'Limited component composition',
                'No virtual DOM benefits',
                'Avoid for complex data visualization',
            ],
            'best_practices' => [
                'Keep component state simple and flat',
                'Use x-data for component initialization',
                'Leverage Alpine directives for common patterns',
                'Optimize for performance with x-show vs x-if',
            ],
        ],

        'vue' => [
            'responsibilities' => [
                'Complex interactive dashboards',
                'Real-time data visualization',
                'Advanced form handling with complex validation',
                'Single-page application features',
                'Complex state management with Pinia',
                'Advanced routing and navigation',
            ],
            'restrictions' => [
                'Should be lazy-loaded when possible',
                'Avoid for simple static content',
                'Consider bundle size impact',
                'Not suitable for SEO-critical content',
            ],
            'best_practices' => [
                'Use composition API for better TypeScript support',
                'Implement proper component lifecycle hooks',
                'Leverage Vue 3 performance improvements',
                'Use Pinia for complex state management',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sports Events Platform Specific Settings
    |--------------------------------------------------------------------------
    |
    | Configuration specific to the HD Tickets sports events platform,
    | including component categorization and domain-specific validations.
    |
    */
    'sports_platform' => [
        // Component categories specific to sports events
        'categories' => [
            'sports-tickets' => 'Components related to sports event tickets',
            'events'         => 'Sports event display and management components',
            'venues'         => 'Sports venue and location components',
            'analytics'      => 'Sports analytics and reporting components',
            'booking'        => 'Ticket booking and purchase components',
            'monitoring'     => 'Platform monitoring and health components',
            'scraping'       => 'Ticket scraping and data collection components',
            'dashboard'      => 'Administrative and user dashboard components',
            'forms'          => 'Form components for ticket and event management',
            'ui'             => 'General user interface components',
        ],

        // Sport categories
        'sport_categories' => [
            'football' => 'Football/Soccer',
            'rugby'    => 'Rugby Union and League',
            'cricket'  => 'Cricket matches and tournaments',
            'tennis'   => 'Tennis tournaments and matches',
            'other'    => 'Other sports events',
        ],

        // Platform sources
        'platform_sources' => [
            'ticketmaster' => 'Ticketmaster',
            'stubhub'      => 'StubHub',
            'seatgeek'     => 'SeatGeek',
            'official'     => 'Official venue/team websites',
        ],

        // Availability statuses
        'availability_statuses' => [
            'available' => 'Available for purchase',
            'limited'   => 'Limited availability',
            'sold_out'  => 'Sold out',
            'on_hold'   => 'Temporarily on hold',
        ],

        // Prop validation rules for sports platform
        'prop_validations' => [
            'ticket_id' => [
                'required' => TRUE,
                'pattern'  => '/^TKT-[A-Z0-9]{6}$/',
                'example'  => 'TKT-ABC123',
            ],
            'event_id' => [
                'required' => TRUE,
                'pattern'  => '/^EVT-[0-9]{6}$/',
                'example'  => 'EVT-123456',
            ],
            'price' => [
                'required' => TRUE,
                'type'     => 'numeric',
                'min'      => 0,
                'max'      => 10000,
            ],
            'venue' => [
                'required'   => TRUE,
                'type'       => 'string',
                'min_length' => 2,
                'max_length' => 100,
            ],
            'sport_category' => [
                'required' => TRUE,
                'enum'     => ['football', 'rugby', 'cricket', 'tennis', 'other'],
            ],
            'availability_status' => [
                'required' => TRUE,
                'enum'     => ['available', 'limited', 'sold_out', 'on_hold'],
            ],
            'platform_source' => [
                'required' => TRUE,
                'enum'     => ['ticketmaster', 'stubhub', 'seatgeek', 'official'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance and Optimization Settings
    |--------------------------------------------------------------------------
    |
    | Settings for optimizing component performance and reducing resource usage.
    |
    */
    'performance' => [
        // Lazy loading settings
        'lazy_loading' => [
            'enabled'        => env('COMPONENT_LAZY_LOADING', TRUE),
            'threshold'      => '0px', // Intersection observer threshold
            'vue_chunk_size' => 50000, // bytes
            'alpine_defer'   => TRUE,
        ],

        // Component bundling
        'bundling' => [
            'enabled'        => env('COMPONENT_BUNDLING', TRUE),
            'bundle_alpine'  => TRUE,
            'bundle_vue'     => TRUE,
            'code_splitting' => TRUE,
        ],

        // Caching strategies
        'caching' => [
            'component_definitions' => TRUE,
            'rendered_output'       => env('APP_ENV') === 'production',
            'cache_driver'          => env('COMPONENT_CACHE_DRIVER', 'file'),
        ],

        // Resource optimization
        'optimization' => [
            'minify_alpine'   => env('APP_ENV') === 'production',
            'tree_shake_vue'  => TRUE,
            'compress_props'  => TRUE,
            'debounce_events' => TRUE,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Development and Debugging Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for development tools and debugging features.
    |
    */
    'development' => [
        // Debug mode
        'debug' => env('COMPONENT_DEBUG', env('APP_DEBUG', FALSE)),

        // Logging
        'logging' => [
            'enabled'                 => env('COMPONENT_LOGGING', FALSE),
            'level'                   => env('COMPONENT_LOG_LEVEL', 'info'),
            'log_channel'             => env('COMPONENT_LOG_CHANNEL', 'daily'),
            'log_lifecycle_events'    => env('APP_ENV') === 'local',
            'log_performance_metrics' => env('APP_ENV') === 'local',
        ],

        // Development tools
        'dev_tools' => [
            'component_inspector'  => env('APP_ENV') === 'local',
            'performance_profiler' => env('APP_ENV') === 'local',
            'hot_reload'           => env('VITE_HMR_HOST') !== NULL,
            'vue_devtools'         => env('APP_ENV') === 'local',
        ],

        // Testing
        'testing' => [
            'mock_external_apis'   => env('APP_ENV') === 'testing',
            'disable_lazy_loading' => env('APP_ENV') === 'testing',
            'skip_validation'      => env('APP_ENV') === 'testing',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security-related configuration for component architecture.
    |
    */
    'security' => [
        // Content Security Policy
        'csp' => [
            'enabled'                  => env('COMPONENT_CSP', TRUE),
            'nonce_for_inline_scripts' => TRUE,
            'trusted_domains'          => [
                'cdn.jsdelivr.net',
                'unpkg.com',
            ],
        ],

        // XSS Prevention
        'xss_protection' => [
            'escape_props_by_default' => TRUE,
            'validate_event_handlers' => TRUE,
            'sanitize_html_props'     => TRUE,
        ],

        // Component isolation
        'isolation' => [
            'sandbox_third_party_components' => FALSE,
            'restrict_dom_access'            => FALSE,
            'validate_prop_sources'          => TRUE,
        ],
    ],
];
