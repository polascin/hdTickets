<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration for Sports Events Monitoring System
    |--------------------------------------------------------------------------
    |
    | This configuration file contains security settings specific to the
    | comprehensive sports events entry tickets monitoring, scraping and
    | purchase system.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy (CSP)
    |--------------------------------------------------------------------------
    |
    | Define allowed sources for various content types to prevent XSS attacks
    | and other code injection attacks.
    |
    */
    'csp' => [
        'default-src' => ["'self'"],
        'script-src' => [
            "'self'",
            "'unsafe-inline'", // Required for Laravel Blade templates
            "'unsafe-eval'", // Required for some JavaScript libraries
            'https://cdn.jsdelivr.net',
            'https://unpkg.com',
            'https://cdnjs.cloudflare.com',
        ],
        'style-src' => [
            "'self'",
            "'unsafe-inline'", // Required for CSS styling
            'https://cdn.jsdelivr.net',
            'https://unpkg.com',
            'https://cdnjs.cloudflare.com',
            'https://fonts.googleapis.com',
        ],
        'font-src' => [
            "'self'",
            'https://fonts.gstatic.com',
            'https://cdn.jsdelivr.net',
        ],
        'img-src' => [
            "'self'",
            'data:',
            'https:',
            'blob:',
        ],
        'connect-src' => [
            "'self'",
            'ws:',
            'wss:',
        ],
        'frame-src' => ["'none'"],
        'frame-ancestors' => ["'none'"],
        'object-src' => ["'none'"],
        'base-uri' => ["'self'"],
        'form-action' => ["'self'"],
        'upgrade-insecure-requests' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    |
    | Configure security headers to protect against various attacks
    |
    */
    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=(), payment=(), usb=()',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
    ],

    /*
    |--------------------------------------------------------------------------
    | Input Validation
    |--------------------------------------------------------------------------
    |
    | Configuration for input validation and sanitization
    |
    */
    'input_validation' => [
        'max_string_length' => 10000,
        'max_array_depth' => 5,
        'max_file_size' => 5 * 1024 * 1024, // 5MB
        'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png', 'gif'],
        'dangerous_patterns' => [
            // SQL Injection
            '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|EXECUTE|UNION|SCRIPT)\b)/i',
            // XSS
            '/<script[^>]*>.*?<\/script>/si',
            '/javascript:/i',
            '/on\w+\s*=/i',
            // Command injection
            '/[;&|`$(){}[\]]/i',
            // Path traversal
            '/\.\.[\/\\\\]/i',
            // PHP code injection
            '/(<\?php|\?>)/i',
            // HTML injection
            '/<iframe[^>]*>/i',
            '/<object[^>]*>/i',
            '/<embed[^>]*>/i',
            '/<form[^>]*>/i',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Security
    |--------------------------------------------------------------------------
    |
    | Configuration for API security, rate limiting, and authentication
    |
    */
    'api' => [
        'rate_limits' => [
            'default' => 1000, // requests per hour
            'burst' => 60, // requests per minute
            'scraping' => 500, // scraping requests per hour
            'purchase' => 100, // purchase requests per hour
        ],
        'key_length' => 40,
        'signature_algorithm' => 'sha256',
        'timestamp_tolerance' => 300, // 5 minutes
        'require_signature' => env('API_REQUIRE_SIGNATURE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sports Events Security Specific Settings
    |--------------------------------------------------------------------------
    |
    | Security settings specific to sports events monitoring and ticket purchasing
    |
    */
    'sports_events' => [
        'allowed_sports' => [
            'football', 'basketball', 'baseball', 'soccer', 'tennis',
            'cricket', 'rugby', 'motorsport', 'other'
        ],
        'max_tickets_per_request' => 100,
        'max_price_per_ticket' => 99999.99,
        'trusted_platforms' => [
            'ticketmaster', 'stubhub', 'viagogo', 'seetickets',
            'ticketek', 'eventim'
        ],
        'max_scraping_frequency' => 60, // seconds between scraping requests
        'purchase_security' => [
            'require_2fa' => true,
            'max_purchase_amount' => 10000, // per transaction
            'verification_timeout' => 300, // 5 minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    |
    | Enhanced session security settings
    |
    */
    'session' => [
        'regenerate_on_login' => true,
        'invalidate_on_logout' => true,
        'max_concurrent_sessions' => 3,
        'fingerprint_validation' => true,
        'ip_validation' => env('SESSION_IP_VALIDATION', false),
        'user_agent_validation' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging and Monitoring
    |--------------------------------------------------------------------------
    |
    | Security logging and monitoring configuration
    |
    */
    'logging' => [
        'log_all_requests' => env('SECURITY_LOG_ALL_REQUESTS', false),
        'log_failed_auth' => true,
        'log_suspicious_activity' => true,
        'log_retention_days' => 90,
        'alert_thresholds' => [
            'failed_logins' => 5,
            'suspicious_requests' => 10,
            'rate_limit_exceeded' => 3,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CSRF Protection
    |--------------------------------------------------------------------------
    |
    | Enhanced CSRF protection settings
    |
    */
    'csrf' => [
        'regenerate_token_frequency' => 3600, // 1 hour
        'additional_validation' => true,
        'session_fingerprinting' => true,
        'request_frequency_check' => true,
        'suspicious_pattern_detection' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    |
    | Security settings for file uploads
    |
    */
    'file_upload' => [
        'max_size' => 5 * 1024 * 1024, // 5MB
        'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'gif'],
        'allowed_mime_types' => [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
        ],
        'scan_for_malware' => env('SCAN_UPLOADS_FOR_MALWARE', false),
        'quarantine_suspicious_files' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption Settings
    |--------------------------------------------------------------------------
    |
    | Additional encryption settings for sensitive data
    |
    */
    'encryption' => [
        'algorithm' => 'AES-256-CBC',
        'encrypt_sensitive_fields' => true,
        'key_rotation_enabled' => false,
        'key_rotation_frequency' => 30, // days
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Filtering and Geolocation
    |--------------------------------------------------------------------------
    |
    | IP-based security controls
    |
    */
    'ip_filtering' => [
        'enable_geoblocking' => env('ENABLE_GEOBLOCKING', false),
        'allowed_countries' => ['US', 'CA', 'GB', 'AU', 'DE', 'FR'],
        'blocked_ips' => [],
        'trusted_proxies' => [],
        'max_requests_per_ip' => 100, // per hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication
    |--------------------------------------------------------------------------
    |
    | 2FA security settings
    |
    */
    'two_factor' => [
        'required_for_admin' => true,
        'required_for_purchase' => true,
        'backup_codes_count' => 8,
        'recovery_window' => 300, // 5 minutes
        'rate_limit_attempts' => 5,
    ],
];
