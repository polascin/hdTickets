<?php declare(strict_types=1);

/**
 * PHPStan bootstrap adjustments.
 * - Disables performance logging to avoid permission issues during static analysis.
 * - Defines a PHPSTAN_RUNNING constant for conditional logic if needed.
 */

// Force-disable performance logging (channel will downgrade to single/null-safe behavior)
putenv('PERFORMANCE_LOGGING=false');
$_ENV['PERFORMANCE_LOGGING'] = 'false';
$_SERVER['PERFORMANCE_LOGGING'] = 'false';

if (!defined('PHPSTAN_RUNNING')) {
    define('PHPSTAN_RUNNING', TRUE);
}

// Optionally mark application as running under static analysis
putenv('APP_ENV=testing'); // Ensure minimal side-effects

// Capture the application instance so we can safely register stubs
$app = require __DIR__ . '/bootstrap/app.php';

// -------------------------------------------------------------------------
// Container Stubs for Static Analysis
// -------------------------------------------------------------------------
// Some bindings may not be resolvable in the trimmed/static context that
// Larastan boots under (especially if conditionally registered by env).
// Provide lightweight stand-ins so PHPStan doesn't crash with internal errors.

// Stub the 'files' binding if not present
if (!$app->bound('files')) {
    $app->singleton('files', function () {
        // Use real Filesystem if available; otherwise a minimal anonymous stub
        if (class_exists('Illuminate\\Filesystem\\Filesystem')) {
            $filesystemClass = 'Illuminate\\Filesystem\\Filesystem';

            return new $filesystemClass();
        }

        return new class() {
            public function get($path)
            {
                return '';
            }

            public function exists($path)
            {
                return FALSE;
            }
        };
    });
}

// Stub the 'view' binding if not present
if (!$app->bound('view')) {
    $app->singleton('view', function () {
        return new class() {
            public function make(...$args)
            {
                return $this;
            }

            public function exists($view)
            {
                return TRUE;
            }

            public function share(...$args)
            {
                return $this;
            }

            public function composer(...$args)
            {
                return $this;
            }

            public function addNamespace(...$args)
            {
                return $this;
            }

            public function file(...$args)
            {
                return $this;
            }

            public function render()
            {
                return '';
            }

            public function __toString()
            {
                return '';
            }
        };
    });
}

// Stub the 'queue' binding if not present
if (!$app->bound('queue')) {
    $app->singleton('queue', function () {
        return new class() {
            public function push()
            {
                return $this;
            }

            public function later()
            {
                return $this;
            }

            public function connection()
            {
                return $this;
            }

            public function size()
            {
                return 0;
            }

            public function driver()
            {
                return $this;
            }
        };
    });
}

// Stub the 'config' binding if not present
if (!$app->bound('config')) {
    $app->singleton('config', function () {
        // Use real Config Repository if available
        if (class_exists('Illuminate\\Config\\Repository')) {
            $configRepositoryClass = 'Illuminate\\Config\\Repository';

            return new $configRepositoryClass([]);
        }
        // Fallback stub: implement interface only if it exists to avoid undefined type errors
        if (interface_exists('Illuminate\\Contracts\\Config\\Repository')) {
            // @phpstan-ignore-next-line Undefined type provided only during runtime when Laravel is installed.
            return new class() implements \Illuminate\Contracts\Config\Repository {
                public function has($key)
                {
                    return FALSE;
                }

                public function get($key, $default = NULL)
                {
                    return $default;
                }

                public function getMany($keys)
                {
                    return [];
                }

                public function set($key, $value = NULL)
                {
                    return $this;
                }

                public function prepend($key, $value)
                {
                    return $this;
                }

                public function push($key, $value)
                {
                    return $this;
                }
            };
        }

        return new class() {
            public function has($key)
            {
                return FALSE;
            }

            public function get($key, $default = NULL)
            {
                return $default;
            }

            public function getMany($keys)
            {
                return [];
            }

            public function set($key, $value = NULL)
            {
                return $this;
            }

            public function prepend($key, $value)
            {
                return $this;
            }

            public function push($key, $value)
            {
                return $this;
            }
        };
    });
}

// Stub the 'session' binding if not present
if (!$app->bound('session')) {
    $app->singleton('session', function () {
        return new class() {
            public function getId()
            {
                return 'stub-session-id';
            }

            public function get($key, $default = NULL)
            {
                return $default;
            }

            public function put($key, $value)
            {
                return $this;
            }

            public function has($key)
            {
                return FALSE;
            }

            public function driver()
            {
                return $this;
            }
        };
    });
}

// Return the app instance (harmless for PHPStan; ignored otherwise)
return $app;
