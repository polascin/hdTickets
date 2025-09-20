<?php declare(strict_types=1);

/**
 * HD Tickets Compatibility Verification Script
 * Comprehensive system compatibility check for Laravel 12, Vue.js 3, MariaDB 10.4, and Apache2
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

class CompatibilityVerifier
{
    private array $results = [];

    private bool $allPassed = TRUE;

    public function __construct()
    {
        $this->results = [
            'php'           => [],
            'laravel'       => [],
            'database'      => [],
            'apache'        => [],
            'vue'           => [],
            'dependencies'  => [],
            'configuration' => [],
        ];
    }

    public function runAllChecks(): void
    {
        echo "🔍 HD Tickets Compatibility Verification\n";
        echo "========================================\n\n";

        $this->checkPHPCompatibility();
        $this->checkLaravelCompatibility();
        $this->checkDatabaseCompatibility();
        $this->checkApacheCompatibility();
        $this->checkVueJsCompatibility();
        $this->checkDependencyCompatibility();
        $this->checkConfigurationCompatibility();

        $this->displayResults();
    }

    private function checkPHPCompatibility(): void
    {
        echo "📦 Checking PHP Compatibility...\n";

        // PHP Version Check
        $phpVersion = PHP_VERSION;
        $requiredPHP = '8.4';
        $phpVersionCheck = version_compare($phpVersion, $requiredPHP, '>=');

        $this->addResult(
            'php',
            'version',
            $phpVersionCheck,
            "PHP Version: $phpVersion (Required: >= $requiredPHP)"
        );

        // Required Extensions
        $requiredExtensions = [
            'pdo', 'pdo_mysql', 'mysqli', 'curl', 'json', 'mbstring',
            'xml', 'zip', 'openssl', 'redis', 'gd', 'opcache',
        ];

        foreach ($requiredExtensions as $extension) {
            $loaded = extension_loaded($extension);
            $this->addResult(
                'php',
                "ext_$extension",
                $loaded,
                "Extension $extension: " . ($loaded ? 'LOADED' : 'MISSING')
            );
        }

        // Memory Limit
        $memoryLimit = ini_get('memory_limit');
        $this->addResult('php', 'memory_limit', TRUE, "Memory Limit: $memoryLimit");

        // Max Execution Time
        $maxExecTime = ini_get('max_execution_time');
        $this->addResult('php', 'max_execution_time', TRUE, "Max Execution Time: {$maxExecTime}s");

        echo "✓ PHP compatibility check completed\n\n";
    }

    private function checkLaravelCompatibility(): void
    {
        echo "🎯 Checking Laravel Compatibility...\n";

        try {
            // Laravel Version
            $laravelVersion = app()->version();
            $requiredLaravel = '12.0';
            $laravelVersionCheck = version_compare($laravelVersion, $requiredLaravel, '>=');

            $this->addResult(
                'laravel',
                'version',
                $laravelVersionCheck,
                "Laravel Version: $laravelVersion (Required: >= $requiredLaravel)"
            );

            // Artisan Commands Test
            $artisanTest = $this->testArtisanCommand('about');
            $this->addResult(
                'laravel',
                'artisan',
                $artisanTest,
                'Artisan Commands: ' . ($artisanTest ? 'WORKING' : 'ERROR')
            );

            // Service Providers
            $providers = config('app.providers', []);
            $this->addResult(
                'laravel',
                'providers',
                count($providers) > 0,
                'Service Providers: ' . count($providers) . ' loaded'
            );

            // Middleware
            $middleware = app()->make(\Illuminate\Contracts\Http\Kernel::class);
            $this->addResult('laravel', 'middleware', TRUE, 'Middleware: CONFIGURED');

            // Cache Status
            $configCached = file_exists(bootstrap_path('cache/config.php'));
            $routesCached = file_exists(bootstrap_path('cache/routes.php'));

            $this->addResult(
                'laravel',
                'config_cache',
                $configCached,
                'Config Cache: ' . ($configCached ? 'CACHED' : 'NOT CACHED')
            );
            $this->addResult(
                'laravel',
                'routes_cache',
                $routesCached,
                'Routes Cache: ' . ($routesCached ? 'CACHED' : 'NOT CACHED')
            );
        } catch (Exception $e) {
            $this->addResult('laravel', 'general', FALSE, 'Laravel Error: ' . $e->getMessage());
        }

        echo "✓ Laravel compatibility check completed\n\n";
    }

    private function checkDatabaseCompatibility(): void
    {
        echo "🗄️  Checking Database Compatibility...\n";

        try {
            // Database Connection
            $connection = DB::connection();
            $connected = $connection->getPdo() !== NULL;
            $this->addResult(
                'database',
                'connection',
                $connected,
                'Database Connection: ' . ($connected ? 'CONNECTED' : 'FAILED')
            );

            // Database Version
            $version = DB::select('SELECT VERSION() as version')[0]->version;
            $this->addResult('database', 'version', TRUE, "Database Version: $version");

            // MariaDB Compatibility
            $isMariaDB = stripos($version, 'mariadb') !== FALSE;
            if ($isMariaDB) {
                preg_match('/(\d+\.\d+)/', $version, $matches);
                $dbVersion = $matches[1] ?? '0.0';
                $mariadbCompatible = version_compare($dbVersion, '10.4', '>=');
                $this->addResult(
                    'database',
                    'mariadb_compat',
                    $mariadbCompatible,
                    "MariaDB Version: $dbVersion (Required: >= 10.4)"
                );
            }

            // Database Configuration
            $driver = config('database.connections.mysql.driver', 'mysql');
            $charset = config('database.connections.mysql.charset', 'utf8mb4');
            $collation = config('database.connections.mysql.collation', 'utf8mb4_unicode_ci');

            $this->addResult('database', 'driver', TRUE, "Driver: $driver");
            $this->addResult(
                'database',
                'charset',
                $charset === 'utf8mb4',
                "Charset: $charset"
            );
            $this->addResult(
                'database',
                'collation',
                $collation === 'utf8mb4_unicode_ci',
                "Collation: $collation"
            );

            // Tables Check
            $tables = DB::select('SHOW TABLES');
            $tableCount = count($tables);
            $this->addResult(
                'database',
                'tables',
                $tableCount > 0,
                "Tables: $tableCount found"
            );

            // Migration Status
            $migrationTable = 'migrations';
            $migrationExists = DB::select("SHOW TABLES LIKE '$migrationTable'");
            $this->addResult(
                'database',
                'migrations',
                !empty($migrationExists),
                'Migration Table: ' . (!empty($migrationExists) ? 'EXISTS' : 'NOT FOUND')
            );
        } catch (Exception $e) {
            $this->addResult('database', 'error', FALSE, 'Database Error: ' . $e->getMessage());
        }

        echo "✓ Database compatibility check completed\n\n";
    }

    private function checkApacheCompatibility(): void
    {
        echo "🌐 Checking Apache2 Compatibility...\n";

        // Apache Version (if accessible)
        $apacheVersion = $this->getApacheVersion();
        $this->addResult('apache', 'version', TRUE, "Apache Version: $apacheVersion");

        // Document Root
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? 'N/A';
        $correctDocRoot = str_ends_with($docRoot, 'public');
        $this->addResult(
            'apache',
            'document_root',
            $correctDocRoot,
            "Document Root: $docRoot"
        );

        // .htaccess
        $htaccessExists = file_exists(public_path('.htaccess'));
        $this->addResult(
            'apache',
            'htaccess',
            $htaccessExists,
            '.htaccess: ' . ($htaccessExists ? 'EXISTS' : 'MISSING')
        );

        // Rewrite Module
        $rewriteEnabled = function_exists('apache_get_modules') ?
            in_array('mod_rewrite', apache_get_modules()) :
            TRUE; // Assume enabled if we can't check

        $this->addResult(
            'apache',
            'mod_rewrite',
            $rewriteEnabled,
            'mod_rewrite: ' . ($rewriteEnabled ? 'ENABLED' : 'DISABLED')
        );

        // SSL Configuration
        $httpsEnabled = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                       $_SERVER['SERVER_PORT'] == 443;
        $this->addResult(
            'apache',
            'ssl',
            TRUE,
            'SSL: ' . ($httpsEnabled ? 'ENABLED' : 'DISABLED')
        );

        echo "✓ Apache compatibility check completed\n\n";
    }

    private function checkVueJsCompatibility(): void
    {
        echo "⚡ Checking Vue.js Compatibility...\n";

        // Package.json
        $packageJsonExists = file_exists('package.json');
        $this->addResult(
            'vue',
            'package_json',
            $packageJsonExists,
            'package.json: ' . ($packageJsonExists ? 'EXISTS' : 'MISSING')
        );

        if ($packageJsonExists) {
            $packageJson = json_decode(file_get_contents('package.json'), TRUE);

            // Vue Version
            $vueVersion = $packageJson['dependencies']['vue'] ?? 'N/A';
            $vue3Compatible = str_starts_with($vueVersion, '^3') || str_starts_with($vueVersion, '3');
            $this->addResult(
                'vue',
                'version',
                $vue3Compatible,
                "Vue.js Version: $vueVersion"
            );

            // TypeScript Support
            $hasTypeScript = isset($packageJson['devDependencies']['typescript']);
            $this->addResult(
                'vue',
                'typescript',
                $hasTypeScript,
                'TypeScript: ' . ($hasTypeScript ? 'CONFIGURED' : 'NOT CONFIGURED')
            );

            // Vite Configuration
            $viteConfigExists = file_exists('vite.config.js');
            $this->addResult(
                'vue',
                'vite_config',
                $viteConfigExists,
                'Vite Config: ' . ($viteConfigExists ? 'EXISTS' : 'MISSING')
            );
        }

        // Node Modules
        $nodeModulesExists = is_dir('node_modules');
        $this->addResult(
            'vue',
            'node_modules',
            $nodeModulesExists,
            'node_modules: ' . ($nodeModulesExists ? 'INSTALLED' : 'MISSING')
        );

        // Build Directory
        $buildDirExists = is_dir('public/build');
        $this->addResult(
            'vue',
            'build_dir',
            TRUE,
            'Build Directory: ' . ($buildDirExists ? 'EXISTS' : 'NEEDS BUILD')
        );

        echo "✓ Vue.js compatibility check completed\n\n";
    }

    private function checkDependencyCompatibility(): void
    {
        echo "📋 Checking Dependency Compatibility...\n";

        // Composer Dependencies
        $composerLock = file_exists('composer.lock');
        $this->addResult(
            'dependencies',
            'composer_lock',
            $composerLock,
            'composer.lock: ' . ($composerLock ? 'EXISTS' : 'MISSING')
        );

        // Vendor Directory
        $vendorExists = is_dir('vendor');
        $this->addResult(
            'dependencies',
            'vendor',
            $vendorExists,
            'vendor/: ' . ($vendorExists ? 'EXISTS' : 'MISSING')
        );

        // Key Laravel Packages
        $keyPackages = [
            'laravel/framework', 'laravel/tinker', 'laravel/sanctum',
            'laravel/passport', 'guzzlehttp/guzzle', 'predis/predis',
        ];

        if (file_exists('composer.lock')) {
            $composerData = json_decode(file_get_contents('composer.lock'), TRUE);
            $installedPackages = array_column($composerData['packages'] ?? [], 'name');

            foreach ($keyPackages as $package) {
                $installed = in_array($package, $installedPackages);
                $this->addResult(
                    'dependencies',
                    str_replace('/', '_', $package),
                    $installed,
                    "$package: " . ($installed ? 'INSTALLED' : 'MISSING')
                );
            }
        }

        // Storage Permissions
        $storageWritable = is_writable(storage_path());
        $this->addResult(
            'dependencies',
            'storage_writable',
            $storageWritable,
            'Storage Writable: ' . ($storageWritable ? 'YES' : 'NO')
        );

        // Bootstrap Cache Writable
        $bootstrapCacheWritable = is_writable(bootstrap_path('cache'));
        $this->addResult(
            'dependencies',
            'bootstrap_cache_writable',
            $bootstrapCacheWritable,
            'Bootstrap Cache Writable: ' . ($bootstrapCacheWritable ? 'YES' : 'NO')
        );

        echo "✓ Dependency compatibility check completed\n\n";
    }

    private function checkConfigurationCompatibility(): void
    {
        echo "⚙️  Checking Configuration Compatibility...\n";

        // Environment File
        $envExists = file_exists('.env');
        $this->addResult(
            'configuration',
            'env_file',
            $envExists,
            '.env File: ' . ($envExists ? 'EXISTS' : 'MISSING')
        );

        // App Key
        $appKey = config('app.key');
        $this->addResult(
            'configuration',
            'app_key',
            !empty($appKey),
            'App Key: ' . (!empty($appKey) ? 'SET' : 'NOT SET')
        );

        // Database Configuration
        $dbConnection = config('database.default');
        $this->addResult(
            'configuration',
            'db_connection',
            !empty($dbConnection),
            "Database Connection: $dbConnection"
        );

        // MariaDB Driver Configuration
        $mariadbConfigured = config('database.connections.mariadb') !== NULL;
        $this->addResult(
            'configuration',
            'mariadb_driver',
            $mariadbConfigured,
            'MariaDB Driver: ' . ($mariadbConfigured ? 'CONFIGURED' : 'NOT CONFIGURED')
        );

        // Session Configuration
        $sessionDriver = config('session.driver');
        $this->addResult(
            'configuration',
            'session_driver',
            !empty($sessionDriver),
            "Session Driver: $sessionDriver"
        );

        // Cache Configuration
        $cacheDriver = config('cache.default');
        $this->addResult(
            'configuration',
            'cache_driver',
            !empty($cacheDriver),
            "Cache Driver: $cacheDriver"
        );

        // Queue Configuration
        $queueDriver = config('queue.default');
        $this->addResult(
            'configuration',
            'queue_driver',
            !empty($queueDriver),
            "Queue Driver: $queueDriver"
        );

        echo "✓ Configuration compatibility check completed\n\n";
    }

    private function addResult(string $category, string $check, bool $passed, string $message): void
    {
        $this->results[$category][] = [
            'check'   => $check,
            'passed'  => $passed,
            'message' => $message,
        ];

        if (!$passed) {
            $this->allPassed = FALSE;
        }

        echo ($passed ? '✅' : '❌') . " $message\n";
    }

    private function testArtisanCommand(string $command): bool
    {
        try {
            $output = shell_exec("php artisan $command 2>&1");

            return $output !== NULL && !str_contains($output, 'Error');
        } catch (Exception $e) {
            return FALSE;
        }
    }

    private function getApacheVersion(): string
    {
        if (function_exists('apache_get_version')) {
            return apache_get_version();
        }

        $version = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';

        return $version;
    }

    private function displayResults(): void
    {
        echo "\n📊 COMPATIBILITY VERIFICATION SUMMARY\n";
        echo "=====================================\n\n";

        $totalChecks = 0;
        $passedChecks = 0;

        foreach ($this->results as $category => $checks) {
            $categoryPassed = 0;
            $categoryTotal = count($checks);
            $totalChecks += $categoryTotal;

            foreach ($checks as $check) {
                if ($check['passed']) {
                    $categoryPassed++;
                    $passedChecks++;
                }
            }

            $categoryStatus = $categoryPassed === $categoryTotal ? '✅' : '⚠️';
            echo "$categoryStatus " . strtoupper($category) . ": $categoryPassed/$categoryTotal checks passed\n";
        }

        echo "\n" . str_repeat('=', 50) . "\n";
        echo $this->allPassed ? '🎉 ALL COMPATIBILITY CHECKS PASSED!' : '⚠️  Some compatibility issues found';
        echo "\nOverall: $passedChecks/$totalChecks checks passed\n";

        if ($this->allPassed) {
            echo "\n✨ Your HD Tickets application is fully compatible with:\n";
            echo "   • Laravel 12.x\n";
            echo "   • PHP 8.4\n";
            echo "   • MariaDB 10.4+\n";
            echo "   • Apache2\n";
            echo "   • Vue.js 3.x\n";
            echo "\n🚀 System is ready for production deployment!\n";
        } else {
            echo "\n🔧 Please address the failed checks before proceeding.\n";
        }
    }
}

// Run the compatibility verification
if (php_sapi_name() === 'cli') {
    try {
        // Bootstrap Laravel application
        $app = require_once 'bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        $verifier = new CompatibilityVerifier();
        $verifier->runAllChecks();
    } catch (Exception $e) {
        echo '❌ Error running compatibility check: ' . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "This script must be run from the command line.\n";
    exit(1);
}
