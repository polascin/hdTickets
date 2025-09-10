<?php declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Override;

class CssTimestampServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    /**
     * Register
     */
    #[Override]
    public function register(): void
    {
        // Register the CSS timestamp helper as a singleton
        $this->app->singleton('css.timestamp', fn ($app): object => new class() {
            /**
             * Generate a timestamped CSS URL
             */
            /**
             * Generate
             */
            public function generate(string $path): string
            {
                // Check if it's an external URL
                if (filter_var($path, FILTER_VALIDATE_URL)) {
                    return $this->addTimestampToUrl($path, time());
                }

                // Handle local assets
                $fullPath = public_path($path);

                // Check if file exists and get modification time using filemtime
                if (File::exists($fullPath)) {
                    $timestamp = filemtime($fullPath) ?: File::lastModified($fullPath);
                } else {
                    // If file doesn't exist, use current time as fallback
                    $timestamp = time();
                }

                // Use Laravel's asset helper for proper URL generation
                $assetUrl = asset($path);

                return $this->addTimestampToUrl($assetUrl, $timestamp);
            }

            /**
             * Add timestamp parameter to URL
             */
            /**
             * AddTimestampToUrl
             */
            private function addTimestampToUrl(string $url, int $timestamp): string
            {
                $separator = str_contains($url, '?') ? '&' : '?';

                return $url . $separator . 'v=' . $timestamp;
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    /**
     * Boot
     */
    public function boot(): void
    {
        // Register the custom Blade directive
        Blade::directive('cssWithTimestamp', fn ($expression): string => "<?php echo app('css.timestamp')->generate({$expression}); ?>");
    }
}
