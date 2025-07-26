<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use App\Services\ActivityLogger;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        if (!Auth::check()) {
            $this->logSecurityEvent('admin_access_attempt_unauthenticated', 'Unauthenticated access attempt to admin area', [
                'route' => $request->route() ? $request->route()->getName() : 'unknown',
                'url' => $request->fullUrl(),
                'method' => $request->method()
            ]);
            return redirect()->route('login')->with('error', 'Please login to access this area.');
        }

        $user = Auth::user();

        // Check if user is admin
        if (!$user->isAdmin()) {
            $this->logSecurityEvent('admin_access_denied_insufficient_privileges', 'Non-admin user attempted to access admin area', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'route' => $request->route() ? $request->route()->getName() : 'unknown',
                'url' => $request->fullUrl(),
                'method' => $request->method()
            ]);
            abort(403, 'Access denied. Admin privileges required.');
        }

        // Check if user is active
        if (!$user->is_active) {
            $this->logSecurityEvent('admin_access_denied_inactive_account', 'Inactive admin account attempted access', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'route' => $request->route() ? $request->route()->getName() : 'unknown',
                'url' => $request->fullUrl()
            ]);
            Auth::logout();
            return redirect()->route('login')->with('error', 'Account is disabled. Contact administrator.');
        }

        // Check specific permission if provided
        if ($permission) {
            $permissions = $user->getPermissions();
            
            if (!isset($permissions[$permission]) || !$permissions[$permission]) {
                $this->logSecurityEvent('admin_access_denied_missing_permission', 'Admin user missing required permission', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'required_permission' => $permission,
                    'route' => $request->route() ? $request->route()->getName() : 'unknown',
                    'url' => $request->fullUrl()
                ]);
                abort(403, "Access denied. Missing permission: {$permission}");
            }
        }

        // Log successful admin access
        $this->logAdminActivity('admin_access_granted', 'Admin user accessed admin area', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'permission' => $permission,
            'route' => $request->route() ? $request->route()->getName() : 'unknown',
            'url' => $request->fullUrl(),
            'method' => $request->method()
        ]);

        return $next($request);
    }

    /**
     * Log admin activity with graceful error handling
     *
     * @param string $action
     * @param string $description
     * @param array $context
     * @return void
     */
    private function logAdminActivity(string $action, string $description, array $context = []): void
    {
        try {
            // Check if ActivityLogger service is available
            if (App::bound(ActivityLogger::class)) {
                $activityLogger = App::make(ActivityLogger::class);
                $activityLogger->logAdminActivity($action, $description, $context);
            } else {
                // Fallback to Laravel's built-in logging
                $this->fallbackLog('admin', $action, $description, $context);
            }
        } catch (\Exception $e) {
            // Fallback logging if ActivityLogger fails
            $this->fallbackLog('admin', $action, $description, $context, $e->getMessage());
        }
    }

    /**
     * Log security events with graceful error handling
     *
     * @param string $action
     * @param string $description
     * @param array $context
     * @return void
     */
    private function logSecurityEvent(string $action, string $description, array $context = []): void
    {
        try {
            // Check if ActivityLogger service is available
            if (App::bound(ActivityLogger::class)) {
                $activityLogger = App::make(ActivityLogger::class);
                $activityLogger->logSecurityActivity($action, $description, $context);
            } else {
                // Fallback to Laravel's built-in logging
                $this->fallbackLog('security', $action, $description, $context);
            }
        } catch (\Exception $e) {
            // Fallback logging if ActivityLogger fails
            $this->fallbackLog('security', $action, $description, $context, $e->getMessage());
        }
    }

    /**
     * Fallback logging mechanism using Laravel's built-in Log facade
     *
     * @param string $type
     * @param string $action
     * @param string $description
     * @param array $context
     * @param string|null $error
     * @return void
     */
    private function fallbackLog(string $type, string $action, string $description, array $context = [], ?string $error = null): void
    {
        try {
            $user = Auth::user();
            
            $logData = [
                'timestamp' => now()->toDateTimeString(),
                'type' => $type,
                'user_id' => $user ? $user->id : null,
                'user_email' => $user ? $user->email : 'unknown',
                'action' => $action,
                'description' => $description,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'context' => $context,
            ];

            if ($error) {
                $logData['activity_logger_error'] = $error;
                Log::warning('[AdminMiddleware] ActivityLogger failed, using fallback: ' . $description, $logData);
            } else {
                $logData['fallback_reason'] = 'ActivityLogger service not available';
                
                if ($type === 'security') {
                    Log::warning('[AdminMiddleware] Security Event: ' . $description, $logData);
                } else {
                    Log::info('[AdminMiddleware] Admin Activity: ' . $description, $logData);
                }
            }
        } catch (\Exception $e) {
            // Last resort: simple log entry
            Log::error('[AdminMiddleware] Logging system failure - Action: ' . $action . ', Error: ' . $e->getMessage());
        }
    }
}
