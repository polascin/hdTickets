<?php declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sms' => [
        'default' => env('SMS_SERVICE', 'log'), // twilio, nexmo, log

        'twilio' => [
            'sid'             => env('TWILIO_ACCOUNT_SID'),
            'token'           => env('TWILIO_AUTH_TOKEN'),
            'from'            => env('TWILIO_FROM_NUMBER'),
            'webhook_url'     => env('TWILIO_WEBHOOK_URL'),
            'status_callback' => env('TWILIO_STATUS_CALLBACK'),
        ],

        'nexmo' => [
            'key'         => env('NEXMO_API_KEY'),
            'secret'      => env('NEXMO_API_SECRET'),
            'from'        => env('NEXMO_FROM_NUMBER'),
            'webhook_url' => env('NEXMO_WEBHOOK_URL'),
        ],
    ],

    'pusher' => [
        'app_id'    => env('PUSHER_APP_ID'),
        'key'       => env('PUSHER_APP_KEY'),
        'secret'    => env('PUSHER_APP_SECRET'),
        'cluster'   => env('PUSHER_APP_CLUSTER', 'mt1'),
        'host'      => env('PUSHER_HOST'),
        'port'      => env('PUSHER_PORT', 443),
        'scheme'    => env('PUSHER_SCHEME', 'https'),
        'encrypted' => TRUE,
    ],

    'websockets' => [
        'ssl' => [
            'local_cert' => env('LARAVEL_WEBSOCKETS_SSL_LOCAL_CERT'),
            'local_pk'   => env('LARAVEL_WEBSOCKETS_SSL_LOCAL_PK'),
            'passphrase' => env('LARAVEL_WEBSOCKETS_SSL_PASSPHRASE'),
        ],
        'port' => env('LARAVEL_WEBSOCKETS_PORT', 6001),
        'host' => env('LARAVEL_WEBSOCKETS_HOST', '0.0.0.0'),
    ],

    'github' => [
        'client_id'     => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect'      => env('GITHUB_REDIRECT_URI'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URL'),
    ],

    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('FACEBOOK_REDIRECT_URI'),
    ],

    'stripe' => [
        'key'    => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'twilio' => [
        'sid'   => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from'  => env('TWILIO_FROM'),
    ],

    'paypal' => [
        'mode'    => env('PAYPAL_MODE', 'sandbox'),
        'sandbox' => [
            'client_id'     => env('PAYPAL_SANDBOX_CLIENT_ID'),
            'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET'),
        ],
        'live' => [
            'client_id'     => env('PAYPAL_LIVE_CLIENT_ID'),
            'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET'),
        ],
        'webhook_id'      => env('PAYPAL_WEBHOOK_ID'),
        'receiver_email'  => env('PAYPAL_RECEIVER_EMAIL', 'walter.csoelle@gmail.com'),
        'monthly_plan_id' => env('PAYPAL_MONTHLY_PLAN_ID'),
        'annual_plan_id'  => env('PAYPAL_ANNUAL_PLAN_ID'),
        // Legacy config for backwards compatibility
        'client_id'   => env('PAYPAL_CLIENT_ID', env('PAYPAL_SANDBOX_CLIENT_ID')),
        'secret'      => env('PAYPAL_SECRET', env('PAYPAL_SANDBOX_CLIENT_SECRET')),
        'environment' => env('PAYPAL_ENVIRONMENT', env('PAYPAL_MODE', 'sandbox')),
    ],

    /*
    |--------------------------------------------------------------------------
    | CAPTCHA Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for automated CAPTCHA solving services used for
    | sports events monitoring and scraping operations.
    |
    */

    'captcha' => [
        'enabled'          => env('CAPTCHA_ENABLED', FALSE),
        'service'          => env('CAPTCHA_SERVICE', '2captcha'),
        'timeout'          => env('CAPTCHA_TIMEOUT', 120),
        'polling_interval' => env('CAPTCHA_POLLING_INTERVAL', 5),

        '2captcha' => [
            'api_key'  => env('TWOCAPTCHA_API_KEY'),
            'soft_id'  => env('TWOCAPTCHA_SOFT_ID'),
            'base_url' => 'http://2captcha.com',
        ],

        'anticaptcha' => [
            'api_key'  => env('ANTICAPTCHA_API_KEY'),
            'base_url' => 'https://api.anti-captcha.com',
        ],

        'captchasolver' => [
            'api_key'  => env('CAPTCHASOLVER_API_KEY'),
            'base_url' => 'https://api.captchasolver.com',
        ],

        'deathbycaptcha' => [
            'username' => env('DEATHBYCAPTCHA_USERNAME'),
            'password' => env('DEATHBYCAPTCHA_PASSWORD'),
            'base_url' => 'http://api.deathbycaptcha.com',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Load Testing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for load testing and performance monitoring.
    |
    */

    'load_testing' => [
        'enabled'              => env('LOAD_TESTING_ENABLED', FALSE),
        'max_concurrent_users' => env('MAX_CONCURRENT_USERS', 1000),
        'connection_pool_size' => env('CONNECTION_POOL_SIZE', 50),
        'request_timeout'      => env('REQUEST_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security-related service configurations.
    |
    */

    'security' => [
        'encryption_key_rotation_enabled'   => env('ENCRYPTION_KEY_ROTATION_ENABLED', FALSE),
        'security_audit_enabled'            => env('SECURITY_AUDIT_ENABLED', TRUE),
        'account_health_monitoring_enabled' => env('ACCOUNT_HEALTH_MONITORING_ENABLED', TRUE),
        'failed_login_threshold'            => env('FAILED_LOGIN_THRESHOLD', 5),
        'account_lockout_duration'          => env('ACCOUNT_LOCKOUT_DURATION', 1800),
        'suspicious_activity_threshold'     => env('SUSPICIOUS_ACTIVITY_THRESHOLD', 10),
        'account_validation_interval'       => env('ACCOUNT_VALIDATION_INTERVAL', 3600),
    ],

    /*
    |--------------------------------------------------------------------------
    | WebPush Configuration
    |--------------------------------------------------------------------------
    |
    | WebPush service configuration for browser push notifications.
    | VAPID keys are used for application identification.
    |
    */

    'webpush' => [
        'vapid' => [
            'subject'     => env('WEBPUSH_VAPID_SUBJECT', config('app.url')),
            'public_key'  => env('WEBPUSH_VAPID_PUBLIC_KEY'),
            'private_key' => env('WEBPUSH_VAPID_PRIVATE_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA Configuration
    |--------------------------------------------------------------------------
    |
    | Google reCAPTCHA v3 configuration for HD Tickets login security.
    | Used to detect and prevent automated attacks on the authentication system.
    |
    */

    'recaptcha' => [
        'enabled'       => env('RECAPTCHA_ENABLED', FALSE),
        'site_key'      => env('RECAPTCHA_SITE_KEY', ''),
        'secret_key'    => env('RECAPTCHA_SECRET_KEY', ''),
        'minimum_score' => env('RECAPTCHA_MINIMUM_SCORE', 0.5),
        'timeout'       => env('RECAPTCHA_TIMEOUT', 5),

        // Actions for different parts of the application
        'actions' => [
            'login'          => 'hd_tickets_login',
            'register'       => 'hd_tickets_register',
            'password_reset' => 'hd_tickets_password_reset',
            'contact'        => 'hd_tickets_contact',
            'purchase'       => 'hd_tickets_purchase',
        ],

        // Risk assessment thresholds
        'risk_thresholds' => [
            'high'   => 0.7,  // Always challenge
            'medium' => 0.4,  // Challenge during suspicious activity
            'low'    => 0.2,   // Challenge only after failed attempts
        ],
    ],
];
