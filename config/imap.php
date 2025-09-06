<?php

return [

    /*
    |--------------------------------------------------------------------------
    | IMAP Configuration for HD Tickets Sports Events Monitoring System
    |--------------------------------------------------------------------------
    |
    | This configuration file manages IMAP email connections for monitoring
    | sports event ticket availability notifications and automated email
    | processing within the HD Tickets system.
    |
    */

    'default' => env('IMAP_DEFAULT_CONNECTION', 'gmail'),

    /*
    |--------------------------------------------------------------------------
    | IMAP Connections
    |--------------------------------------------------------------------------
    |
    | Define multiple IMAP connections for different email providers.
    | Each connection can be used for monitoring different types of 
    | sports event notifications.
    |
    */

    'connections' => [

        'gmail' => [
            'host' => env('IMAP_GMAIL_HOST', 'imap.gmail.com'),
            'port' => env('IMAP_GMAIL_PORT', 993),
            'protocol' => env('IMAP_GMAIL_PROTOCOL', 'imap'),
            'encryption' => env('IMAP_GMAIL_ENCRYPTION', 'ssl'),
            'validate_cert' => env('IMAP_GMAIL_VALIDATE_CERT', true),
            'username' => env('IMAP_GMAIL_USERNAME'),
            'password' => env('IMAP_GMAIL_PASSWORD'),
            'timeout' => env('IMAP_GMAIL_TIMEOUT', 60),
            'retry_count' => env('IMAP_GMAIL_RETRY_COUNT', 3),
            'retry_delay' => env('IMAP_GMAIL_RETRY_DELAY', 5), // seconds
        ],

        'outlook' => [
            'host' => env('IMAP_OUTLOOK_HOST', 'outlook.office365.com'),
            'port' => env('IMAP_OUTLOOK_PORT', 993),
            'protocol' => env('IMAP_OUTLOOK_PROTOCOL', 'imap'),
            'encryption' => env('IMAP_OUTLOOK_ENCRYPTION', 'ssl'),
            'validate_cert' => env('IMAP_OUTLOOK_VALIDATE_CERT', true),
            'username' => env('IMAP_OUTLOOK_USERNAME'),
            'password' => env('IMAP_OUTLOOK_PASSWORD'),
            'timeout' => env('IMAP_OUTLOOK_TIMEOUT', 60),
            'retry_count' => env('IMAP_OUTLOOK_RETRY_COUNT', 3),
            'retry_delay' => env('IMAP_OUTLOOK_RETRY_DELAY', 5),
        ],

        'yahoo' => [
            'host' => env('IMAP_YAHOO_HOST', 'imap.mail.yahoo.com'),
            'port' => env('IMAP_YAHOO_PORT', 993),
            'protocol' => env('IMAP_YAHOO_PROTOCOL', 'imap'),
            'encryption' => env('IMAP_YAHOO_ENCRYPTION', 'ssl'),
            'validate_cert' => env('IMAP_YAHOO_VALIDATE_CERT', true),
            'username' => env('IMAP_YAHOO_USERNAME'),
            'password' => env('IMAP_YAHOO_PASSWORD'),
            'timeout' => env('IMAP_YAHOO_TIMEOUT', 60),
            'retry_count' => env('IMAP_YAHOO_RETRY_COUNT', 3),
            'retry_delay' => env('IMAP_YAHOO_RETRY_DELAY', 5),
        ],

        'custom' => [
            'host' => env('IMAP_CUSTOM_HOST'),
            'port' => env('IMAP_CUSTOM_PORT', 993),
            'protocol' => env('IMAP_CUSTOM_PROTOCOL', 'imap'),
            'encryption' => env('IMAP_CUSTOM_ENCRYPTION', 'ssl'),
            'validate_cert' => env('IMAP_CUSTOM_VALIDATE_CERT', true),
            'username' => env('IMAP_CUSTOM_USERNAME'),
            'password' => env('IMAP_CUSTOM_PASSWORD'),
            'timeout' => env('IMAP_CUSTOM_TIMEOUT', 60),
            'retry_count' => env('IMAP_CUSTOM_RETRY_COUNT', 3),
            'retry_delay' => env('IMAP_CUSTOM_RETRY_DELAY', 5),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Email Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for monitoring sports event ticket notifications
    | from various ticket platforms and sources.
    |
    */

    'monitoring' => [

        // Default mailbox to monitor (usually INBOX)
        'default_mailbox' => env('IMAP_DEFAULT_MAILBOX', 'INBOX'),

        // Alternative mailboxes to monitor
        'mailboxes' => [
            'INBOX',
            'Promotions',
            'Updates',
            '[Gmail]/Promotions',
            '[Gmail]/Updates',
        ],

        // Email processing batch size
        'batch_size' => env('IMAP_BATCH_SIZE', 50),

        // Maximum age of emails to process (in days)
        'max_age_days' => env('IMAP_MAX_AGE_DAYS', 7),

        // Mark processed emails as read
        'mark_as_read' => env('IMAP_MARK_AS_READ', true),

        // Move processed emails to specific folder
        'move_processed' => env('IMAP_MOVE_PROCESSED', false),
        'processed_folder' => env('IMAP_PROCESSED_FOLDER', 'INBOX.Processed'),

        // Archive old emails
        'auto_archive' => env('IMAP_AUTO_ARCHIVE', false),
        'archive_folder' => env('IMAP_ARCHIVE_FOLDER', 'INBOX.Archive'),
        'archive_after_days' => env('IMAP_ARCHIVE_AFTER_DAYS', 30),

    ],

    /*
    |--------------------------------------------------------------------------
    | Platform Email Patterns
    |--------------------------------------------------------------------------
    |
    | Email sender patterns and keywords for identifying sports event
    | ticket notifications from different platforms.
    |
    */

    'platform_patterns' => [

        'ticketmaster' => [
            'from_patterns' => [
                '*@ticketmaster.com',
                '*@tm.e.ticketmaster.com',
                'noreply@ticketmaster.com',
            ],
            'subject_keywords' => [
                'tickets available',
                'on sale now',
                'presale',
                'general sale',
                'limited availability',
                'last chance',
            ],
            'body_keywords' => [
                'sports event',
                'tickets',
                'availability',
                'purchase',
                'stadium',
                'arena',
            ],
        ],

        'stubhub' => [
            'from_patterns' => [
                '*@stubhub.com',
                '*@email.stubhub.com',
                'noreply@stubhub.com',
            ],
            'subject_keywords' => [
                'tickets posted',
                'price drop',
                'new listings',
                'favorites',
                'recommended',
            ],
            'body_keywords' => [
                'sports',
                'event',
                'tickets',
                'listing',
                'price',
            ],
        ],

        'viagogo' => [
            'from_patterns' => [
                '*@viagogo.com',
                '*@email.viagogo.com',
                'notifications@viagogo.com',
            ],
            'subject_keywords' => [
                'new tickets',
                'price alert',
                'availability',
                'recommendation',
            ],
            'body_keywords' => [
                'sports',
                'tickets',
                'event',
                'available',
            ],
        ],

        'seatgeek' => [
            'from_patterns' => [
                '*@seatgeek.com',
                '*@emails.seatgeek.com',
                'noreply@seatgeek.com',
            ],
            'subject_keywords' => [
                'deal alert',
                'price drop',
                'tickets available',
                'new listings',
            ],
            'body_keywords' => [
                'sports',
                'event',
                'tickets',
                'deal',
            ],
        ],

        'tickpick' => [
            'from_patterns' => [
                '*@tickpick.com',
                '*@email.tickpick.com',
            ],
            'subject_keywords' => [
                'no fees',
                'tickets available',
                'deal',
                'price alert',
            ],
            'body_keywords' => [
                'sports',
                'tickets',
                'event',
                'no fees',
            ],
        ],

        'generic' => [
            'from_patterns' => [
                '*@*.com',
            ],
            'subject_keywords' => [
                'ticket',
                'sports',
                'event',
                'game',
                'match',
                'championship',
                'season',
                'playoff',
            ],
            'body_keywords' => [
                'sports',
                'event',
                'ticket',
                'stadium',
                'arena',
                'game',
                'match',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security settings for IMAP connections and email processing.
    |
    */

    'security' => [

        // Enable SSL/TLS verification
        'verify_peer' => env('IMAP_VERIFY_PEER', true),
        'verify_peer_name' => env('IMAP_VERIFY_PEER_NAME', true),

        // Allow self-signed certificates (use only for testing)
        'allow_self_signed' => env('IMAP_ALLOW_SELF_SIGNED', false),

        // Connection timeout in seconds
        'connection_timeout' => env('IMAP_CONNECTION_TIMEOUT', 30),

        // Read timeout in seconds
        'read_timeout' => env('IMAP_READ_TIMEOUT', 60),

        // Maximum email size to process (in MB)
        'max_email_size_mb' => env('IMAP_MAX_EMAIL_SIZE_MB', 10),

        // Enable rate limiting for email processing
        'rate_limiting' => env('IMAP_RATE_LIMITING', true),
        'rate_limit_per_minute' => env('IMAP_RATE_LIMIT_PER_MINUTE', 60),

    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Logging configuration for IMAP email processing activities.
    |
    */

    'logging' => [

        // Enable detailed logging
        'enabled' => env('IMAP_LOGGING_ENABLED', true),

        // Log level (debug, info, warning, error)
        'level' => env('IMAP_LOGGING_LEVEL', 'info'),

        // Log channel
        'channel' => env('IMAP_LOGGING_CHANNEL', 'imap'),

        // Log successful email processing
        'log_processed' => env('IMAP_LOG_PROCESSED', true),

        // Log failed email processing
        'log_failures' => env('IMAP_LOG_FAILURES', true),

        // Log connection events
        'log_connections' => env('IMAP_LOG_CONNECTIONS', true),

        // Maximum log file size (in MB)
        'max_log_size_mb' => env('IMAP_MAX_LOG_SIZE_MB', 100),

    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Queue configuration for processing emails asynchronously.
    |
    */

    'queue' => [

        // Enable queue processing
        'enabled' => env('IMAP_QUEUE_ENABLED', true),

        // Queue connection to use
        'connection' => env('IMAP_QUEUE_CONNECTION', 'redis'),

        // Queue name for email processing
        'name' => env('IMAP_QUEUE_NAME', 'email-processing'),

        // Number of retry attempts for failed jobs
        'retry_attempts' => env('IMAP_QUEUE_RETRY_ATTEMPTS', 3),

        // Delay between retry attempts (in seconds)
        'retry_delay' => env('IMAP_QUEUE_RETRY_DELAY', 60),

    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache configuration for email processing and connection optimization.
    |
    */

    'cache' => [

        // Enable caching
        'enabled' => env('IMAP_CACHE_ENABLED', true),

        // Cache driver
        'driver' => env('IMAP_CACHE_DRIVER', 'redis'),

        // Cache key prefix
        'prefix' => env('IMAP_CACHE_PREFIX', 'hdtickets_imap'),

        // Connection cache TTL (in minutes)
        'connection_ttl' => env('IMAP_CONNECTION_CACHE_TTL', 30),

        // Email metadata cache TTL (in minutes)
        'email_ttl' => env('IMAP_EMAIL_CACHE_TTL', 60),

        // Processed emails cache TTL (in hours)
        'processed_ttl' => env('IMAP_PROCESSED_CACHE_TTL', 24),

    ],

];
