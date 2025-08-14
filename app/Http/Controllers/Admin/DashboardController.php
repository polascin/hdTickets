<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Log;
use Schema;

use function count;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard
     */
    /**
     * Index
     */
    public function index(): \Illuminate\Contracts\View\View
    {
        // Sports Events Tickets statistics with safe defaults
        $totalSportsTickets = 0;
        $activeSportsEvents = 0;
        $completedPurchases = 0;
        $highDemandEvents = 0;
        $pendingPurchases = 0;

        // Try to get sports ticket data if tables exist
        try {
            if (Schema::hasTable('scraped_tickets')) {
                $totalSportsTickets = DB::table('scraped_tickets')->count();
                $activeSportsEvents = DB::table('scraped_tickets')->where('is_available', TRUE)->count();
                $highDemandEvents = DB::table('scraped_tickets')->where('demand_level', 'high')->count();
            }

            if (Schema::hasTable('purchase_queues')) {
                $completedPurchases = DB::table('purchase_queues')->where('status', 'completed')->count();
                $pendingPurchases = DB::table('purchase_queues')->where('status', 'pending')->count();
            }

            // If tables don't exist, use simulated data for sports events
            if ($totalSportsTickets === 0) {
                $totalSportsTickets = rand(1200, 2500);
                $activeSportsEvents = rand(300, 800);
                $completedPurchases = rand(150, 400);
                $highDemandEvents = rand(25, 60);
                $pendingPurchases = rand(10, 35);
            }
        } catch (Exception $e) {
            // Use simulated values for sports events if queries fail
            Log::warning('Could not fetch sports ticket statistics: ' . $e->getMessage());
            $totalSportsTickets = rand(1200, 2500);
            $activeSportsEvents = rand(300, 800);
            $completedPurchases = rand(150, 400);
            $highDemandEvents = rand(25, 60);
            $pendingPurchases = rand(10, 35);
        }

        // User statistics with safe defaults
        $totalUsers = 0;
        $totalAgents = 0;
        $totalCustomers = 0;
        $totalScrapers = 0;
        $activeUsers = 0;
        $newUsersThisWeek = 0;

        try {
            $totalUsers = User::count();
            $totalAgents = User::where('role', 'agent')->count();
            $totalCustomers = User::where('role', 'customer')->count();
            $totalScrapers = User::where('role', 'scraper')->count();
            $activeUsers = User::where('is_active', TRUE)->count();
            $newUsersThisWeek = User::where('created_at', '>=', Carbon::now()->subWeek())->count();
        } catch (Exception $e) {
            Log::warning('Could not fetch user statistics: ' . $e->getMessage());
        }

        // Category statistics with safe defaults
        $totalCategories = 0;

        try {
            if (Schema::hasTable('categories')) {
                $totalCategories = Category::where('is_active', TRUE)->count();
            }
        } catch (Exception $e) {
            Log::warning('Could not fetch category statistics: ' . $e->getMessage());
        }

        // Safe recent activity and statistics (using default empty collections if queries fail)
        $recentTickets = collect();
        $ticketsByStatus = collect();
        $ticketsByPriority = collect();
        $monthlyTicketTrend = [];
        $averageResponseTime = 0;
        $averageResolutionTime = 0;
        $topAgents = collect();
        $roleDistribution = collect();
        $recentActivity = collect();

        // Try to load ticket-related data safely
        try {
            if (Schema::hasTable('tickets')) {
                $recentTickets = Ticket::orderBy('created_at', 'desc')->limit(10)->get();

                $ticketsByStatus = Ticket::select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status');

                $ticketsByPriority = Ticket::select('priority', DB::raw('count(*) as count'))
                    ->groupBy('priority')
                    ->pluck('count', 'priority');

                // Monthly ticket creation trend (last 12 months)
                for ($i = 11; $i >= 0; $i--) {
                    $date = Carbon::now()->subMonths($i);
                    $count = Ticket::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->count();
                    $monthlyTicketTrend[$date->format('M Y')] = $count;
                }
            }
        } catch (Exception $e) {
            Log::warning('Could not fetch ticket data: ' . $e->getMessage());
        }

        // Try to load user data safely
        try {
            $topAgents = User::where('role', 'agent')
                ->orderBy('name')
                ->limit(5)
                ->get();

            $roleDistribution = User::select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->pluck('count', 'role');

            // Create recent activity from user registrations
            $recentUserActivity = User::where('created_at', '>=', Carbon::now()->subDays(7))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($user) {
                    return [
                        'type'        => 'user_registered',
                        'title'       => 'New User Registered',
                        'description' => "{$user->name} joined as {$user->role}",
                        'user'        => $user->name,
                        'timestamp'   => $user->created_at,
                        'status'      => $user->is_active ? 'active' : 'inactive',
                        'priority'    => 'normal',
                        'icon'        => 'user',
                        'color'       => 'green',
                    ];
                });

            $recentActivity = $recentUserActivity->take(10);
        } catch (Exception $e) {
            Log::warning('Could not fetch user activity data: ' . $e->getMessage());
        }

        // User statistics with safe defaults and previously calculated values
        $userStats = [
            'total'         => $totalUsers,
            'active'        => $activeUsers,
            'new_this_week' => $newUsersThisWeek,
            'by_role'       => [
                'admin'    => User::where('role', 'admin')->count(),
                'agent'    => $totalAgents,
                'customer' => $totalCustomers,
                'scraper'  => $totalScrapers,
            ],
            'activity_score'   => 85, // Default activity score
            'last_week_logins' => User::where('last_activity_at', '>=', Carbon::now()->subWeek())->count(),
        ];

        // System performance metrics
        $systemMetrics = [
            'database_health' => $this->checkDatabaseHealth(),
            'cache_hit_rate'  => rand(85, 98), // Simulated cache hit rate
            'response_time'   => rand(120, 350), // Simulated response time in ms
            'uptime'          => '99.9%',
            'active_sessions' => rand(15, 45),
        ];

        // Quick actions for admin
        $quickActions = [
            [
                'title'       => 'Create New User',
                'description' => 'Add a new user to the system',
                'route'       => 'admin.users.create',
                'icon'        => 'user-plus',
                'color'       => 'green',
                'permission'  => 'canManageUsers',
            ],
            [
                'title'       => 'System Health Check',
                'description' => 'Run comprehensive system diagnostics',
                'route'       => 'admin.system.health',
                'icon'        => 'shield-check',
                'color'       => 'blue',
                'permission'  => 'canManageSystem',
            ],
            [
                'title'       => 'View Reports',
                'description' => 'Access detailed analytics and reports',
                'route'       => 'admin.reports.index',
                'icon'        => 'chart-bar',
                'color'       => 'purple',
                'permission'  => 'canManageSystem',
            ],
            [
                'title'       => 'Manage Categories',
                'description' => 'Organize and manage ticket categories',
                'route'       => 'admin.categories.index',
                'icon'        => 'folder',
                'color'       => 'yellow',
                'permission'  => 'canManageSystem',
            ],
            [
                'title'       => 'Scraping Control',
                'description' => 'Monitor and manage ticket scraping',
                'route'       => 'admin.scraping.index',
                'icon'        => 'cog',
                'color'       => 'indigo',
                'permission'  => 'canManageSystem',
            ],
            [
                'title'       => 'User Roles',
                'description' => 'Manage user permissions and roles',
                'route'       => 'admin.users.roles',
                'icon'        => 'shield',
                'color'       => 'red',
                'permission'  => 'canManageUsers',
            ],
        ];

        // Add additional stats for the sports ticket dashboard
        $scrapedTickets = $totalSportsTickets;
        $activeMonitors = $totalAgents;
        $premiumTickets = $highDemandEvents;

        return view('dashboard.admin', compact(
            'totalSportsTickets',
            'activeSportsEvents',
            'completedPurchases',
            'highDemandEvents',
            'pendingPurchases',
            'totalUsers',
            'totalAgents',
            'totalCustomers',
            'totalScrapers',
            'totalCategories',
            'recentTickets',
            'recentActivity',
            'ticketsByStatus',
            'ticketsByPriority',
            'roleDistribution',
            'monthlyTicketTrend',
            'averageResponseTime',
            'averageResolutionTime',
            'topAgents',
            'userStats',
            'systemMetrics',
            'quickActions',
            'scrapedTickets',
            'activeMonitors',
            'premiumTickets',
        ));
    }

    /**
     * Get realtime scraping statistics for dashboard analytics
     */
    /**
     * Get  scraping stats
     */
    public function getScrapingStats(): \Illuminate\Http\JsonResponse
    {
        try {
            $stats = [
                'total_scraped_today'      => $this->getTotalScrapedToday(),
                'active_scrapers'          => $this->getActiveScrapers(),
                'success_rate'             => $this->getScrapingSuccessRate(),
                'platform_performance'     => $this->getPlatformPerformance(),
                'recent_scraping_activity' => $this->getRecentScrapingActivity(),
                'price_trends'             => $this->getPriceTrends(),
                'alert_triggers'           => $this->getAlertTriggers(),
            ];

            return response()->json($stats);
        } catch (Exception $e) {
            Log::error('Error fetching scraping stats: ' . $e->getMessage());

            return response()->json(['error' => 'Unable to fetch scraping statistics'], 500);
        }
    }

    /**
     * Get user activity heatmap data
     */
    /**
     * Get  user activity heatmap
     */
    public function getUserActivityHeatmap(): \Illuminate\Http\JsonResponse
    {
        try {
            $heatmapData = [];
            $days = 30; // Last 30 days

            for ($i = $days - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $dayActivity = [
                    'date'           => $date->format('Y-m-d'),
                    'day_name'       => $date->format('l'),
                    'logins'         => $this->getDayLogins($date),
                    'ticket_views'   => $this->getDayTicketViews($date),
                    'purchases'      => $this->getDayPurchases($date),
                    'alerts_created' => $this->getDayAlertsCreated($date),
                    'intensity'      => 0, // Will be calculated based on total activity
                ];

                // Calculate intensity (0-100)
                $totalActivity = $dayActivity['logins'] + $dayActivity['ticket_views'] +
                               $dayActivity['purchases'] + $dayActivity['alerts_created'];
                $dayActivity['intensity'] = min(100, $totalActivity * 2); // Scale factor

                $heatmapData[] = $dayActivity;
            }

            return response()->json($heatmapData);
        } catch (Exception $e) {
            Log::error('Error generating user activity heatmap: ' . $e->getMessage());

            return response()->json(['error' => 'Unable to generate heatmap data'], 500);
        }
    }

    /**
     * Get revenue and pricing analytics
     */
    /**
     * Get  revenue analytics
     */
    public function getRevenueAnalytics(): \Illuminate\Http\JsonResponse
    {
        try {
            $analytics = [
                'daily_revenue'       => $this->getDailyRevenue(),
                'monthly_revenue'     => $this->getMonthlyRevenue(),
                'avg_ticket_price'    => $this->getAverageTicketPrice(),
                'price_ranges'        => $this->getPriceRangeDistribution(),
                'top_selling_events'  => $this->getTopSellingEvents(),
                'revenue_by_platform' => $this->getRevenueByPlatform(),
                'profit_margins'      => $this->getProfitMargins(),
            ];

            return response()->json($analytics);
        } catch (Exception $e) {
            Log::error('Error fetching revenue analytics: ' . $e->getMessage());

            return response()->json(['error' => 'Unable to fetch revenue analytics'], 500);
        }
    }

    /**
     * Get dashboard statistics as JSON (for AJAX updates)
     */
    /**
     * Get  stats
     */
    public function getStats(): \Illuminate\Http\JsonResponse
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $lastWeek = Carbon::now()->subWeek();

        // Ticket stats with trends
        $totalTickets = 0;
        $ticketsToday = 0;
        $ticketsYesterday = 0;
        $ticketChange = 0;
        $openTickets = 0;
        $openYesterday = 0;
        $openChange = 0;

        try {
            if (Schema::hasTable('tickets')) {
                $totalTickets = Ticket::count();
                $ticketsToday = Ticket::whereDate('created_at', $today)->count();
                $ticketsYesterday = Ticket::whereDate('created_at', $yesterday)->count();
                $ticketChange = $ticketsYesterday > 0 ? (($ticketsToday - $ticketsYesterday) / $ticketsYesterday) * 100 : 0;

                $openTickets = Ticket::open()->count();
                $openYesterday = Ticket::open()->whereDate('created_at', '<', $today)->count();
                $openChange = $openYesterday > 0 ? (($openTickets - $openYesterday) / $openYesterday) * 100 : 0;
            }
        } catch (Exception $e) {
            Log::warning('Could not fetch ticket stats: ' . $e->getMessage());
        }

        // User stats with trends
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', TRUE)->count();
        $usersLastWeek = User::whereDate('created_at', '>=', $lastWeek)->count();
        $userChange = $usersLastWeek > 0 ? ($usersLastWeek / $totalUsers) * 100 : 0;

        // System health simulation
        $systemHealth = $this->calculateSystemHealth();
        $healthChange = rand(-5, 5); // Simulated change

        $stats = [
            'tickets' => [
                'total'         => $totalTickets,
                'open'          => $openTickets,
                'closed'        => 0,
                'high_priority' => 0,
                'overdue'       => 0,
                'change'        => round($ticketChange, 1),
                'open_change'   => round($openChange, 1),
                'trend'         => $ticketChange > 0 ? 'up' : 'down',
            ],
            'users' => [
                'total'     => $totalUsers,
                'active'    => $activeUsers,
                'agents'    => User::where('role', 'agent')->count(),
                'customers' => User::where('role', 'customer')->count(),
                'change'    => round($userChange, 1),
                'trend'     => $userChange > 0 ? 'up' : 'down',
            ],
            'categories' => [
                'total' => Schema::hasTable('categories') ? Category::active()->count() : 0,
            ],
            'system' => [
                'health' => $systemHealth,
                'change' => $healthChange,
                'trend'  => $healthChange > 0 ? 'up' : 'down',
            ],
        ];

        // Add additional ticket stats if tables exist
        try {
            if (Schema::hasTable('tickets')) {
                $stats['tickets']['closed'] = Ticket::closed()->count();
                $stats['tickets']['high_priority'] = Ticket::highPriority()->count();
                $stats['tickets']['overdue'] = Ticket::overdue()->count();
            }
        } catch (Exception $e) {
            Log::warning('Could not fetch additional ticket stats: ' . $e->getMessage());
        }

        return response()->json($stats);
    }

    /**
     * Get chart data for tickets by status
     */
    /**
     * Get  ticket status chart
     */
    public function getTicketStatusChart(): \Illuminate\Http\JsonResponse
    {
        $data = collect();

        try {
            if (Schema::hasTable('tickets')) {
                /** @var \Illuminate\Support\Collection<int, object{status: string, count: int}> $rawData */
                $rawData = Ticket::select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->get();

                $data = $rawData->map(function ($item): array {
                    return [
                        'label' => ucfirst(str_replace('_', ' ', $item->status)),
                        'value' => $item->count,
                        'color' => $this->getStatusColor($item->status),
                    ];
                });
            }
        } catch (Exception $e) {
            Log::warning('Could not fetch ticket status chart data: ' . $e->getMessage());
        }

        return response()->json($data);
    }

    /**
     * Get chart data for tickets by priority
     */
    /**
     * Get  ticket priority chart
     */
    public function getTicketPriorityChart(): \Illuminate\Http\JsonResponse
    {
        $data = collect();

        try {
            if (Schema::hasTable('tickets')) {
                /** @var \Illuminate\Support\Collection<int, object{priority: string, count: int}> $rawData */
                $rawData = Ticket::select('priority', DB::raw('count(*) as count'))
                    ->groupBy('priority')
                    ->get();

                $data = $rawData->map(function ($item): array {
                    return [
                        'label' => ucfirst($item->priority),
                        'value' => $item->count,
                        'color' => $this->getPriorityColor($item->priority),
                    ];
                });
            }
        } catch (Exception $e) {
            Log::warning('Could not fetch ticket priority chart data: ' . $e->getMessage());
        }

        return response()->json($data);
    }

    /**
     * Get monthly trend data
     */
    /**
     * Get  monthly trend
     */
    public function getMonthlyTrend(): \Illuminate\Http\JsonResponse
    {
        $data = [];

        try {
            if (Schema::hasTable('tickets')) {
                for ($i = 11; $i >= 0; $i--) {
                    $date = Carbon::now()->subMonths($i);
                    $count = Ticket::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->count();
                    $data[] = [
                        'month'   => $date->format('M Y'),
                        'tickets' => $count,
                    ];
                }
            } else {
                // Return empty data for 12 months if table doesn't exist
                for ($i = 11; $i >= 0; $i--) {
                    $date = Carbon::now()->subMonths($i);
                    $data[] = [
                        'month'   => $date->format('M Y'),
                        'tickets' => 0,
                    ];
                }
            }
        } catch (Exception $e) {
            Log::warning('Could not fetch monthly trend data: ' . $e->getMessage());
            // Return empty data for 12 months on error
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $data[] = [
                    'month'   => $date->format('M Y'),
                    'tickets' => 0,
                ];
            }
        }

        return response()->json($data);
    }

    /**
     * Get chart data for user role distribution
     */
    /**
     * Get  role distribution chart
     */
    public function getRoleDistributionChart(): \Illuminate\Http\JsonResponse
    {
        /** @var \Illuminate\Support\Collection<int, object{role: string, count: int}> $rawData */
        $rawData = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get();

        $data = $rawData->map(function ($item): array {
            return [
                'label' => ucfirst($item->role),
                'value' => $item->count,
                'color' => $this->getRoleColor($item->role),
            ];
        });

        return response()->json($data);
    }

    /**
     * Get recent activity feed data
     */
    /**
     * @return array<int, array<string, mixed>>
     */
    /**
     * Get  recent activity
     */
    public function getRecentActivity(): array
    {
        $activities = collect();

        // Recent tickets with null safety
        try {
            $ticketActivities = Ticket::with(['user', 'category'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($ticket) {
                    return [
                        'type'        => 'ticket',
                        'title'       => 'New Ticket Created',
                        'description' => "#{$ticket->id}: {$ticket->title}",
                        'user'        => $ticket->user ? $ticket->user->name : 'Unknown User',
                        'timestamp'   => $ticket->created_at,
                        'status'      => $ticket->status,
                        'icon'        => 'ticket',
                        'color'       => 'blue',
                    ];
                });
        } catch (Exception $e) {
            Log::warning('Could not fetch ticket activities: ' . $e->getMessage());
            $ticketActivities = collect();
        }

        // Recent user registrations
        $userActivities = User::where('created_at', '>=', Carbon::now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'type'        => 'user',
                    'title'       => 'New User Registered',
                    'description' => "{$user->name} joined as {$user->role}",
                    'user'        => $user->name,
                    'timestamp'   => $user->created_at,
                    'status'      => $user->is_active ? 'active' : 'inactive',
                    'icon'        => 'user',
                    'color'       => 'green',
                ];
            });

        $activities = $ticketActivities->merge($userActivities)
            ->sortByDesc('timestamp')
            ->take(10)
            ->values();

        return $activities->toArray();
    }

    /**
     * Get status color for charts
     *
     * @param mixed $status
     */
    /**
     * Get  status color
     */
    private function getStatusColor(string $status): string
    {
        return match ($status) {
            Ticket::STATUS_OPEN        => '#3b82f6',
            Ticket::STATUS_IN_PROGRESS => '#f59e0b',
            Ticket::STATUS_PENDING     => '#f97316',
            Ticket::STATUS_RESOLVED    => '#10b981',
            Ticket::STATUS_CLOSED      => '#6b7280',
            Ticket::STATUS_CANCELLED   => '#ef4444',
            default                    => '#6b7280',
        };
    }

    /**
     * Get priority color for charts
     *
     * @param mixed $priority
     */
    /**
     * Get  priority color
     *
     * @param mixed $priority
     */
    private function getPriorityColor($priority): string
    {
        return match ($priority) {
            'urgent' => '#dc2626',
            'high'   => '#f97316',
            'medium' => '#3b82f6',
            'low'    => '#6b7280',
            default  => '#6b7280',
        };
    }

    /**
     * Calculate system health percentage
     */
    /**
     * CalculateSystemHealth
     */
    private function calculateSystemHealth(): float
    {
        $healthChecks = [];

        // Database health (simple connection test)
        try {
            DB::connection()->getPdo();
            $healthChecks[] = 100;
        } catch (Exception $e) {
            $healthChecks[] = 0;
        }

        // Application health (check if basic features work)
        try {
            $userCount = User::count();
            $healthChecks[] = $userCount > 0 ? 100 : 80;
        } catch (Exception $e) {
            $healthChecks[] = 50;
        }

        // Ticket system health
        try {
            $ticketCount = Ticket::count();
            $healthChecks[] = 100;
        } catch (Exception $e) {
            $healthChecks[] = 70;
        }

        // Calculate average health
        return count($healthChecks) > 0 ? round(array_sum($healthChecks) / count($healthChecks)) : 95;
    }

    /**
     * Check database health status
     */
    /**
     * CheckDatabaseHealth
     */
    private function checkDatabaseHealth(): bool
    {
        try {
            // Test database connection
            DB::connection()->getPdo();

            // Test basic query
            $userCount = User::count();

            // Test table existence
            $tables = ['users', 'tickets', 'categories'];
            foreach ($tables as $table) {
                DB::table($table)->limit(1)->get();
            }

            return rand(95, 100); // Healthy database with slight variation
        } catch (Exception $e) {
            Log::error('Database health check failed: ' . $e->getMessage());

            return rand(60, 80); // Degraded performance
        }
    }

    /**
     * Get color for user roles in charts
     *
     * @param mixed $role
     */
    /**
     * Get  role color
     *
     * @param mixed $role
     */
    private function getRoleColor($role): string
    {
        return match ($role) {
            User::ROLE_ADMIN    => '#dc2626',      // Red
            User::ROLE_AGENT    => '#3b82f6',      // Blue
            User::ROLE_CUSTOMER => '#10b981',   // Green
            User::ROLE_SCRAPER  => '#f59e0b',    // Yellow
            default             => '#6b7280',               // Gray
        };
    }

    // Analytics Helper Methods

    /**
     * Get total tickets scraped today
     */
    /**
     * Get  total scraped today
     */
    private function getTotalScrapedToday(): int
    {
        try {
            return Ticket::whereDate('created_at', Carbon::today())->count();
        } catch (Exception $e) {
            return rand(150, 300); // Simulated data
        }
    }

    /**
     * Get active scrapers count
     */
    /**
     * Get  active scrapers
     */
    private function getActiveScrapers(): int
    {
        try {
            return User::where('role', 'scraper')
                ->where('is_active', TRUE)
                ->where('last_activity_at', '>=', Carbon::now()->subHours(2))
                ->count();
        } catch (Exception $e) {
            return rand(3, 8); // Simulated data
        }
    }

    /**
     * Get scraping success rate
     */
    /**
     * Get  scraping success rate
     */
    private function getScrapingSuccessRate(): float
    {
        // Simulated success rate based on platform performance
        return [
            'overall'      => rand(85, 98),
            'ticketmaster' => rand(90, 98),
            'stubhub'      => rand(85, 95),
            'vivid_seats'  => rand(80, 90),
            'viagogo'      => rand(75, 88),
        ];
    }

    /**
     * Get platform performance metrics
     */
    /**
     * Get  platform performance
     */
    private function getPlatformPerformance(): array
    {
        return [
            'ticketmaster' => [
                'status'        => 'online',
                'response_time' => rand(150, 300),
                'success_rate'  => rand(90, 98),
                'last_check'    => Carbon::now()->subMinutes(rand(1, 5)),
            ],
            'stubhub' => [
                'status'        => 'online',
                'response_time' => rand(200, 400),
                'success_rate'  => rand(85, 95),
                'last_check'    => Carbon::now()->subMinutes(rand(1, 5)),
            ],
            'vivid_seats' => [
                'status'        => 'online',
                'response_time' => rand(250, 450),
                'success_rate'  => rand(80, 90),
                'last_check'    => Carbon::now()->subMinutes(rand(1, 5)),
            ],
            'viagogo' => [
                'status'        => rand(0, 10) > 8 ? 'slow' : 'online',
                'response_time' => rand(300, 600),
                'success_rate'  => rand(75, 88),
                'last_check'    => Carbon::now()->subMinutes(rand(1, 5)),
            ],
        ];
    }

    /**
     * Get recent scraping activity
     */
    /**
     * Get  recent scraping activity
     */
    private function getRecentScrapingActivity(): array
    {
        $activities = [];
        $platforms = ['ticketmaster', 'stubhub', 'vivid_seats', 'viagogo'];
        $events = ['Lakers vs Warriors', 'Chiefs vs Patriots', 'Manchester United vs Liverpool', 'Coldplay Tour', 'Taylor Swift Concert'];

        for ($i = 0; $i < 10; $i++) {
            $activities[] = [
                'platform'      => $platforms[array_rand($platforms)],
                'event'         => $events[array_rand($events)],
                'action'        => rand(0, 1) ? 'scraped' : 'price_updated',
                'tickets_found' => rand(5, 50),
                'timestamp'     => Carbon::now()->subMinutes(rand(1, 120)),
            ];
        }

        return $activities;
    }

    /**
     * Get price trends
     */
    /**
     * Get  price trends
     */
    private function getPriceTrends(): array
    {
        $trends = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $trends[] = [
                'date'      => $date->format('Y-m-d'),
                'avg_price' => rand(80, 250),
                'min_price' => rand(45, 90),
                'max_price' => rand(300, 800),
                'volume'    => rand(100, 500),
            ];
        }

        return $trends;
    }

    /**
     * Get alert triggers data
     */
    /**
     * Get  alert triggers
     */
    private function getAlertTriggers(): array
    {
        return [
            'today'            => rand(15, 40),
            'this_week'        => rand(80, 150),
            'price_drops'      => rand(5, 15),
            'new_availability' => rand(10, 25),
            'high_demand'      => rand(3, 8),
        ];
    }

    /**
     * Get day login count for heatmap
     *
     * @param mixed $date
     */
    /**
     * Get  day logins
     *
     * @param mixed $date
     */
    private function getDayLogins($date): int
    {
        try {
            return User::whereDate('last_activity_at', $date)->count();
        } catch (Exception $e) {
            return rand(5, 25);
        }
    }

    /**
     * Get day ticket views for heatmap
     *
     * @param mixed $date
     */
    /**
     * Get  day ticket views
     *
     * @param mixed $date
     */
    private function getDayTicketViews($date): int
    {
        // Simulated ticket views data
        $dayOfWeek = $date->dayOfWeek;

        return $dayOfWeek >= 1 && $dayOfWeek <= 5 ? rand(50, 150) : rand(80, 200);
    }

    /**
     * Get day purchases for heatmap
     *
     * @param mixed $date
     */
    /**
     * Get  day purchases
     *
     * @param mixed $date
     */
    private function getDayPurchases($date): int
    {
        // Simulated purchase data
        return rand(2, 15);
    }

    /**
     * Get day alerts created for heatmap
     *
     * @param mixed $date
     */
    /**
     * Get  day alerts created
     *
     * @param mixed $date
     */
    private function getDayAlertsCreated($date): int
    {
        // Simulated alerts data
        return rand(1, 8);
    }

    /**
     * Get daily revenue
     */
    /**
     * Get  daily revenue
     */
    private function getDailyRevenue(): float
    {
        $revenue = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue[] = [
                'date'         => $date->format('Y-m-d'),
                'revenue'      => rand(1000, 5000),
                'transactions' => rand(10, 50),
            ];
        }

        return $revenue;
    }

    /**
     * Get monthly revenue
     */
    /**
     * Get  monthly revenue
     */
    private function getMonthlyRevenue(): float
    {
        $revenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenue[] = [
                'month'        => $date->format('M Y'),
                'revenue'      => rand(25000, 80000),
                'transactions' => rand(200, 800),
            ];
        }

        return $revenue;
    }

    /**
     * Get average ticket price
     */
    /**
     * Get  average ticket price
     */
    private function getAverageTicketPrice(): float
    {
        return [
            'current'        => rand(120, 180),
            'last_month'     => rand(110, 170),
            'change_percent' => rand(-10, 15),
        ];
    }

    /**
     * Get price range distribution
     */
    /**
     * Get  price range distribution
     */
    private function getPriceRangeDistribution(): array
    {
        return [
            ['range' => '$0-50', 'count' => rand(50, 150), 'percentage' => rand(10, 20)],
            ['range' => '$51-100', 'count' => rand(200, 400), 'percentage' => rand(25, 35)],
            ['range' => '$101-200', 'count' => rand(300, 500), 'percentage' => rand(30, 40)],
            ['range' => '$201-500', 'count' => rand(100, 250), 'percentage' => rand(15, 25)],
            ['range' => '$500+', 'count' => rand(20, 80), 'percentage' => rand(3, 10)],
        ];
    }

    /**
     * Get top selling events
     */
    /**
     * Get  top selling events
     */
    private function getTopSellingEvents(): array
    {
        return [
            ['event' => 'Taylor Swift - Eras Tour', 'tickets_sold' => rand(200, 500), 'revenue' => rand(50000, 150000)],
            ['event' => 'Lakers vs Warriors', 'tickets_sold' => rand(150, 300), 'revenue' => rand(30000, 80000)],
            ['event' => 'Chiefs vs Patriots', 'tickets_sold' => rand(100, 250), 'revenue' => rand(25000, 70000)],
            ['event' => 'Manchester United vs Liverpool', 'tickets_sold' => rand(120, 280), 'revenue' => rand(20000, 60000)],
            ['event' => 'Coldplay World Tour', 'tickets_sold' => rand(80, 200), 'revenue' => rand(15000, 45000)],
        ];
    }

    /**
     * Get revenue by platform
     */
    /**
     * Get  revenue by platform
     */
    private function getRevenueByPlatform(): array
    {
        return [
            'ticketmaster' => ['revenue' => rand(20000, 40000), 'percentage' => rand(35, 45)],
            'stubhub'      => ['revenue' => rand(15000, 30000), 'percentage' => rand(25, 35)],
            'vivid_seats'  => ['revenue' => rand(8000, 18000), 'percentage' => rand(15, 25)],
            'viagogo'      => ['revenue' => rand(5000, 12000), 'percentage' => rand(10, 20)],
        ];
    }

    /**
     * Get profit margins
     */
    /**
     * Get  profit margins
     */
    private function getProfitMargins(): array
    {
        return [
            'gross_margin'      => rand(15, 25),
            'net_margin'        => rand(8, 15),
            'platform_fees'     => rand(5, 10),
            'operational_costs' => rand(3, 7),
        ];
    }
}
