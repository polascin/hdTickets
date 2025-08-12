<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\TwoFactorAuthService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

use function count;
use function in_array;

class RequireTwoFactorMiddleware
{
    protected $twoFactorService;

    public function __construct(TwoFactorAuthService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, string $action = 'default'): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Check if 2FA is required for this user/action
        $requires2FA = $this->requiresTwoFactor($user, $action);

        if ($requires2FA && ! $this->twoFactorService->isEnabled($user)) {
            // Redirect to 2FA setup if required but not enabled
            Session::put('2fa_required_for', $action);
            Session::put('2fa_redirect_url', $request->fullUrl());

            activity('two_factor_setup_required')
                ->performedOn($user)
                ->causedBy($user)
                ->withProperties([
                    'action'     => $action,
                    'url'        => $request->fullUrl(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])
                ->log('Two-factor authentication setup required for sensitive action');

            return redirect()->route('2fa.setup')
                ->with('warning', 'Two-factor authentication is required for this action. Please set it up first.');
        }

        if ($requires2FA && $this->twoFactorService->isEnabled($user)) {
            // Check if 2FA was recently verified for this session
            $sessionKey = "2fa_verified_for_{$action}";
            $lastVerified = Session::get($sessionKey);

            $verificationTimeout = config('security.two_factor.recovery_window', 300); // 5 minutes default

            if (! $lastVerified || now()->diffInSeconds($lastVerified) > $verificationTimeout) {
                // Store the original request for after 2FA verification
                Session::put('2fa_pending_action', $action);
                Session::put('2fa_pending_url', $request->fullUrl());
                Session::put('2fa_pending_method', $request->method());
                Session::put('2fa_pending_data', $request->all());

                activity('two_factor_challenge_required')
                    ->performedOn($user)
                    ->causedBy($user)
                    ->withProperties([
                        'action'        => $action,
                        'url'           => $request->fullUrl(),
                        'ip_address'    => $request->ip(),
                        'user_agent'    => $request->userAgent(),
                        'last_verified' => $lastVerified,
                    ])
                    ->log('Two-factor authentication challenge required');

                if ($request->expectsJson()) {
                    return response()->json([
                        'error'        => 'Two-factor authentication required',
                        'redirect_url' => route('2fa.challenge'),
                        'action'       => $action,
                    ], 403);
                }

                return redirect()->route('2fa.challenge')
                    ->with('info', 'Please verify your identity with two-factor authentication to continue.');
            }
        }

        return $next($request);
    }

    /**
     * Mark 2FA as verified for a specific action in this session
     */
    public static function markTwoFactorVerified(string $action): void
    {
        $sessionKey = "2fa_verified_for_{$action}";
        Session::put($sessionKey, now());

        activity('two_factor_verification_recorded')
            ->causedBy(Auth::user())
            ->withProperties([
                'action'     => $action,
                'session_id' => Session::getId(),
                'ip_address' => request()->ip(),
            ])
            ->log('Two-factor authentication verification recorded for action');
    }

    /**
     * Clear all 2FA verifications from session (e.g., on logout)
     */
    public static function clearTwoFactorVerifications(): void
    {
        $sessionKeys = collect(Session::all())
            ->keys()
            ->filter(fn ($key) => str_starts_with($key, '2fa_verified_for_'))
            ->toArray();

        foreach ($sessionKeys as $key) {
            Session::forget($key);
        }

        activity('two_factor_verifications_cleared')
            ->causedBy(Auth::user())
            ->withProperties([
                'cleared_actions' => count($sessionKeys),
                'session_id'      => Session::getId(),
            ])
            ->log('All two-factor authentication verifications cleared from session');
    }

    /**
     * Determine if 2FA is required for the given user and action
     *
     * @param mixed $user
     */
    protected function requiresTwoFactor($user, string $action): bool
    {
        $securityConfig = config('security.two_factor', []);

        // Always require 2FA for admins if configured
        if ($user->isAdmin() && $securityConfig['required_for_admin'] ?? FALSE) {
            return TRUE;
        }

        // Check action-specific requirements
        switch ($action) {
            case 'purchase':
                return $securityConfig['required_for_purchase'] ?? TRUE;
            case 'admin_actions':
                return $user->isAdmin();
            case 'user_management':
                return $user->canManageUsers();
            case 'system_management':
                return $user->canManageSystem();
            case 'financial_access':
                return $user->canAccessFinancials();
            case 'bulk_operations':
                return TRUE; // Always require 2FA for bulk operations
            case 'password_reset':
                return TRUE;
            case 'account_deletion':
                return TRUE;
            case 'api_key_generation':
                return TRUE;
            case 'sensitive_data_export':
                return TRUE;
            default:
                // Check if user has 2FA enabled and enforce for general sensitive actions
                return $this->twoFactorService->isEnabled($user) && $this->isSensitiveAction($action);
        }
    }

    /**
     * Check if an action is considered sensitive
     */
    protected function isSensitiveAction(string $action): bool
    {
        $sensitiveActions = [
            'delete',
            'destroy',
            'disable',
            'update_critical',
            'export',
            'import',
            'configure',
            'reset',
        ];

        return in_array($action, $sensitiveActions, TRUE);
    }
}
