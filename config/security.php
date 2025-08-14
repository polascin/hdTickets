<?php declare(strict_types=1);

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
        'script-src'  => [
            "'self'",
            "'unsafe-inline'", // Required for Laravel Blade templates
            "'unsafe-eval'", // Required for some JavaScript libraries
            'https://cdn.jsdelivr.net',
            'https://unpkg.com',
            'https://cdnjs.cloudflare.com',
            'https://cdn.tailwindcss.com',
        ],
        'style-src' => [
            "'self'",
            "'unsafe-inline'", // Required for CSS styling
            'https://cdn.jsdelivr.net',
            'https://unpkg.com',
            'https://cdnjs.cloudflare.com',
            'https://fonts.googleapis.com',
            'https://cdn.tailwindcss.com',
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
        'frame-src'                 => ["'none'"],
        'frame-ancestors'           => ["'none'"],
        'object-src'                => ["'none'"],
        'base-uri'                  => ["'self'"],
        'form-action'               => ["'self'"],
        'upgrade-insecure-requests' => TRUE,
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
        'X-Content-Type-Options'    => 'nosniff',
        'X-Frame-Options'           => 'DENY',
        'X-XSS-Protection'          => '1; mode=block',
        'Referrer-Policy'           => 'strict-origin-when-cross-origin',
        'Permissions-Policy'        => 'geolocation=(), microphone=(), camera=(), payment=(), usb=()',
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
        'max_string_length'  => 10000,
        'max_array_depth'    => 5,
        'max_file_size'      => 5 * 1024 * 1024, // 5MB
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
            'default'  => 1000, // requests per hour
            'burst'    => 60, // requests per minute
            'scraping' => 500, // scraping requests per hour
            'purchase' => 100, // purchase requests per hour
        ],
        'key_length'          => 40,
        'signature_algorithm' => 'sha256',
        'timestamp_tolerance' => 300, // 5 minutes
        'require_signature'   => env('API_REQUIRE_SIGNATURE', FALSE),
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
            'cricket', 'rugby', 'motorsport', 'other',
        ],
        'max_tickets_per_request' => 100,
        'max_price_per_ticket'    => 99999.99,
        'trusted_platforms'       => [
            'ticketmaster', 'stubhub', 'viagogo', 'seetickets',
            'ticketek', 'eventim',
        ],
        'max_scraping_frequency' => 60, // seconds between scraping requests
        'purchase_security'      => [
            'require_2fa'          => TRUE,
            'max_purchase_amount'  => 10000, // per transaction
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
        'regenerate_on_login'     => TRUE,
        'invalidate_on_logout'    => TRUE,
        'max_concurrent_sessions' => 3,
        'fingerprint_validation'  => TRUE,
        'ip_validation'           => env('SESSION_IP_VALIDATION', FALSE),
        'user_agent_validation'   => TRUE,
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
        'log_all_requests'        => env('SECURITY_LOG_ALL_REQUESTS', FALSE),
        'log_failed_auth'         => TRUE,
        'log_suspicious_activity' => TRUE,
        'log_retention_days'      => 90,
        'alert_thresholds'        => [
            'failed_logins'       => 5,
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
        'regenerate_token_frequency'   => 3600, // 1 hour
        'additional_validation'        => TRUE,
        'session_fingerprinting'       => TRUE,
        'request_frequency_check'      => TRUE,
        'suspicious_pattern_detection' => TRUE,
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
        'max_size'           => 5 * 1024 * 1024, // 5MB
        'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'gif'],
        'allowed_mime_types' => [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
        ],
        'scan_for_malware'            => env('SCAN_UPLOADS_FOR_MALWARE', FALSE),
        'quarantine_suspicious_files' => TRUE,
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
        'algorithm'                   => 'AES-256-CBC',
        'encrypt_sensitive_fields'    => TRUE,
        'key_rotation_enabled'        => TRUE,
        'key_rotation_frequency'      => 30, // days
        'field_level_encryption'      => TRUE,
        'database_encryption_at_rest' => env('DB_ENCRYPTION_AT_REST', FALSE),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Security
    |--------------------------------------------------------------------------
    |
    | Enhanced authentication security settings
    |
    */
    'authentication' => [
        'oauth2_enabled'         => TRUE,
        'jwt_expiry'             => 86400, // 24 hours
        'biometric_auth_enabled' => env('BIOMETRIC_AUTH_ENABLED', FALSE),
        'device_fingerprinting'  => TRUE,
        'anomaly_detection'      => TRUE,
        'progressive_delays'     => TRUE,
        'max_failed_attempts'    => 5,
        'lockout_duration'       => 900, // 15 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Role-Based Access Control
    |--------------------------------------------------------------------------
    |
    | RBAC configuration settings
    |
    */
    'rbac' => [
        'permission_caching'         => TRUE,
        'cache_duration'             => 3600, // 1 hour
        'dynamic_roles_enabled'      => TRUE,
        'permission_inheritance'     => TRUE,
        'resource_based_permissions' => TRUE,
        'log_permission_checks'      => env('LOG_PERMISSION_CHECKS', FALSE),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Monitoring
    |--------------------------------------------------------------------------
    |
    | Intrusion detection and monitoring settings
    |
    */
    'monitoring' => [
        'intrusion_detection_enabled'  => TRUE,
        'real_time_monitoring'         => TRUE,
        'automated_response'           => env('AUTOMATED_SECURITY_RESPONSE', TRUE),
        'threat_correlation'           => TRUE,
        'behavioral_analysis'          => TRUE,
        'geographic_anomaly_detection' => TRUE,
        'alert_thresholds'             => [
            'critical_events'     => 1,
            'high_events'         => 5,
            'failed_logins'       => 5,
            'suspicious_patterns' => 10,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Vulnerability Scanning
    |--------------------------------------------------------------------------
    |
    | Automated vulnerability scanning configuration
    |
    */
    'vulnerability_scanning' => [
        'enabled'        => env('VULNERABILITY_SCANNING_ENABLED', TRUE),
        'scan_frequency' => 'weekly',
        'scan_types'     => [
            'configuration'    => TRUE,
            'dependencies'     => TRUE,
            'database'         => TRUE,
            'web_application'  => TRUE,
            'file_permissions' => TRUE,
        ],
        'auto_remediation'      => FALSE,
        'report_generation'     => TRUE,
        'compliance_frameworks' => ['gdpr', 'iso27001', 'pci_dss', 'sox'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Security
    |--------------------------------------------------------------------------
    |
    | Data protection and privacy settings
    |
    */
    'data_security' => [
        'classification_enabled'  => TRUE,
        'auto_masking'            => TRUE,
        'tokenization_enabled'    => TRUE,
        'secure_backups'          => TRUE,
        'data_retention_policies' => TRUE,
        'integrity_validation'    => TRUE,
        'audit_data_access'       => TRUE,
    ],

    /*
    |--------------------------------------------------------------------------
    | Compliance Reporting
    |--------------------------------------------------------------------------
    |
    | Compliance and audit reporting configuration
    |
    */
    'compliance' => [
        'automated_reports' => TRUE,
        'report_frequency'  => 'monthly',
        'frameworks'        => [
            'gdpr' => [
                'enabled'             => TRUE,
                'data_subject_rights' => TRUE,
                'consent_management'  => TRUE,
                'breach_notification' => TRUE,
            ],
            'pci_dss' => [
                'enabled'                    => env('PCI_DSS_COMPLIANCE', FALSE),
                'cardholder_data_protection' => TRUE,
                'network_security'           => TRUE,
            ],
            'iso27001' => [
                'enabled'                         => TRUE,
                'information_security_management' => TRUE,
                'risk_assessment'                 => TRUE,
            ],
            'sox' => [
                'enabled'                      => env('SOX_COMPLIANCE', FALSE),
                'financial_reporting_controls' => TRUE,
                'audit_trails'                 => TRUE,
            ],
        ],
        'alert_on_non_compliance'  => TRUE,
        'minimum_compliance_score' => 85,
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
        'enable_geoblocking'  => env('ENABLE_GEOBLOCKING', FALSE),
        'allowed_countries'   => ['US', 'CA', 'GB', 'AU', 'DE', 'FR'],
        'blocked_ips'         => [],
        'trusted_proxies'     => [],
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
        'required_for_admin'    => TRUE,
        'required_for_purchase' => TRUE,
        'backup_codes_count'    => 8,
        'recovery_window'       => 300, // 5 minutes
        'rate_limit_attempts'   => 5,
    ],
];
