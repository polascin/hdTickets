<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\TicketPriceHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use function array_slice;
use function count;

class ChartDataService
{
    private const CACHE_TTL = 900; // 15 minutes for chart data

    /**
     * Generate comprehensive ticket trends chart data
     */
    /**
     * Get  ticket trends chart data
     */
    public function getTicketTrendsChartData(array $filters = []): array
    {
        $cacheKey = 'chart:ticket_trends:' . md5(serialize($filters));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters): array {
            $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
            $endDate = $filters['end_date'] ?? Carbon::now();
            $platforms = $filters['platforms'] ?? [];

            // Daily ticket counts with status breakdown
            $dailyTrends = ScrapedTicket::whereBetween('created_at', [$startDate, $endDate])
                ->when(! empty($platforms), function ($query) use ($platforms): void {
                    $query->whereIn('platform', $platforms);
                })
                ->select([
                    DB::raw('DATE(created_at) as date'),
                    'status',
                    DB::raw('COUNT(*) as count'),
                ])
                ->groupBy('date', 'status')
                ->orderBy('date')
                ->get()
                ->groupBy('date');

            $labels = [];
            $availableData = [];
            $soldOutData = [];
            $expiredData = [];

            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $dateStr = $currentDate->format('Y-m-d');
                $dayData = $dailyTrends->get($dateStr, collect());

                $labels[] = $currentDate->format('M d');
                $availableData[] = $dayData->where('status', 'available')->sum('count');
                $soldOutData[] = $dayData->where('status', 'sold_out')->sum('count');
                $expiredData[] = $dayData->where('status', 'expired')->sum('count');

                $currentDate->addDay();
            }

            return [
                'type' => 'line',
                'data' => [
                    'labels'   => $labels,
                    'datasets' => [
                        [
                            'label'           => 'Available Tickets',
                            'data'            => $availableData,
                            'borderColor'     => '#22c55e',
                            'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                            'fill'            => TRUE,
                            'tension'         => 0.4,
                        ],
                        [
                            'label'           => 'Sold Out',
                            'data'            => $soldOutData,
                            'borderColor'     => '#ef4444',
                            'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                            'fill'            => TRUE,
                            'tension'         => 0.4,
                        ],
                        [
                            'label'           => 'Expired',
                            'data'            => $expiredData,
                            'borderColor'     => '#64748b',
                            'backgroundColor' => 'rgba(100, 116, 139, 0.1)',
                            'fill'            => TRUE,
                            'tension'         => 0.4,
                        ],
                    ],
                ],
                'options' => [
                    'responsive'          => TRUE,
                    'maintainAspectRatio' => FALSE,
                    'plugins'             => [
                        'title' => [
                            'display' => TRUE,
                            'text'    => 'Ticket Availability Trends',
                            'font'    => ['size' => 16, 'weight' => 'bold'],
                        ],
                        'legend' => [
                            'position' => 'top',
                        ],
                    ],
                    'scales' => [
                        'x' => [
                            'display' => TRUE,
                            'title'   => ['display' => TRUE, 'text' => 'Date'],
                        ],
                        'y' => [
                            'display'     => TRUE,
                            'title'       => ['display' => TRUE, 'text' => 'Number of Tickets'],
                            'beginAtZero' => TRUE,
                        ],
                    ],
                ],
            ];
        });
    }

    /**
     * Generate price volatility heatmap data
     */
    /**
     * Get  price volatility heatmap data
     */
    public function getPriceVolatilityHeatmapData(array $filters = []): array
    {
        $cacheKey = 'chart:price_volatility:' . md5(serialize($filters));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters): array {
            $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
            $endDate = $filters['end_date'] ?? Carbon::now();

            $volatilityData = TicketPriceHistory::whereBetween('recorded_at', [$startDate, $endDate])
                ->with(['ticket:id,title,platform'])
                ->select([
                    'ticket_id',
                    DB::raw('STDDEV(price) as price_volatility'),
                    DB::raw('AVG(price) as avg_price'),
                    DB::raw('COUNT(*) as data_points'),
                ])
                ->groupBy('ticket_id')
                ->having('data_points', '>=', 5)
                ->orderBy('price_volatility', 'desc')
                ->limit(20)
                ->get();

            $labels = $volatilityData->map(fn ($item): string => substr($item->ticket->title ?? 'Event ' . $item->ticket_id, 0, 30))->toArray();

            $data = $volatilityData->map(fn ($item): float => round($item->price_volatility, 2))->toArray();

            $backgroundColors = $volatilityData->map(function ($item): string {
                $volatility = $item->price_volatility;
                if ($volatility > 50) {
                    return '#ef4444';
                } // High volatility - red
                if ($volatility > 20) {
                    return '#f59e0b';
                } // Medium volatility - amber

                return '#22c55e'; // Low volatility - green
            })->toArray();

            return [
                'type' => 'bar',
                'data' => [
                    'labels'   => $labels,
                    'datasets' => [[
                        'label'           => 'Price Volatility ($)',
                        'data'            => $data,
                        'backgroundColor' => $backgroundColors,
                        'borderColor'     => $backgroundColors,
                        'borderWidth'     => 1,
                    ]],
                ],
                'options' => [
                    'responsive'          => TRUE,
                    'maintainAspectRatio' => FALSE,
                    'indexAxis'           => 'y',
                    'plugins'             => [
                        'title' => [
                            'display' => TRUE,
                            'text'    => 'Price Volatility by Event (Top 20)',
                            'font'    => ['size' => 16, 'weight' => 'bold'],
                        ],
                        'legend' => [
                            'display' => FALSE,
                        ],
                    ],
                    'scales' => [
                        'x' => [
                            'beginAtZero' => TRUE,
                            'title'       => ['display' => TRUE, 'text' => 'Price Volatility ($)'],
                        ],
                        'y' => [
                            'title' => ['display' => TRUE, 'text' => 'Events'],
                        ],
                    ],
                ],
            ];
        });
    }

    /**
     * Generate platform market share pie chart data
     */
    /**
     * Get  platform market share data
     */
    public function getPlatformMarketShareData(array $filters = []): array
    {
        $cacheKey = 'chart:platform_market_share:' . md5(serialize($filters));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters): array {
            $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
            $endDate = $filters['end_date'] ?? Carbon::now();

            $platformData = ScrapedTicket::whereBetween('created_at', [$startDate, $endDate])
                ->select('platform', DB::raw('COUNT(*) as ticket_count'))
                ->whereNotNull('platform')
                ->groupBy('platform')
                ->orderBy('ticket_count', 'desc')
                ->get();

            $totalTickets = $platformData->sum('ticket_count');

            $labels = $platformData->pluck('platform')->map(fn ($platform): string => ucfirst(str_replace('_', ' ', $platform)))->toArray();

            $data = $platformData->pluck('ticket_count')->toArray();

            $backgroundColors = [
                '#3b82f6', '#ef4444', '#22c55e', '#f59e0b', '#8b5cf6',
                '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1',
            ];

            return [
                'type' => 'doughnut',
                'data' => [
                    'labels'   => $labels,
                    'datasets' => [[
                        'data'            => $data,
                        'backgroundColor' => array_slice($backgroundColors, 0, count($data)),
                        'borderWidth'     => 2,
                        'borderColor'     => '#ffffff',
                    ]],
                ],
                'options' => [
                    'responsive'          => TRUE,
                    'maintainAspectRatio' => FALSE,
                    'cutout'              => '60%',
                    'plugins'             => [
                        'title' => [
                            'display' => TRUE,
                            'text'    => 'Platform Market Share',
                            'font'    => ['size' => 16, 'weight' => 'bold'],
                        ],
                        'legend' => [
                            'position' => 'right',
                        ],
                        'tooltip' => [
                            'callbacks' => [
                                'label' => "function(context) {
                                    const percentage = ((context.parsed / {$totalTickets}) * 100).toFixed(1);
                                    return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                }",
                            ],
                        ],
                    ],
                ],
            ];
        });
    }

    /**
     * Generate user engagement funnel chart data
     */
    /**
     * Get  user engagement funnel data
     */
    public function getUserEngagementFunnelData(array $filters = []): array
    {
        $cacheKey = 'chart:user_engagement_funnel:' . md5(serialize($filters));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters): array {
            $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
            $endDate = $filters['end_date'] ?? Carbon::now();

            // Calculate funnel metrics
            $totalUsers = User::whereBetween('created_at', [$startDate, $endDate])->count();
            $activeUsers = User::where('last_activity_at', '>=', $startDate)->count();
            $usersWithAlerts = TicketAlert::whereBetween('created_at', [$startDate, $endDate])
                ->distinct('user_id')->count('user_id');
            $convertedUsers = TicketAlert::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'triggered')->distinct('user_id')->count('user_id');

            $stages = ['Total Users', 'Active Users', 'Users with Alerts', 'Converted Users'];
            $values = [$totalUsers, $activeUsers, $usersWithAlerts, $convertedUsers];

            return [
                'type' => 'bar',
                'data' => [
                    'labels'   => $stages,
                    'datasets' => [[
                        'label'           => 'Users',
                        'data'            => $values,
                        'backgroundColor' => [
                            '#3b82f6',
                            '#22c55e',
                            '#f59e0b',
                            '#ef4444',
                        ],
                        'borderColor' => [
                            '#2563eb',
                            '#16a34a',
                            '#d97706',
                            '#dc2626',
                        ],
                        'borderWidth' => 2,
                    ]],
                ],
                'options' => [
                    'responsive'          => TRUE,
                    'maintainAspectRatio' => FALSE,
                    'plugins'             => [
                        'title' => [
                            'display' => TRUE,
                            'text'    => 'User Engagement Funnel',
                            'font'    => ['size' => 16, 'weight' => 'bold'],
                        ],
                        'legend' => [
                            'display' => FALSE,
                        ],
                    ],
                    'scales' => [
                        'x' => [
                            'title' => ['display' => TRUE, 'text' => 'Engagement Stage'],
                        ],
                        'y' => [
                            'beginAtZero' => TRUE,
                            'title'       => ['display' => TRUE, 'text' => 'Number of Users'],
                        ],
                    ],
                ],
            ];
        });
    }

    /**
     * Generate sports category performance radar chart data
     */
    /**
     * Get  sports category radar data
     */
    public function getSportsCategoryRadarData(array $filters = []): array
    {
        $cacheKey = 'chart:sports_category_radar:' . md5(serialize($filters));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters): array {
            $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
            $endDate = $filters['end_date'] ?? Carbon::now();

            $categoryMetrics = Category::withCount([
                'scrapedTickets as total_tickets' => function ($query) use ($startDate, $endDate): void {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                },
                'scrapedTickets as available_tickets' => function ($query) use ($startDate, $endDate): void {
                    $query->whereBetween('created_at', [$startDate, $endDate])
                        ->where('status', 'available');
                },
                'scrapedTickets as high_demand_tickets' => function ($query) use ($startDate, $endDate): void {
                    $query->whereBetween('created_at', [$startDate, $endDate])
                        ->where('is_high_demand', TRUE);
                },
            ])
                ->get()
                ->map(function ($category): array {
                    $availabilityRate = $category->total_tickets > 0
                        ? ($category->available_tickets / $category->total_tickets) * 100
                        : 0;
                    $demandRate = $category->total_tickets > 0
                        ? ($category->high_demand_tickets / $category->total_tickets) * 100
                        : 0;

                    return [
                        'category'          => $category->name,
                        'volume_score'      => min(100, ($category->total_tickets / 10)), // Normalize to 0-100
                        'availability_rate' => round($availabilityRate, 1),
                        'demand_rate'       => round($demandRate, 1),
                        'engagement_score'  => random_int(60, 95), // Placeholder for actual engagement calculation
                    ];
                });

            $labels = ['Volume', 'Availability', 'Demand', 'Engagement'];
            $datasets = [];
            $colors = ['#3b82f6', '#ef4444', '#22c55e', '#f59e0b', '#8b5cf6'];

            foreach ($categoryMetrics->take(5) as $index => $metric) {
                $datasets[] = [
                    'label' => $metric['category'],
                    'data'  => [
                        $metric['volume_score'],
                        $metric['availability_rate'],
                        $metric['demand_rate'],
                        $metric['engagement_score'],
                    ],
                    'borderColor'               => $colors[$index % count($colors)],
                    'backgroundColor'           => $colors[$index % count($colors)] . '20',
                    'pointBackgroundColor'      => $colors[$index % count($colors)],
                    'pointBorderColor'          => '#ffffff',
                    'pointHoverBackgroundColor' => '#ffffff',
                    'pointHoverBorderColor'     => $colors[$index % count($colors)],
                ];
            }

            return [
                'type' => 'radar',
                'data' => [
                    'labels'   => $labels,
                    'datasets' => $datasets,
                ],
                'options' => [
                    'responsive'          => TRUE,
                    'maintainAspectRatio' => FALSE,
                    'plugins'             => [
                        'title' => [
                            'display' => TRUE,
                            'text'    => 'Sports Category Performance Analysis',
                            'font'    => ['size' => 16, 'weight' => 'bold'],
                        ],
                        'legend' => [
                            'position' => 'top',
                        ],
                    ],
                    'scales' => [
                        'r' => [
                            'beginAtZero' => TRUE,
                            'max'         => 100,
                            'ticks'       => [
                                'stepSize' => 20,
                            ],
                        ],
                    ],
                ],
            ];
        });
    }

    /**
     * Generate hourly activity heatmap data
     */
    /**
     * Get  hourly activity heatmap data
     */
    public function getHourlyActivityHeatmapData(array $filters = []): array
    {
        $cacheKey = 'chart:hourly_activity_heatmap:' . md5(serialize($filters));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters): array {
            $startDate = $filters['start_date'] ?? Carbon::now()->subDays(7);
            $endDate = $filters['end_date'] ?? Carbon::now();

            $activityData = DB::table('activity_log')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select([
                    DB::raw('DAYOFWEEK(created_at) as day_of_week'),
                    DB::raw('HOUR(created_at) as hour'),
                    DB::raw('COUNT(*) as activity_count'),
                ])
                ->groupBy('day_of_week', 'hour')
                ->get()
                ->groupBy(['day_of_week', 'hour']);

            $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            $hours = range(0, 23);
            $heatmapData = [];

            foreach (range(1, 7) as $dayIndex) {
                foreach ($hours as $hour) {
                    $count = $activityData->get($dayIndex, collect())->get($hour, collect())->first()->activity_count ?? 0;
                    $heatmapData[] = [
                        'x' => $hour,
                        'y' => $days[$dayIndex - 1],
                        'v' => $count,
                    ];
                }
            }

            return [
                'type' => 'scatter',
                'data' => [
                    'datasets' => [[
                        'label'           => 'User Activity',
                        'data'            => $heatmapData,
                        'backgroundColor' => function (array $context): string {
                            $value = $context['parsed']['v'];
                            $alpha = min(1, $value / 100); // Normalize opacity

                            return "rgba(59, 130, 246, {$alpha})";
                        },
                        'pointRadius' => 8,
                    ]],
                ],
                'options' => [
                    'responsive'          => TRUE,
                    'maintainAspectRatio' => FALSE,
                    'plugins'             => [
                        'title' => [
                            'display' => TRUE,
                            'text'    => 'User Activity Heatmap (Hours vs Days)',
                            'font'    => ['size' => 16, 'weight' => 'bold'],
                        ],
                        'legend' => [
                            'display' => FALSE,
                        ],
                    ],
                    'scales' => [
                        'x' => [
                            'type'     => 'linear',
                            'position' => 'bottom',
                            'min'      => 0,
                            'max'      => 23,
                            'ticks'    => ['stepSize' => 2],
                            'title'    => ['display' => TRUE, 'text' => 'Hour of Day'],
                        ],
                        'y' => [
                            'type'   => 'category',
                            'labels' => $days,
                            'title'  => ['display' => TRUE, 'text' => 'Day of Week'],
                        ],
                    ],
                ],
            ];
        });
    }

    /**
     * Generate comprehensive dashboard chart data
     */
    /**
     * Get  dashboard charts data
     */
    public function getDashboardChartsData(array $filters = []): array
    {
        return [
            'ticket_trends'           => $this->getTicketTrendsChartData($filters),
            'price_volatility'        => $this->getPriceVolatilityHeatmapData($filters),
            'platform_market_share'   => $this->getPlatformMarketShareData($filters),
            'user_engagement_funnel'  => $this->getUserEngagementFunnelData($filters),
            'sports_category_radar'   => $this->getSportsCategoryRadarData($filters),
            'hourly_activity_heatmap' => $this->getHourlyActivityHeatmapData($filters),
        ];
    }

    /**
     * Clear chart data cache
     */
    /**
     * ClearChartCache
     */
    public function clearChartCache(): void
    {
        $cacheKeys = [
            'chart:ticket_trends:*',
            'chart:price_volatility:*',
            'chart:platform_market_share:*',
            'chart:user_engagement_funnel:*',
            'chart:sports_category_radar:*',
            'chart:hourly_activity_heatmap:*',
        ];

        foreach ($cacheKeys as $pattern) {
            Cache::forget($pattern);
        }
    }
}
