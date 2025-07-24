<?php

namespace App\Services\Scraping;

use App\Services\ProxyRotationService;
use App\Services\TicketApis\BaseWebScrapingClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class PluginBasedScraperManager
{
    protected $plugins = [];
    protected $proxyService;
    protected $enabledPlugins = [];

    public function __construct(ProxyRotationService $proxyService)
    {
        $this->proxyService = $proxyService;
        $this->loadPlugins();
    }

    /**
     * Load and register scraper plugins
     */
    protected function loadPlugins(): void
    {
        $this->enabledPlugins = config('scraping.enabled_plugins', [
            'ticketmaster',
            'manchester_united',
            'stubhub',
            'seatgeek',
            'viagogo',
            'tickpick',
            'funzone'
        ]);

        // Auto-discover plugins
        $this->discoverPlugins();
        
        // Load plugin configurations
        $this->loadPluginConfigurations();
    }

    /**
     * Auto-discover scraper plugins
     */
    protected function discoverPlugins(): void
    {
        $pluginPath = app_path('Services/Scraping/Plugins');
        
        if (!is_dir($pluginPath)) {
            return;
        }

        $pluginFiles = glob($pluginPath . '/*Plugin.php');
        
        foreach ($pluginFiles as $file) {
            $className = 'App\\Services\\Scraping\\Plugins\\' . basename($file, '.php');
            
            if (class_exists($className)) {
                $pluginName = strtolower(str_replace('Plugin', '', basename($file, '.php')));
                
                if (in_array($pluginName, $this->enabledPlugins)) {
                    try {
                        $this->plugins[$pluginName] = new $className($this->proxyService);
                        Log::info("Loaded scraper plugin: {$pluginName}");
                    } catch (Exception $e) {
                        Log::error("Failed to load scraper plugin {$pluginName}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Load plugin configurations from cache/config
     */
    protected function loadPluginConfigurations(): void
    {
        foreach ($this->plugins as $name => $plugin) {
            $config = Cache::get("plugin_config_{$name}", config("scraping.plugins.{$name}", []));
            
            if (method_exists($plugin, 'configure')) {
                $plugin->configure($config);
            }
        }
    }

    /**
     * Register a new plugin
     */
    public function registerPlugin(string $name, ScraperPluginInterface $plugin): void
    {
        $this->plugins[$name] = $plugin;
        Log::info("Registered scraper plugin: {$name}");
    }

    /**
     * Get all registered plugins
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * Get specific plugin
     */
    public function getPlugin(string $name): ?ScraperPluginInterface
    {
        return $this->plugins[$name] ?? null;
    }

    /**
     * Run scraping across all enabled plugins
     */
    public function scrapeAll(array $criteria): array
    {
        $results = [];
        $totalResults = 0;
        $errors = [];

        foreach ($this->plugins as $name => $plugin) {
            if (!$plugin->isEnabled()) {
                continue;
            }

            try {
                Log::info("Starting scraping with plugin: {$name}", $criteria);
                
                $pluginResults = $plugin->scrape($criteria);
                
                $results[$name] = [
                    'status' => 'success',
                    'results' => $pluginResults,
                    'count' => count($pluginResults),
                    'plugin_info' => $plugin->getInfo()
                ];
                
                $totalResults += count($pluginResults);
                
                Log::info("Plugin {$name} scraping completed", [
                    'results_found' => count($pluginResults)
                ]);
                
            } catch (Exception $e) {
                $errors[] = [
                    'plugin' => $name,
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ];
                
                $results[$name] = [
                    'status' => 'error',
                    'error' => $e->getMessage(),
                    'results' => [],
                    'count' => 0
                ];
                
                Log::error("Plugin {$name} scraping failed", [
                    'error' => $e->getMessage(),
                    'criteria' => $criteria
                ]);
            }
        }

        return [
            'summary' => [
                'total_plugins' => count($this->plugins),
                'successful_plugins' => count($results) - count($errors),
                'failed_plugins' => count($errors),
                'total_results' => $totalResults
            ],
            'results' => $results,
            'errors' => $errors
        ];
    }

    /**
     * Run scraping with specific plugin
     */
    public function scrapeWithPlugin(string $pluginName, array $criteria): array
    {
        $plugin = $this->getPlugin($pluginName);
        
        if (!$plugin) {
            throw new Exception("Plugin '{$pluginName}' not found");
        }

        if (!$plugin->isEnabled()) {
            throw new Exception("Plugin '{$pluginName}' is disabled");
        }

        return $plugin->scrape($criteria);
    }

    /**
     * Enable plugin
     */
    public function enablePlugin(string $name): void
    {
        if (isset($this->plugins[$name])) {
            $this->plugins[$name]->enable();
            Log::info("Plugin {$name} enabled");
        }
    }

    /**
     * Disable plugin
     */
    public function disablePlugin(string $name): void
    {
        if (isset($this->plugins[$name])) {
            $this->plugins[$name]->disable();
            Log::info("Plugin {$name} disabled");
        }
    }

    /**
     * Get plugin statistics
     */
    public function getPluginStats(): array
    {
        $stats = [];
        
        foreach ($this->plugins as $name => $plugin) {
            $stats[$name] = [
                'info' => $plugin->getInfo(),
                'enabled' => $plugin->isEnabled(),
                'last_run' => Cache::get("plugin_last_run_{$name}"),
                'success_rate' => Cache::get("plugin_success_rate_{$name}", 0),
                'total_runs' => Cache::get("plugin_total_runs_{$name}", 0),
                'avg_results' => Cache::get("plugin_avg_results_{$name}", 0)
            ];
        }
        
        return $stats;
    }

    /**
     * Update plugin statistics
     */
    public function updatePluginStats(string $name, bool $success, int $resultCount): void
    {
        $totalRuns = Cache::get("plugin_total_runs_{$name}", 0) + 1;
        $successCount = Cache::get("plugin_success_count_{$name}", 0) + ($success ? 1 : 0);
        $totalResults = Cache::get("plugin_total_results_{$name}", 0) + $resultCount;
        
        $successRate = ($successCount / $totalRuns) * 100;
        $avgResults = $totalResults / $totalRuns;
        
        Cache::put("plugin_last_run_{$name}", now()->toISOString(), 3600 * 24);
        Cache::put("plugin_total_runs_{$name}", $totalRuns, 3600 * 24 * 30);
        Cache::put("plugin_success_count_{$name}", $successCount, 3600 * 24 * 30);
        Cache::put("plugin_success_rate_{$name}", $successRate, 3600 * 24 * 30);
        Cache::put("plugin_total_results_{$name}", $totalResults, 3600 * 24 * 30);
        Cache::put("plugin_avg_results_{$name}", $avgResults, 3600 * 24 * 30);
    }

    /**
     * Configure plugin
     */
    public function configurePlugin(string $name, array $config): void
    {
        if (isset($this->plugins[$name])) {
            if (method_exists($this->plugins[$name], 'configure')) {
                $this->plugins[$name]->configure($config);
                Cache::put("plugin_config_{$name}", $config, 3600 * 24);
                Log::info("Plugin {$name} configuration updated");
            }
        }
    }

    /**
     * Test plugin functionality
     */
    public function testPlugin(string $name): array
    {
        $plugin = $this->getPlugin($name);
        
        if (!$plugin) {
            return [
                'status' => 'error',
                'message' => "Plugin '{$name}' not found"
            ];
        }

        try {
            $testCriteria = [
                'keyword' => 'test',
                'max_results' => 1
            ];
            
            $startTime = microtime(true);
            $results = $plugin->scrape($testCriteria);
            $duration = (microtime(true) - $startTime) * 1000;
            
            return [
                'status' => 'success',
                'plugin_info' => $plugin->getInfo(),
                'test_results' => count($results),
                'duration_ms' => round($duration, 2),
                'enabled' => $plugin->isEnabled()
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'plugin_info' => $plugin->getInfo(),
                'enabled' => $plugin->isEnabled()
            ];
        }
    }

    /**
     * Get plugin performance metrics
     */
    public function getPluginMetrics(string $name): array
    {
        $plugin = $this->getPlugin($name);
        
        if (!$plugin) {
            return [];
        }

        return [
            'info' => $plugin->getInfo(),
            'enabled' => $plugin->isEnabled(),
            'statistics' => [
                'last_run' => Cache::get("plugin_last_run_{$name}"),
                'total_runs' => Cache::get("plugin_total_runs_{$name}", 0),
                'success_rate' => Cache::get("plugin_success_rate_{$name}", 0),
                'avg_results' => Cache::get("plugin_avg_results_{$name}", 0),
                'total_results' => Cache::get("plugin_total_results_{$name}", 0)
            ],
            'recent_errors' => Cache::get("plugin_recent_errors_{$name}", []),
            'performance' => [
                'avg_response_time' => Cache::get("plugin_avg_response_time_{$name}", 0),
                'last_response_time' => Cache::get("plugin_last_response_time_{$name}", 0)
            ]
        ];
    }

    /**
     * Clear plugin cache and statistics
     */
    public function clearPluginCache(string $name): void
    {
        $cacheKeys = [
            "plugin_config_{$name}",
            "plugin_last_run_{$name}",
            "plugin_total_runs_{$name}",
            "plugin_success_count_{$name}",
            "plugin_success_rate_{$name}",
            "plugin_total_results_{$name}",
            "plugin_avg_results_{$name}",
            "plugin_recent_errors_{$name}",
            "plugin_avg_response_time_{$name}",
            "plugin_last_response_time_{$name}"
        ];
        
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
        
        Log::info("Cleared cache for plugin: {$name}");
    }

    /**
     * Get overall scraping health status
     */
    public function getHealthStatus(): array
    {
        $totalPlugins = count($this->plugins);
        $enabledPlugins = 0;
        $healthyPlugins = 0;
        $recentErrors = 0;

        foreach ($this->plugins as $name => $plugin) {
            if ($plugin->isEnabled()) {
                $enabledPlugins++;
                
                $successRate = Cache::get("plugin_success_rate_{$name}", 0);
                if ($successRate > 70) { // Consider healthy if success rate > 70%
                    $healthyPlugins++;
                }
                
                $errors = Cache::get("plugin_recent_errors_{$name}", []);
                $recentErrors += count($errors);
            }
        }

        $healthPercentage = $enabledPlugins > 0 ? ($healthyPlugins / $enabledPlugins) * 100 : 0;
        
        return [
            'overall_health' => $healthPercentage,
            'status' => $healthPercentage > 80 ? 'healthy' : ($healthPercentage > 50 ? 'warning' : 'critical'),
            'total_plugins' => $totalPlugins,
            'enabled_plugins' => $enabledPlugins,
            'healthy_plugins' => $healthyPlugins,
            'recent_errors' => $recentErrors,
            'timestamp' => now()->toISOString()
        ];
    }
}
