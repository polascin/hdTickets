<?php declare(strict_types=1);

if (! function_exists('css_with_timestamp')) {
    /**
     * Generate a timestamped CSS URL
     */
    function css_with_timestamp(string $path): string
    {
        return app('css.timestamp')->generate($path);
    }
}

if (! function_exists('css_timestamp')) {
    /**
     * Generate a timestamped CSS URL for use in PHP code outside of Blade templates
     * This function provides the same functionality as the @cssWithTimestamp Blade directive
     * and can be used in controllers, middleware, or other PHP files.
     *
     * @param string $path The CSS file path (relative to public directory) or external URL
     *
     * @return string The CSS URL with timestamp parameter for cache busting
     *
     * @example
     * // For local CSS files
     * $cssUrl = css_timestamp('css/app.css');
     * // Result: http://yoursite.com/css/app.css?v=1672531200
     *
     * // For external CSS files
     * $cssUrl = css_timestamp('https://cdn.example.com/styles.css');
     * // Result: https://cdn.example.com/styles.css?v=1672531200
     */
    function css_timestamp(string $path): string
    {
        return app('css.timestamp')->generate($path);
    }
}
