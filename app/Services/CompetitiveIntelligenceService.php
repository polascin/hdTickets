<?php declare(strict_types=1);

namespace App\Services;

use App\Models\ScrapedTicket;
use App\Models\TicketSource;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Competitive Intelligence Service
 *
 * Provides comprehensive market analysis and competitive insights for sports event tickets.
 * Analyzes pricing strategies, market positioning, and competitive advantages across platforms.
 */
class CompetitiveIntelligenceService
{
    private const CACHE_TTL = 3600; // 1 hour

    private const CACHE_PREFIX = 'competitive_intelligence';

    /**
     * Get comprehensive competitive analysis dashboard data
     */
    public function getCompetitiveDashboard(array $filters = []): array
    {
        $cacheKey = self::CACHE_PREFIX . '_dashboard_' . md5(serialize($filters));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters) {
            return [
                'market_overview'      => $this->getMarketOverview($filters),
                'price_comparison'     => $this->getPriceComparison($filters),
                'platform_positioning' => $this->getPlatformPositioning($filters),
                'competitive_gaps'     => $this->getCompetitiveGaps($filters),
                'market_share'         => $this->getMarketShare($filters),
                'pricing_strategies'   => $this->getPricingStrategies($filters),
                'opportunity_analysis' => $this->getOpportunityAnalysis($filters),
                'threat_assessment'    => $this->getThreatAssessment($filters),
            ];
        });
    }

    /**
     * Market overview with key competitive metrics
     */
    public function getMarketOverview(array $filters = []): array
    {
        $query = ScrapedTicket::query()
            ->join('ticket_sources', 'scraped_tickets.source_id', '=', 'ticket_sources.id')
            ->where('scraped_tickets.created_at', '>=', Carbon::now()->subDays(30));

        $this->applyFilters($query, $filters);

        $totalTickets = $query->count();
        $avgPrice = $query->avg('scraped_tickets.price');
        $platformCount = $query->distinct('source_id')->count();

        $priceRanges = $query
            ->selectRaw('
                COUNT(*) as total,
                AVG(price) as avg_price,
                MIN(price) as min_price,
                MAX(price) as max_price,
                CASE 
                    WHEN price < 50 THEN "budget"
                    WHEN price < 150 THEN "mid_range" 
                    WHEN price < 300 THEN "premium"
                    ELSE "luxury"
                END as price_segment
            ')
            ->groupBy('price_segment')
            ->get();

        return [
            'summary' => [
                'total_tickets'  => $totalTickets,
                'average_price'  => round($avgPrice, 2),
                'platform_count' => $platformCount,
                'last_updated'   => Carbon::now()->toISOString(),
            ],
            'price_segments' => $priceRanges->mapWithKeys(function ($segment) {
                return [$segment->price_segment => [
                    'count'     => $segment->total,
                    'avg_price' => round($segment->avg_price, 2),
                    'min_price' => round($segment->min_price, 2),
                    'max_price' => round($segment->max_price, 2),
                ]];
            }),
            'market_trends' => $this->getMarketTrends($filters),
        ];
    }

    /**
     * Cross-platform price comparison analysis
     */
    public function getPriceComparison(array $filters = []): array
    {
        $cacheKey = self::CACHE_PREFIX . '_price_comparison_' . md5(serialize($filters));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters) {
            // Similar events across platforms
            $eventComparisons = DB::table('scraped_tickets as st1')
                ->join('ticket_sources as ts1', 'st1.source_id', '=', 'ts1.id')
                ->join('scraped_tickets as st2', function ($join) {
                    $join->on('st1.event_name', '=', 'st2.event_name')
                         ->on('st1.source_id', '!=', 'st2.source_id');
                })
                ->join('ticket_sources as ts2', 'st2.source_id', '=', 'ts2.id')
                ->select([
                    'st1.event_name',
                    'st1.event_date',
                    'ts1.name as platform1',
                    'ts2.name as platform2',
                    'st1.price as price1',
                    'st2.price as price2',
                    DB::raw('ABS(st1.price - st2.price) as price_diff'),
                    DB::raw('((st1.price - st2.price) / st2.price) * 100 as price_diff_percent'),
                ])
                ->where('st1.created_at', '>=', Carbon::now()->subDays(7))
                ->orderBy('price_diff', 'desc')
                ->limit(50)
                ->get();

            // Platform pricing statistics
            $platformStats = ScrapedTicket::query()
                ->join('ticket_sources', 'scraped_tickets.source_id', '=', 'ticket_sources.id')
                ->selectRaw('
                    ticket_sources.name as platform,
                    COUNT(*) as ticket_count,
                    AVG(price) as avg_price,
                    MIN(price) as min_price,
                    MAX(price) as max_price,
                    STDDEV(price) as price_stddev
                ')
                ->where('scraped_tickets.created_at', '>=', Carbon::now()->subDays(30))
                ->groupBy('ticket_sources.id', 'ticket_sources.name')
                ->orderBy('avg_price', 'desc')
                ->get();

            return [
                'event_comparisons'      => $eventComparisons,
                'platform_statistics'    => $platformStats,
                'price_gaps'             => $this->identifyPriceGaps($eventComparisons),
                'competitive_advantages' => $this->analyzeCompetitiveAdvantages($platformStats),
            ];
        });
    }

    /**
     * Platform market positioning analysis
     */
    public function getPlatformPositioning(array $filters = []): array
    {
        $platforms = TicketSource::with(['scrapedTickets' => function ($query) use ($filters) {
            $query->where('created_at', '>=', Carbon::now()->subDays(30));
            $this->applyTicketFilters($query, $filters);
        }])->get();

        $positioning = $platforms->map(function ($platform) {
            $tickets = $platform->scrapedTickets;

            if ($tickets->isEmpty()) {
                return NULL;
            }

            $avgPrice = $tickets->avg('price');
            $totalTickets = $tickets->count();
            $priceRange = $tickets->max('price') - $tickets->min('price');

            // Calculate market position
            $position = $this->calculateMarketPosition($avgPrice, $totalTickets, $priceRange);

            return [
                'platform'              => $platform->name,
                'market_position'       => $position,
                'avg_price'             => round($avgPrice, 2),
                'ticket_volume'         => $totalTickets,
                'price_range'           => round($priceRange, 2),
                'specialization'        => $this->identifySpecialization($tickets),
                'competitive_strengths' => $this->identifyStrengths($platform, $tickets),
                'market_share_estimate' => $this->estimateMarketShare($totalTickets, $platforms->sum(fn ($p) => $p->scrapedTickets->count())),
            ];
        })->filter();

        return [
            'platform_positions' => $positioning,
            'market_leaders'     => $positioning->sortByDesc('market_share_estimate')->take(3),
            'niche_players'      => $positioning->where('specialization', '!=', 'general')->take(5),
            'positioning_matrix' => $this->createPositioningMatrix($positioning),
        ];
    }

    /**
     * Identify competitive gaps and opportunities
     */
    public function getCompetitiveGaps(array $filters = []): array
    {
        $cacheKey = self::CACHE_PREFIX . '_gaps_' . md5(serialize($filters));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters) {
            // Price gaps analysis
            $priceGaps = $this->identifyPriceGaps();

            // Geographic gaps
            $geographicGaps = $this->identifyGeographicGaps($filters);

            // Sport category gaps
            $categoryGaps = $this->identifyCategoryGaps($filters);

            // Timing gaps
            $timingGaps = $this->identifyTimingGaps($filters);

            return [
                'price_opportunities'      => $priceGaps,
                'geographic_opportunities' => $geographicGaps,
                'category_opportunities'   => $categoryGaps,
                'timing_opportunities'     => $timingGaps,
                'overall_score'            => $this->calculateOpportunityScore($priceGaps, $geographicGaps, $categoryGaps, $timingGaps),
            ];
        });
    }

    /**
     * Market share analysis across platforms
     */
    public function getMarketShare(array $filters = []): array
    {
        $query = ScrapedTicket::query()
            ->join('ticket_sources', 'scraped_tickets.source_id', '=', 'ticket_sources.id')
            ->selectRaw('
                ticket_sources.name as platform,
                COUNT(*) as ticket_count,
                SUM(price) as total_value,
                AVG(price) as avg_price
            ')
            ->where('scraped_tickets.created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('ticket_sources.id', 'ticket_sources.name');

        $this->applyFilters($query, $filters);

        $marketData = $query->get();
        $totalTickets = $marketData->sum('ticket_count');
        $totalValue = $marketData->sum('total_value');

        $marketShare = $marketData->map(function ($platform) use ($totalTickets, $totalValue) {
            return [
                'platform'      => $platform->platform,
                'ticket_share'  => round(($platform->ticket_count / $totalTickets) * 100, 2),
                'value_share'   => round(($platform->total_value / $totalValue) * 100, 2),
                'avg_price'     => round($platform->avg_price, 2),
                'total_tickets' => $platform->ticket_count,
                'total_value'   => round($platform->total_value, 2),
            ];
        })->sortByDesc('value_share');

        return [
            'market_share_data'     => $marketShare,
            'market_concentration'  => $this->calculateMarketConcentration($marketShare),
            'growth_trends'         => $this->getMarketShareTrends($filters),
            'competitive_intensity' => $this->calculateCompetitiveIntensity($marketShare),
        ];
    }

    /**
     * Pricing strategies analysis
     */
    public function getPricingStrategies(array $filters = []): array
    {
        $platforms = TicketSource::all();

        $strategies = $platforms->map(function ($platform) use ($filters) {
            $tickets = ScrapedTicket::where('source_id', $platform->id)
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->get();

            if ($tickets->isEmpty()) {
                return NULL;
            }

            return [
                'platform'             => $platform->name,
                'strategy_type'        => $this->identifyPricingStrategy($tickets),
                'price_consistency'    => $this->calculatePriceConsistency($tickets),
                'premium_positioning'  => $this->calculatePremiumPositioning($tickets),
                'discount_frequency'   => $this->calculateDiscountFrequency($tickets),
                'dynamic_pricing'      => $this->detectDynamicPricing($tickets),
                'competitive_response' => $this->analyzeCompetitiveResponse($platform, $tickets),
            ];
        })->filter();

        return [
            'platform_strategies'    => $strategies,
            'strategy_effectiveness' => $this->evaluateStrategyEffectiveness($strategies),
            'market_recommendations' => $this->generatePricingRecommendations($strategies),
        ];
    }

    /**
     * Opportunity analysis for market expansion
     */
    public function getOpportunityAnalysis(array $filters = []): array
    {
        return [
            'underserved_segments'      => $this->identifyUnderservedSegments($filters),
            'price_optimization'        => $this->identifyPriceOptimization($filters),
            'geographic_expansion'      => $this->identifyGeographicOpportunities($filters),
            'partnership_opportunities' => $this->identifyPartnershipOpportunities($filters),
            'technology_gaps'           => $this->identifyTechnologyGaps($filters),
        ];
    }

    /**
     * Threat assessment and risk analysis
     */
    public function getThreatAssessment(array $filters = []): array
    {
        return [
            'competitive_threats'    => $this->identifyCompetitiveThreats($filters),
            'market_disruption_risk' => $this->assessDisruptionRisk($filters),
            'price_war_indicators'   => $this->detectPriceWarIndicators($filters),
            'market_saturation'      => $this->assessMarketSaturation($filters),
            'regulatory_risks'       => $this->assessRegulatoryRisks($filters),
        ];
    }

    // Helper methods

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['sport'])) {
            $query->where('scraped_tickets.sport', $filters['sport']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('scraped_tickets.event_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('scraped_tickets.event_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['price_min'])) {
            $query->where('scraped_tickets.price', '>=', $filters['price_min']);
        }

        if (!empty($filters['price_max'])) {
            $query->where('scraped_tickets.price', '<=', $filters['price_max']);
        }
    }

    private function applyTicketFilters($query, array $filters): void
    {
        if (!empty($filters['sport'])) {
            $query->where('sport', $filters['sport']);
        }

        if (!empty($filters['price_min'])) {
            $query->where('price', '>=', $filters['price_min']);
        }

        if (!empty($filters['price_max'])) {
            $query->where('price', '<=', $filters['price_max']);
        }
    }

    private function getMarketTrends(array $filters): array
    {
        $trends = [];

        // Weekly price trends
        $weeklyTrends = ScrapedTicket::query()
            ->selectRaw('
                WEEK(created_at) as week,
                AVG(price) as avg_price,
                COUNT(*) as ticket_count
            ')
            ->where('created_at', '>=', Carbon::now()->subWeeks(12))
            ->groupBy('week')
            ->orderBy('week')
            ->get();

        $trends['weekly_price'] = $weeklyTrends;

        return $trends;
    }

    private function identifyPriceGaps(Collection $comparisons = NULL): array
    {
        if (!$comparisons) {
            $comparisons = collect();
        }

        $significantGaps = $comparisons->where('price_diff_percent', '>', 20);

        return [
            'high_gap_events'     => $significantGaps->take(10),
            'average_gap_percent' => $comparisons->avg('price_diff_percent'),
            'opportunity_count'   => $significantGaps->count(),
        ];
    }

    private function analyzeCompetitiveAdvantages(Collection $platformStats): array
    {
        $advantages = [];

        $lowestAvg = $platformStats->min('avg_price');
        $highestAvg = $platformStats->max('avg_price');
        $mostTickets = $platformStats->max('ticket_count');

        foreach ($platformStats as $platform) {
            $platformAdvantages = [];

            if ($platform->avg_price == $lowestAvg) {
                $platformAdvantages[] = 'lowest_prices';
            }

            if ($platform->ticket_count == $mostTickets) {
                $platformAdvantages[] = 'highest_volume';
            }

            if ($platform->price_stddev < 50) {
                $platformAdvantages[] = 'consistent_pricing';
            }

            $advantages[$platform->platform] = $platformAdvantages;
        }

        return $advantages;
    }

    private function calculateMarketPosition($avgPrice, $ticketCount, $priceRange): string
    {
        $priceScore = $avgPrice > 200 ? 'premium' : ($avgPrice > 100 ? 'mid_market' : 'budget');
        $volumeScore = $ticketCount > 1000 ? 'high_volume' : ($ticketCount > 100 ? 'medium_volume' : 'low_volume');

        return "{$priceScore}_{$volumeScore}";
    }

    private function identifySpecialization(Collection $tickets): string
    {
        $sports = $tickets->groupBy('sport');

        if ($sports->count() == 1) {
            return $sports->keys()->first() . '_specialist';
        }

        $dominantSport = $sports->map->count()->sortDesc()->keys()->first();
        $dominantPercentage = ($sports[$dominantSport]->count() / $tickets->count()) * 100;

        return $dominantPercentage > 70 ? $dominantSport . '_focused' : 'general';
    }

    private function identifyStrengths($platform, Collection $tickets): array
    {
        $strengths = [];

        $avgPrice = $tickets->avg('price');
        $ticketCount = $tickets->count();

        if ($avgPrice < 100) {
            $strengths[] = 'competitive_pricing';
        }

        if ($ticketCount > 500) {
            $strengths[] = 'high_inventory';
        }

        $sports = $tickets->groupBy('sport');
        if ($sports->count() > 5) {
            $strengths[] = 'diverse_offerings';
        }

        return $strengths;
    }

    private function estimateMarketShare($platformTickets, $totalTickets): float
    {
        if ($totalTickets == 0) {
            return 0;
        }

        return round(($platformTickets / $totalTickets) * 100, 2);
    }

    private function createPositioningMatrix(Collection $positioning): array
    {
        return $positioning->map(function ($platform) {
            return [
                'platform' => $platform['platform'],
                'x'        => $platform['avg_price'], // Price axis
                'y'        => $platform['ticket_volume'], // Volume axis
                'size'     => $platform['market_share_estimate'], // Bubble size
                'quadrant' => $this->determineQuadrant($platform['avg_price'], $platform['ticket_volume']),
            ];
        })->toArray();
    }

    private function determineQuadrant($price, $volume): string
    {
        $highPrice = $price > 150;
        $highVolume = $volume > 500;

        if ($highPrice && $highVolume) {
            return 'premium_leader';
        }
        if ($highPrice && !$highVolume) {
            return 'niche_premium';
        }
        if (!$highPrice && $highVolume) {
            return 'volume_leader';
        }

        return 'budget_focused';
    }

    private function identifyGeographicGaps(array $filters): array
    {
        // Placeholder for geographic analysis
        return [
            'underserved_regions'     => [],
            'expansion_opportunities' => [],
        ];
    }

    private function identifyCategoryGaps(array $filters): array
    {
        $categories = ScrapedTicket::selectRaw('sport, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('sport')
            ->orderBy('count', 'asc')
            ->get();

        return [
            'underrepresented_sports' => $categories->take(5),
            'growth_potential'        => $categories->where('count', '<', 100),
        ];
    }

    private function identifyTimingGaps(array $filters): array
    {
        // Analyze timing patterns for opportunities
        return [
            'seasonal_gaps'              => [],
            'event_timing_opportunities' => [],
        ];
    }

    private function calculateOpportunityScore(...$gaps): int
    {
        // Simple scoring algorithm
        $totalOpportunities = array_sum(array_map(function ($gap) {
            return is_array($gap) ? count($gap) : 0;
        }, $gaps));

        return min(100, $totalOpportunities * 10);
    }

    private function calculateMarketConcentration(Collection $marketShare): array
    {
        $hhi = $marketShare->sum(function ($platform) {
            return pow($platform['value_share'], 2);
        });

        $concentration = 'low';
        if ($hhi > 2500) {
            $concentration = 'high';
        } elseif ($hhi > 1500) {
            $concentration = 'moderate';
        }

        return [
            'hhi_index'           => round($hhi, 2),
            'concentration_level' => $concentration,
            'market_leaders'      => $marketShare->take(3),
        ];
    }

    private function getMarketShareTrends(array $filters): array
    {
        // Placeholder for trend analysis
        return [
            'growing_platforms'   => [],
            'declining_platforms' => [],
        ];
    }

    private function calculateCompetitiveIntensity(Collection $marketShare): string
    {
        $topThreeShare = $marketShare->take(3)->sum('value_share');

        if ($topThreeShare > 75) {
            return 'low';
        }
        if ($topThreeShare > 50) {
            return 'moderate';
        }

        return 'high';
    }

    private function identifyPricingStrategy(Collection $tickets): string
    {
        $prices = $tickets->pluck('price');
        $stdDev = $this->calculateStandardDeviation($prices);
        $mean = $prices->avg();
        $cv = $stdDev / $mean; // Coefficient of variation

        if ($cv < 0.1) {
            return 'fixed_pricing';
        }
        if ($cv < 0.3) {
            return 'tiered_pricing';
        }

        return 'dynamic_pricing';
    }

    private function calculatePriceConsistency(Collection $tickets): float
    {
        $prices = $tickets->pluck('price');
        $stdDev = $this->calculateStandardDeviation($prices);
        $mean = $prices->avg();

        return round(1 - ($stdDev / $mean), 3); // Higher = more consistent
    }

    private function calculatePremiumPositioning(Collection $tickets): float
    {
        // Calculate what percentage of tickets are priced above market average
        $avgMarketPrice = 150; // This should be dynamic
        $premiumTickets = $tickets->where('price', '>', $avgMarketPrice)->count();

        return round(($premiumTickets / $tickets->count()) * 100, 2);
    }

    private function calculateDiscountFrequency(Collection $tickets): float
    {
        // Placeholder - would need historical pricing data
        return 15.5; // Percentage
    }

    private function detectDynamicPricing(Collection $tickets): bool
    {
        // Simple heuristic based on price variance
        $prices = $tickets->pluck('price');
        $stdDev = $this->calculateStandardDeviation($prices);
        $mean = $prices->avg();

        return ($stdDev / $mean) > 0.2;
    }

    private function analyzeCompetitiveResponse($platform, Collection $tickets): array
    {
        // Placeholder for competitive response analysis
        return [
            'price_matching'  => FALSE,
            'response_speed'  => 'slow',
            'strategic_focus' => 'volume',
        ];
    }

    private function evaluateStrategyEffectiveness(Collection $strategies): array
    {
        // Placeholder for strategy effectiveness evaluation
        return [
            'most_effective'  => 'dynamic_pricing',
            'least_effective' => 'fixed_pricing',
            'recommendations' => [],
        ];
    }

    private function generatePricingRecommendations(Collection $strategies): array
    {
        return [
            'optimize_dynamic_pricing'     => 'Implement more sophisticated dynamic pricing algorithms',
            'improve_competitive_response' => 'Faster price matching and response times',
            'segment_pricing'              => 'Better price segmentation by customer type',
        ];
    }

    private function calculateStandardDeviation(Collection $values): float
    {
        $mean = $values->avg();
        $variance = $values->map(function ($value) use ($mean) {
            return pow($value - $mean, 2);
        })->avg();

        return sqrt($variance);
    }

    // Additional helper methods for opportunity and threat analysis
    private function identifyUnderservedSegments(array $filters): array
    {
        return ['budget_family_packages', 'premium_corporate_boxes', 'student_discounts'];
    }

    private function identifyPriceOptimization(array $filters): array
    {
        return ['dynamic_surge_pricing', 'early_bird_discounts', 'last_minute_deals'];
    }

    private function identifyGeographicOpportunities(array $filters): array
    {
        return ['international_markets', 'underserved_cities', 'mobile_markets'];
    }

    private function identifyPartnershipOpportunities(array $filters): array
    {
        return ['venue_partnerships', 'team_collaborations', 'media_partnerships'];
    }

    private function identifyTechnologyGaps(array $filters): array
    {
        return ['ai_pricing', 'mobile_optimization', 'real_time_inventory'];
    }

    private function identifyCompetitiveThreats(array $filters): array
    {
        return ['new_market_entrants', 'platform_consolidation', 'direct_venue_sales'];
    }

    private function assessDisruptionRisk(array $filters): string
    {
        return 'moderate'; // low, moderate, high
    }

    private function detectPriceWarIndicators(array $filters): array
    {
        return ['rapid_price_drops', 'below_cost_pricing', 'aggressive_promotions'];
    }

    private function assessMarketSaturation(array $filters): string
    {
        return 'growing'; // growing, mature, saturated
    }

    private function assessRegulatoryRisks(array $filters): array
    {
        return ['ticket_resale_laws', 'consumer_protection', 'antitrust_concerns'];
    }
}
