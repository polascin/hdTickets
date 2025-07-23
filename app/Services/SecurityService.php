<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Facades\LogActivity;
use Spatie\Activitylog\Models\Activity;

class SecurityService
{
    /**
     * Log security-related activities with enhanced context
     */
    public function logSecurityActivity(string $description, array $properties = [], $subject = null): void
    {
        $user = Auth::user();
        $request = request();
        
        $enhancedProperties = array_merge([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
            'session_id' => session()->getId(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => $user?->id,
            'user_role' => $user?->role,
            'risk_level' => $this->calculateRiskLevel($description, $properties),
        ], $properties);

        activity('security')
            ->causedBy($user)
            ->performedOn($subject)
            ->withProperties($enhancedProperties)
            ->log($description);
    }

    /**
     * Log user actions with context
     */
    public function logUserActivity(string $action, array $context = [], $subject = null): void
    {
        $user = Auth::user();
        $request = request();
        
        $properties = array_merge([
            'action' => $action,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
            'session_id' => session()->getId(),
            'user_role' => $user?->role,
        ], $context);

        activity('user_actions')
            ->causedBy($user)
            ->performedOn($subject)
            ->withProperties($properties)
            ->log("User performed action: {$action}");
    }

    /**
     * Log bulk operations with detailed tracking
     */
    public function logBulkOperation(string $operation, array $items, array $results = []): void
    {
        $user = Auth::user();
        
        $properties = [
            'operation' => $operation,
            'item_count' => count($items),
            'item_ids' => array_slice($items, 0, 100), // Limit to prevent too large logs
            'success_count' => $results['success'] ?? 0,
            'failure_count' => $results['failure'] ?? 0,
            'errors' => $results['errors'] ?? [],
            'execution_time' => $results['execution_time'] ?? null,
            'user_role' => $user?->role,
        ];

        activity('bulk_operations')
            ->causedBy($user)
            ->withProperties($properties)
            ->log("Bulk operation performed: {$operation} on " . count($items) . " items");
    }

    /**
     * Log authentication events
     */
    public function logAuthEvent(string $event, array $context = []): void
    {
        $request = request();
        
        $properties = array_merge([
            'event' => $event,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
            'session_id' => session()->getId(),
        ], $context);

        activity('authentication')
            ->withProperties($properties)
            ->log("Authentication event: {$event}");
    }

    /**
     * Check if user has permission for action with logging
     */
    public function checkPermission(User $user, string $permission, array $context = []): bool
    {
        $hasPermission = match($permission) {
            'manage_users' => $user->canManageUsers(),
            'manage_system' => $user->canManageSystem(),
            'manage_platforms' => $user->canManagePlatforms(),
            'access_financials' => $user->canAccessFinancials(),
            'delete_any_data' => $user->canDeleteAnyData(),
            'select_purchase_tickets' => $user->canSelectAndPurchaseTickets(),
            'make_purchase_decisions' => $user->canMakePurchaseDecisions(),
            'manage_monitoring' => $user->canManageMonitoring(),
            'view_scraping_metrics' => $user->canViewScrapingMetrics(),
            'access_system' => $user->canAccessSystem(),
            'bulk_operations' => $this->canPerformBulkOperations($user),
            default => false
        };

        if (!$hasPermission) {
            $this->logSecurityActivity(
                'Permission denied',
                array_merge([
                    'permission' => $permission,
                    'user_role' => $user->role,
                    'attempted_action' => $context['action'] ?? 'unknown',
                ], $context)
            );
        }

        return $hasPermission;
    }

    /**
     * Check if user can perform bulk operations
     */
    public function canPerformBulkOperations(User $user): bool
    {
        return $user->isAdmin() || $user->isAgent();
    }

    /**
     * Validate bulk operation security
     */
    public function validateBulkOperation(array $items, string $operation, User $user): array
    {
        $validation = [
            'valid' => true,
            'errors' => [],
            'warnings' => [],
        ];

        // Check item count limits
        $maxItems = $this->getMaxBulkItems($user, $operation);
        if (count($items) > $maxItems) {
            $validation['valid'] = false;
            $validation['errors'][] = "Bulk operation exceeds maximum limit of {$maxItems} items";
        }

        // Check for destructive operations
        if (in_array($operation, ['delete', 'disable', 'remove'])) {
            if (!$user->canDeleteAnyData() && count($items) > 10) {
                $validation['valid'] = false;
                $validation['errors'][] = "Destructive bulk operations limited to 10 items for non-root users";
            }
        }

        // Rate limiting check
        if ($this->isBulkOperationRateLimited($user)) {
            $validation['valid'] = false;
            $validation['errors'][] = "Bulk operation rate limit exceeded. Please wait before trying again.";
        }

        return $validation;
    }

    /**
     * Generate secure CSRF token for bulk operations
     */
    public function generateBulkOperationToken(string $operation, array $items): string
    {
        $payload = [
            'operation' => $operation,
            'item_count' => count($items),
            'user_id' => Auth::id(),
            'timestamp' => now()->timestamp,
            'nonce' => bin2hex(random_bytes(16)),
        ];

        return Hash::make(json_encode($payload));
    }

    /**
     * Validate bulk operation token
     */
    public function validateBulkOperationToken(string $token, string $operation, array $items): bool
    {
        $payload = [
            'operation' => $operation,
            'item_count' => count($items),
            'user_id' => Auth::id(),
            'timestamp' => now()->timestamp,
        ];

        // Allow 5 minute window for token validity
        for ($i = 0; $i < 5; $i++) {
            $testPayload = $payload;
            $testPayload['timestamp'] -= ($i * 60);
            
            if (Hash::check(json_encode($testPayload), $token)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get recent security activities
     */
    public function getRecentSecurityActivities(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return Activity::where('log_name', 'security')
            ->with('causer')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user activity summary
     */
    public function getUserActivitySummary(User $user, int $days = 30): array
    {
        $activities = Activity::where('causer_id', $user->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        return [
            'total_activities' => $activities->count(),
            'security_events' => $activities->where('log_name', 'security')->count(),
            'user_actions' => $activities->where('log_name', 'user_actions')->count(),
            'bulk_operations' => $activities->where('log_name', 'bulk_operations')->count(),
            'authentication_events' => $activities->where('log_name', 'authentication')->count(),
            'recent_activities' => $activities->sortByDesc('created_at')->take(10)->values(),
        ];
    }

    /**
     * Calculate risk level based on activity
     */
    private function calculateRiskLevel(string $description, array $properties): string
    {
        $highRiskKeywords = ['delete', 'disable', 'permission', 'admin', 'bulk', 'failed'];
        $mediumRiskKeywords = ['update', 'modify', 'change', 'access'];

        $description = strtolower($description);
        
        foreach ($highRiskKeywords as $keyword) {
            if (str_contains($description, $keyword)) {
                return 'high';
            }
        }

        foreach ($mediumRiskKeywords as $keyword) {
            if (str_contains($description, $keyword)) {
                return 'medium';
            }
        }

        return 'low';
    }

    /**
     * Get maximum bulk items based on user role and operation
     */
    private function getMaxBulkItems(User $user, string $operation): int
    {
        if ($user->isRootAdmin()) {
            return 1000;
        }

        if ($user->isAdmin()) {
            return in_array($operation, ['delete', 'disable']) ? 100 : 500;
        }

        if ($user->isAgent()) {
            return in_array($operation, ['delete', 'disable']) ? 10 : 100;
        }

        return 10;
    }

    /**
     * Check if user is rate limited for bulk operations
     */
    private function isBulkOperationRateLimited(User $user): bool
    {
        $recentBulkOps = Activity::where('causer_id', $user->id)
            ->where('log_name', 'bulk_operations')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count();

        $limit = $user->isAdmin() ? 10 : 3;
        
        return $recentBulkOps >= $limit;
    }
}
