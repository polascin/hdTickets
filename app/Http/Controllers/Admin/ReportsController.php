<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Ticket;
use App\Models\ScrapedTicket;
use App\Models\PurchaseAttempt;
use App\Models\Category;
use App\Exports\UsersExport;
use App\Exports\ScrapedTicketsExport;
use App\Exports\AuditTrailExport;
use App\Imports\UsersImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facades\Pdf;
use Spatie\Activitylog\Models\Activity;

class ReportsController extends Controller
{
    /**
     * Display the reports dashboard
     */
    public function index()
    {
        $this->authorize('access_reports');
        
        $totalUsers = User::count();
        $totalScrapedTickets = ScrapedTicket::count();
        $totalCategories = Category::count();
        $totalActivities = Activity::count();
        
        // For the view compatibility, we'll use ScrapedTickets as "tickets" data
        $totalTickets = $totalScrapedTickets;
        $openTickets = ScrapedTicket::where('status', ScrapedTicket::STATUS_ACTIVE)->count();
        $resolvedTickets = ScrapedTicket::where('status', ScrapedTicket::STATUS_SOLD_OUT)->count();
        $overdueTickets = ScrapedTicket::where('status', ScrapedTicket::STATUS_EXPIRED)->count();
        
        // Performance metrics (placeholder values for now)
        $avgResponseTime = 2.5; // hours
        $avgResolutionTime = 8.2; // hours
        $resolutionRate = $totalTickets > 0 ? round(($resolvedTickets / $totalTickets) * 100, 1) : 0;
        
        // Get top agents (placeholder for now)
        $topAgents = collect();
        $agentWorkload = collect();
        
        // Get weekly trend data for chart
        $weeklyTrend = $this->getWeeklyTrendForScrapedTickets();

        // Recent activity summary
        $recentActivities = Activity::with('causer')
            ->latest()
            ->limit(10)
            ->get();

        // User statistics
        $userStats = [
            'by_role' => User::select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->pluck('count', 'role')
                ->toArray(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'unverified' => User::whereNull('email_verified_at')->count(),
        ];

        // Scraped tickets statistics
        $ticketStats = [
            'by_platform' => ScrapedTicket::select('platform', DB::raw('count(*) as count'))
                ->groupBy('platform')
                ->pluck('count', 'platform')
                ->toArray(),
            'by_status' => ScrapedTicket::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
        ];

        return view('admin.reports.index', compact(
            'totalUsers',
            'totalScrapedTickets', 
            'totalCategories',
            'totalActivities',
            'totalTickets',
            'openTickets',
            'resolvedTickets', 
            'overdueTickets',
            'avgResponseTime',
            'avgResolutionTime',
            'resolutionRate',
            'topAgents',
            'agentWorkload',
            'weeklyTrend',
            'recentActivities',
            'userStats',
            'ticketStats'
        ));
    }

    /**
     * Generate ticket volume report
     */
    public function ticketVolume(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth()->startOfDay());
        $endDate = $request->input('end_date', now()->endOfDay());
        $groupBy = $request->input('group_by', 'day'); // day, week, month

        $query = ScrapedTicket::whereBetween('created_at', [$startDate, $endDate]);

        $dateFormat = match($groupBy) {
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $data = $query
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active'),
                DB::raw('SUM(CASE WHEN status = "sold_out" THEN 1 ELSE 0 END) as sold_out'),
                DB::raw('SUM(CASE WHEN status = "expired" THEN 1 ELSE 0 END) as expired'),
                DB::raw('SUM(CASE WHEN is_high_demand = 1 THEN 1 ELSE 0 END) as high_demand')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        if ($request->expectsJson()) {
            return response()->json($data);
        }

        return view('admin.reports.ticket-volume', compact('data', 'startDate', 'endDate', 'groupBy'));
    }

    /**
     * Generate agent performance report
     */
    public function agentPerformance(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth()->startOfDay());
        $endDate = $request->input('end_date', now()->endOfDay());

        $agents = User::where('role', User::ROLE_AGENT)
            ->with([
                'assignedTickets' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            ])
            ->get()
            ->map(function ($agent) use ($startDate, $endDate) {
                $assignedTickets = $agent->assignedTickets;
                $resolvedTickets = $assignedTickets->where('status', Ticket::STATUS_RESOLVED);
                
                return [
                    'name' => $agent->full_name,
                    'email' => $agent->email,
                    'assigned_tickets' => $assignedTickets->count(),
                    'resolved_tickets' => $resolvedTickets->count(),
                    'resolution_rate' => $assignedTickets->count() > 0 
                        ? round(($resolvedTickets->count() / $assignedTickets->count()) * 100, 1) 
                        : 0,
                    'avg_resolution_time' => $this->getAgentAverageResolutionTime($agent->id, $startDate, $endDate),
                    'first_response_time' => $this->getAgentAverageResponseTime($agent->id, $startDate, $endDate),
                ];
            })
            ->sortByDesc('resolved_tickets')
            ->values();

        if ($request->expectsJson()) {
            return response()->json($agents);
        }

        return view('admin.reports.agent-performance', compact('agents', 'startDate', 'endDate'));
    }

    /**
     * Generate category analysis report
     */
    public function categoryAnalysis(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth()->startOfDay());
        $endDate = $request->input('end_date', now()->endOfDay());

        $categoryData = Category::withCount([
                'tickets as total_tickets' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                },
                'tickets as resolved_tickets' => function ($query) use ($startDate, $endDate) {
                    $query->where('status', Ticket::STATUS_RESOLVED)
                          ->whereBetween('created_at', [$startDate, $endDate]);
                },
                'tickets as overdue_tickets' => function ($query) use ($startDate, $endDate) {
                    $query->where('due_date', '<', now())
                          ->whereNotIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED, Ticket::STATUS_CANCELLED])
                          ->whereBetween('created_at', [$startDate, $endDate]);
                }
            ])
            ->having('total_tickets', '>', 0)
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'total_tickets' => $category->total_tickets,
                    'resolved_tickets' => $category->resolved_tickets,
                    'overdue_tickets' => $category->overdue_tickets,
                    'resolution_rate' => $category->total_tickets > 0 
                        ? round(($category->resolved_tickets / $category->total_tickets) * 100, 1) 
                        : 0,
                    'avg_resolution_time' => $this->getCategoryAverageResolutionTime($category->id, $startDate, $endDate),
                ];
            })
            ->sortByDesc('total_tickets')
            ->values();

        if ($request->expectsJson()) {
            return response()->json($categoryData);
        }

        return view('admin.reports.category-analysis', compact('categoryData', 'startDate', 'endDate'));
    }

    /**
     * Generate response time report
     */
    public function responseTime(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth()->startOfDay());
        $endDate = $request->input('end_date', now()->endOfDay());

        $responseTimeData = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('first_response_at')
            ->select([
                'id',
                'title',
                'priority',
                'created_at',
                'first_response_at',
                DB::raw('TIMESTAMPDIFF(MINUTE, created_at, first_response_at) as response_minutes')
            ])
            ->with(['user', 'assignedTo', 'category'])
            ->orderBy('response_minutes', 'desc')
            ->get();

        // Calculate statistics
        $stats = [
            'avg_response_time' => $responseTimeData->avg('response_minutes'),
            'median_response_time' => $this->calculateMedian($responseTimeData->pluck('response_minutes')->toArray()),
            'fastest_response' => $responseTimeData->min('response_minutes'),
            'slowest_response' => $responseTimeData->max('response_minutes'),
            'within_1_hour' => $responseTimeData->where('response_minutes', '<=', 60)->count(),
            'within_4_hours' => $responseTimeData->where('response_minutes', '<=', 240)->count(),
            'within_24_hours' => $responseTimeData->where('response_minutes', '<=', 1440)->count(),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $responseTimeData,
                'statistics' => $stats
            ]);
        }

        return view('admin.reports.response-time', compact('responseTimeData', 'stats', 'startDate', 'endDate'));
    }

    /**
     * Export report data
     */
    public function export(Request $request)
    {
        $type = $request->input('type', 'tickets');
        $format = $request->input('format', 'csv');
        
        switch ($type) {
            case 'agent_performance':
                return $this->exportAgentPerformance($request, $format);
            case 'category_analysis':
                return $this->exportCategoryAnalysis($request, $format);
            case 'response_time':
                return $this->exportResponseTime($request, $format);
            default:
                return $this->exportTickets($request, $format);
        }
    }

    /**
     * Get average response time in hours
     */
    private function getAverageResponseTime()
    {
        $avg = Ticket::whereNotNull('first_response_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, first_response_at)) as avg_hours')
            ->value('avg_hours');

        return $avg ? round($avg, 1) : 0;
    }

    /**
     * Get average resolution time in hours
     */
    private function getAverageResolutionTime()
    {
        $avg = Ticket::whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
            ->value('avg_hours');

        return $avg ? round($avg, 1) : 0;
    }

    /**
     * Get top performing agents
     */
    private function getTopAgents($limit = 5)
    {
        return User::where('role', User::ROLE_AGENT)
            ->withCount(['assignedTickets as resolved_tickets' => function ($query) {
                $query->where('status', Ticket::STATUS_RESOLVED)
                      ->where('resolved_at', '>=', now()->subMonth());
            }])
            ->orderBy('resolved_tickets', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get agent workload distribution
     */
    private function getAgentWorkload()
    {
        return User::where('role', User::ROLE_AGENT)
            ->withCount(['assignedTickets as active_tickets' => function ($query) {
                $query->whereIn('status', [Ticket::STATUS_OPEN, Ticket::STATUS_IN_PROGRESS, Ticket::STATUS_PENDING]);
            }])
            ->orderBy('active_tickets', 'desc')
            ->get();
    }

    /**
     * Get weekly ticket trend
     */
    private function getWeeklyTrend()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Ticket::whereDate('created_at', $date)->count();
            $data[] = [
                'date' => $date->format('M j'),
                'tickets' => $count
            ];
        }
        return $data;
    }

    /**
     * Get weekly scraped ticket trend for chart
     */
    private function getWeeklyTrendForScrapedTickets()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = ScrapedTicket::whereDate('created_at', $date)->count();
            $data[] = [
                'date' => $date->format('M j'),
                'tickets' => $count
            ];
        }
        return $data;
    }

    /**
     * Get monthly ticket trend
     */
    private function getMonthlyTrend()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Ticket::whereYear('created_at', $date->year)
                          ->whereMonth('created_at', $date->month)
                          ->count();
            $data[] = [
                'month' => $date->format('M Y'),
                'tickets' => $count
            ];
        }
        return $data;
    }

    /**
     * Calculate median from array
     */
    private function calculateMedian(array $numbers)
    {
        if (empty($numbers)) {
            return 0;
        }

        sort($numbers);
        $count = count($numbers);
        $middle = floor($count / 2);

        if ($count % 2 === 0) {
            return ($numbers[$middle - 1] + $numbers[$middle]) / 2;
        }

        return $numbers[$middle];
    }

    /**
     * Get agent average resolution time
     */
    private function getAgentAverageResolutionTime($agentId, $startDate, $endDate)
    {
        $avg = Ticket::where('assigned_to', $agentId)
            ->whereNotNull('resolved_at')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
            ->value('avg_hours');

        return $avg ? round($avg, 1) : 0;
    }

    /**
     * Get agent average response time
     */
    private function getAgentAverageResponseTime($agentId, $startDate, $endDate)
    {
        $avg = Ticket::where('assigned_to', $agentId)
            ->whereNotNull('first_response_at')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, first_response_at)) as avg_hours')
            ->value('avg_hours');

        return $avg ? round($avg, 1) : 0;
    }

    /**
     * Get category average resolution time
     */
    private function getCategoryAverageResolutionTime($categoryId, $startDate, $endDate)
    {
        $avg = Ticket::where('category_id', $categoryId)
            ->whereNotNull('resolved_at')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
            ->value('avg_hours');

        return $avg ? round($avg, 1) : 0;
    }

    /**
     * Export users data
     */
    public function exportUsers(Request $request)
    {
        $this->authorize('access_reports');
        
        $format = $request->get('format', 'csv');
        $users = User::with(['activities' => function($query) {
            $query->latest()->limit(1);
        }])->get();
        
        if ($format === 'excel') {
            return Excel::download(new UsersExport($users), 'users_export_' . date('Y-m-d') . '.xlsx');
        }
        
        return Excel::download(new UsersExport($users), 'users_export_' . date('Y-m-d') . '.csv');
    }

    /**
     * Export scraped tickets data
     */
    public function exportScrapedTickets(Request $request)
    {
        $this->authorize('access_reports');
        
        $format = $request->get('format', 'csv');
        $tickets = ScrapedTicket::with(['category', 'user'])->get();
        
        if ($format === 'excel') {
            return Excel::download(new ScrapedTicketsExport($tickets), 'scraped_tickets_export_' . date('Y-m-d') . '.xlsx');
        }
        
        return Excel::download(new ScrapedTicketsExport($tickets), 'scraped_tickets_export_' . date('Y-m-d') . '.csv');
    }

    /**
     * Export audit trail data
     */
    public function exportAuditTrail(Request $request)
    {
        $this->authorize('access_reports');
        
        $format = $request->get('format', 'csv');
        $activities = Activity::with(['causer', 'subject'])
            ->latest()
            ->limit(10000)
            ->get();
        
        if ($format === 'excel') {
            return Excel::download(new AuditTrailExport($activities), 'audit_trail_export_' . date('Y-m-d') . '.xlsx');
        }
        
        return Excel::download(new AuditTrailExport($activities), 'audit_trail_export_' . date('Y-m-d') . '.csv');
    }

    /**
     * Import users from file
     */
    public function importUsers(Request $request)
    {
        $this->authorize('manage_users');
        
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240'
        ]);
        
        try {
            $import = new UsersImport();
            Excel::import($import, $request->file('file'));
            
            return redirect()->back()->with('success', 'Users imported successfully. ' . $import->getRowCount() . ' users processed.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate Users PDF Report
     */
    public function generateUsersPDF(Request $request)
    {
        $this->authorize('access_reports');
        
        $users = User::with(['activities' => function($query) {
            $query->latest()->limit(5);
        }])->get();
        
        $data = [
            'title' => 'Users Report',
            'date' => now()->format('F d, Y'),
            'users' => $users,
            'totalUsers' => $users->count(),
            'activeUsers' => $users->where('is_active', true)->count(),
            'verifiedUsers' => $users->whereNotNull('email_verified_at')->count()
        ];
        
        $pdf = Pdf::loadView('admin.reports.pdf.users', $data);
        return $pdf->download('users_report_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Generate Tickets PDF Report
     */
    public function generateTicketsPDF(Request $request)
    {
        $this->authorize('access_reports');
        
        $tickets = ScrapedTicket::with(['category', 'user'])
            ->latest()
            ->limit(1000)
            ->get();
        
        $data = [
            'title' => 'Scraped Tickets Report',
            'date' => now()->format('F d, Y'),
            'tickets' => $tickets,
            'totalTickets' => $tickets->count(),
            'platformStats' => $tickets->groupBy('platform')->map->count(),
            'statusStats' => $tickets->groupBy('status')->map->count()
        ];
        
        $pdf = Pdf::loadView('admin.reports.pdf.tickets', $data);
        return $pdf->download('tickets_report_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Generate Audit Trail PDF Report
     */
    public function generateAuditPDF(Request $request)
    {
        $this->authorize('access_reports');
        
        $activities = Activity::with(['causer', 'subject'])
            ->latest()
            ->limit(500)
            ->get();
        
        $data = [
            'title' => 'Audit Trail Report',
            'date' => now()->format('F d, Y'),
            'activities' => $activities,
            'totalActivities' => $activities->count(),
            'userActivities' => $activities->groupBy('causer.name')->map->count(),
            'eventTypes' => $activities->groupBy('event')->map->count()
        ];
        
        $pdf = Pdf::loadView('admin.reports.pdf.audit', $data);
        return $pdf->download('audit_trail_report_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export methods (placeholder implementations)
     */
    private function exportTickets($request, $format)
    {
        // Implementation for exporting tickets
        return response()->json(['message' => 'Export functionality to be implemented']);
    }

    private function exportAgentPerformance($request, $format)
    {
        // Implementation for exporting agent performance
        return response()->json(['message' => 'Export functionality to be implemented']);
    }

    private function exportCategoryAnalysis($request, $format)
    {
        // Implementation for exporting category analysis
        return response()->json(['message' => 'Export functionality to be implemented']);
    }

    private function exportResponseTime($request, $format)
    {
        // Implementation for exporting response time data
        return response()->json(['message' => 'Export functionality to be implemented']);
    }
}
