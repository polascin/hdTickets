<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Ticket Comparison Engine API Controller
 *
 * Provides side-by-side ticket comparison across platforms
 * Includes pricing breakdowns, seller ratings, and value analysis
 */
class TicketComparisonController extends Controller
{
    /**
     * Compare tickets for a specific event
     */
    public function compare(Request $request): JsonResponse
    {
        $request->validate([
            'event_name'          => 'required|string',
            'venue'               => 'nullable|string',
            'date'                => 'nullable|date',
            'section'             => 'nullable|string',
            'sort_by'             => 'nullable|string|in:price_total,price_face,value_score,platform,section',
            'filters'             => 'nullable|array',
            'filters.platforms'   => 'nullable|array',
            'filters.price_range' => 'nullable|array',
            'filters.sections'    => 'nullable|array',
            'filters.features'    => 'nullable|array',
        ]);

        $eventName = $request->get('event_name');
        $venue = $request->get('venue');
        $date = $request->get('date');
        $section = $request->get('section');
        $sortBy = $request->get('sort_by', 'price_total');
        $filters = $request->get('filters', []);

        $cacheKey = 'ticket_comparison_' . md5(serialize($request->all()));

        $data = Cache::remember($cacheKey, 600, function () use ($eventName, $venue, $date, $section, $sortBy, $filters) {
            $query = $this->buildComparisonQuery($eventName, $venue, $date, $section, $filters);
            $tickets = $this->sortTickets($query, $sortBy);

            return [
                'tickets' => $tickets->map(function ($ticket) {
                    return $this->formatTicketForComparison($ticket);
                }),
                'summary'             => $this->getComparisonSummary($tickets),
                'best_value'          => $this->findBestValue($tickets),
                'platform_comparison' => $this->getPlatformComparison($tickets),
                'filters_applied'     => $filters,
            ];
        });

        return response()->json([
            'success' => TRUE,
            'data'    => $data,
        ]);
    }

    /**
     * Get detailed comparison for specific tickets
     */
    public function detailed(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_ids'   => 'required|array|min:2|max:5',
            'ticket_ids.*' => 'string|exists:tickets,uuid',
        ]);

        $ticketIds = $request->get('ticket_ids');

        $tickets = Ticket::whereIn('uuid', $ticketIds)->get();

        if ($tickets->count() < 2) {
            return response()->json([
                'success' => FALSE,
                'message' => 'At least 2 tickets required for comparison',
            ], 400);
        }

        $comparison = [
            'tickets' => $tickets->map(function ($ticket) {
                return $this->formatDetailedTicket($ticket);
            }),
            'side_by_side'      => $this->getSideBySideComparison($tickets),
            'recommendation'    => $this->getRecommendation($tickets),
            'savings_potential' => $this->calculateSavings($tickets),
        ];

        return response()->json([
            'success' => TRUE,
            'data'    => $comparison,
        ]);
    }

    /**
     * Get platform comparison statistics
     */
    public function platforms(Request $request): JsonResponse
    {
        $request->validate([
            'event_name' => 'nullable|string',
            'sport'      => 'nullable|string',
            'timeframe'  => 'nullable|string|in:24h,7d,30d',
        ]);

        $eventName = $request->get('event_name');
        $sport = $request->get('sport');
        $timeframe = $request->get('timeframe', '7d');

        $cacheKey = "platform_comparison_{$sport}_{$timeframe}";

        $data = Cache::remember($cacheKey, 1800, function () use ($eventName, $sport, $timeframe) {
            $timeFilter = match ($timeframe) {
                '24h'   => now()->subDay(),
                '7d'    => now()->subWeek(),
                '30d'   => now()->subMonth(),
                default => now()->subWeek(),
            };

            $query = Ticket::where('created_at', '>=', $timeFilter);

            if ($eventName) {
                $query->where('event_name', 'LIKE', "%{$eventName}%");
            }

            if ($sport) {
                $query->where('sport', $sport);
            }

            $platforms = $query->groupBy('platform')
                ->select(
                    'platform',
                    DB::raw('COUNT(*) as ticket_count'),
                    DB::raw('AVG(current_price) as avg_price'),
                    DB::raw('MIN(current_price) as min_price'),
                    DB::raw('MAX(current_price) as max_price'),
                    DB::raw('AVG(CASE WHEN seller_rating > 0 THEN seller_rating END) as avg_rating'),
                    DB::raw('SUM(CASE WHEN is_available = 1 THEN 1 ELSE 0 END) as available_count')
                )
                ->get()
                ->map(function ($platform) {
                    return [
                        'platform'     => $platform->platform,
                        'ticket_count' => $platform->ticket_count,
                        'avg_price'    => round($platform->avg_price, 2),
                        'price_range'  => [
                            'min' => $platform->min_price,
                            'max' => $platform->max_price,
                        ],
                        'avg_rating'        => round($platform->avg_rating ?? 0, 1),
                        'availability_rate' => round(($platform->available_count / $platform->ticket_count) * 100, 1),
                        'market_share'      => 0, // Will be calculated below
                    ];
                });

            // Calculate market share
            $totalTickets = $platforms->sum('ticket_count');
            $platforms = $platforms->map(function ($platform) use ($totalTickets) {
                $platform['market_share'] = round(($platform['ticket_count'] / $totalTickets) * 100, 1);

                return $platform;
            });

            return [
                'platforms' => $platforms->sortByDesc('market_share')->values(),
                'summary'   => [
                    'total_platforms'        => $platforms->count(),
                    'total_tickets'          => $totalTickets,
                    'avg_market_price'       => round($platforms->avg('avg_price'), 2),
                    'highest_rated_platform' => $platforms->sortByDesc('avg_rating')->first()['platform'] ?? NULL,
                ],
            ];
        });

        return response()->json([
            'success' => TRUE,
            'data'    => $data,
        ]);
    }

    /**
     * Get value analysis for tickets
     */
    public function valueAnalysis(Request $request): JsonResponse
    {
        $request->validate([
            'event_name' => 'required|string',
            'section'    => 'nullable|string',
        ]);

        $eventName = $request->get('event_name');
        $section = $request->get('section');

        $query = Ticket::where('event_name', 'LIKE', "%{$eventName}%");

        if ($section) {
            $query->where('section', $section);
        }

        $tickets = $query->get();

        if ($tickets->isEmpty()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'No tickets found for the specified event',
            ], 404);
        }

        $analysis = [
            'price_distribution'        => $this->getPriceDistribution($tickets),
            'value_segments'            => $this->getValueSegments($tickets),
            'platform_value_comparison' => $this->getPlatformValueComparison($tickets),
            'recommendations'           => $this->getValueRecommendations($tickets),
            'market_insights'           => $this->getMarketInsights($tickets),
        ];

        return response()->json([
            'success' => TRUE,
            'data'    => $analysis,
        ]);
    }

    /**
     * Build comparison query
     */
    private function buildComparisonQuery(string $eventName, ?string $venue, ?string $date, ?string $section, array $filters)
    {
        $query = Ticket::where('event_name', 'LIKE', "%{$eventName}%");

        if ($venue) {
            $query->where('venue', 'LIKE', "%{$venue}%");
        }

        if ($date) {
            $query->whereDate('event_date', $date);
        }

        if ($section) {
            $query->where('section', 'LIKE', "%{$section}%");
        }

        // Apply filters
        if (!empty($filters['platforms'])) {
            $query->whereIn('platform', $filters['platforms']);
        }

        if (!empty($filters['price_range'])) {
            $priceRange = $filters['price_range'];
            if (isset($priceRange['min'])) {
                $query->where('current_price', '>=', $priceRange['min']);
            }
            if (isset($priceRange['max'])) {
                $query->where('current_price', '<=', $priceRange['max']);
            }
        }

        if (!empty($filters['sections'])) {
            $query->whereIn('section', $filters['sections']);
        }

        return $query;
    }

    /**
     * Sort tickets by specified criteria
     */
    private function sortTickets($query, string $sortBy)
    {
        switch ($sortBy) {
            case 'price_total':
                return $query->orderBy('current_price', 'asc')->get();
            case 'price_face':
                return $query->orderBy('face_value', 'asc')->get();
            case 'value_score':
                return $query->get()->sortByDesc(function ($ticket) {
                    return $this->calculateValueScore($ticket);
                });
            case 'platform':
                return $query->orderBy('platform')->get();
            case 'section':
                return $query->orderBy('section')->get();
            default:
                return $query->orderBy('current_price', 'asc')->get();
        }
    }

    /**
     * Format ticket for comparison
     */
    private function formatTicketForComparison(Ticket $ticket): array
    {
        return [
            'id'           => $ticket->uuid,
            'event_name'   => $ticket->event_name,
            'platform'     => $ticket->platform,
            'section'      => $ticket->section,
            'row'          => $ticket->row,
            'seat_numbers' => $ticket->seat_numbers,
            'quantity'     => $ticket->quantity,
            'pricing'      => [
                'face_value'        => $ticket->face_value,
                'current_price'     => $ticket->current_price,
                'fees'              => $ticket->platform_fees ?? 0,
                'total_price'       => $ticket->current_price + ($ticket->platform_fees ?? 0),
                'markup_percentage' => $this->calculateMarkup($ticket),
            ],
            'seller' => [
                'type'     => $ticket->seller_type,
                'rating'   => $ticket->seller_rating,
                'verified' => $ticket->seller_verified ?? FALSE,
            ],
            'features' => [
                'instant_download' => $ticket->instant_download ?? FALSE,
                'mobile_transfer'  => $ticket->mobile_transfer ?? FALSE,
                'guarantee'        => $ticket->has_guarantee ?? FALSE,
                'parking_included' => $ticket->parking_included ?? FALSE,
            ],
            'availability' => [
                'is_available' => $ticket->is_available,
                'last_updated' => $ticket->last_scraped_at,
                'expires_at'   => $ticket->listing_expires_at,
            ],
            'value_score'   => $this->calculateValueScore($ticket),
            'is_best_value' => FALSE, // Will be set by findBestValue method
        ];
    }

    /**
     * Calculate value score for a ticket
     */
    private function calculateValueScore(Ticket $ticket): float
    {
        $score = 50; // Base score

        // Price factor (lower price = higher score)
        $price = $ticket->current_price + ($ticket->platform_fees ?? 0);
        if ($price <= 50) {
            $score += 20;
        } elseif ($price <= 100) {
            $score += 15;
        } elseif ($price <= 200) {
            $score += 10;
        } elseif ($price > 300) {
            $score -= 10;
        }

        // Seller rating factor
        if ($ticket->seller_rating >= 4.5) {
            $score += 15;
        } elseif ($ticket->seller_rating >= 4.0) {
            $score += 10;
        } elseif ($ticket->seller_rating >= 3.5) {
            $score += 5;
        } elseif ($ticket->seller_rating < 3.0) {
            $score -= 10;
        }

        // Features factor
        if ($ticket->instant_download) {
            $score += 5;
        }
        if ($ticket->mobile_transfer) {
            $score += 5;
        }
        if ($ticket->has_guarantee) {
            $score += 10;
        }
        if ($ticket->parking_included) {
            $score += 5;
        }

        // Platform reliability factor
        $reliablePlatforms = ['Ticketmaster', 'StubHub', 'Viagogo'];
        if (in_array($ticket->platform, $reliablePlatforms)) {
            $score += 10;
        }

        // Availability factor
        if (!$ticket->is_available) {
            $score = 0;
        }

        return max(0, min(100, $score));
    }

    /**
     * Calculate markup percentage
     */
    private function calculateMarkup(Ticket $ticket): float
    {
        if (!$ticket->face_value || $ticket->face_value <= 0) {
            return 0;
        }

        $totalPrice = $ticket->current_price + ($ticket->platform_fees ?? 0);

        return round((($totalPrice - $ticket->face_value) / $ticket->face_value) * 100, 1);
    }

    /**
     * Get comparison summary
     */
    private function getComparisonSummary($tickets): array
    {
        if ($tickets->isEmpty()) {
            return [];
        }

        $prices = $tickets->pluck('current_price');
        $totalPrices = $tickets->map(function ($ticket) {
            return $ticket->current_price + ($ticket->platform_fees ?? 0);
        });

        return [
            'total_tickets'   => $tickets->count(),
            'platforms_count' => $tickets->unique('platform')->count(),
            'price_range'     => [
                'min' => $prices->min(),
                'max' => $prices->max(),
                'avg' => round($prices->avg(), 2),
            ],
            'total_price_range' => [
                'min' => $totalPrices->min(),
                'max' => $totalPrices->max(),
                'avg' => round($totalPrices->avg(), 2),
            ],
            'available_tickets' => $tickets->where('is_available', TRUE)->count(),
        ];
    }

    /**
     * Find best value ticket
     */
    private function findBestValue($tickets)
    {
        if ($tickets->isEmpty()) {
            return NULL;
        }

        $bestValue = $tickets->sortByDesc(function ($ticket) {
            return $this->calculateValueScore($ticket);
        })->first();

        return $bestValue ? $this->formatTicketForComparison($bestValue) : NULL;
    }

    /**
     * Get platform comparison
     */
    private function getPlatformComparison($tickets): array
    {
        return $tickets->groupBy('platform')->map(function ($platformTickets, $platform) {
            $prices = $platformTickets->pluck('current_price');
            $ratings = $platformTickets->pluck('seller_rating')->filter();

            return [
                'platform'     => $platform,
                'ticket_count' => $platformTickets->count(),
                'avg_price'    => round($prices->avg(), 2),
                'min_price'    => $prices->min(),
                'max_price'    => $prices->max(),
                'avg_rating'   => $ratings->isNotEmpty() ? round($ratings->avg(), 1) : NULL,
                'features'     => [
                    'instant_download' => $platformTickets->where('instant_download', TRUE)->count(),
                    'guarantee'        => $platformTickets->where('has_guarantee', TRUE)->count(),
                ],
            ];
        })->values()->toArray();
    }

    /**
     * Format detailed ticket information
     */
    private function formatDetailedTicket(Ticket $ticket): array
    {
        $basic = $this->formatTicketForComparison($ticket);

        // Add more detailed information
        $basic['detailed_info'] = [
            'listing_id'       => $ticket->external_id,
            'listed_at'        => $ticket->created_at,
            'view_count'       => $ticket->views_count ?? 0,
            'price_history'    => $this->getTicketPriceHistory($ticket),
            'similar_listings' => $this->getSimilarListings($ticket),
        ];

        return $basic;
    }

    /**
     * Get side by side comparison
     */
    private function getSideBySideComparison($tickets): array
    {
        $fields = [
            'platform', 'section', 'row', 'quantity', 'current_price',
            'platform_fees', 'seller_rating', 'instant_download', 'has_guarantee',
        ];

        $comparison = [];

        foreach ($fields as $field) {
            $comparison[$field] = $tickets->map(function ($ticket) use ($field) {
                return $ticket->$field;
            })->toArray();
        }

        return $comparison;
    }

    /**
     * Get recommendation based on comparison
     */
    private function getRecommendation($tickets): array
    {
        $bestValue = $tickets->sortByDesc(function ($ticket) {
            return $this->calculateValueScore($ticket);
        })->first();

        $cheapest = $tickets->sortBy('current_price')->first();
        $highestRated = $tickets->sortByDesc('seller_rating')->first();

        return [
            'best_value'     => $bestValue->uuid,
            'cheapest'       => $cheapest->uuid,
            'highest_rated'  => $highestRated->uuid,
            'recommendation' => $bestValue->uuid,
            'reasoning'      => $this->getRecommendationReasoning($bestValue, $tickets),
        ];
    }

    /**
     * Calculate potential savings
     */
    private function calculateSavings($tickets): array
    {
        $prices = $tickets->pluck('current_price');
        $totalPrices = $tickets->map(function ($ticket) {
            return $ticket->current_price + ($ticket->platform_fees ?? 0);
        });

        return [
            'max_savings'        => $prices->max() - $prices->min(),
            'max_total_savings'  => $totalPrices->max() - $totalPrices->min(),
            'avg_vs_min'         => round($prices->avg() - $prices->min(), 2),
            'percentage_savings' => round((($prices->max() - $prices->min()) / $prices->max()) * 100, 1),
        ];
    }

    /**
     * Additional helper methods for value analysis
     */
    private function getPriceDistribution($tickets): array
    {
        $prices = $tickets->pluck('current_price');

        return [
            'min'       => $prices->min(),
            'max'       => $prices->max(),
            'median'    => $prices->median(),
            'avg'       => round($prices->avg(), 2),
            'std_dev'   => round($prices->std(), 2),
            'quartiles' => [
                'q1' => $prices->percentile(25),
                'q2' => $prices->median(),
                'q3' => $prices->percentile(75),
            ],
        ];
    }

    private function getValueSegments($tickets): array
    {
        $segments = [
            'budget'    => $tickets->filter(fn ($t) => $t->current_price < 100),
            'mid_range' => $tickets->filter(fn ($t) => $t->current_price >= 100 && $t->current_price < 300),
            'premium'   => $tickets->filter(fn ($t) => $t->current_price >= 300),
        ];

        return collect($segments)->map(function ($segmentTickets, $segment) {
            return [
                'segment'    => $segment,
                'count'      => $segmentTickets->count(),
                'avg_price'  => round($segmentTickets->avg('current_price'), 2),
                'best_value' => $segmentTickets->sortByDesc(function ($ticket) {
                    return $this->calculateValueScore($ticket);
                })->first()?->uuid,
            ];
        })->values()->toArray();
    }

    private function getPlatformValueComparison($tickets): array
    {
        return $tickets->groupBy('platform')->map(function ($platformTickets, $platform) {
            $avgValueScore = $platformTickets->avg(function ($ticket) {
                return $this->calculateValueScore($ticket);
            });

            return [
                'platform'              => $platform,
                'avg_value_score'       => round($avgValueScore, 1),
                'ticket_count'          => $platformTickets->count(),
                'price_competitiveness' => $this->calculatePriceCompetitiveness($platformTickets),
            ];
        })->sortByDesc('avg_value_score')->values()->toArray();
    }

    private function getValueRecommendations($tickets): array
    {
        $recommendations = [];

        // Best overall value
        $bestValue = $tickets->sortByDesc(function ($ticket) {
            return $this->calculateValueScore($ticket);
        })->first();

        if ($bestValue) {
            $recommendations[] = [
                'type'      => 'best_value',
                'ticket_id' => $bestValue->uuid,
                'reason'    => 'Highest value score combining price, features, and reliability',
            ];
        }

        // Best budget option
        $budgetTickets = $tickets->filter(fn ($t) => $t->current_price < 100);
        if ($budgetTickets->isNotEmpty()) {
            $bestBudget = $budgetTickets->sortByDesc(function ($ticket) {
                return $this->calculateValueScore($ticket);
            })->first();

            $recommendations[] = [
                'type'      => 'best_budget',
                'ticket_id' => $bestBudget->uuid,
                'reason'    => 'Best value under £100',
            ];
        }

        return $recommendations;
    }

    private function getMarketInsights($tickets): array
    {
        return [
            'market_trend'          => $this->determineMarketTrend($tickets),
            'platform_distribution' => $tickets->groupBy('platform')->map->count(),
            'availability_pressure' => $this->calculateAvailabilityPressure($tickets),
            'pricing_strategy'      => $this->determinePricingStrategy($tickets),
        ];
    }

    private function calculatePriceCompetitiveness($platformTickets): string
    {
        $avgPrice = $platformTickets->avg('current_price');

        if ($avgPrice < 100) {
            return 'very_competitive';
        }
        if ($avgPrice < 200) {
            return 'competitive';
        }
        if ($avgPrice < 300) {
            return 'moderate';
        }

        return 'premium';
    }

    private function determineMarketTrend($tickets): string
    {
        // Simplified trend analysis - in reality you'd look at historical data
        $avgMarkup = $tickets->avg(function ($ticket) {
            return $this->calculateMarkup($ticket);
        });

        if ($avgMarkup > 50) {
            return 'sellers_market';
        }
        if ($avgMarkup < 20) {
            return 'buyers_market';
        }

        return 'balanced';
    }

    private function calculateAvailabilityPressure($tickets): string
    {
        $availablePercent = ($tickets->where('is_available', TRUE)->count() / $tickets->count()) * 100;

        if ($availablePercent > 80) {
            return 'low';
        }
        if ($availablePercent > 50) {
            return 'medium';
        }

        return 'high';
    }

    private function determinePricingStrategy($tickets): string
    {
        $priceVariance = $tickets->pluck('current_price')->std();

        if ($priceVariance > 100) {
            return 'diverse_pricing';
        }
        if ($priceVariance < 20) {
            return 'uniform_pricing';
        }

        return 'moderate_variance';
    }

    private function getTicketPriceHistory(Ticket $ticket): array
    {
        // Placeholder - would get from price_history table
        return [];
    }

    private function getSimilarListings(Ticket $ticket): array
    {
        // Placeholder - would find similar tickets
        return [];
    }

    private function getRecommendationReasoning(Ticket $recommended, $allTickets): string
    {
        $reasons = [];

        if ($recommended->current_price <= $allTickets->min('current_price')) {
            $reasons[] = 'lowest price';
        }

        if ($recommended->seller_rating >= 4.0) {
            $reasons[] = 'highly rated seller';
        }

        if ($recommended->has_guarantee) {
            $reasons[] = 'includes guarantee';
        }

        if ($recommended->instant_download) {
            $reasons[] = 'instant delivery';
        }

        if (empty($reasons)) {
            $reasons[] = 'best overall value';
        }

        return 'Recommended for: ' . implode(', ', $reasons);
    }
}
