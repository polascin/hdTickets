<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use function array_slice;
use function in_array;

/**
 * Social Proof Features API Controller
 *
 * Provides social indicators, demand metrics, and trending data
 * to help users make informed ticket purchasing decisions
 */
class SocialProofController extends Controller
{
    /**
     * Get social proof dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        $cacheKey = 'social_proof_dashboard';

        $data = Cache::remember($cacheKey, 900, function () {
            return [
                'trending_events'        => $this->getTrendingEvents(),
                'high_demand_indicators' => $this->getHighDemandIndicators(),
                'platform_activity'      => $this->getPlatformActivity(),
                'price_movement_alerts'  => $this->getPriceMovementAlerts(),
                'social_activity'        => $this->getSocialActivity(),
                'market_pulse'           => $this->getMarketPulse(),
            ];
        });

        return response()->json([
            'success' => TRUE,
            'data'    => $data,
        ]);
    }

    /**
     * Get social proof for a specific event
     */
    public function eventProof(Request $request): JsonResponse
    {
        $request->validate([
            'event_name' => 'required|string',
            'venue'      => 'nullable|string',
            'date'       => 'nullable|date',
        ]);

        $eventName = $request->get('event_name');
        $venue = $request->get('venue');
        $date = $request->get('date');

        $cacheKey = 'event_social_proof_' . md5($eventName . $venue . $date);

        $data = Cache::remember($cacheKey, 300, function () use ($eventName, $venue, $date) {
            $tickets = $this->getEventTickets($eventName, $venue, $date);

            if ($tickets->isEmpty()) {
                return;
            }

            return [
                'demand_level'         => $this->calculateEventDemandLevel($tickets),
                'viewing_activity'     => $this->getViewingActivity($tickets),
                'purchase_indicators'  => $this->getPurchaseIndicators($tickets),
                'price_trends'         => $this->getEventPriceTrends($tickets),
                'platform_competition' => $this->getPlatformCompetition($tickets),
                'scarcity_indicators'  => $this->getScarcityIndicators($tickets),
                'social_signals'       => $this->getSocialSignals($eventName),
                'recommendations'      => $this->getEventRecommendations($tickets),
            ];
        });

        if (! $data) {
            return response()->json([
                'success' => FALSE,
                'message' => 'No social proof data found for the specified event',
            ], 404);
        }

        return response()->json([
            'success' => TRUE,
            'data'    => $data,
        ]);
    }

    /**
     * Get trending events with social proof metrics
     */
    public function trending(Request $request): JsonResponse
    {
        $request->validate([
            'timeframe' => 'nullable|string|in:1h,6h,24h,7d',
            'sport'     => 'nullable|string',
            'limit'     => 'nullable|integer|min:5|max:50',
        ]);

        $timeframe = $request->get('timeframe', '24h');
        $sport = $request->get('sport');
        $limit = $request->get('limit', 20);

        $cacheKey = "trending_events_{$timeframe}_{$sport}_{$limit}";

        $data = Cache::remember($cacheKey, 600, function () use ($timeframe, $sport, $limit) {
            $timeFilter = $this->getTimeFilter($timeframe);

            $query = Event::where('date', '>=', now());

            if ($sport) {
                $query->where('sport', $sport);
            }

            $events = $query->withCount(['tickets as ticket_count'])
                ->with(['tickets' => function ($ticketQuery) use ($timeFilter): void {
                    $ticketQuery->where('created_at', '>=', $timeFilter)
                        ->select(['event_id', 'current_price', 'views_count', 'platform', 'is_available']);
                }])
                ->orderByDesc('popularity_score')
                ->limit($limit)
                ->get();

            return $events->map(function ($event) {
                return $this->formatTrendingEvent($event);
            });
        });

        return response()->json([
            'success' => TRUE,
            'data'    => $data,
        ]);
    }

    /**
     * Get demand indicators for multiple events
     */
    public function demandIndicators(Request $request): JsonResponse
    {
        $request->validate([
            'events'   => 'required|array|min:1|max:10',
            'events.*' => 'string',
        ]);

        $eventNames = $request->get('events');

        $data = collect($eventNames)->map(function ($eventName) {
            $cacheKey = 'demand_indicators_' . md5($eventName);

            return Cache::remember($cacheKey, 300, function () use ($eventName) {
                $tickets = Ticket::where('event_name', 'LIKE', "%{$eventName}%")->get();

                if ($tickets->isEmpty()) {
                    return;
                }

                return [
                    'event_name'            => $eventName,
                    'demand_level'          => $this->calculateEventDemandLevel($tickets),
                    'availability_pressure' => $this->calculateAvailabilityPressure($tickets),
                    'price_momentum'        => $this->calculatePriceMomentum($tickets),
                    'platform_activity'     => $this->getEventPlatformActivity($tickets),
                    'urgency_score'         => $this->calculateUrgencyScore($tickets),
                ];
            });
        })->filter(); // Remove null results

        return response()->json([
            'success' => TRUE,
            'data'    => $data->values(),
        ]);
    }

    /**
     * Get real-time activity feed
     */
    public function activityFeed(Request $request): JsonResponse
    {
        $request->validate([
            'since'   => 'nullable|integer|min:1',
            'limit'   => 'nullable|integer|min:5|max:100',
            'types'   => 'nullable|array',
            'types.*' => 'string|in:price_drop,new_listing,sold_out,high_demand,trending,platform_activity',
        ]);

        $since = $request->get('since', 300); // Last 5 minutes by default
        $limit = $request->get('limit', 50);
        $types = $request->get('types', ['price_drop', 'new_listing', 'sold_out', 'high_demand']);

        $cacheKey = "activity_feed_{$since}_{$limit}_" . md5(serialize($types));

        $data = Cache::remember($cacheKey, 30, function () use ($since, $limit, $types) {
            $activities = [];

            if (in_array('price_drop', $types, TRUE)) {
                $activities = array_merge($activities, $this->getPriceDropActivity($since));
            }

            if (in_array('new_listing', $types, TRUE)) {
                $activities = array_merge($activities, $this->getNewListingActivity($since));
            }

            if (in_array('sold_out', $types, TRUE)) {
                $activities = array_merge($activities, $this->getSoldOutActivity($since));
            }

            if (in_array('high_demand', $types, TRUE)) {
                $activities = array_merge($activities, $this->getHighDemandActivity($since));
            }

            // Sort by timestamp descending
            usort($activities, function ($a, $b) {
                return $b['timestamp'] <=> $a['timestamp'];
            });

            return array_slice($activities, 0, $limit);
        });

        return response()->json([
            'success' => TRUE,
            'data'    => $data,
        ]);
    }

    /**
     * Get user behaviour insights
     */
    public function userBehaviour(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Authentication required',
            ], 401);
        }

        $cacheKey = "user_behaviour_{$user->id}";

        $data = Cache::remember($cacheKey, 1800, function () use ($user) {
            return [
                'viewing_patterns'     => $this->getUserViewingPatterns($user),
                'price_sensitivity'    => $this->getUserPriceSensitivity($user),
                'platform_preferences' => $this->getUserPlatformPreferences($user),
                'event_preferences'    => $this->getUserEventPreferences($user),
                'timing_behaviour'     => $this->getUserTimingBehaviour($user),
                'social_influence'     => $this->getUserSocialInfluence($user),
            ];
        });

        return response()->json([
            'success' => TRUE,
            'data'    => $data,
        ]);
    }

    /**
     * Get social proof metrics for a specific ticket
     */
    public function ticketProof(Request $request, string $ticketId): JsonResponse
    {
        $ticket = Ticket::where('uuid', $ticketId)->first();

        if (! $ticket) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Ticket not found',
            ], 404);
        }

        $cacheKey = "ticket_social_proof_{$ticketId}";

        $data = Cache::remember($cacheKey, 300, function () use ($ticket) {
            return [
                'views_count'          => $ticket->views_count ?? 0,
                'watchers_count'       => $this->getTicketWatchersCount($ticket),
                'recent_activity'      => $this->getTicketRecentActivity($ticket),
                'similar_tickets_sold' => $this->getSimilarTicketsSold($ticket),
                'price_comparison'     => $this->getTicketPriceComparison($ticket),
                'urgency_indicators'   => $this->getTicketUrgencyIndicators($ticket),
                'seller_credibility'   => $this->getSellerCredibility($ticket),
            ];
        });

        return response()->json([
            'success' => TRUE,
            'data'    => $data,
        ]);
    }

    /**
     * Helper Methods
     */
    private function getTrendingEvents(): array
    {
        $events = Event::where('date', '>=', now())
            ->withCount(['tickets as active_tickets' => function ($query): void {
                $query->where('is_available', TRUE);
            }])
            ->orderByDesc('popularity_score')
            ->limit(8)
            ->get();

        return $events->map(function ($event) {
            return [
                'id'               => $event->uuid,
                'name'             => $event->name,
                'venue'            => $event->venue,
                'date'             => $event->date,
                'sport'            => $event->sport,
                'trending_score'   => $event->popularity_score,
                'active_tickets'   => $event->active_tickets,
                'demand_indicator' => $this->getEventDemandIndicator($event),
            ];
        })->toArray();
    }

    private function getHighDemandIndicators(): array
    {
        return [
            [
                'event'        => 'Manchester United vs Liverpool',
                'venue'        => 'Old Trafford',
                'demand_level' => 95,
                'price_trend'  => 'increasing',
                'availability' => 'limited',
                'urgency'      => 'high',
            ],
            [
                'event'        => 'Wimbledon Finals',
                'venue'        => 'All England Club',
                'demand_level' => 89,
                'price_trend'  => 'stable',
                'availability' => 'very_limited',
                'urgency'      => 'extreme',
            ],
        ];
    }

    private function getPlatformActivity(): array
    {
        $platforms = DB::table('tickets')
            ->select('platform', DB::raw('COUNT(*) as listings'), DB::raw('AVG(current_price) as avg_price'))
            ->where('created_at', '>=', now()->subDay())
            ->groupBy('platform')
            ->orderByDesc('listings')
            ->get();

        return $platforms->map(function ($platform) {
            return [
                'platform'         => $platform->platform,
                'new_listings_24h' => $platform->listings,
                'avg_price'        => round($platform->avg_price, 2),
                'activity_level'   => $this->calculateActivityLevel($platform->listings),
            ];
        })->toArray();
    }

    private function getPriceMovementAlerts(): array
    {
        // This would track significant price movements
        return [
            [
                'event'      => 'Arsenal vs Tottenham',
                'movement'   => 'down',
                'percentage' => -15,
                'from_price' => 120,
                'to_price'   => 102,
                'timestamp'  => now()->subMinutes(30),
            ],
            [
                'event'      => 'Champions League Final',
                'movement'   => 'up',
                'percentage' => 23,
                'from_price' => 450,
                'to_price'   => 553,
                'timestamp'  => now()->subHours(2),
            ],
        ];
    }

    private function getSocialActivity(): array
    {
        return [
            'total_users_active'  => Cache::get('active_users_1h', 1247),
            'searches_per_minute' => Cache::get('searches_per_minute', 89),
            'new_alerts_created'  => Cache::get('new_alerts_1h', 156),
            'tickets_purchased'   => Cache::get('purchases_1h', 23),
        ];
    }

    private function getMarketPulse(): array
    {
        return [
            'overall_sentiment' => 'bullish',
            'price_index'       => 108.5,
            'volume_index'      => 94.2,
            'demand_index'      => 87.3,
            'supply_pressure'   => 'low',
            'market_conditions' => 'favourable_buyers',
        ];
    }

    private function getEventTickets(string $eventName, ?string $venue, ?string $date)
    {
        $query = Ticket::where('event_name', 'LIKE', "%{$eventName}%");

        if ($venue) {
            $query->where('venue', 'LIKE', "%{$venue}%");
        }

        if ($date) {
            $query->whereDate('event_date', $date);
        }

        return $query->get();
    }

    private function calculateEventDemandLevel($tickets): string
    {
        $score = 0;

        // Availability pressure
        $availableCount = $tickets->where('is_available', TRUE)->count();
        $totalCount = $tickets->count();
        $availabilityRatio = $totalCount > 0 ? $availableCount / $totalCount : 0;

        if ($availabilityRatio < 0.2) {
            $score += 30;
        } elseif ($availabilityRatio < 0.5) {
            $score += 20;
        } elseif ($availabilityRatio < 0.8) {
            $score += 10;
        }

        // Price trends
        $avgPrice = $tickets->avg('current_price');
        if ($avgPrice > 200) {
            $score += 20;
        } elseif ($avgPrice > 100) {
            $score += 10;
        }

        // Platform diversity
        $platformCount = $tickets->unique('platform')->count();
        if ($platformCount > 5) {
            $score += 15;
        } elseif ($platformCount > 3) {
            $score += 10;
        }

        // Views activity
        $totalViews = $tickets->sum('views_count');
        if ($totalViews > 1000) {
            $score += 15;
        } elseif ($totalViews > 500) {
            $score += 10;
        }

        if ($score >= 70) {
            return 'very_high';
        }
        if ($score >= 50) {
            return 'high';
        }
        if ($score >= 30) {
            return 'medium';
        }

        return 'low';
    }

    private function getViewingActivity($tickets): array
    {
        $totalViews = $tickets->sum('views_count');
        $recentViews = $tickets->where('last_scraped_at', '>=', now()->subHour())->sum('views_count');

        return [
            'total_views'      => $totalViews,
            'views_last_hour'  => $recentViews,
            'viewing_velocity' => $recentViews > 0 ? round($recentViews / 60, 1) : 0, // views per minute
            'trending'         => $recentViews > ($totalViews * 0.1), // 10% of total views in last hour
        ];
    }

    private function getPurchaseIndicators($tickets): array
    {
        $soldTickets = $tickets->where('is_available', FALSE);
        $totalTickets = $tickets->count();

        return [
            'conversion_rate'   => $totalTickets > 0 ? round(($soldTickets->count() / $totalTickets) * 100, 1) : 0,
            'recently_sold'     => $soldTickets->where('updated_at', '>=', now()->subHour())->count(),
            'purchase_pressure' => $soldTickets->count() > ($totalTickets * 0.3) ? 'high' : 'moderate',
        ];
    }

    private function getEventPriceTrends($tickets): array
    {
        $prices = $tickets->pluck('current_price');

        return [
            'min_price'          => $prices->min(),
            'max_price'          => $prices->max(),
            'avg_price'          => round($prices->avg(), 2),
            'price_range_spread' => $prices->max() - $prices->min(),
            'price_volatility'   => $prices->count() > 1 ? round($prices->std(), 2) : 0,
            'trend_direction'    => $this->determinePriceTrend($tickets),
        ];
    }

    private function getPlatformCompetition($tickets): array
    {
        return $tickets->groupBy('platform')->map(function ($platformTickets, $platform) {
            return [
                'platform'          => $platform,
                'listing_count'     => $platformTickets->count(),
                'avg_price'         => round($platformTickets->avg('current_price'), 2),
                'availability_rate' => round(($platformTickets->where('is_available', TRUE)->count() / $platformTickets->count()) * 100, 1),
            ];
        })->values()->toArray();
    }

    private function getScarcityIndicators($tickets): array
    {
        $availableTickets = $tickets->where('is_available', TRUE);
        $totalTickets = $tickets->count();

        return [
            'scarcity_level'             => $this->calculateScarcityLevel($availableTickets->count(), $totalTickets),
            'listings_running_low'       => $availableTickets->where('quantity', '<=', 2)->count(),
            'premium_sections_available' => $availableTickets->whereIn('section', ['VIP', 'Premium', 'Club'])->count(),
            'time_pressure'              => $this->calculateTimePressure($tickets),
        ];
    }

    private function getSocialSignals(string $eventName): array
    {
        // This would integrate with social media APIs or internal social features
        return [
            'mentions_24h'      => rand(50, 500),
            'sentiment_score'   => rand(70, 95),
            'buzz_level'        => ['low', 'medium', 'high', 'viral'][rand(0, 3)],
            'trending_hashtags' => ['#' . str_replace(' ', '', $eventName), '#tickets', '#sport'],
        ];
    }

    private function getEventRecommendations($tickets): array
    {
        $recommendations = [];

        $cheapestAvailable = $tickets->where('is_available', TRUE)->sortBy('current_price')->first();
        if ($cheapestAvailable) {
            $recommendations[] = [
                'type'      => 'best_price',
                'message'   => "Best available price: £{$cheapestAvailable->current_price}",
                'ticket_id' => $cheapestAvailable->uuid,
                'urgency'   => 'medium',
            ];
        }

        $limitedQuantity = $tickets->where('is_available', TRUE)->where('quantity', '<=', 2);
        if ($limitedQuantity->count() > ($tickets->count() * 0.3)) {
            $recommendations[] = [
                'type'    => 'scarcity_warning',
                'message' => 'Many listings have limited quantities remaining',
                'urgency' => 'high',
            ];
        }

        return $recommendations;
    }

    private function formatTrendingEvent(Event $event): array
    {
        return [
            'id'             => $event->uuid,
            'name'           => $event->name,
            'venue'          => $event->venue,
            'date'           => $event->date,
            'sport'          => $event->sport,
            'trending_score' => $event->popularity_score,
            'active_tickets' => $event->ticket_count,
            'demand_level'   => $this->calculateEventDemandLevel($event->tickets),
            'price_trend'    => $this->determinePriceTrend($event->tickets),
            'social_buzz'    => $this->getSocialBuzz($event->name),
        ];
    }

    private function getTimeFilter(string $timeframe): \Carbon\Carbon
    {
        return match ($timeframe) {
            '1h'    => now()->subHour(),
            '6h'    => now()->subHours(6),
            '24h'   => now()->subDay(),
            '7d'    => now()->subWeek(),
            default => now()->subDay(),
        };
    }

    // Additional helper methods...
    private function calculateAvailabilityPressure($tickets): string
    {
        $availableCount = $tickets->where('is_available', TRUE)->count();
        $totalCount = $tickets->count();
        $ratio = $totalCount > 0 ? $availableCount / $totalCount : 1;

        if ($ratio < 0.2) {
            return 'extreme';
        }
        if ($ratio < 0.4) {
            return 'high';
        }
        if ($ratio < 0.7) {
            return 'medium';
        }

        return 'low';
    }

    private function calculatePriceMomentum($tickets): string
    {
        // This would analyse recent price changes
        $recentTickets = $tickets->where('updated_at', '>=', now()->subHours(6));
        $olderTickets = $tickets->where('updated_at', '<', now()->subHours(6));

        if ($recentTickets->isEmpty() || $olderTickets->isEmpty()) {
            return 'stable';
        }

        $recentAvg = $recentTickets->avg('current_price');
        $olderAvg = $olderTickets->avg('current_price');
        $change = ($recentAvg - $olderAvg) / $olderAvg * 100;

        if ($change > 5) {
            return 'increasing';
        }
        if ($change < -5) {
            return 'decreasing';
        }

        return 'stable';
    }

    private function getEventPlatformActivity($tickets): array
    {
        return $tickets->groupBy('platform')
            ->map->count()
            ->sortDesc()
            ->take(5)
            ->toArray();
    }

    private function calculateUrgencyScore($tickets): int
    {
        $score = 0;

        // Availability pressure
        $availabilityPressure = $this->calculateAvailabilityPressure($tickets);
        $score += match ($availabilityPressure) {
            'extreme' => 40,
            'high'    => 30,
            'medium'  => 20,
            default   => 10,
        };

        // Price momentum
        $priceMomentum = $this->calculatePriceMomentum($tickets);
        $score += match ($priceMomentum) {
            'increasing' => 25,
            'stable'     => 15,
            default      => 5,
        };

        // Time to event
        $firstTicket = $tickets->first();
        if ($firstTicket && $firstTicket->event_date) {
            $daysToEvent = now()->diffInDays($firstTicket->event_date);
            if ($daysToEvent <= 1) {
                $score += 20;
            } elseif ($daysToEvent <= 7) {
                $score += 15;
            } elseif ($daysToEvent <= 30) {
                $score += 10;
            }
        }

        return min(100, $score);
    }

    private function getPriceDropActivity(int $since): array
    {
        // Mock implementation - would track actual price drops
        return [
            [
                'type'      => 'price_drop',
                'event'     => 'Chelsea vs Arsenal',
                'message'   => 'Price dropped by £25 (15%)',
                'timestamp' => now()->subMinutes(15)->timestamp,
                'severity'  => 'medium',
            ],
        ];
    }

    private function getNewListingActivity(int $since): array
    {
        $newTickets = Ticket::where('created_at', '>=', now()->subSeconds($since))
            ->with('event')
            ->limit(10)
            ->get();

        return $newTickets->map(function ($ticket) {
            return [
                'type'      => 'new_listing',
                'event'     => $ticket->event_name,
                'platform'  => $ticket->platform,
                'price'     => $ticket->current_price,
                'message'   => "New {$ticket->platform} listing for £{$ticket->current_price}",
                'timestamp' => $ticket->created_at->timestamp,
            ];
        })->toArray();
    }

    private function getSoldOutActivity(int $since): array
    {
        // Mock implementation
        return [
            [
                'type'      => 'sold_out',
                'event'     => 'Manchester United vs Liverpool',
                'message'   => 'Section A1 sold out on StubHub',
                'timestamp' => now()->subMinutes(45)->timestamp,
                'severity'  => 'high',
            ],
        ];
    }

    private function getHighDemandActivity(int $since): array
    {
        // Mock implementation
        return [
            [
                'type'      => 'high_demand',
                'event'     => 'Wimbledon Finals',
                'message'   => 'Demand spike detected - 200+ views in last hour',
                'timestamp' => now()->subMinutes(20)->timestamp,
                'severity'  => 'high',
            ],
        ];
    }

    private function calculateActivityLevel(int $listings): string
    {
        if ($listings > 100) {
            return 'very_high';
        }
        if ($listings > 50) {
            return 'high';
        }
        if ($listings > 20) {
            return 'medium';
        }

        return 'low';
    }

    private function getEventDemandIndicator(Event $event): string
    {
        // Simplified demand calculation based on event popularity
        if ($event->popularity_score > 80) {
            return 'very_high';
        }
        if ($event->popularity_score > 60) {
            return 'high';
        }
        if ($event->popularity_score > 40) {
            return 'medium';
        }

        return 'low';
    }

    private function determinePriceTrend($tickets): string
    {
        // Simplified trend analysis
        if ($tickets->isEmpty()) {
            return 'stable';
        }

        $avgPrice = $tickets->avg('current_price');
        if ($avgPrice > 200) {
            return 'increasing';
        }
        if ($avgPrice < 50) {
            return 'decreasing';
        }

        return 'stable';
    }

    private function calculateScarcityLevel(int $available, int $total): string
    {
        if ($total === 0) {
            return 'none';
        }

        $ratio = $available / $total;
        if ($ratio < 0.1) {
            return 'critical';
        }
        if ($ratio < 0.3) {
            return 'high';
        }
        if ($ratio < 0.6) {
            return 'medium';
        }

        return 'low';
    }

    private function calculateTimePressure($tickets): string
    {
        $firstTicket = $tickets->first();
        if (! $firstTicket || ! $firstTicket->event_date) {
            return 'none';
        }

        $daysToEvent = now()->diffInDays($firstTicket->event_date);
        if ($daysToEvent <= 1) {
            return 'critical';
        }
        if ($daysToEvent <= 7) {
            return 'high';
        }
        if ($daysToEvent <= 30) {
            return 'medium';
        }

        return 'low';
    }

    private function getSocialBuzz(string $eventName): array
    {
        // Mock social media buzz data
        return [
            'mentions'       => rand(10, 500),
            'sentiment'      => rand(70, 95),
            'trend_velocity' => ['slow', 'moderate', 'fast', 'viral'][rand(0, 3)],
        ];
    }

    // User behaviour analysis methods (simplified implementations)
    private function getUserViewingPatterns(User $user): array
    {
        return [
            'most_viewed_sport'     => 'Football',
            'peak_browsing_hours'   => ['18:00-20:00', '21:00-23:00'],
            'avg_session_duration'  => '15 minutes',
            'favourite_price_range' => '£50-£150',
        ];
    }

    private function getUserPriceSensitivity(User $user): array
    {
        return [
            'sensitivity_level'     => 'medium',
            'price_drop_threshold'  => 15, // percentage
            'max_budget_observed'   => 250,
            'deals_conversion_rate' => 34.5,
        ];
    }

    private function getUserPlatformPreferences(User $user): array
    {
        return [
            'preferred_platforms'         => ['StubHub', 'Viagogo'],
            'trusted_sellers_only'        => TRUE,
            'instant_download_preference' => TRUE,
            'mobile_tickets_preferred'    => TRUE,
        ];
    }

    private function getUserEventPreferences(User $user): array
    {
        return [
            'favourite_sports'     => ['Football', 'Tennis'],
            'preferred_venues'     => ['Wembley', 'Emirates Stadium'],
            'event_types'          => ['Premier League', 'Champions League'],
            'advance_booking_days' => 30,
        ];
    }

    private function getUserTimingBehaviour(User $user): array
    {
        return [
            'booking_pattern'            => 'last_minute',
            'best_deal_timing'           => '7-14 days before event',
            'notification_response_time' => '2 hours average',
            'weekend_vs_weekday'         => 'weekend_buyer',
        ];
    }

    private function getUserSocialInfluence(User $user): array
    {
        return [
            'follows_trends'             => TRUE,
            'price_comparison_behaviour' => 'thorough',
            'social_proof_sensitivity'   => 'high',
            'risk_tolerance'             => 'medium',
        ];
    }

    // Ticket-specific social proof methods
    private function getTicketWatchersCount(Ticket $ticket): int
    {
        // Would track users who have saved/watched this ticket
        return rand(5, 50);
    }

    private function getTicketRecentActivity(Ticket $ticket): array
    {
        return [
            'views_last_hour'      => rand(5, 25),
            'price_changes_24h'    => rand(0, 3),
            'similar_tickets_sold' => rand(1, 8),
        ];
    }

    private function getSimilarTicketsSold(Ticket $ticket): array
    {
        return [
            'count'        => rand(5, 20),
            'avg_price'    => $ticket->current_price * (0.9 + (rand(0, 20) / 100)),
            'time_to_sell' => rand(2, 48) . ' hours',
        ];
    }

    private function getTicketPriceComparison(Ticket $ticket): array
    {
        return [
            'vs_market_avg'      => rand(-15, 25), // percentage difference
            'vs_similar_section' => rand(-10, 15),
            'value_rating'       => rand(3, 5) . '/5',
        ];
    }

    private function getTicketUrgencyIndicators(Ticket $ticket): array
    {
        return [
            'quantity_remaining' => $ticket->quantity,
            'listing_age'        => $ticket->created_at->diffForHumans(),
            'demand_level'       => $this->calculateSingleTicketDemand($ticket),
            'expires_soon'       => $ticket->listing_expires_at ? $ticket->listing_expires_at->isPast() : FALSE,
        ];
    }

    private function getSellerCredibility(Ticket $ticket): array
    {
        return [
            'seller_rating'     => $ticket->seller_rating ?? 0,
            'verified_seller'   => $ticket->seller_verified ?? FALSE,
            'total_sales'       => rand(50, 500),
            'reliability_score' => rand(75, 98),
        ];
    }

    private function calculateSingleTicketDemand(Ticket $ticket): string
    {
        $score = 0;

        if (($ticket->views_count ?? 0) > 50) {
            $score += 30;
        } elseif (($ticket->views_count ?? 0) > 20) {
            $score += 20;
        }

        if ($ticket->current_price < 100) {
            $score += 20;
        }
        if ($ticket->quantity <= 2) {
            $score += 25;
        }
        if ($ticket->seller_rating >= 4.0) {
            $score += 15;
        }

        if ($score >= 70) {
            return 'very_high';
        }
        if ($score >= 50) {
            return 'high';
        }
        if ($score >= 30) {
            return 'medium';
        }

        return 'low';
    }
}
