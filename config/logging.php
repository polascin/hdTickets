<?php

declare(strict_types=1);

use App\Logging\ErrorTrackingLogger;
use App\Logging\PerformanceLogger;
use App\Logging\QueryLogger;
use App\Logging\TicketApiFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace'   => FALSE,
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver'   => 'stack',
            'channels' => (function () {
                $channels = explode(',', (string) env('LOG_STACK_CHANNELS', 'single,performance'));
                $perfEnabled = (bool) env('PERFORMANCE_LOGGING', FALSE);
                $logsWritable = @is_writable(storage_path('logs'));
                if (! $perfEnabled || ! $logsWritable) {
                    $channels = array_filter($channels, fn ($c) => trim($c) !== 'performance');
                }
                // Ensure at least one channel remains
                if (empty($channels)) {
                    $channels = ['single'];
                }

                return array_values($channels);
            })(),
            'ignore_exceptions' => FALSE,
        ],

        'single' => [
            'driver'               => 'single',
            'path'                 => storage_path('logs/laravel.log'),
            'level'                => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => TRUE,
        ],

        'daily' => [
            'driver'               => 'daily',
            'path'                 => storage_path('logs/laravel.log'),
            'level'                => env('LOG_LEVEL', 'debug'),
            'days'                 => 30,
            'replace_placeholders' => TRUE,
        ],

        'slack' => [
            'driver'   => 'slack',
            'url'      => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'HDTickets Monitor',
            'emoji'    => ':warning:',
            'level'    => env('LOG_LEVEL', 'critical'),
        ],

        'papertrail' => [
            'driver'       => 'monolog',
            'level'        => env('LOG_LEVEL', 'debug'),
            'handler'      => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host'             => env('PAPERTRAIL_URL'),
                'port'             => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://' . env('PAPERTRAIL_URL') . ':' . env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver'    => 'monolog',
            'level'     => env('LOG_LEVEL', 'debug'),
            'handler'   => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with'      => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level'  => env('LOG_LEVEL', 'debug'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level'  => env('LOG_LEVEL', 'debug'),
        ],

        'null' => [
            'driver'  => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        'imap' => [
            'driver'               => 'daily',
            'path'                 => storage_path('logs/imap.log'),
            'level'                => env('LOG_LEVEL', 'debug'),
            'days'                 => 14,
            'replace_placeholders' => TRUE,
        ],

        /*
        |--------------------------------------------------------------------------
        | Ticket APIs Log Channel
        |--------------------------------------------------------------------------
        |
        | Dedicated log channel for ticket APIs, scraping operations, and monitoring.
        | This channel logs to a separate file for better organization and analysis.
        |
        */
        'ticket_apis' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/ticket_apis.log'),
            'level'  => env('LOG_LEVEL', 'debug'),
            'days'   => 30, // Keep logs for 30 days
            'tap'    => [TicketApiFormatter::class],
        ],

        /*
        |--------------------------------------------------------------------------
        | Monitoring Log Channel
        |--------------------------------------------------------------------------
        |
        | Dedicated channel for platform monitoring alerts and statistics.
        |
        */
        'monitoring' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/monitoring.log'),
            'level'  => env('LOG_LEVEL', 'info'),
            'days'   => 60, // Keep monitoring logs longer
        ],

        /*
        |--------------------------------------------------------------------------
        | Performance Log Channel
        |--------------------------------------------------------------------------
        |
        | Channel for tracking performance metrics and slow operations.
        |
        */
        'performance' => array_merge([
            // Allow disabling via env to avoid permission issues in limited environments / CI
            // Set PERFORMANCE_LOGGING=false in .env (or unset) to disable this channel safely
            'driver' => env('PERFORMANCE_LOGGING', FALSE) ? 'daily' : 'single',
            'path'   => storage_path('logs/performance.log'),
            'level'  => env('LOG_LEVEL', 'info'),
            'days'   => 14,
        ], env('PERFORMANCE_LOGGING', FALSE) ? [
            'tap' => [PerformanceLogger::class],
        ] : []),

        /*
        |--------------------------------------------------------------------------
        | Security Log Channel
        |--------------------------------------------------------------------------
        |
        | Dedicated channel for security events, authentication, and suspicious activities.
        |
        */
        'security' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/security.log'),
            'level'  => env('LOG_LEVEL', 'info'),
            'days'   => 90, // Keep security logs for 90 days
        ],

        /*
        |--------------------------------------------------------------------------
        | Critical Alerts Channel
        |--------------------------------------------------------------------------
        |
        | Channel for critical system alerts that need immediate attention.
        | Uses Slack for real-time notifications.
        |
        */
        'critical_alerts' => [
            'driver'            => 'stack',
            'channels'          => ['daily', 'slack'],
            'ignore_exceptions' => FALSE,
        ],

        /*
        |--------------------------------------------------------------------------
        | Audit Log Channel
        |--------------------------------------------------------------------------
        |
        | Channel for audit trails of sensitive operations and administrative actions.
        |
        */
        'audit' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/audit.log'),
            'level'  => 'info',
            'days'   => 180, // Keep audit logs for 6 months
        ],

        /*
        |--------------------------------------------------------------------------
        | Authentication Debug Log Channel
        |--------------------------------------------------------------------------
        |
        | Dedicated channel for debugging authentication flows, role assignments,
        | and session management. Used for diagnosing auth issues.
        |
        */
        'auth_debug' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/auth_debug.log'),
            'level'  => env('LOG_LEVEL', 'debug'),
            'days'   => 7, // Keep auth debug logs for 7 days
        ],

        /*
        |--------------------------------------------------------------------------
        | Database Query Log Channel
        |--------------------------------------------------------------------------
        |
        | Dedicated channel for logging database queries for performance optimization.
        | Logs slow queries and query patterns for analysis.
        |
        */
        'query' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/queries.log'),
            'level'  => env('LOG_LEVEL', 'debug'),
            'days'   => 7,
            'tap'    => [QueryLogger::class],
        ],

        /*
        |--------------------------------------------------------------------------
        | Error Tracking Log Channel
        |--------------------------------------------------------------------------
        |
        | Dedicated channel for structured error tracking and analysis.
        |
        */
        'error_tracking' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/error_tracking.log'),
            'level'  => 'error',
            'days'   => 30,
            'tap'    => [ErrorTrackingLogger::class],
        ],

        /*
        |--------------------------------------------------------------------------
        | System Metrics Log Channel
        |--------------------------------------------------------------------------
        |
        | Channel for logging system metrics like CPU, memory, disk usage.
        |
        */
        'metrics' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/metrics.log'),
            'level'  => 'info',
            'days'   => 30,
        ],

        /*
        |--------------------------------------------------------------------------
        | Request/Response Log Channel
        |--------------------------------------------------------------------------
        |
        | Channel for logging HTTP requests and responses for debugging and monitoring.
        |
        */
        'requests' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/requests.log'),
            'level'  => env('LOG_LEVEL', 'info'),
            'days'   => 7,
        ],
    ],
];
