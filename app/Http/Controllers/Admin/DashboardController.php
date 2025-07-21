<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Category;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index()
    {
        // Ticket statistics
        $totalTickets = Ticket::count();
        $openTickets = Ticket::open()->count();
        $closedTickets = Ticket::closed()->count();
        $highPriorityTickets = Ticket::highPriority()->count();
        $overdueTickets = Ticket::overdue()->count();

        // User statistics
        $totalUsers = User::count();
        $totalAgents = User::where('role', User::ROLE_AGENT)->count();
        $totalCustomers = User::where('role', User::ROLE_CUSTOMER)->count();

        // Category statistics
        $totalCategories = Category::active()->count();

        // Recent activity
        $recentTickets = Ticket::with(['user', 'category', 'assignedTo'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Ticket statistics by status for chart
        $ticketsByStatus = Ticket::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Ticket statistics by priority for chart
        $ticketsByPriority = Ticket::select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority');

        // Monthly ticket creation trend (last 12 months)
        $monthlyTicketTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = Ticket::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $monthlyTicketTrend[$date->format('M Y')] = $count;
        }

        // Average response time (in hours)
        $averageResponseTime = Ticket::whereNotNull('first_response_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, first_response_at)) as avg_response')
            ->value('avg_response');

        // Average resolution time (in hours)
        $averageResolutionTime = Ticket::whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_resolution')
            ->value('avg_resolution');

        // Top performing agents
        $topAgents = User::where('role', User::ROLE_AGENT)
            ->withCount(['assignedTickets as resolved_tickets' => function ($query) {
                $query->where('status', Ticket::STATUS_RESOLVED);
            }])
            ->orderBy('resolved_tickets', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.admin', compact(
            'totalTickets',
            'openTickets', 
            'closedTickets',
            'highPriorityTickets',
            'overdueTickets',
            'totalUsers',
            'totalAgents',
            'totalCustomers',
            'totalCategories',
            'recentTickets',
            'ticketsByStatus',
            'ticketsByPriority',
            'monthlyTicketTrend',
            'averageResponseTime',
            'averageResolutionTime',
            'topAgents'
        ));
    }

    /**
     * Get dashboard statistics as JSON (for AJAX updates)
     */
    public function getStats()
    {
        $stats = [
            'tickets' => [
                'total' => Ticket::count(),
                'open' => Ticket::open()->count(),
                'closed' => Ticket::closed()->count(),
                'high_priority' => Ticket::highPriority()->count(),
                'overdue' => Ticket::overdue()->count(),
            ],
            'users' => [
                'total' => User::count(),
                'agents' => User::where('role', User::ROLE_AGENT)->count(),
                'customers' => User::where('role', User::ROLE_CUSTOMER)->count(),
            ],
            'categories' => [
                'total' => Category::active()->count(),
            ]
        ];

        return response()->json($stats);
    }

    /**
     * Get chart data for tickets by status
     */
    public function getTicketStatusChart()
    {
        $data = Ticket::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => ucfirst(str_replace('_', ' ', $item->status)),
                    'value' => $item->count,
                    'color' => $this->getStatusColor($item->status)
                ];
            });

        return response()->json($data);
    }

    /**
     * Get chart data for tickets by priority
     */
    public function getTicketPriorityChart()
    {
        $data = Ticket::select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => ucfirst($item->priority),
                    'value' => $item->count,
                    'color' => $this->getPriorityColor($item->priority)
                ];
            });

        return response()->json($data);
    }

    /**
     * Get monthly trend data
     */
    public function getMonthlyTrend()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = Ticket::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $data[] = [
                'month' => $date->format('M Y'),
                'tickets' => $count
            ];
        }

        return response()->json($data);
    }

    /**
     * Get status color for charts
     */
    private function getStatusColor($status)
    {
        return match($status) {
            Ticket::STATUS_OPEN => '#3b82f6',
            Ticket::STATUS_IN_PROGRESS => '#f59e0b',
            Ticket::STATUS_PENDING => '#f97316',
            Ticket::STATUS_RESOLVED => '#10b981',
            Ticket::STATUS_CLOSED => '#6b7280',
            Ticket::STATUS_CANCELLED => '#ef4444',
            default => '#6b7280',
        };
    }

    /**
     * Get priority color for charts
     */
    private function getPriorityColor($priority)
    {
        return match($priority) {
            Ticket::PRIORITY_CRITICAL => '#dc2626',
            Ticket::PRIORITY_URGENT => '#f97316',
            Ticket::PRIORITY_HIGH => '#f59e0b',
            Ticket::PRIORITY_MEDIUM => '#3b82f6',
            Ticket::PRIORITY_LOW => '#6b7280',
            default => '#6b7280',
        };
    }
}
