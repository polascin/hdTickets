<?php

namespace App\Http\Middleware;

use App\Services\SecurityService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ActivityLoggerMiddleware
{
    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Handle an incoming request and log user activity
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated users
        if (Auth::check()) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    /**
     * Log user activity based on request
     */
    private function logActivity(Request $request, Response $response): void
    {
        $user = Auth::user();
        $method = $request->method();
        $route = $request->route();
        $routeName = $route ? $route->getName() : 'unknown';
        $statusCode = $response->getStatusCode();

        // Skip logging for certain routes
        if ($this->shouldSkipLogging($routeName, $request)) {
            return;
        }

        // Determine activity type and description
        $activityData = $this->determineActivity($method, $routeName, $request, $statusCode);

        if ($activityData) {
            $this->securityService->logUserActivity(
                $activityData['action'],
                array_merge($activityData['context'], [
                    'route' => $routeName,
                    'method' => $method,
                    'status_code' => $statusCode,
                    'parameters' => $this->getRelevantParameters($request),
                ])
            );
        }
    }

    /**
     * Determine if we should skip logging for this request
     */
    private function shouldSkipLogging(string $routeName, Request $request): bool
    {
        $skipRoutes = [
            'ajax.dashboard.stats',
            'admin.activities.recent',
            'admin.stats.json',
            'admin.chart.*',
        ];

        // Skip AJAX polling routes
        if ($request->ajax() && in_array($routeName, $skipRoutes)) {
            return true;
        }

        // Skip asset requests
        if ($request->is('css/*', 'js/*', 'images/*', 'fonts/*')) {
            return true;
        }

        return false;
    }

    /**
     * Determine activity type and context based on the request
     */
    private function determineActivity(string $method, string $routeName, Request $request, int $statusCode): ?array
    {
        // Map routes to activities
        $routeActions = [
            // User Management
            'admin.users.index' => ['action' => 'view_users', 'context' => []],
            'admin.users.show' => ['action' => 'view_user_details', 'context' => ['user_id' => $request->route('user')]],
            'admin.users.create' => ['action' => 'view_create_user_form', 'context' => []],
            'admin.users.store' => ['action' => 'create_user', 'context' => ['email' => $request->input('email')]],
            'admin.users.edit' => ['action' => 'view_edit_user_form', 'context' => ['user_id' => $request->route('user')]],
            'admin.users.update' => ['action' => 'update_user', 'context' => ['user_id' => $request->route('user')]],
            'admin.users.destroy' => ['action' => 'delete_user', 'context' => ['user_id' => $request->route('user')]],
            'admin.users.bulk-action' => ['action' => 'bulk_user_action', 'context' => ['bulk_action' => $request->input('action')]],
            'admin.users.toggle-status' => ['action' => 'toggle_user_status', 'context' => ['user_id' => $request->route('user')]],
            'admin.users.reset-password' => ['action' => 'reset_user_password', 'context' => ['user_id' => $request->route('user')]],
            'admin.users.impersonate' => ['action' => 'impersonate_user', 'context' => ['user_id' => $request->route('user')]],
            'admin.users.stop-impersonating' => ['action' => 'stop_impersonating', 'context' => []],

            // System Management
            'admin.system.index' => ['action' => 'view_system_dashboard', 'context' => []],
            'admin.system.configuration' => ['action' => 'view_system_configuration', 'context' => []],
            'admin.system.configuration.update' => ['action' => 'update_system_configuration', 'context' => []],
            'admin.system.cache.clear' => ['action' => 'clear_system_cache', 'context' => []],
            'admin.system.maintenance' => ['action' => 'run_system_maintenance', 'context' => []],

            // Scraping Management
            'admin.scraping.index' => ['action' => 'view_scraping_dashboard', 'context' => []],
            'admin.scraping.configuration' => ['action' => 'view_scraping_configuration', 'context' => []],
            'admin.scraping.configuration.update' => ['action' => 'update_scraping_configuration', 'context' => []],

            // Ticket Management
            'tickets.scraping.index' => ['action' => 'view_tickets', 'context' => []],
            'tickets.scraping.show' => ['action' => 'view_ticket_details', 'context' => ['ticket_id' => $request->route('ticket')]],
            'tickets.scraping.purchase' => ['action' => 'purchase_ticket', 'context' => ['ticket_id' => $request->route('ticket')]],

            // Purchase Decisions
            'purchase-decisions.index' => ['action' => 'view_purchase_decisions', 'context' => []],
            'purchase-decisions.add-to-queue' => ['action' => 'add_to_purchase_queue', 'context' => ['ticket_id' => $request->route('scrapedTicket')]],
            'purchase-decisions.process' => ['action' => 'process_purchase_queue', 'context' => ['queue_id' => $request->route('purchaseQueue')]],
            'purchase-decisions.bulk-action' => ['action' => 'bulk_purchase_action', 'context' => ['bulk_action' => $request->input('action')]],

            // Reports
            'admin.reports.index' => ['action' => 'view_reports', 'context' => []],
            'admin.reports.users.export' => ['action' => 'export_users_report', 'context' => []],
            'admin.reports.tickets.export' => ['action' => 'export_tickets_report', 'context' => []],
            'admin.reports.audit.export' => ['action' => 'export_audit_report', 'context' => []],

            // Authentication
            'login' => ['action' => 'view_login_form', 'context' => []],
            'register' => ['action' => 'view_register_form', 'context' => []],
            'dashboard' => ['action' => 'view_dashboard', 'context' => []],
            'profile.edit' => ['action' => 'view_profile', 'context' => []],
            'profile.update' => ['action' => 'update_profile', 'context' => []],
        ];

        // Check if we have a specific mapping for this route
        if (isset($routeActions[$routeName])) {
            $action = $routeActions[$routeName];
            
            // Add success/failure context based on status code
            $action['context']['success'] = $statusCode < 400;
            if ($statusCode >= 400) {
                $action['context']['error_code'] = $statusCode;
            }
            
            return $action;
        }

        // Generic activity logging for unmapped routes
        if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH' || $method === 'DELETE') {
            return [
                'action' => strtolower($method) . '_request',
                'context' => [
                    'route' => $routeName,
                    'success' => $statusCode < 400,
                ]
            ];
        }

        return null;
    }

    /**
     * Get relevant parameters from request (excluding sensitive data)
     */
    private function getRelevantParameters(Request $request): array
    {
        $parameters = $request->all();
        
        // Remove sensitive parameters
        $sensitiveKeys = [
            'password',
            'password_confirmation',
            'current_password',
            'token',
            '_token',
            'bulk_token',
            'api_key',
            'secret',
        ];

        foreach ($sensitiveKeys as $key) {
            unset($parameters[$key]);
        }

        // Limit parameter size to prevent log bloat
        $parameters = array_slice($parameters, 0, 10);
        
        return $parameters;
    }
}
