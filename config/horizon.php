<?php declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Horizon Domain
    |--------------------------------------------------------------------------
    |
    | This is the subdomain where Horizon will be accessible from. If this
    | setting is null, Horizon will reside under the same domain as the
    | application. Otherwise, this value will serve as the subdomain.
    |
    */

    'domain' => env('HORIZON_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Horizon will be accessible from. Feel free
    | to change this path to anything you like. Note that the URI will not
    | affect the paths of its internal API that aren't exposed to users.
    |
    */

    'path' => env('HORIZON_PATH', 'horizon'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Connection
    |--------------------------------------------------------------------------
    |
    | This is the name of the Redis connection where Horizon will store the
    | meta information required for it to function. It includes the list
    | of supervisors, failed jobs, job metrics, and other information.
    |
    */

    'use' => env('HORIZON_REDIS_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Prefix
    |--------------------------------------------------------------------------
    |
    | This prefix will be used when storing all Horizon data in Redis. You
    | may modify the prefix when you are running multiple installations
    | of Horizon on the same server so that they don't have problems.
    |
    */

    'prefix' => env(
        'HORIZON_PREFIX',
        'hdtickets_horizon:',
    ),

    /*
    |--------------------------------------------------------------------------
    | Horizon Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will get attached onto each Horizon route, giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Or, you can simply stick with this list.
    |
    */

    'middleware' => ['web', 'auth', 'verified'],

    /*
    |--------------------------------------------------------------------------
    | Queue Wait Time Thresholds
    |--------------------------------------------------------------------------
    |
    | This option allows you to configure when the LongWaitDetected event
    | will be fired. Every connection / queue combination may have its
    | own, unique threshold (in seconds) before this event is fired.
    |
    */

    'waits' => [
        'redis:default'       => 30,
        'redis:high'          => 15,
        'redis:low'           => 120,
        'redis:scraping'      => 60,
        'redis:notifications' => 45,
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Trimming Times
    |--------------------------------------------------------------------------
    |
    | Here you can configure for how long (in minutes) you desire Horizon to
    | persist the recent and failed jobs. Typically, recent jobs are kept
    | for one hour while all failed jobs are stored for an entire week.
    |
    */

    'trim' => [
        'recent'        => 1440,        // 24 hours for recent jobs
        'pending'       => 1440,       // 24 hours for pending jobs
        'completed'     => 2880,     // 48 hours for completed jobs
        'recent_failed' => 10080, // 1 week for recent failed jobs
        'failed'        => 20160,       // 2 weeks for failed jobs
        'monitored'     => 10080,    // 1 week for monitored jobs
    ],

    /*
    |--------------------------------------------------------------------------
    | Silenced Jobs
    |--------------------------------------------------------------------------
    |
    | Silencing a job will instruct Horizon to not place the job in the list
    | of completed jobs within the Horizon dashboard. This setting may be
    | used to fully remove any noisy jobs from the completed jobs list.
    |
    */

    'silenced' => [
        // App\Jobs\TicketHealthCheck::class,
        // App\Jobs\SystemHeartbeat::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Metrics
    |--------------------------------------------------------------------------
    |
    | Here you can configure how many snapshots should be kept to display in
    | the metrics graph. This will get used in combination with Horizon's
    | `horizon:snapshot` schedule to define how long to retain metrics.
    |
    */

    'metrics' => [
        'trim_snapshots' => [
            'job'   => 48,    // 48 hours of job history
            'queue' => 48,  // 48 hours of queue metrics
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fast Termination
    |--------------------------------------------------------------------------
    |
    | When this option is enabled, Horizon's "terminate" command will not
    | wait on all of the workers to terminate unless the --wait option
    | is provided. Fast termination can shorten deployment delay by
    | allowing a new instance of Horizon to start while the last
    | instance will continue to terminate each of its workers.
    |
    */

    'fast_termination' => env('HORIZON_FAST_TERMINATION', FALSE),

    /*
    |--------------------------------------------------------------------------
    | Memory Limit (MB)
    |--------------------------------------------------------------------------
    |
    | This value describes the maximum amount of memory the Horizon master
    | supervisor may consume before it is terminated and restarted. For
    | configuring these limits on your workers, see the next section.
    |
    */

    'memory_limit' => env('HORIZON_MEMORY_LIMIT', 128),

    /*
    |--------------------------------------------------------------------------
    | Queue Worker Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define the queue worker settings used by your application
    | in all environments. These supervisors and settings handle all your
    | queued jobs and will be provisioned by Horizon during deployment.
    |
    */

    'defaults' => [
        'default-supervisor' => [
            'connection'          => 'redis',
            'queue'               => ['default'],
            'balance'             => 'auto',
            'autoScalingStrategy' => 'time',
            'maxProcesses'        => 3,
            'maxTime'             => 0,
            'maxJobs'             => 1000,
            'memory'              => 256,
            'tries'               => 3,
            'timeout'             => 300,
            'nice'                => 0,
        ],
    ],

    'environments' => [
        'production' => [
            // High Priority Queue - Critical operations
            'high-priority-supervisor' => [
                'connection'          => 'redis',
                'queue'               => ['high'],
                'balance'             => 'auto',
                'autoScalingStrategy' => 'time',
                'maxProcesses'        => 8,
                'maxTime'             => 0,
                'maxJobs'             => 500,
                'memory'              => 512,
                'tries'               => 5,
                'timeout'             => 180,
                'nice'                => -5,
                'balanceMaxShift'     => 2,
                'balanceCooldown'     => 2,
            ],

            // Default Queue - Standard operations
            'default-supervisor' => [
                'connection'          => 'redis',
                'queue'               => ['default'],
                'balance'             => 'auto',
                'autoScalingStrategy' => 'time',
                'maxProcesses'        => 6,
                'maxTime'             => 0,
                'maxJobs'             => 1000,
                'memory'              => 256,
                'tries'               => 3,
                'timeout'             => 300,
                'nice'                => 0,
                'balanceMaxShift'     => 1,
                'balanceCooldown'     => 3,
            ],

            // Scraping Queue - Ticket scraping operations
            'scraping-supervisor' => [
                'connection'          => 'redis',
                'queue'               => ['scraping'],
                'balance'             => 'auto',
                'autoScalingStrategy' => 'time',
                'maxProcesses'        => 4,
                'maxTime'             => 0,
                'maxJobs'             => 200,
                'memory'              => 512,
                'tries'               => 3,
                'timeout'             => 600, // 10 minutes for scraping
                'nice'                => 5,
                'balanceMaxShift'     => 1,
                'balanceCooldown'     => 5,
            ],

            // Notifications Queue - Email, SMS, Slack notifications
            'notifications-supervisor' => [
                'connection'          => 'redis',
                'queue'               => ['notifications'],
                'balance'             => 'auto',
                'autoScalingStrategy' => 'time',
                'maxProcesses'        => 3,
                'maxTime'             => 0,
                'maxJobs'             => 500,
                'memory'              => 128,
                'tries'               => 5,
                'timeout'             => 120,
                'nice'                => 0,
                'balanceMaxShift'     => 1,
                'balanceCooldown'     => 3,
            ],

            // Low Priority Queue - Background maintenance
            'low-priority-supervisor' => [
                'connection'          => 'redis',
                'queue'               => ['low'],
                'balance'             => 'auto',
                'autoScalingStrategy' => 'time',
                'maxProcesses'        => 2,
                'maxTime'             => 0,
                'maxJobs'             => 100,
                'memory'              => 256,
                'tries'               => 2,
                'timeout'             => 900, // 15 minutes
                'nice'                => 10,
                'balanceMaxShift'     => 1,
                'balanceCooldown'     => 5,
            ],
        ],

        'staging' => [
            'staging-supervisor' => [
                'connection'          => 'redis',
                'queue'               => ['default', 'high', 'scraping', 'notifications', 'low'],
                'balance'             => 'auto',
                'autoScalingStrategy' => 'time',
                'maxProcesses'        => 4,
                'maxTime'             => 0,
                'maxJobs'             => 500,
                'memory'              => 256,
                'tries'               => 3,
                'timeout'             => 300,
                'nice'                => 0,
                'balanceMaxShift'     => 1,
                'balanceCooldown'     => 3,
            ],
        ],

        'local' => [
            'local-supervisor' => [
                'connection'          => 'redis',
                'queue'               => ['default', 'high', 'scraping', 'notifications', 'low'],
                'balance'             => 'simple',
                'autoScalingStrategy' => 'time',
                'maxProcesses'        => 2,
                'maxTime'             => 0,
                'maxJobs'             => 100,
                'memory'              => 128,
                'tries'               => 3,
                'timeout'             => 300,
                'nice'                => 0,
            ],
        ],
    ],
];
