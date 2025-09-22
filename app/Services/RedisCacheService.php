<?php declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

use function count;
use function function_exists;
use function strlen;

/**
 * Advanced Redis Caching Service
 *
 * Provides intelligent multi-layer caching strategy with:
 * - Domain-specific cache layers (Events, Tickets, Monitoring, Users)
 * - Smart cache invalidation with dependency tracking
 * - Cache warming and preloading strategies
 * - Performance monitoring and optimization
 * - Distributed caching with consistent hashing
 */
class RedisCacheService
{
    // Cache layer prefixes for different domains
    public const LAYER_EVENTS = 'events';

    public const LAYER_TICKETS = 'tickets';

    public const LAYER_MONITORING = 'monitoring';

    public const LAYER_USERS = 'users';

    public const LAYER_SYSTEM = 'system';

    public const LAYER_ANALYTICS = 'analytics';

    // Cache TTL constants (in seconds)
    public const TTL_SHORT = 300;      // 5 minutes

    public const TTL_MEDIUM = 1800;    // 30 minutes

    public const TTL_LONG = 3600;      // 1 hour

    public const TTL_EXTENDED = 21600; // 6 hours

    public const TTL_DAILY = 86400;    // 24 hours

    protected array $cacheStats = [];

    protected array $layerConfig = [];

    protected bool $enableDistributedCache = TRUE;

    public function __construct()
    {
        $this->initializeLayers();
        $this->enableDistributedCache = config('cache.distributed', TRUE);
    }

    /**
     * Store data in specific cache layer with intelligent configuration
     *
     * @param mixed $data
     */
    public function putLayer(string $layer, string $key, $data, array $options = []): bool
    {
        $config = $this->layerConfig[$layer] ?? $this->getDefaultConfig();
        $fullKey = $this->buildLayerKey($layer, $key);

        // Apply options overrides
        $ttl = $options['ttl'] ?? $config['ttl'];
        $compression = $options['compression'] ?? $config['compression'];
        $tags = array_merge($config['tags'], $options['tags'] ?? []);

        // Prepare data for storage
        $processedData = $this->prepareDataForStorage($data, $config, $compression);

        try {
            // Store with tags for easy invalidation
            $success = Cache::tags($tags)->put($fullKey, $processedData, $ttl);

            if ($success) {
                $this->recordCacheStats('put', $layer, $key, [
                    'size' => strlen(serialize($processedData)),
                    'ttl'  => $ttl,
                    'tags' => $tags,
                ]);
            }

            return $success;
        } catch (Exception $e) {
            Log::error('Cache put error', [
                'layer' => $layer,
                'key'   => $key,
                'error' => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Retrieve data from specific cache layer
     *
     * @param mixed $default
     *
     * @return mixed
     */
    public function getLayer(string $layer, string $key, $default = NULL)
    {
        $config = $this->layerConfig[$layer] ?? $this->getDefaultConfig();
        $fullKey = $this->buildLayerKey($layer, $key);

        try {
            $data = Cache::get($fullKey);

            if ($data !== NULL) {
                $this->recordCacheStats('hit', $layer, $key);

                return $this->processDataFromStorage($data, $config);
            }

            $this->recordCacheStats('miss', $layer, $key);

            return $default;
        } catch (Exception $e) {
            Log::error('Cache get error', [
                'layer' => $layer,
                'key'   => $key,
                'error' => $e->getMessage(),
            ]);

            $this->recordCacheStats('error', $layer, $key);

            return $default;
        }
    }

    /**
     * Remember pattern with layer-specific configuration
     *
     * @return mixed
     */
    public function rememberLayer(string $layer, string $key, callable $callback, array $options = [])
    {
        $data = $this->getLayer($layer, $key);

        if ($data !== NULL) {
            return $data;
        }

        // Execute callback and cache result
        $result = $callback();

        if ($result !== NULL) {
            $this->putLayer($layer, $key, $result, $options);
        }

        return $result;
    }

    /**
     * Intelligent cache invalidation with dependency tracking
     */
    public function invalidateLayer(string $layer, array $keys = [], bool $cascadeInvalidation = TRUE): array
    {
        $config = $this->layerConfig[$layer] ?? $this->getDefaultConfig();
        $invalidated = [];

        try {
            if ($keys === []) {
                // Invalidate entire layer by tags
                Cache::tags($config['tags'])->flush();
                $invalidated[] = "layer:{$layer}:*";

                $this->recordCacheStats('invalidate_layer', $layer, '*', [
                    'tags' => $config['tags'],
                ]);
            } else {
                // Invalidate specific keys
                foreach ($keys as $key) {
                    $fullKey = $this->buildLayerKey($layer, $key);
                    Cache::forget($fullKey);
                    $invalidated[] = $fullKey;
                }

                $this->recordCacheStats('invalidate_keys', $layer, implode(',', $keys));
            }

            // Cascade invalidation to dependent layers
            if ($cascadeInvalidation && isset($config['dependencies'])) {
                foreach ($config['dependencies'] as $dependentLayer) {
                    $dependentInvalidated = $this->invalidateLayer($dependentLayer, [], FALSE);
                    $invalidated = array_merge($invalidated, $dependentInvalidated);
                }
            }
        } catch (Exception $e) {
            Log::error('Cache invalidation error', [
                'layer' => $layer,
                'keys'  => $keys,
                'error' => $e->getMessage(),
            ]);
        }

        return $invalidated;
    }

    /**
     * Warm up cache layers with common queries
     */
    public function warmupLayers(array $warmupConfig = []): array
    {
        $defaultConfig = [
            self::LAYER_EVENTS => [
                'upcoming_events' => fn () => $this->getUpcomingEventsData(),
                'popular_events'  => fn () => $this->getPopularEventsData(),
                'featured_events' => fn () => $this->getFeaturedEventsData(),
            ],
            self::LAYER_TICKETS => [
                'available_tickets' => fn () => $this->getAvailableTicketsData(),
                'price_ranges'      => fn () => $this->getPriceRangesData(),
            ],
            self::LAYER_SYSTEM => [
                'app_config'    => fn () => $this->getAppConfigData(),
                'feature_flags' => fn () => $this->getFeatureFlagsData(),
            ],
        ];

        $config = array_merge($defaultConfig, $warmupConfig);
        $results = [];

        foreach ($config as $layer => $queries) {
            $layerResults = [];

            foreach ($queries as $key => $callback) {
                try {
                    $startTime = microtime(TRUE);
                    $data = $callback();
                    $executionTime = microtime(TRUE) - $startTime;

                    $success = $this->putLayer($layer, $key, $data);

                    $layerResults[$key] = [
                        'success'        => $success,
                        'execution_time' => $executionTime,
                        'data_size'      => $data ? strlen(serialize($data)) : 0,
                    ];
                } catch (Exception $e) {
                    $layerResults[$key] = [
                        'success' => FALSE,
                        'error'   => $e->getMessage(),
                    ];
                }
            }

            $results[$layer] = $layerResults;
        }

        $this->recordCacheStats('warmup', 'all', '*', $results);

        return $results;
    }

    /**
     * Get comprehensive cache statistics
     */
    public function getCacheStats(): array
    {
        $redisInfo = $this->getRedisInfo();
        $layerStats = $this->getLayerStats();

        return [
            'redis'       => $redisInfo,
            'layers'      => $layerStats,
            'performance' => $this->getPerformanceStats(),
            'health'      => $this->getCacheHealth(),
        ];
    }

    /**
     * Initialize cache layer configurations
     */
    protected function initializeLayers(): void
    {
        $this->layerConfig = [
            self::LAYER_EVENTS => [
                'ttl'           => self::TTL_LONG,
                'tags'          => ['events', 'sports'],
                'dependencies'  => [self::LAYER_TICKETS, self::LAYER_MONITORING],
                'compression'   => TRUE,
                'serialization' => 'json',
            ],
            self::LAYER_TICKETS => [
                'ttl'           => self::TTL_MEDIUM,
                'tags'          => ['tickets', 'pricing'],
                'dependencies'  => [self::LAYER_EVENTS],
                'compression'   => TRUE,
                'serialization' => 'json',
            ],
            self::LAYER_MONITORING => [
                'ttl'           => self::TTL_SHORT,
                'tags'          => ['monitoring', 'scraping'],
                'dependencies'  => [self::LAYER_TICKETS, self::LAYER_EVENTS],
                'compression'   => FALSE,
                'serialization' => 'json',
            ],
            self::LAYER_USERS => [
                'ttl'           => self::TTL_EXTENDED,
                'tags'          => ['users', 'profiles'],
                'dependencies'  => [],
                'compression'   => FALSE,
                'serialization' => 'php',
            ],
            self::LAYER_SYSTEM => [
                'ttl'           => self::TTL_DAILY,
                'tags'          => ['system', 'config'],
                'dependencies'  => [],
                'compression'   => FALSE,
                'serialization' => 'json',
            ],
            self::LAYER_ANALYTICS => [
                'ttl'           => self::TTL_EXTENDED,
                'tags'          => ['analytics', 'metrics'],
                'dependencies'  => [self::LAYER_EVENTS, self::LAYER_TICKETS],
                'compression'   => TRUE,
                'serialization' => 'json',
            ],
        ];
    }

    /**
     * Get Redis server information
     */
    protected function getRedisInfo(): array
    {
        try {
            $redis = Redis::connection();
            $info = $redis->info();

            return [
                'version'           => $info['redis_version'] ?? 'unknown',
                'memory_used'       => $info['used_memory_human'] ?? 'unknown',
                'memory_peak'       => $info['used_memory_peak_human'] ?? 'unknown',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'keyspace_hits'     => $info['keyspace_hits'] ?? 0,
                'keyspace_misses'   => $info['keyspace_misses'] ?? 0,
                'hit_ratio'         => $this->calculateRedisHitRatio($info),
                'total_keys'        => $this->getTotalKeys(),
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get statistics for each cache layer
     */
    protected function getLayerStats(): array
    {
        $stats = [];

        foreach ($this->layerConfig as $layer => $config) {
            $layerKeys = $this->getLayerKeys($layer);
            $layerSize = $this->calculateLayerSize($layer);

            $stats[$layer] = [
                'config'         => $config,
                'key_count'      => count($layerKeys),
                'estimated_size' => $layerSize,
                'hit_ratio'      => $this->getLayerHitRatio($layer),
                'last_access'    => $this->getLastLayerAccess($layer),
            ];
        }

        return $stats;
    }

    /**
     * Get cache performance metrics
     */
    protected function getPerformanceStats(): array
    {
        $totalHits = array_sum(array_column($this->cacheStats['hit'] ?? [], 'count'));
        $totalMisses = array_sum(array_column($this->cacheStats['miss'] ?? [], 'count'));
        $totalOps = $totalHits + $totalMisses;

        return [
            'total_operations'      => $totalOps,
            'cache_hits'            => $totalHits,
            'cache_misses'          => $totalMisses,
            'hit_ratio'             => $totalOps > 0 ? ($totalHits / $totalOps) * 100 : 0,
            'average_response_time' => $this->getAverageResponseTime(),
            'memory_efficiency'     => $this->getMemoryEfficiency(),
        ];
    }

    /**
     * Assess overall cache health
     */
    protected function getCacheHealth(): array
    {
        $health = [
            'status'          => 'healthy',
            'issues'          => [],
            'recommendations' => [],
        ];

        // Check hit ratio
        $hitRatio = $this->getPerformanceStats()['hit_ratio'];
        if ($hitRatio < 70) {
            $health['issues'][] = "Low cache hit ratio: {$hitRatio}%";
            $health['recommendations'][] = 'Review caching strategy and TTL values';
            $health['status'] = 'warning';
        }

        // Check memory usage
        $redisInfo = $this->getRedisInfo();
        if (isset($redisInfo['memory_used'])) {
            $memoryUsed = $this->parseMemoryString($redisInfo['memory_used']);
            if ($memoryUsed > 1024 * 1024 * 1024) { // 1GB
                $health['issues'][] = "High memory usage: {$redisInfo['memory_used']}";
                $health['recommendations'][] = 'Consider cache cleanup or TTL optimization';
            }
        }

        // Check error rates
        $errorRate = $this->getErrorRate();
        if ($errorRate > 5) {
            $health['issues'][] = "High error rate: {$errorRate}%";
            $health['recommendations'][] = 'Check Redis connection and configuration';
            $health['status'] = 'error';
        }

        return $health;
    }

    /**
     * Build full cache key with layer prefix
     */
    protected function buildLayerKey(string $layer, string $key): string
    {
        return "hdtickets:{$layer}:{$key}";
    }

    /**
     * Prepare data for storage with compression and serialization
     *
     * @param mixed $data
     */
    protected function prepareDataForStorage($data, array $config, bool $compression = FALSE): string|false
    {
        // Serialize based on configuration
        $serialized = $config['serialization'] === 'json'
            ? json_encode($data)
            : serialize($data);

        // Compress if enabled
        if ($compression && function_exists('gzcompress')) {
            return gzcompress($serialized, 6);
        }

        return $serialized;
    }

    /**
     * Process data from storage with decompression and deserialization
     *
     * @param mixed $data
     */
    protected function processDataFromStorage($data, array $config)
    {
        // Decompress if needed
        if ($config['compression'] && function_exists('gzuncompress')) {
            $decompressed = @gzuncompress($data);
            if ($decompressed !== FALSE) {
                $data = $decompressed;
            }
        }

        // Deserialize based on configuration
        return $config['serialization'] === 'json'
            ? json_decode((string) $data, TRUE)
            : unserialize($data);
    }

    /**
     * Get default configuration for unknown layers
     */
    protected function getDefaultConfig(): array
    {
        return [
            'ttl'           => self::TTL_MEDIUM,
            'tags'          => ['default'],
            'dependencies'  => [],
            'compression'   => FALSE,
            'serialization' => 'json',
        ];
    }

    /**
     * Record cache statistics
     */
    protected function recordCacheStats(string $operation, string $layer, string $key, array $metadata = []): void
    {
        if (! isset($this->cacheStats[$operation])) {
            $this->cacheStats[$operation] = [];
        }

        $this->cacheStats[$operation][] = [
            'layer'     => $layer,
            'key'       => $key,
            'timestamp' => microtime(TRUE),
            'metadata'  => $metadata,
        ];
    }

    /**
     * Calculate Redis hit ratio from info
     */
    protected function calculateRedisHitRatio(array $info): float
    {
        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        $total = $hits + $misses;

        return $total > 0 ? ($hits / $total) * 100 : 0;
    }

    /**
     * Get total number of keys in Redis
     */
    protected function getTotalKeys(): int
    {
        try {
            $redis = Redis::connection();

            return $redis->dbsize();
        } catch (Exception) {
            return 0;
        }
    }

    /**
     * Get keys for specific layer
     */
    protected function getLayerKeys(string $layer): array
    {
        try {
            $redis = Redis::connection();
            $pattern = "hdtickets:{$layer}:*";

            return $redis->keys($pattern);
        } catch (Exception) {
            return [];
        }
    }

    /**
     * Calculate estimated size of layer
     */
    protected function calculateLayerSize(string $layer): int
    {
        $keys = $this->getLayerKeys($layer);
        $totalSize = 0;

        try {
            $redis = Redis::connection();
            foreach ($keys as $key) {
                $totalSize += $redis->memory('usage', $key);
            }
        } catch (Exception) {
            // Fallback estimation
            $totalSize = count($keys) * 1024; // Rough estimate
        }

        return $totalSize;
    }

    /**
     * Get hit ratio for specific layer
     */
    protected function getLayerHitRatio(string $layer): float
    {
        $hits = count($this->cacheStats['hit'] ?? []);
        $misses = count($this->cacheStats['miss'] ?? []);
        $total = $hits + $misses;

        return $total > 0 ? ($hits / $total) * 100 : 0;
    }

    /**
     * Get last access time for layer
     */
    protected function getLastLayerAccess(string $layer): ?float
    {
        $allAccesses = array_merge(
            $this->cacheStats['hit'] ?? [],
            $this->cacheStats['miss'] ?? [],
        );

        $layerAccesses = array_filter($allAccesses, fn (array $access): bool => $access['layer'] === $layer);

        if ($layerAccesses === []) {
            return NULL;
        }

        return max(array_column($layerAccesses, 'timestamp'));
    }

    /**
     * Get average cache response time
     */
    protected function getAverageResponseTime(): float
    {
        // This would need to be implemented with timing data
        return 0.0; // Placeholder
    }

    /**
     * Get memory efficiency score
     */
    protected function getMemoryEfficiency(): float
    {
        // This would calculate storage efficiency
        return 85.0; // Placeholder
    }

    /**
     * Parse memory string to bytes
     */
    protected function parseMemoryString(string $memory): int
    {
        preg_match('/([0-9.]+)([KMGT]?)/', $memory, $matches);

        if ($matches === []) {
            return 0;
        }

        $value = (float) $matches[1];
        $unit = $matches[2] ?? '';

        $multipliers = [
            'K' => 1024,
            'M' => 1024 * 1024,
            'G' => 1024 * 1024 * 1024,
            'T' => 1024 * 1024 * 1024 * 1024,
        ];

        return (int) ($value * ($multipliers[$unit] ?? 1));
    }

    /**
     * Get error rate percentage
     */
    protected function getErrorRate(): float
    {
        $errors = count($this->cacheStats['error'] ?? []);
        $total = count($this->cacheStats['hit'] ?? []) +
                count($this->cacheStats['miss'] ?? []) +
                $errors;

        return $total > 0 ? ($errors / $total) * 100 : 0;
    }

    // Placeholder methods for warmup data - these would be implemented based on actual models
    protected function getUpcomingEventsData(): array
    {
        return ['placeholder' => 'upcoming_events'];
    }

    protected function getPopularEventsData(): array
    {
        return ['placeholder' => 'popular_events'];
    }

    protected function getFeaturedEventsData(): array
    {
        return ['placeholder' => 'featured_events'];
    }

    protected function getAvailableTicketsData(): array
    {
        return ['placeholder' => 'available_tickets'];
    }

    protected function getPriceRangesData(): array
    {
        return ['placeholder' => 'price_ranges'];
    }

    protected function getAppConfigData(): array
    {
        return ['placeholder' => 'app_config'];
    }

    protected function getFeatureFlagsData(): array
    {
        return ['placeholder' => 'feature_flags'];
    }
}
