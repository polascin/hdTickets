<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Database Query Optimization Service
 * 
 * Provides comprehensive database query optimization strategies including:
 * - Intelligent eager loading
 * - Query result caching
 * - Performance monitoring
 * - Query optimization suggestions
 * - Batch processing utilities
 */
class DatabaseOptimizationService
{
    protected array $performanceMetrics = [];
    protected array $queryCache = [];
    protected bool $enableProfiling = true;
    protected float $slowQueryThreshold = 1.0; // seconds
    
    public function __construct()
    {
        $this->enableProfiling = config('database.enable_profiling', true);
        $this->slowQueryThreshold = config('database.slow_query_threshold', 1.0);
    }

    /**
     * Execute optimized query with caching and performance monitoring
     *
     * @param Builder $query
     * @param array $cacheOptions
     * @return Collection|Model|null
     */
    public function optimizedQuery(Builder $query, array $cacheOptions = [])
    {
        $cacheKey = $this->generateCacheKey($query, $cacheOptions);
        $cacheEnabled = $cacheOptions['enabled'] ?? true;
        $cacheTTL = $cacheOptions['ttl'] ?? 3600; // 1 hour default

        // Check cache first
        if ($cacheEnabled && Cache::has($cacheKey)) {
            $this->recordMetric('cache_hit', $cacheKey);
            return Cache::get($cacheKey);
        }

        // Execute query with performance monitoring
        $startTime = microtime(true);
        
        try {
            $result = $query->get();
            $executionTime = microtime(true) - $startTime;
            
            // Record performance metrics
            $this->recordQueryMetrics($query, $executionTime, count($result));
            
            // Cache result if enabled
            if ($cacheEnabled && $result->isNotEmpty()) {
                Cache::put($cacheKey, $result, $cacheTTL);
                $this->recordMetric('cache_set', $cacheKey);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            $this->recordQueryError($query, $e, $executionTime);
            throw $e;
        }
    }

    /**
     * Optimized eager loading for relationships
     *
     * @param Builder $query
     * @param array $relations
     * @return Builder
     */
    public function optimizedEagerLoad(Builder $query, array $relations): Builder
    {
        // Analyze relations for optimization
        $optimizedRelations = $this->optimizeRelations($relations);
        
        return $query->with($optimizedRelations);
    }

    /**
     * Batch processing for large datasets
     *
     * @param Builder $query
     * @param callable $callback
     * @param int $chunkSize
     * @return array
     */
    public function batchProcess(Builder $query, callable $callback, int $chunkSize = 1000): array
    {
        $results = [];
        $totalProcessed = 0;
        $startTime = microtime(true);

        $query->chunk($chunkSize, function ($items) use ($callback, &$results, &$totalProcessed) {
            $chunkStartTime = microtime(true);
            
            $chunkResults = $callback($items);
            $results = array_merge($results, (array) $chunkResults);
            
            $totalProcessed += count($items);
            $chunkTime = microtime(true) - $chunkStartTime;
            
            $this->recordMetric('batch_chunk', [
                'size' => count($items),
                'time' => $chunkTime,
                'processed' => $totalProcessed
            ]);
        });

        $totalTime = microtime(true) - $startTime;
        
        $this->recordMetric('batch_complete', [
            'total_processed' => $totalProcessed,
            'total_time' => $totalTime,
            'avg_per_item' => $totalProcessed > 0 ? $totalTime / $totalProcessed : 0
        ]);

        return $results;
    }

    /**
     * Intelligent query optimization suggestions
     *
     * @param Builder $query
     * @return array
     */
    public function analyzeQuery(Builder $query): array
    {
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        
        $suggestions = [];
        
        // Analyze for common optimization opportunities
        if (strpos($sql, 'SELECT *') !== false) {
            $suggestions[] = [
                'type' => 'select_optimization',
                'message' => 'Consider selecting specific columns instead of using SELECT *',
                'impact' => 'medium',
                'suggestion' => 'Use ->select([\'column1\', \'column2\']) to reduce data transfer'
            ];
        }
        
        if (strpos($sql, 'ORDER BY') !== false && strpos($sql, 'LIMIT') === false) {
            $suggestions[] = [
                'type' => 'pagination',
                'message' => 'ORDER BY without LIMIT can be expensive for large datasets',
                'impact' => 'high',
                'suggestion' => 'Consider adding pagination with ->paginate() or ->take()'
            ];
        }
        
        if (preg_match('/WHERE.*LIKE.*%.*%/', $sql)) {
            $suggestions[] = [
                'type' => 'fulltext_search',
                'message' => 'LIKE with wildcards on both ends is inefficient',
                'impact' => 'high',
                'suggestion' => 'Consider using full-text search or search indices'
            ];
        }
        
        // Check for N+1 query potential
        $model = $query->getModel();
        if ($model && !$query->getEagerLoads()) {
            $suggestions[] = [
                'type' => 'eager_loading',
                'message' => 'Query might cause N+1 problems if relationships are accessed',
                'impact' => 'high',
                'suggestion' => 'Use ->with([\'relationship\']) to eager load related data'
            ];
        }

        return [
            'query' => $sql,
            'bindings' => $bindings,
            'suggestions' => $suggestions,
            'estimated_impact' => $this->estimateQueryImpact($sql)
        ];
    }

    /**
     * Get database performance statistics
     *
     * @return array
     */
    public function getPerformanceStats(): array
    {
        $cacheStats = [
            'hits' => $this->getMetricCount('cache_hit'),
            'sets' => $this->getMetricCount('cache_set'),
            'hit_ratio' => $this->calculateCacheHitRatio()
        ];

        $queryStats = [
            'total_queries' => count($this->performanceMetrics['queries'] ?? []),
            'slow_queries' => $this->getSlowQueriesCount(),
            'average_time' => $this->getAverageQueryTime(),
            'total_time' => $this->getTotalQueryTime()
        ];

        return [
            'cache' => $cacheStats,
            'queries' => $queryStats,
            'optimization_opportunities' => $this->getOptimizationOpportunities(),
            'performance_score' => $this->calculatePerformanceScore()
        ];
    }

    /**
     * Clear query caches
     *
     * @param string|null $pattern
     * @return int
     */
    public function clearQueryCache(string $pattern = null): int
    {
        if ($pattern) {
            // Clear specific cache pattern
            $keys = $this->getCacheKeysByPattern($pattern);
            foreach ($keys as $key) {
                Cache::forget($key);
            }
            return count($keys);
        }

        // Clear all query caches
        $keys = $this->getCacheKeysByPattern('query:*');
        foreach ($keys as $key) {
            Cache::forget($key);
        }

        $this->recordMetric('cache_clear', ['keys_cleared' => count($keys)]);
        return count($keys);
    }

    /**
     * Warm up cache with common queries
     *
     * @param array $queries
     * @return array
     */
    public function warmupCache(array $queries): array
    {
        $results = [];
        $startTime = microtime(true);

        foreach ($queries as $queryConfig) {
            try {
                $query = $queryConfig['query'];
                $cacheOptions = $queryConfig['cache'] ?? [];
                
                $result = $this->optimizedQuery($query, $cacheOptions);
                $results[] = [
                    'query' => $query->toSql(),
                    'success' => true,
                    'count' => $result ? count($result) : 0
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'query' => $queryConfig['query']->toSql(),
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        $totalTime = microtime(true) - $startTime;
        
        $this->recordMetric('cache_warmup', [
            'queries_warmed' => count($queries),
            'successful' => array_sum(array_column($results, 'success')),
            'total_time' => $totalTime
        ]);

        return $results;
    }

    /**
     * Generate cache key for query
     */
    protected function generateCacheKey(Builder $query, array $options = []): string
    {
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        $prefix = $options['prefix'] ?? 'query';
        
        $key = $prefix . ':' . md5($sql . serialize($bindings));
        
        // Add model class to key for uniqueness
        if ($query->getModel()) {
            $key .= ':' . class_basename($query->getModel());
        }
        
        return $key;
    }

    /**
     * Optimize relations array for better performance
     */
    protected function optimizeRelations(array $relations): array
    {
        $optimized = [];
        
        foreach ($relations as $relation) {
            if (is_string($relation)) {
                // Simple relation optimization
                $optimized[] = $relation;
            } elseif (is_array($relation)) {
                // Complex relation with constraints
                $optimized = array_merge($optimized, $relation);
            }
        }
        
        return array_unique($optimized);
    }

    /**
     * Record query performance metrics
     */
    protected function recordQueryMetrics(Builder $query, float $executionTime, int $resultCount): void
    {
        if (!$this->enableProfiling) {
            return;
        }

        $metric = [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'execution_time' => $executionTime,
            'result_count' => $resultCount,
            'timestamp' => microtime(true),
            'memory_usage' => memory_get_usage(true)
        ];

        $this->performanceMetrics['queries'][] = $metric;

        // Log slow queries
        if ($executionTime > $this->slowQueryThreshold) {
            Log::warning('Slow query detected', [
                'execution_time' => $executionTime,
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);
            
            $this->performanceMetrics['slow_queries'][] = $metric;
        }
    }

    /**
     * Record query error
     */
    protected function recordQueryError(Builder $query, \Exception $e, float $executionTime): void
    {
        $this->performanceMetrics['errors'][] = [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'error' => $e->getMessage(),
            'execution_time' => $executionTime,
            'timestamp' => microtime(true)
        ];

        Log::error('Query execution error', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'error' => $e->getMessage(),
            'execution_time' => $executionTime
        ]);
    }

    /**
     * Record general metric
     */
    protected function recordMetric(string $type, $data): void
    {
        if (!isset($this->performanceMetrics[$type])) {
            $this->performanceMetrics[$type] = [];
        }
        
        $this->performanceMetrics[$type][] = [
            'data' => $data,
            'timestamp' => microtime(true)
        ];
    }

    /**
     * Get metric count by type
     */
    protected function getMetricCount(string $type): int
    {
        return count($this->performanceMetrics[$type] ?? []);
    }

    /**
     * Calculate cache hit ratio
     */
    protected function calculateCacheHitRatio(): float
    {
        $hits = $this->getMetricCount('cache_hit');
        $total = $hits + $this->getMetricCount('cache_set');
        
        return $total > 0 ? ($hits / $total) * 100 : 0;
    }

    /**
     * Get slow queries count
     */
    protected function getSlowQueriesCount(): int
    {
        return count($this->performanceMetrics['slow_queries'] ?? []);
    }

    /**
     * Get average query time
     */
    protected function getAverageQueryTime(): float
    {
        $queries = $this->performanceMetrics['queries'] ?? [];
        
        if (empty($queries)) {
            return 0;
        }
        
        $totalTime = array_sum(array_column($queries, 'execution_time'));
        return $totalTime / count($queries);
    }

    /**
     * Get total query time
     */
    protected function getTotalQueryTime(): float
    {
        $queries = $this->performanceMetrics['queries'] ?? [];
        return array_sum(array_column($queries, 'execution_time'));
    }

    /**
     * Get optimization opportunities
     */
    protected function getOptimizationOpportunities(): array
    {
        $opportunities = [];
        
        // Check for frequent slow queries
        $slowQueries = $this->performanceMetrics['slow_queries'] ?? [];
        if (count($slowQueries) > 0) {
            $opportunities[] = [
                'type' => 'slow_queries',
                'count' => count($slowQueries),
                'suggestion' => 'Review and optimize slow queries'
            ];
        }
        
        // Check cache hit ratio
        $hitRatio = $this->calculateCacheHitRatio();
        if ($hitRatio < 80) {
            $opportunities[] = [
                'type' => 'cache_optimization',
                'hit_ratio' => $hitRatio,
                'suggestion' => 'Improve caching strategy to increase hit ratio'
            ];
        }
        
        return $opportunities;
    }

    /**
     * Calculate overall performance score (0-100)
     */
    protected function calculatePerformanceScore(): int
    {
        $score = 100;
        
        // Penalize for slow queries
        $slowQueryRatio = $this->getSlowQueriesCount() / max(1, count($this->performanceMetrics['queries'] ?? []));
        $score -= $slowQueryRatio * 30;
        
        // Bonus for good cache hit ratio
        $hitRatio = $this->calculateCacheHitRatio();
        if ($hitRatio > 80) {
            $score += 5;
        } elseif ($hitRatio < 50) {
            $score -= 15;
        }
        
        // Penalize for errors
        $errorCount = count($this->performanceMetrics['errors'] ?? []);
        $score -= $errorCount * 10;
        
        return max(0, min(100, (int) $score));
    }

    /**
     * Estimate query impact based on SQL analysis
     */
    protected function estimateQueryImpact(string $sql): string
    {
        // Simple heuristic-based impact estimation
        $highImpactPatterns = [
            '/SELECT \* FROM/',
            '/ORDER BY.*LIMIT \d+$/',
            '/GROUP BY/',
            '/HAVING/',
            '/UNION/'
        ];
        
        $mediumImpactPatterns = [
            '/JOIN/',
            '/WHERE.*LIKE/',
            '/ORDER BY/'
        ];
        
        foreach ($highImpactPatterns as $pattern) {
            if (preg_match($pattern, $sql)) {
                return 'high';
            }
        }
        
        foreach ($mediumImpactPatterns as $pattern) {
            if (preg_match($pattern, $sql)) {
                return 'medium';
            }
        }
        
        return 'low';
    }

    /**
     * Get cache keys by pattern
     */
    protected function getCacheKeysByPattern(string $pattern): array
    {
        // This is a simplified implementation
        // In production, you might want to use Redis SCAN for better performance
        try {
            $redis = Cache::getRedis();
            return $redis->keys($pattern);
        } catch (\Exception $e) {
            return [];
        }
    }
}
