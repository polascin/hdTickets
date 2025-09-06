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
    | Audit Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Comprehensive audit logging for compliance and security tracking
    |
    */
    'audit' => [
        'enabled' => env('SECURITY_AUDIT_ENABLED', true),
        'retention_days' => env('SECURITY_AUDIT_RETENTION_DAYS', 365),
        'sensitive_actions' => [
            'login',
            'logout',
            'password_change',
            'role_change',
            'permission_grant',
            'permission_revoke',
            'user_create',
            'user_update',
            'user_delete',
            'account_lock',
            'account_unlock',
            'data_export',
            'system_config_change',
            'security_setting_change',
            'audit_log_access',
            'backup_create',
            'backup_restore',
        ],
        'exclude_actions' => [
            'view',
            'read',
            'list',
        ],
        'log_request_data' => env('SECURITY_AUDIT_LOG_REQUEST_DATA', false),
        'anonymize_after_days' => env('SECURITY_AUDIT_ANONYMIZE_DAYS', 90),
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
        
        // Enhanced Threat Detection Rules
        'failed_login_threshold' => env('SECURITY_FAILED_LOGIN_THRESHOLD', 5),
        'login_rate_limit' => env('SECURITY_LOGIN_RATE_LIMIT', 10), // per minute
        'suspicious_ip_threshold' => env('SECURITY_SUSPICIOUS_IP_THRESHOLD', 3),
        'account_lockout_duration' => env('SECURITY_ACCOUNT_LOCKOUT_DURATION', 30), // minutes
        'incident_escalation_threshold' => env('SECURITY_INCIDENT_ESCALATION_THRESHOLD', 3),
        'anomaly_detection_window' => env('SECURITY_ANOMALY_DETECTION_WINDOW', 24), // hours
        'brute_force_detection_window' => env('SECURITY_BRUTE_FORCE_WINDOW', 60), // minutes
        'account_enumeration_threshold' => env('SECURITY_ACCOUNT_ENUMERATION_THRESHOLD', 10),
        'coordinated_attack_threshold' => env('SECURITY_COORDINATED_ATTACK_THRESHOLD', 3),
        'distributed_login_threshold' => env('SECURITY_DISTRIBUTED_LOGIN_THRESHOLD', 3),

        // Threat Scoring
        'base_threat_scores' => [
            'login_failed' => 20,
            'multiple_failed_logins' => 40,
            'suspicious_login' => 60,
            'brute_force_detected' => 80,
            'account_takeover_attempt' => 90,
            'unauthorized_access_attempt' => 70,
            'privilege_escalation_attempt' => 85,
            'data_breach_attempt' => 95,
            'malicious_request' => 75,
            'bot_detected' => 50,
            'rate_limit_exceeded' => 30,
            'account_enumeration_detected' => 65,
            'coordinated_attack_detected' => 85,
            'distributed_login_attempt' => 70,
        ],

        // Automated Response Thresholds
        'response_thresholds' => [
            'monitoring_increase' => 40,
            'additional_auth_required' => 60,
            'ip_temporary_block' => 70,
            'account_temporary_lock' => 80,
            'incident_creation' => 80,
            'alert_notification' => 75,
            'critical_escalation' => 90,
        ],

        // Original alert thresholds
        'alert_thresholds'             => [
            'critical_events'     => 1,
            'high_events'         => 5,
            'failed_logins'       => 5,
            'suspicious_patterns' => 10,
        ],

        // IP Blocking Configuration
        'ip_blocking' => [
            'default_duration' => 60, // minutes
            'escalation_durations' => [
                1 => 60,   // First block: 1 hour
                2 => 240,  // Second block: 4 hours
                3 => 1440, // Third block: 24 hours
                4 => 10080, // Fourth+ block: 7 days
            ],
            'whitelist' => [
                '127.0.0.1',
                '::1',
                // Add trusted IP ranges here
            ],
        ],

        // Incident Management
        'incidents' => [
            'auto_assignment' => [
                'enabled' => env('SECURITY_AUTO_ASSIGNMENT_ENABLED', true),
                'rules' => [
                    'critical' => 'security_team_lead',
                    'high' => 'security_analyst',
                    'medium' => 'security_analyst',
                    'low' => 'junior_analyst',
                ],
            ],
            'escalation_rules' => [
                'critical_response_time' => 15, // minutes
                'high_response_time' => 60, // minutes
                'medium_response_time' => 240, // minutes
                'low_response_time' => 1440, // minutes (24 hours)
            ],
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
