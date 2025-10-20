<?php declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Override;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    /**
     * Boot
     */
    #[Override]
    public function boot(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));

        // Login limiter: sensitive endpoint with per-email+IP key
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');
            $key = $request->ip() . '|' . $email;

            return [
                Limit::perMinute(15)->by($key),
            ];
        });

        // Specific rate limiter for dashboard realtime endpoint
        RateLimiter::for('dashboard-realtime', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Registration limiter: allow up to 12 attempts per minute, keyed by IP+email when available
        RateLimiter::for('register', function (Request $request) {
            $key = $request->ip() . '|' . (string) $request->input('email');

            return Limit::perMinute(12)->by($key);
        });

        $this->routes(function (): void {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api-enhanced-alerts.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('web')
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->group(base_path('routes/auth.php'));
        });
    }
}
