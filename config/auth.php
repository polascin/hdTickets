<?php declare(strict_types=1);

use App\Models\User;

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard'     => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver'   => 'passport',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => User::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => 10800,

    /*
    |--------------------------------------------------------------------------
    | Enhanced Login Features
    |--------------------------------------------------------------------------
    |
    | Configuration for enhanced login features including security,
    | UX improvements, and progressive validation.
    |
    */

    'enhanced_login' => env('AUTH_ENHANCED_LOGIN', TRUE),

    'security' => [
        'max_failed_attempts'   => env('AUTH_MAX_FAILED_ATTEMPTS', 5),
        'lockout_duration'      => env('AUTH_LOCKOUT_DURATION', 900), // 15 minutes
        'device_fingerprinting' => env('AUTH_DEVICE_FINGERPRINTING', TRUE),
        'geolocation_tracking'  => env('AUTH_GEOLOCATION_TRACKING', TRUE),
        'high_risk_countries'   => [
            'CN', 'RU', 'IR', 'KP', 'PK', 'BD', 'NG', 'IN',
        ],
    ],

    'ux_features' => [
        'progressive_validation'  => env('AUTH_PROGRESSIVE_VALIDATION', TRUE),
        'biometric_auth'          => env('AUTH_BIOMETRIC_AUTH', TRUE),
        'password_strength_meter' => env('AUTH_PASSWORD_STRENGTH_METER', TRUE),
        'session_warnings'        => env('AUTH_SESSION_WARNINGS', TRUE),
    ],

    // Comprehensive Login Configuration
    'comprehensive_login' => env('AUTH_COMPREHENSIVE_LOGIN', TRUE),
];
