<?php declare(strict_types=1);

namespace App\Http;

use App\Http\Middleware\ActivityLoggerMiddleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AgentMiddleware;
use App\Http\Middleware\Api\CheckApiRole;
use App\Http\Middleware\ApiSecurityMiddleware;
use App\Http\Middleware\AppendCssTimestamp;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\ComprehensiveLoggingMiddleware;
use App\Http\Middleware\CustomerMiddleware;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\EnhancedLoginSecurity;
use App\Http\Middleware\IosErrorTracker;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\PreventScraperWebAccess;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\ScraperMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\ValidateSignature;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\VerifyPayPalWebhook;
use App\Http\Middleware\WelcomePageMiddleware;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Bootstrap\BootProviders;
use Illuminate\Foundation\Bootstrap\RegisterFacades;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Foundation\Bootstrap\SetRequestForConsole;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use App\Http\Middleware\RequestIdMiddleware;

class Kernel extends HttpKernel
{
    /**
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected $bootstrappers = [
        RegisterFacades::class,
        SetRequestForConsole::class,
        RegisterProviders::class,
        BootProviders::class,
    ];

    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \\App\\Http\\Middleware\\TrustHosts::class,
        RequestIdMiddleware::class,
        IosErrorTracker::class, // Track iOS errors early in the stack
        SecurityHeadersMiddleware::class, // Move to top
        TrustProxies::class,
        HandleCors::class,
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            ActivityLoggerMiddleware::class,
            PreventScraperWebAccess::class,
            // Append timestamps to all local CSS links automatically
            AppendCssTimestamp::class,
        ],

        'api' => [
            EnsureFrontendRequestsAreStateful::class,
            ComprehensiveLoggingMiddleware::class,
            ApiSecurityMiddleware::class,
            ThrottleRequests::class . ':api',
            SubstituteBindings::class,
        ],

        // Role-based middleware groups
        'admin' => [
            'web',
            'auth',
            'admin',
            'activity.log',
        ],

        'agent' => [
            'web',
            'auth',
            'agent',
            'activity.log',
        ],

        'scraper' => [
            'auth',
            'throttle:scraper',
        ],

        'customer' => [
            'web',
            'auth',
            'customer.role',
            'verified',
        ],
    ];

    /**
     * The application's middleware aliases.
     *
     * Aliases may be used instead of class names to conveniently assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth'                    => Authenticate::class,
        'auth.basic'              => AuthenticateWithBasicAuth::class,
        'auth.session'            => AuthenticateSession::class,
        'cache.headers'           => SetCacheHeaders::class,
        'can'                     => Authorize::class,
        'guest'                   => RedirectIfAuthenticated::class,
        'password.confirm'        => RequirePassword::class,
        'precognitive'            => HandlePrecognitiveRequests::class,
        'recaptcha'               => Middleware\RecaptchaMiddleware::class,
        'signed'                  => ValidateSignature::class,
        'throttle'                => ThrottleRequests::class,
        'verified'                => EnsureEmailIsVerified::class,
        'activity.log'            => ActivityLoggerMiddleware::class,
        'admin'                   => AdminMiddleware::class,
        'agent'                   => AgentMiddleware::class,
        'scraper'                 => ScraperMiddleware::class,
        'customer.role'           => CustomerMiddleware::class,
        'prevent.scraper.web'     => PreventScraperWebAccess::class,
        'role'                    => RoleMiddleware::class,
        'api.role'                => CheckApiRole::class,
        'api.security'            => ApiSecurityMiddleware::class,
        'security.headers'        => SecurityHeadersMiddleware::class,
        'enhanced.login.security' => EnhancedLoginSecurity::class,
        'verify.paypal.webhook'   => VerifyPayPalWebhook::class,
        'welcome.page'            => WelcomePageMiddleware::class,
        'ios.error.tracker'       => IosErrorTracker::class,
    ];
}
