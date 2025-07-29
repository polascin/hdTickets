<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SecurityService;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Display activity logs dashboard
     */
    public function index(Request $request)
    {
        // Check permissions
        if (!auth()->user()->canManageSystem()) {
            abort(403, 'You do not have permission to view activity logs.');
        }

        $this->securityService->logUserActivity('view_activity_logs');

        // Get filter parameters
        $logName = $request->get('log_name', 'all');
        $userId = $request->get('user_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $riskLevel = $request->get('risk_level');
        $perPage = $request->get('per_page', 25);

        // Build query
        $query = Activity::with(['causer:id,name,surname,email', 'subject'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($logName && $logName !== 'all') {
            $query->where('log_name', $logName);
        }

        if ($userId) {
            $query->where('causer_id', $userId);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($riskLevel) {
            $query->whereJsonContains('properties->risk_level', $riskLevel);
        }

        $activities = $query->paginate($perPage)->appends($request->query());

        // Get filter options
        $logNames = Activity::distinct()->pluck('log_name');
        $users = User::where('is_active', true)->select('id', 'name', 'surname', 'email')->get();

        // Get summary statistics
        $stats = $this->getActivityStats();

        return view('admin.activity-logs.index', compact(
            'activities', 'logNames', 'users', 'stats', 
            'logName', 'userId', 'startDate', 'endDate', 'riskLevel'
        ));
    }

    /**
     * Show detailed activity log entry
     */
    public function show(Activity $activity)
    {
        if (!auth()->user()->canManageSystem()) {
            abort(403, 'You do not have permission to view activity log details.');
        }

        $this->securityService->logUserActivity('view_activity_log_details', [
            'activity_id' => $activity->id
        ]);

        return view('admin.activity-logs.show', compact('activity'));
    }

    /**
     * Get security activities for dashboard
     */
    public function getSecurityActivities(Request $request)
    {
        if (!auth()->user()->canManageSystem()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $limit = $request->get('limit', 10);
        $activities = $this->securityService->getRecentSecurityActivities($limit);

        return response()->json([
            'activities' => $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'causer' => $activity->causer ? $activity->causer->name : 'System',
                    'properties' => $activity->properties,
                    'created_at' => $activity->created_at->format('M j, Y H:i:s'),
                    'created_at_human' => $activity->created_at->diffForHumans(),
                    'risk_level' => $activity->properties['risk_level'] ?? 'low',
                ];
            })
        ]);
    }

    /**
     * Get user activity summary
     */
    public function getUserActivitySummary(Request $request, User $user)
    {
        if (!auth()->user()->canManageUsers()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $days = $request->get('days', 30);
        $summary = $this->securityService->getUserActivitySummary($user, $days);

        return response()->json($summary);
    }

    /**
     * Generate bulk operation token
     */
    public function generateBulkToken(Request $request)
    {
        $request->validate([
            'operation' => 'required|string',
            'items' => 'required|array',
        ]);

        if (!$this->securityService->checkPermission(auth()->user(), 'bulk_operations')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $token = $this->securityService->generateBulkOperationToken(
            $request->input('operation'),
            $request->input('items')
        );

        return response()->json(['token' => $token]);
    }

    /**
     * Export activity logs
     */
    public function export(Request $request)
    {
        if (!auth()->user()->canManageSystem()) {
            abort(403, 'You do not have permission to export activity logs.');
        }

        $this->securityService->logUserActivity('export_activity_logs');

        $logName = $request->get('log_name');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Activity::with('causer:id,name,surname,email')
            ->orderBy('created_at', 'desc');

        if ($logName && $logName !== 'all') {
            $query->where('log_name', $logName);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $activities = $query->limit(10000)->get(); // Limit for performance

        $filename = 'activity_logs_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($activities) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'ID',
                'Log Name',
                'Description',
                'User',
                'User Email',
                'Subject Type',
                'Subject ID',
                'Properties',
                'Risk Level',
                'IP Address',
                'Created At'
            ]);

            // Add activity data
            foreach ($activities as $activity) {
                $properties = $activity->properties ?? [];
                
                fputcsv($file, [
                    $activity->id,
                    $activity->log_name,
                    $activity->description,
                    $activity->causer ? $activity->causer->name . ' ' . $activity->causer->surname : 'System',
                    $activity->causer ? $activity->causer->email : '',
                    $activity->subject_type,
                    $activity->subject_id,
                    json_encode($properties),
                    $properties['risk_level'] ?? 'unknown',
                    $properties['ip_address'] ?? 'unknown',
                    $activity->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Delete old activity logs
     */
    public function cleanup(Request $request)
    {
        if (!auth()->user()->isRootAdmin()) {
            abort(403, 'Only root admin can perform log cleanup.');
        }

        $request->validate([
            'older_than_days' => 'required|integer|min:30|max:365'
        ]);

        $days = $request->input('older_than_days');
        $cutoffDate = now()->subDays($days);

        $deletedCount = Activity::where('created_at', '<', $cutoffDate)->delete();

        $this->securityService->logSecurityActivity('Activity log cleanup performed', [
            'deleted_count' => $deletedCount,
            'older_than_days' => $days,
            'cutoff_date' => $cutoffDate->toDateString()
        ]);

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$deletedCount} old activity log entries.",
            'deleted_count' => $deletedCount
        ]);
    }

    /**
     * Get activity statistics
     */
    private function getActivityStats()
    {
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        return [
            'total_activities' => Activity::count(),
            'today' => Activity::where('created_at', '>=', $today)->count(),
            'this_week' => Activity::where('created_at', '>=', $thisWeek)->count(),
            'this_month' => Activity::where('created_at', '>=', $thisMonth)->count(),
            'security_events' => Activity::where('log_name', 'security')->count(),
            'user_actions' => Activity::where('log_name', 'user_actions')->count(),
            'bulk_operations' => Activity::where('log_name', 'bulk_operations')->count(),
            'high_risk_events' => Activity::whereJsonContains('properties->risk_level', 'high')->count(),
            'by_log_name' => Activity::selectRaw('log_name, COUNT(*) as count')
                ->groupBy('log_name')
                ->pluck('count', 'log_name')
                ->toArray(),
            'recent_high_risk' => Activity::whereJsonContains('properties->risk_level', 'high')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
        ];
    }
}
