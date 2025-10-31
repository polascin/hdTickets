<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\Event;
use App\Models\Order;
use App\Models\ScrapingSource;
use App\Models\SystemSetting;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use PDF;

use function count;
use function in_array;
use function is_array;

/**
 * Admin Controller
 *
 * Handles all admin panel operations including user management,
 * system configuration, analytics, and real-time features.
 */
class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    // ================================
    // USER MANAGEMENT METHODS
    // ================================

    /**
     * Get paginated users with filtering and search
     */
    public function getUsers(Request $request): JsonResponse
    {
        try {
            $query = User::with(['orders', 'tickets'])
                ->select([
                    'id', 'name', 'email', 'role', 'status',
                    'email_verified_at', 'last_login_at', 'created_at',
                ]);

            // Apply search filter
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Apply role filter
            if ($request->has('role') && $request->role !== 'all') {
                $query->where('role', $request->role);
            }

            // Apply status filter
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Apply date range filter
            if ($request->has('date_from') && $request->has('date_to')) {
                $query->whereBetween('created_at', [
                    Carbon::parse($request->date_from)->startOfDay(),
                    Carbon::parse($request->date_to)->endOfDay(),
                ]);
            }

            // Apply email verification filter
            if ($request->has('email_verified') && $request->email_verified !== 'all') {
                if ($request->email_verified === 'verified') {
                    $query->whereNotNull('email_verified_at');
                } else {
                    $query->whereNull('email_verified_at');
                }
            }

            // Sorting
            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            $query->orderBy($sortField, $sortDirection);

            // Pagination
            $perPage = $request->get('per_page', 25);
            $users = $query->paginate($perPage);

            // Add computed fields
            $users->getCollection()->transform(function ($user) {
                $user->total_orders = $user->orders->count();
                $user->total_spent = $user->orders->where('status', 'completed')->sum('total');
                $user->total_tickets = $user->tickets->count();
                $user->is_email_verified = NULL !== $user->email_verified_at;

                // Remove relations to reduce payload size
                $user->orders = NULL;
                $user->tickets = NULL;

                return $user;
            });

            return response()->json([
                'success' => TRUE,
                'data'    => $users,
            ]);
        } catch (Exception $e) {
            Log::error('Admin: Failed to get users - ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to retrieve users',
            ], 500);
        }
    }

    /**
     * Perform user action (activate, suspend, delete, etc.)
     *
     * @param int $id
     */
    public function userAction(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|string|in:activate,suspend,ban,delete,reset_password,change_role,login_as',
            'role'   => 'sometimes|string|in:user,vip,moderator,admin',
            'reason' => 'sometimes|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = User::findOrFail($id);
            $action = $request->action;

            // Prevent self-actions
            if ($user->id === auth()->id() && in_array($action, ['suspend', 'ban', 'delete'], TRUE)) {
                return response()->json([
                    'success' => FALSE,
                    'error'   => 'Cannot perform this action on your own account',
                ], 403);
            }

            switch ($action) {
                case 'activate':
                    $user->update(['status' => 'active']);

                    break;
                case 'suspend':
                    $user->update(['status' => 'suspended']);

                    break;
                case 'ban':
                    $user->update(['status' => 'banned']);

                    break;
                case 'delete':
                    // Soft delete with anonymization
                    $user->update([
                        'name'   => 'Deleted User',
                        'email'  => 'deleted_' . $user->id . '@deleted.com',
                        'status' => 'deleted',
                    ]);
                    $user->delete();

                    break;
                case 'reset_password':
                    $newPassword = str()->random(12);
                    $user->update(['password' => Hash::make($newPassword)]);

                    // Send email with new password (in real app, use password reset link)
                    Mail::send('emails.password-reset', [
                        'user'     => $user,
                        'password' => $newPassword,
                    ], function ($message) use ($user): void {
                        $message->to($user->email)->subject('Password Reset');
                    });

                    break;
                case 'change_role':
                    $user->update(['role' => $request->role]);

                    break;
                case 'login_as':
                    // Generate login token for admin login as user
                    $token = str()->random(64);
                    Cache::put("admin_login_as_{$token}", [
                        'admin_id' => auth()->id(),
                        'user_id'  => $user->id,
                    ], now()->addMinutes(5));

                    return response()->json([
                        'success'   => TRUE,
                        'login_url' => url("/admin/login-as/{$token}"),
                    ]);
            }

            // Log admin action
            Log::info("Admin action: {$action} performed on user {$user->id} by admin " . auth()->id(), [
                'user_id'  => $user->id,
                'admin_id' => auth()->id(),
                'action'   => $action,
                'reason'   => $request->reason,
            ]);

            return response()->json([
                'success' => TRUE,
                'message' => ucfirst($action) . ' action completed successfully',
            ]);
        } catch (Exception $e) {
            Log::error('Admin: User action failed - ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'error'   => 'Action failed to complete',
            ], 500);
        }
    }

    /**
     * Bulk user actions
     */
    public function bulkUserAction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'action'     => 'required|string|in:activate,suspend,ban,delete,change_role,export',
            'role'       => 'sometimes|string|in:user,vip,moderator,admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $userIds = $request->user_ids;
            $action = $request->action;

            // Prevent self-actions
            if (in_array(auth()->id(), $userIds, TRUE) && in_array($action, ['suspend', 'ban', 'delete'], TRUE)) {
                return response()->json([
                    'success' => FALSE,
                    'error'   => 'Cannot perform bulk action on your own account',
                ], 403);
            }

            $affectedCount = 0;

            switch ($action) {
                case 'activate':
                    $affectedCount = User::whereIn('id', $userIds)->update(['status' => 'active']);

                    break;
                case 'suspend':
                    $affectedCount = User::whereIn('id', $userIds)->update(['status' => 'suspended']);

                    break;
                case 'ban':
                    $affectedCount = User::whereIn('id', $userIds)->update(['status' => 'banned']);

                    break;
                case 'delete':
                    User::whereIn('id', $userIds)->update([
                        'status' => 'deleted',
                        'name'   => DB::raw("CONCAT('Deleted User ', id)"),
                        'email'  => DB::raw("CONCAT('deleted_', id, '@deleted.com')"),
                    ]);
                    $affectedCount = User::whereIn('id', $userIds)->delete();

                    break;
                case 'change_role':
                    $affectedCount = User::whereIn('id', $userIds)->update(['role' => $request->role]);

                    break;
                case 'export':
                    return $this->exportUsers($userIds);
            }

            Log::info("Admin bulk action: {$action} performed on {$affectedCount} users by admin " . auth()->id());

            return response()->json([
                'success' => TRUE,
                'message' => "Bulk {$action} completed successfully on {$affectedCount} users",
            ]);
        } catch (Exception $e) {
            Log::error('Admin: Bulk user action failed - ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'error'   => 'Bulk action failed to complete',
            ], 500);
        }
    }

    // ================================
    // SYSTEM CONFIGURATION METHODS
    // ================================

    /**
     * Get system settings
     */
    public function getSettings(): JsonResponse
    {
        try {
            $settings = Cache::remember('system_settings', 300, function () {
                $dbSettings = SystemSetting::all()->pluck('value', 'key');

                // Default structure if no settings exist
                $defaultSettings = [
                    'general' => [
                        'platform_name'      => 'HD Tickets',
                        'platform_url'       => config('app.url'),
                        'support_email'      => 'support@hdtickets.com',
                        'default_currency'   => 'USD',
                        'timezone'           => 'America/New_York',
                        'maintenance_mode'   => FALSE,
                        'user_registration'  => TRUE,
                        'email_verification' => TRUE,
                        'debug_mode'         => config('app.debug'),
                        'analytics_tracking' => TRUE,
                    ],
                    'scraping' => [
                        'sources' => ScrapingSource::all()->toArray(),
                    ],
                    'api' => [
                        'stripe' => [
                            'publishable_key' => config('services.stripe.key'),
                            'secret_key'      => '****', // Masked for security
                        ],
                        'paypal' => [
                            'environment' => config('services.paypal.environment'),
                        ],
                        'google_maps' => [
                            'api_key' => '****',
                        ],
                        'sendgrid' => [
                            'api_key' => '****',
                        ],
                        'twilio' => [
                            'account_sid' => config('services.twilio.account_sid'),
                            'auth_token'  => '****',
                        ],
                    ],
                    'email' => [
                        'templates' => EmailTemplate::all()->keyBy('key')->toArray(),
                    ],
                    'notifications' => [
                        'email' => [
                            'price_alerts'          => TRUE,
                            'booking_confirmations' => TRUE,
                            'account_updates'       => TRUE,
                            'marketing'             => FALSE,
                        ],
                        'push' => [
                            'firebase_key'    => '****',
                            'price_drops'     => TRUE,
                            'new_events'      => TRUE,
                            'booking_updates' => TRUE,
                        ],
                    ],
                    'security' => [
                        'session_timeout'       => 60,
                        'password_min_length'   => 8,
                        'two_factor_auth'       => FALSE,
                        'login_attempts_limit'  => TRUE,
                        'password_requirements' => TRUE,
                        'api_rate_limit'        => 100,
                        'cors_origins'          => '',
                        'api_key_required'      => TRUE,
                        'ssl_required'          => TRUE,
                    ],
                ];

                // Merge with database settings
                foreach ($dbSettings as $key => $value) {
                    $keys = explode('.', $key);
                    $current = &$defaultSettings;

                    foreach ($keys as $k) {
                        $current = &$current[$k];
                    }

                    $current = json_decode($value, TRUE) ?? $value;
                }

                return $defaultSettings;
            });

            return response()->json([
                'success'  => TRUE,
                'settings' => $settings,
            ]);
        } catch (Exception $e) {
            Log::error('Admin: Failed to get settings - ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to retrieve settings',
            ], 500);
        }
    }

    /**
     * Save system settings
     */
    public function saveSettings(Request $request): JsonResponse
    {
        try {
            $settings = $request->all();

            DB::transaction(function () use ($settings): void {
                // Flatten settings and save to database
                $this->saveSettingsRecursive($settings);

                // Update scraping sources
                if (isset($settings['scraping']['sources'])) {
                    ScrapingSource::truncate();
                    foreach ($settings['scraping']['sources'] as $source) {
                        ScrapingSource::create($source);
                    }
                }

                // Update email templates
                if (isset($settings['email']['templates'])) {
                    foreach ($settings['email']['templates'] as $key => $template) {
                        EmailTemplate::updateOrCreate(
                            ['key' => $key],
                            $template,
                        );
                    }
                }
            });

            // Clear settings cache
            Cache::forget('system_settings');

            Log::info('Admin: System settings updated by admin ' . auth()->id());

            return response()->json([
                'success' => TRUE,
                'message' => 'Settings saved successfully',
            ]);
        } catch (Exception $e) {
            Log::error('Admin: Failed to save settings - ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to save settings',
            ], 500);
        }
    }

    /**
     * Test scraping source connection
     */
    public function testScrapingSource(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url'        => 'required|url',
            'rate_limit' => 'required|integer|min:1|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'HDTickets-Bot/1.0',
                ])
                ->get($request->url);

            if ($response->successful()) {
                return response()->json([
                    'success'       => TRUE,
                    'response_time' => $response->transferStats->getTransferTime(),
                    'status_code'   => $response->status(),
                ]);
            }

            return response()->json([
                'success' => FALSE,
                'error'   => "HTTP {$response->status()}: Connection failed",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    // ================================
    // ANALYTICS METHODS
    // ================================

    /**
     * Get analytics data
     */
    public function getAnalytics(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', '30d');
            $cacheKey = "analytics_{$period}";

            $analytics = Cache::remember($cacheKey, 300, function () use ($period) {
                $dateRange = $this->getDateRange($period);
                $previousRange = $this->getPreviousDateRange($period);

                // Calculate metrics
                $metrics = $this->calculateMetrics($dateRange, $previousRange);

                // Get top events
                $topEvents = $this->getTopEvents($dateRange);

                // Get category breakdown
                $topCategories = $this->getTopCategories($dateRange);

                // Get traffic sources (simulated data)
                $trafficSources = $this->getTrafficSources();

                // Get recent activity
                $recentActivity = $this->getRecentActivity();

                // Get system health
                $systemHealth = $this->getSystemHealth();

                return [
                    'metrics'        => $metrics,
                    'topEvents'      => $topEvents,
                    'topCategories'  => $topCategories,
                    'trafficSources' => $trafficSources,
                    'recentActivity' => $recentActivity,
                    'systemHealth'   => $systemHealth,
                    'chartData'      => $this->getChartData($period),
                ];
            });

            return response()->json([
                'success' => TRUE,
                'data'    => $analytics,
            ]);
        } catch (Exception $e) {
            Log::error('Admin: Failed to get analytics - ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to retrieve analytics data',
            ], 500);
        }
    }

    /**
     * Export analytics report
     *
     * @return \Illuminate\Http\Response
     */
    public function exportReport(Request $request)
    {
        try {
            $period = $request->get('period', '30d');
            $analytics = Cache::get("analytics_{$period}");

            if (!$analytics) {
                // Generate fresh analytics data
                $request->merge(['period' => $period]);
                $response = $this->getAnalytics($request);
                $analytics = $response->getData(TRUE)['data'];
            }

            $theme = in_array($request->get('theme'), ['light', 'dark'], TRUE) ? $request->get('theme') : 'light';

            // Prepare logo data URI for reliable PDF embedding
            $logoDataUri = NULL;
            $logoPath = public_path('images/logo-hdtickets.svg');
            if (file_exists($logoPath)) {
                $logoDataUri = 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($logoPath));
            }

            $pdf = PDF::loadView('admin.reports.analytics', [
                'analytics'    => $analytics,
                'period'       => $period,
                'generated_at' => now(),
                'theme'        => $theme,
                'logoDataUri'  => $logoDataUri,
            ]);

            $filename = "analytics-report-{$period}-" . now()->format('Y-m-d') . '.pdf';

            return $pdf->download($filename);
        } catch (Exception $e) {
            Log::error('Admin: Failed to export report - ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to export report',
            ], 500);
        }
    }

    // ================================
    // PRIVATE HELPER METHODS
    // ================================

    /**
     * Save settings recursively to database
     */
    private function saveSettingsRecursive(array $settings, string $prefix = ''): void
    {
        foreach ($settings as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value) && !in_array($key, ['sources', 'templates'], TRUE)) {
                $this->saveSettingsRecursive($value, $fullKey);
            } else {
                SystemSetting::updateOrCreate(
                    ['key' => $fullKey],
                    ['value' => is_array($value) ? json_encode($value) : $value],
                );
            }
        }
    }

    /**
     * Get date range based on period
     */
    private function getDateRange(string $period): array
    {
        switch ($period) {
            case '7d':
                return [Carbon::now()->subDays(7), Carbon::now()];
            case '30d':
                return [Carbon::now()->subDays(30), Carbon::now()];
            case '90d':
                return [Carbon::now()->subDays(90), Carbon::now()];
            case '1y':
                return [Carbon::now()->subYear(), Carbon::now()];
            default:
                return [Carbon::now()->subDays(30), Carbon::now()];
        }
    }

    /**
     * Get previous date range for comparison
     */
    private function getPreviousDateRange(string $period): array
    {
        switch ($period) {
            case '7d':
                return [Carbon::now()->subDays(14), Carbon::now()->subDays(7)];
            case '30d':
                return [Carbon::now()->subDays(60), Carbon::now()->subDays(30)];
            case '90d':
                return [Carbon::now()->subDays(180), Carbon::now()->subDays(90)];
            case '1y':
                return [Carbon::now()->subYears(2), Carbon::now()->subYear()];
            default:
                return [Carbon::now()->subDays(60), Carbon::now()->subDays(30)];
        }
    }

    /**
     * Calculate key metrics
     */
    private function calculateMetrics(array $dateRange, array $previousRange): array
    {
        // Current period metrics
        $currentRevenue = Order::whereBetween('created_at', $dateRange)
            ->where('status', 'completed')
            ->sum('total');

        $currentUsers = User::whereBetween('created_at', $dateRange)->count();

        $currentTickets = Ticket::whereBetween('created_at', $dateRange)->count();

        // Previous period metrics for comparison
        $previousRevenue = Order::whereBetween('created_at', $previousRange)
            ->where('status', 'completed')
            ->sum('total');

        $previousUsers = User::whereBetween('created_at', $previousRange)->count();

        $previousTickets = Ticket::whereBetween('created_at', $previousRange)->count();

        // Calculate conversion rate
        $totalVisitors = 50000; // This would come from analytics service in real app
        $conversionRate = $currentTickets > 0 ? ($currentTickets / $totalVisitors) * 100 : 0;
        $previousConversionRate = $previousTickets > 0 ? ($previousTickets / $totalVisitors) * 100 : 0;

        return [
            'revenue' => [
                'total'  => $currentRevenue,
                'change' => $previousRevenue > 0
                    ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100
                    : 0,
            ],
            'users' => [
                'total'  => $currentUsers,
                'change' => $previousUsers > 0
                    ? (($currentUsers - $previousUsers) / $previousUsers) * 100
                    : 0,
            ],
            'tickets' => [
                'sold'   => $currentTickets,
                'change' => $previousTickets > 0
                    ? (($currentTickets - $previousTickets) / $previousTickets) * 100
                    : 0,
            ],
            'conversion' => [
                'rate'   => round($conversionRate, 2),
                'change' => $previousConversionRate > 0
                    ? (($conversionRate - $previousConversionRate) / $previousConversionRate) * 100
                    : 0,
            ],
        ];
    }

    /**
     * Get top performing events
     */
    private function getTopEvents(array $dateRange): array
    {
        return Event::withCount(['tickets as tickets_sold'])
            ->with(['orders' => function ($query) use ($dateRange): void {
                $query->whereBetween('created_at', $dateRange)
                    ->where('status', 'completed');
            }])
            ->get()
            ->map(function ($event) {
                return [
                    'id'           => $event->id,
                    'name'         => $event->name,
                    'venue'        => $event->venue,
                    'tickets_sold' => $event->tickets_sold,
                    'revenue'      => $event->orders->sum('total'),
                ];
            })
            ->sortByDesc('revenue')
            ->take(5)
            ->values()
            ->toArray();
    }

    /**
     * Get category performance breakdown
     */
    private function getTopCategories(array $dateRange): array
    {
        $categories = Event::select('category', DB::raw('COUNT(*) as event_count'))
            ->groupBy('category')
            ->get();

        $total = $categories->sum('event_count');
        $colors = ['#10B981', '#3B82F6', '#8B5CF6', '#F59E0B', '#EF4444'];

        return $categories->map(function ($category, $index) use ($total, $colors) {
            return [
                'name'       => ucfirst($category->category),
                'percentage' => $total > 0 ? round(($category->event_count / $total) * 100, 1) : 0,
                'color'      => $colors[$index % count($colors)],
            ];
        })->toArray();
    }

    /**
     * Get traffic sources (simulated data)
     */
    private function getTrafficSources(): array
    {
        return [
            ['name' => 'Google Search', 'type' => 'search', 'visitors' => 12543, 'percentage' => 45],
            ['name' => 'Facebook', 'type' => 'social', 'visitors' => 8321, 'percentage' => 30],
            ['name' => 'Direct', 'type' => 'direct', 'visitors' => 4562, 'percentage' => 16],
            ['name' => 'Instagram', 'type' => 'social', 'visitors' => 2134, 'percentage' => 8],
            ['name' => 'Other', 'type' => 'other', 'visitors' => 287, 'percentage' => 1],
        ];
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity(): array
    {
        $activities = collect();

        // Recent orders
        $recentOrders = Order::with('user')->latest()->take(3)->get();
        foreach ($recentOrders as $order) {
            $activities->push([
                'id'          => $order->id,
                'type'        => 'purchase',
                'description' => "User {$order->user->name} purchased tickets for {$order->total}",
                'timestamp'   => $order->created_at->diffForHumans(),
            ]);
        }

        // Recent user registrations
        $recentUsers = User::latest()->take(2)->get();
        foreach ($recentUsers as $user) {
            $activities->push([
                'id'          => $user->id,
                'type'        => 'registration',
                'description' => "New user registered: {$user->email}",
                'timestamp'   => $user->created_at->diffForHumans(),
            ]);
        }

        return $activities->sortByDesc('timestamp')->values()->toArray();
    }

    /**
     * Get system health status
     */
    private function getSystemHealth(): array
    {
        return [
            [
                'name'        => 'Web Server',
                'description' => 'Apache/Nginx',
                'status'      => 'healthy',
                'uptime'      => '99.9%',
            ],
            [
                'name'        => 'Database',
                'description' => 'MySQL/PostgreSQL',
                'status'      => 'healthy',
                'uptime'      => '99.8%',
            ],
            [
                'name'        => 'Cache System',
                'description' => 'Redis/Memcached',
                'status'      => 'warning',
                'uptime'      => '98.5%',
            ],
            [
                'name'        => 'Email Service',
                'description' => 'SendGrid/Mailgun',
                'status'      => 'healthy',
                'uptime'      => '99.7%',
            ],
            [
                'name'        => 'Payment Gateway',
                'description' => 'Stripe/PayPal',
                'status'      => 'healthy',
                'uptime'      => '99.9%',
            ],
        ];
    }

    /**
     * Get chart data for analytics
     */
    private function getChartData(string $period): array
    {
        $days = match ($period) {
            '7d'    => 7,
            '30d'   => 30,
            '90d'   => 90,
            '1y'    => 365,
            default => 30,
        };

        $labels = [];
        $revenueData = [];
        $userData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M j');

            // Simulated data - replace with real queries
            $revenueData[] = rand(10000, 50000);
            $userData[] = rand(500, 2000);
        }

        return [
            'labels'  => $labels,
            'revenue' => $revenueData,
            'users'   => $userData,
        ];
    }

    /**
     * Export users to Excel/CSV
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportUsers(array $userIds)
    {
        $users = User::whereIn('id', $userIds)
            ->select(['id', 'name', 'email', 'role', 'status', 'created_at'])
            ->get();

        $filename = 'users-export-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($users): void {
            $handle = fopen('php://output', 'w');

            // CSV headers
            fputcsv($handle, ['ID', 'Name', 'Email', 'Role', 'Status', 'Created At']);

            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role,
                    $user->status,
                    $user->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
