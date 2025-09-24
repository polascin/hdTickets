<?php declare(strict_types=1);

namespace App\Services\Enhanced;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

use function count;

/**
 * Enhanced Ticket Cache Service for Sports Ticket Scraping
 *
 * Handles Redis caching with intelligent cache warming, invalidation,
 * and performance optimizations specifically for sports ticket data.
 */
class TicketCacheService
{
    protected const DEFAULT_TTL = 3600; // 1 hour

    protected const LONG_TTL = 86400; // 24 hours

    protected const SHORT_TTL = 900; // 15 minutes

    protected const SEARCH_TTL = 1800; // 30 minutes

    protected array $cacheConfig = [
        'tickets' => [
            'ttl'        => self::DEFAULT_TTL,
            'tags'       => ['tickets', 'scraped_data'],
            'key_prefix' => 'sports_ticket:',
        ],
        'search_results' => [
            'ttl'        => self::SEARCH_TTL,
            'tags'       => ['search', 'results'],
            'key_prefix' => 'ticket_search:',
        ],
        'statistics' => [
            'ttl'        => self::SHORT_TTL,
            'tags'       => ['stats', 'analytics'],
            'key_prefix' => 'ticket_stats:',
        ],
        'price_history' => [
            'ttl'        => self::LONG_TTL,
            'tags'       => ['prices', 'history'],
            'key_prefix' => 'price_history:',
        ],
        'popular_tickets' => [
            'ttl'        => self::DEFAULT_TTL,
            'tags'       => ['popular', 'trending'],
            'key_prefix' => 'popular_tickets:',
        ],
    ];

    /**
     * Cache a ticket with intelligent TTL based on event date
     */
    public function cacheTicket(int $ticketId, array $ticketData): bool
    {
        try {
            $key = $this->buildKey('tickets', (string) $ticketId);
            $ttl = $this->calculateTicketTTL($ticketData);

            return Cache::tags($this->cacheConfig['tickets']['tags'])
                ->put($key, $ticketData, $ttl);
        } catch (Exception $e) {
            Log::error('Failed to cache ticket', [
                'ticket_id' => $ticketId,
                'error'     => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Get cached ticket data
     */
    public function getCachedTicket(int $ticketId): ?array
    {
        try {
            $key = $this->buildKey('tickets', (string) $ticketId);

            return Cache::tags($this->cacheConfig['tickets']['tags'])->get($key);
        } catch (Exception $e) {
            Log::error('Failed to retrieve cached ticket', [
                'ticket_id' => $ticketId,
                'error'     => $e->getMessage(),
            ]);

            return NULL;
        }
    }

    /**
     * Cache search results with filters hash as key
     */
    public function cacheSearchResults(array $filters, array $results, array $stats = []): bool
    {
        try {
            $filtersHash = md5(serialize($filters));
            $key = $this->buildKey('search_results', $filtersHash);

            $data = [
                'results'    => $results,
                'stats'      => $stats,
                'filters'    => $filters,
                'cached_at'  => now()->toISOString(),
                'expires_at' => now()->addSeconds($this->cacheConfig['search_results']['ttl'])->toISOString(),
            ];

            return Cache::tags($this->cacheConfig['search_results']['tags'])
                ->put($key, $data, $this->cacheConfig['search_results']['ttl']);
        } catch (Exception $e) {
            Log::error('Failed to cache search results', [
                'filters' => $filters,
                'error'   => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Get cached search results
     */
    public function getCachedSearchResults(array $filters): ?array
    {
        try {
            $filtersHash = md5(serialize($filters));
            $key = $this->buildKey('search_results', $filtersHash);

            return Cache::tags($this->cacheConfig['search_results']['tags'])->get($key);
        } catch (Exception $e) {
            Log::error('Failed to retrieve cached search results', [
                'filters' => $filters,
                'error'   => $e->getMessage(),
            ]);

            return NULL;
        }
    }

    /**
     * Cache price history for a ticket
     */
    public function cachePriceHistory(int $ticketId, array $priceHistory): bool
    {
        try {
            $key = $this->buildKey('price_history', (string) $ticketId);

            return Cache::tags($this->cacheConfig['price_history']['tags'])
                ->put($key, $priceHistory, $this->cacheConfig['price_history']['ttl']);
        } catch (Exception $e) {
            Log::error('Failed to cache price history', [
                'ticket_id' => $ticketId,
                'error'     => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Cache popular/trending tickets by category
     */
    public function cachePopularTickets(string $category, array $tickets): bool
    {
        try {
            $key = $this->buildKey('popular_tickets', $category);

            return Cache::tags($this->cacheConfig['popular_tickets']['tags'])
                ->put($key, $tickets, $this->cacheConfig['popular_tickets']['ttl']);
        } catch (Exception $e) {
            Log::error('Failed to cache popular tickets', [
                'category' => $category,
                'error'    => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Get cached popular tickets
     */
    public function getCachedPopularTickets(string $category): ?array
    {
        try {
            $key = $this->buildKey('popular_tickets', $category);

            return Cache::tags($this->cacheConfig['popular_tickets']['tags'])->get($key);
        } catch (Exception $e) {
            Log::error('Failed to retrieve cached popular tickets', [
                'category' => $category,
                'error'    => $e->getMessage(),
            ]);

            return NULL;
        }
    }

    /**
     * Invalidate cache when ticket data changes
     */
    public function invalidateTicketCache(int $ticketId, array $changes = []): void
    {
        try {
            // Invalidate specific ticket cache
            $ticketKey = $this->buildKey('tickets', (string) $ticketId);
            Cache::forget($ticketKey);

            // Invalidate related caches based on changes
            if (isset($changes['price']) || isset($changes['availability'])) {
                $priceKey = $this->buildKey('price_history', (string) $ticketId);
                Cache::forget($priceKey);
                Cache::tags(['search', 'results'])->flush();
            }

            if (isset($changes['popularity']) || isset($changes['view_count'])) {
                Cache::tags(['popular', 'trending'])->flush();
            }

            if ($changes !== []) {
                Cache::tags(['stats', 'analytics'])->flush();
            }

            Log::debug('Cache invalidated for ticket', [
                'ticket_id' => $ticketId,
                'changes'   => array_keys($changes),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to invalidate ticket cache', [
                'ticket_id' => $ticketId,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    /**
     * Warm up cache with frequently accessed data
     */
    public function warmupCache(): void
    {
        try {
            Log::info('Starting ticket cache warmup');

            // Popular sports
            $popularSports = ['football', 'rugby', 'cricket', 'tennis', 'basketball'];
            foreach ($popularSports as $sport) {
                $this->warmupSportTickets($sport);
            }

            // Popular venues
            $popularVenues = [
                'Wembley Stadium', 'Old Trafford', 'Anfield', 'Emirates Stadium',
                'Stamford Bridge', 'Tottenham Hotsium Stadium', 'Etihad Stadium',
            ];
            foreach ($popularVenues as $venue) {
                $this->warmupVenueTickets($venue);
            }

            $this->warmupTrendingTickets();

            Log::info('Ticket cache warmup completed');
        } catch (Exception $e) {
            Log::error('Ticket cache warmup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Get cache performance statistics
     */
    public function getCacheStatistics(): array
    {
        try {
            return [
                'redis_info'          => Redis::info(),
                'cache_keys'          => $this->getCacheKeyStatistics(),
                'performance_metrics' => $this->getPerformanceMetrics(),
                'hit_ratio'           => $this->calculateHitRatio(),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get cache statistics', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Build cache key with prefix
     */
    protected function buildKey(string $type, string $identifier): string
    {
        $prefix = $this->cacheConfig[$type]['key_prefix'] ?? '';

        return $prefix . $identifier;
    }

    /**
     * Calculate TTL based on ticket event date
     */
    protected function calculateTicketTTL(array $ticketData): int
    {
        try {
            if (!isset($ticketData['event_date'])) {
                return self::DEFAULT_TTL;
            }

            $eventDate = Carbon::parse($ticketData['event_date']);
            $daysUntilEvent = now()->diffInDays($eventDate, FALSE);

            // Events happening soon get shorter cache times for real-time updates
            if ($daysUntilEvent <= 1) {
                return self::SHORT_TTL; // 15 minutes
            }
            if ($daysUntilEvent <= 7) {
                return self::DEFAULT_TTL; // 1 hour
            }

            return self::LONG_TTL; // 24 hours
        } catch (Exception $e) {
            Log::warning('Failed to calculate ticket TTL', [
                'ticket_data' => $ticketData,
                'error'       => $e->getMessage(),
            ]);

            return self::DEFAULT_TTL;
        }
    }

    /**
     * Warm up cache for specific sport
     */
    protected function warmupSportTickets(string $sport): void
    {
        try {
            $key = "sport_popular_{$sport}";
            // In real implementation, this would fetch from database
            $tickets = [];
            $this->cachePopularTickets($key, $tickets);
        } catch (Exception $e) {
            Log::error('Failed to warm up sport tickets', [
                'sport' => $sport,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Warm up cache for specific venue
     */
    protected function warmupVenueTickets(string $venue): void
    {
        try {
            $key = 'venue_' . md5($venue);
            $tickets = []; // Database query would go here
            $this->cachePopularTickets($key, $tickets);
        } catch (Exception $e) {
            Log::error('Failed to warm up venue tickets', [
                'venue' => $venue,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Warm up trending tickets cache
     */
    protected function warmupTrendingTickets(): void
    {
        try {
            $trendingTickets = []; // Database query for trending tickets
            $this->cachePopularTickets('trending_global', $trendingTickets);
        } catch (Exception $e) {
            Log::error('Failed to warm up trending tickets', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get statistics about cache keys
     */
    protected function getCacheKeyStatistics(): array
    {
        try {
            $stats = [];
            foreach ($this->cacheConfig as $type => $config) {
                $pattern = $config['key_prefix'] . '*';
                $keys = Redis::keys($pattern);
                $stats[$type] = [
                    'total_keys' => count($keys),
                    'prefix'     => $config['key_prefix'],
                    'ttl'        => $config['ttl'],
                ];
            }

            return $stats;
        } catch (Exception $e) {
            Log::error('Failed to get cache key statistics', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get performance metrics
     */
    protected function getPerformanceMetrics(): array
    {
        try {
            $info = Redis::info('memory');

            return [
                'memory_usage' => [
                    'used_memory'       => $info['used_memory'] ?? 0,
                    'used_memory_human' => $info['used_memory_human'] ?? '0B',
                    'used_memory_peak'  => $info['used_memory_peak'] ?? 0,
                ],
                'key_count' => $this->getTotalKeyCount(),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get performance metrics', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Calculate cache hit ratio
     */
    protected function calculateHitRatio(): float
    {
        try {
            $info = Redis::info();
            $hits = (int) ($info['keyspace_hits'] ?? 0);
            $misses = (int) ($info['keyspace_misses'] ?? 0);

            if ($hits + $misses === 0) {
                return 0.0;
            }

            return round(($hits / ($hits + $misses)) * 100, 2);
        } catch (Exception) {
            return 0.0;
        }
    }

    /**
     * Get total key count from Redis
     */
    protected function getTotalKeyCount(): int
    {
        try {
            $info = Redis::info('keyspace');
            $keyspaceInfo = $info['db0'] ?? '';

            if (preg_match('/keys=(\d+)/', $keyspaceInfo, $matches)) {
                return (int) $matches[1];
            }

            return 0;
        } catch (Exception) {
            return 0;
        }
    }
}
