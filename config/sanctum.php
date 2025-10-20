<?php

declare(strict_types=1);

use Laravel\Sanctum\Sanctum;

return [
    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Requests from the following domains / hosts will receive stateful API
    | authentication cookies. Typically, these should include your local
    | and production domains which access your API via a frontend SPA.
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        Sanctum::currentApplicationUrlWithPort(),
    ))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    |
    | This array contains the authentication guards that will be checked when
    | Sanctum is trying to authenticate a request. If none of these guards
    | are able to authenticate the request, Sanctum will use the bearer
    | token that's present on an incoming request for authentication.
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes until an issued token will be
    | considered expired. If this value is null, personal access tokens do
    | not expire. This won't tweak the lifetime of first-party sessions.
    |
    */

    'expiration' => env('SANCTUM_TOKEN_EXPIRATION', NULL),

    /*
    |--------------------------------------------------------------------------
    | Token Prefix
    |--------------------------------------------------------------------------
    |
    | Sanctum can prefix new tokens in order to take advantage of numerous
    | security scanning initiatives maintained by open source platforms
    | that notify developers if they commit tokens into repositories.
    |
    | See: https://docs.github.com/en/code-security/secret-scanning/about-secret-scanning
    |
    */

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    |
    | When authenticating your first-party SPA with Sanctum you may need to
    | customize some of the middleware Sanctum uses while processing the
    | request. You may change the middleware listed below as required.
    |
    */

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies'      => App\Http\Middleware\EncryptCookies::class,
        'validate_csrf_token'  => App\Http\Middleware\VerifyCsrfToken::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Personal Access Token Model
    |--------------------------------------------------------------------------
    |
    | When using Sanctum's personal access token features, we'll need to know
    | which Eloquent model should be used to retrieve your access tokens. Of
    | course, it is often just the "PersonalAccessToken" model but you may
    | use whatever you like. The model you specify should be an instance of
    | `Laravel\Sanctum\PersonalAccessToken` or an extension of it.
    |
    */

    'personal_access_token_model' => Laravel\Sanctum\PersonalAccessToken::class,

    /*
    |--------------------------------------------------------------------------
    | HD Tickets API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration specific to HD Tickets API authentication and security.
    |
    */

    'api_tokens' => [
        'default_abilities'  => ['*'],
        'expiration_minutes' => env('SANCTUM_API_TOKEN_EXPIRATION', 525600), // 1 year
        'refresh_threshold'  => env('SANCTUM_REFRESH_THRESHOLD', 43200), // 30 days
    ],

    'rate_limiting' => [
        'enabled'       => env('SANCTUM_RATE_LIMITING', TRUE),
        'max_attempts'  => env('SANCTUM_MAX_ATTEMPTS', 60),
        'decay_minutes' => env('SANCTUM_DECAY_MINUTES', 1),
    ],

    'security' => [
        'secure_cookies' => env('SANCTUM_SECURE_COOKIES', env('APP_ENV') === 'production'),
        'same_site'      => env('SANCTUM_SAME_SITE', 'lax'),
        'domain'         => env('SANCTUM_DOMAIN', NULL),
    ],
];
