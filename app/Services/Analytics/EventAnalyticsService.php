<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Domain\Ticket\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Event Analytics Service
 *
 * Provides analytics and insights for sports events and ticket data
 */
class EventAnalyticsService
{
    /**
     * Get trending events based on recent activity
     */
    public function getTrendingEvents(int $limit = 10): Collection
    {
        return Cache::remember('trending_events', 1800, fn () => DB::table('scraped_tickets')
            ->select([
                'id',
                'title',
                'sport',
                'location',
                'platform',
                'price',
                'event_date',
                DB::raw('COUNT(*) as activity_count'),
            ])
            ->where('status', 'active')
            ->where('event_date', '>', now())
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy(['id', 'title', 'sport', 'location', 'platform', 'price', 'event_date'])
            ->orderBy('activity_count', 'desc')
            ->limit($limit)
            ->get());
    }

    /**
     * Get event popularity metrics
     */
    public function getEventPopularity(int $eventId): array
    {
        $cacheKey = "event_popularity_{$eventId}";

        return Cache::remember($cacheKey, 900, function () use ($eventId): array {
            $viewCount = DB::table('scraped_tickets')
                ->where('id', $eventId)
                ->count();

            $purchaseCount = DB::table('ticket_purchases')
                ->where('ticket_id', $eventId)
                ->count();

            return [
                'views'           => $viewCount,
                'purchases'       => $purchaseCount,
                'conversion_rate' => $viewCount > 0 ? ($purchaseCount / $viewCount) : 0,
            ];
        });
    }

    /**
     * Get sports category analytics
     */
    public function getSportsAnalytics(): array
    {
        return Cache::remember('sports_analytics', 3600, fn () => DB::table('scraped_tickets')
            ->select([
                'sport',
                DB::raw('COUNT(*) as event_count'),
                DB::raw('AVG(price) as avg_price'),
                DB::raw('MIN(price) as min_price'),
                DB::raw('MAX(price) as max_price'),
            ])
            ->where('status', 'active')
            ->where('event_date', '>', now())
            ->groupBy('sport')
            ->orderBy('event_count', 'desc')
            ->get()
            ->toArray());
    }

    /**
     * Get venue analytics
     */
    public function getVenueAnalytics(): array
    {
        return Cache::remember('venue_analytics', 3600, fn () => DB::table('scraped_tickets')
            ->select([
                'location',
                DB::raw('COUNT(*) as event_count'),
                DB::raw('AVG(price) as avg_price'),
            ])
            ->where('status', 'active')
            ->where('event_date', '>', now())
            ->groupBy('location')
            ->orderBy('event_count', 'desc')
            ->limit(20)
            ->get()
            ->toArray());
    }

    /**
     * Get price trend analytics for an event
     */
    public function getEventPriceTrends(int $eventId, int $days = 30): array
    {
        return DB::table('scraped_tickets')
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('AVG(price) as avg_price'),
                DB::raw('MIN(price) as min_price'),
                DB::raw('MAX(price) as max_price'),
            ])
            ->where('id', $eventId)
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Get platform performance analytics
     */
    public function getPlatformAnalytics(): array
    {
        return Cache::remember('platform_analytics', 1800, fn () => DB::table('scraped_tickets')
            ->select([
                'platform',
                DB::raw('COUNT(*) as ticket_count'),
                DB::raw('AVG(price) as avg_price'),
                DB::raw('COUNT(DISTINCT CONCAT(title, location)) as unique_events'),
            ])
            ->where('status', 'active')
            ->where('event_date', '>', now())
            ->groupBy('platform')
            ->orderBy('ticket_count', 'desc')
            ->get()
            ->toArray());
    }

    /**
     * Clear analytics cache
     */
    public function clearCache(): void
    {
        $cacheKeys = [
            'trending_events',
            'sports_analytics',
            'venue_analytics',
            'platform_analytics',
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}
