<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Purchase\Models\TicketPurchase;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AdvancedRBACService;
use App\Services\SecurityMonitoringService;
use App\Services\TicketPurchaseService;
use Closure;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;

use function count;

/**
 * TicketPurchaseValidationMiddleware
 *
 * Comprehensive middleware for validating ticket purchase requests including:
 * - User authentication and role verification
 * - Subscription status and limits enforcement
 * - Security threat detection
 * - Purchase eligibility validation
 * - Rate limiting and abuse prevention
 * - Audit logging for compliance
 */
class TicketPurchaseValidationMiddleware
{
    protected ?TicketPurchaseService $ticketPurchaseService = NULL;
    protected SecurityMonitoringService $securityMonitoring;
    protected AdvancedRBACService $rbacService;

    public function __construct($serviceOrSecurityMonitoring = NULL, ?AdvancedRBACService $rbacService = NULL)
    {
        if ($serviceOrSecurityMonitoring instanceof SecurityMonitoringService) {
            $this->securityMonitoring = $serviceOrSecurityMonitoring;
        } elseif ($serviceOrSecurityMonitoring instanceof TicketPurchaseService) {
            // Backward compatibility: tests may pass TicketPurchaseService directly
            $this->ticketPurchaseService = $serviceOrSecurityMonitoring;
            $this->securityMonitoring = app(SecurityMonitoringService::class);
        } elseif ($serviceOrSecurityMonitoring === NULL) {
            $this->securityMonitoring = app(SecurityMonitoringService::class);
        } else {
            // Fallback: try to resolve expected services from container
            $this->securityMonitoring = app(SecurityMonitoringService::class);
        }

        $this->rbacService = $rbacService ?? app(AdvancedRBACService::class);
    }

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request):((RedirectResponse|Response)) $next
     *
     * @return RedirectResponse|Response
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Backward compatibility: if a TicketPurchaseService was injected, allow it to perform
            // preliminary eligibility checks and gracefully handle service exceptions.
            if ($this->ticketPurchaseService) {
                try {
                    if (is_callable([$this->ticketPurchaseService, 'checkPurchaseEligibility'])) {
                        // Let the service perform any additional checks; ignore return value.
                        $this->ticketPurchaseService->checkPurchaseEligibility($request);
                    }
                } catch (Exception $e) {
                    return response()->json([
                        'success'    => FALSE,
                        'message'    => 'Unable to validate purchase at this time. Please try again later.',
                        'error_code' => 'validation_service_error',
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }

            /** @var User $user */
            $user = $request->user();

            // Validate user authentication
            if (! $user) {
                return $this->denyAccess($request, 'Authentication required', 'unauthenticated');
            }

            // Get ticket being purchased
            $ticket = $this->getTicketFromRequest($request);
            if (! $ticket instanceof Ticket) {
                return $this->denyAccess($request, 'Invalid ticket', 'invalid_ticket', $user);
            }

            // Perform comprehensive purchase validation
            $validation = $this->validatePurchaseEligibility($user, $ticket, $request);

            if (! $validation['can_purchase']) {
                return $this->denyAccess(
                    $request,
                    $validation['message'],
                    'purchase_validation_failed',
                    $user,
                    $validation,
                );
            }

            // Log successful validation
            $this->securityMonitoring->logSecurityEvent(
                'ticket_purchase_validated',
                $user,
                $request,
                [
                    'ticket_id'         => $ticket->id,
                    'ticket_title'      => $ticket->title,
                    'validation_passed' => TRUE,
                ],
            );

            // Add validation data to request for controller use
            $request->merge(['purchase_validation' => $validation]);

            return $next($request);
        } catch (Exception $e) {
            Log::error('Ticket purchase validation middleware error', [
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
                'request_uri' => $request->getRequestUri(),
                'user_id'     => $request->user()?->id,
            ]);

            return response()->json([
                'success'    => FALSE,
                'message'    => 'Purchase validation failed due to system error',
                'error_code' => 'validation_system_error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Validate if user is eligible to purchase tickets
     */
    protected function validatePurchaseEligibility(User $user, Ticket $ticket, Request $request): array
    {
        $validation = [
            'can_purchase'   => FALSE,
            'message'        => '',
            'reasons'        => [],
            'user_info'      => [],
            'ticket_info'    => [],
            'security_score' => 0,
        ];

        // 1. Check user status
        if (! $this->validateUserStatus($user, $validation)) {
            return $validation;
        }

        // 2. Check subscription requirements
        if (! $this->validateSubscription($user, $validation)) {
            return $validation;
        }

        // 3. Check role-based permissions
        if (! $this->validateRolePermissions($user, $validation)) {
            return $validation;
        }

        // 4. Check ticket availability
        if (! $this->validateTicketAvailability($ticket, $request, $validation)) {
            return $validation;
        }

        // 5. Check purchase limits
        if (! $this->validatePurchaseLimits($user, $ticket, $request, $validation)) {
            return $validation;
        }

        // 6. Security checks
        if (! $this->validateSecurity($user, $request, $validation)) {
            return $validation;
        }

        // 7. Rate limiting
        if (! $this->validateRateLimit($user, $request, $validation)) {
            return $validation;
        }

        // All validations passed
        $validation['can_purchase'] = TRUE;
        $validation['message'] = 'Purchase validation successful';

        return $validation;
    }

    /**
     * Validate user account status
     */
    protected function validateUserStatus(User $user, array &$validation): bool
    {
        // Check if account is active
        if (! $user->active) {
            $validation['reasons'][] = 'Account is inactive';
            $validation['message'] = 'Your account is inactive. Please contact support.';

            return FALSE;
        }

        // Check if account is locked
        if ($user->locked_at && $user->locked_at > now()->subHours(24)) {
            $validation['reasons'][] = 'Account is temporarily locked';
            $validation['message'] = 'Your account is temporarily locked due to security concerns.';

            return FALSE;
        }

        // Check email verification
        if (! $user->hasVerifiedEmail()) {
            $validation['reasons'][] = 'Email not verified';
            $validation['message'] = 'Please verify your email address before making purchases.';

            return FALSE;
        }

        $validation['user_info']['account_status'] = 'active';

        return TRUE;
    }

    /**
     * Validate subscription status and limits
     */
    protected function validateSubscription(User $user, array &$validation): bool
    {
        // Agents and admins bypass subscription requirements
        if ($this->rbacService->hasAnyRole($user, ['agent', 'admin'])) {
            $validation['user_info']['subscription_bypass'] = TRUE;

            return TRUE;
        }

        // Scraper role cannot make purchases
        if ($this->rbacService->hasAnyRole($user, ['scraper'])) {
            $validation['reasons'][] = 'Scraper accounts cannot purchase tickets';
            $validation['message'] = 'This account type is not authorized for ticket purchases.';

            return FALSE;
        }

        // Check if customer is within free access period
        $freeAccessDays = config('subscription.free_access_days', 7);
        $withinFreeAccess = $user->created_at->diffInDays(now()) <= $freeAccessDays;

        if ($withinFreeAccess) {
            $validation['user_info']['free_access'] = TRUE;
            $validation['user_info']['free_access_expires'] = $user->created_at->addDays($freeAccessDays);

            return TRUE;
        }

        // Check active subscription
        if (! $user->hasActiveSubscription()) {
            $validation['reasons'][] = 'Active subscription required';
            $validation['message'] = 'An active subscription is required to purchase tickets.';

            return FALSE;
        }

        // Check monthly ticket limits
        $monthlyLimit = $user->getMonthlyTicketLimit();
        $monthlyUsage = $this->getMonthlyTicketUsage($user);

        $validation['user_info']['ticket_limit'] = $monthlyLimit;
        $validation['user_info']['monthly_usage'] = $monthlyUsage;
        $validation['user_info']['remaining_tickets'] = max(0, $monthlyLimit - $monthlyUsage);

        if ($monthlyUsage >= $monthlyLimit) {
            $validation['reasons'][] = 'Monthly ticket limit exceeded';
            $validation['message'] = 'You have reached your monthly ticket limit. Upgrade your subscription for more tickets.';

            return FALSE;
        }

        return TRUE;
    }

    /**
     * Validate role-based permissions
     */
    protected function validateRolePermissions(User $user, array &$validation): bool
    {
        if (! $this->rbacService->hasPermission($user, 'tickets.purchase')) {
            $validation['reasons'][] = 'Insufficient permissions';
            $validation['message'] = 'You do not have permission to purchase tickets.';

            return FALSE;
        }

        $validation['user_info']['permissions'] = 'valid';

        return TRUE;
    }

    /**
     * Validate ticket availability
     */
    protected function validateTicketAvailability(Ticket $ticket, Request $request, array &$validation): bool
    {
        $quantity = (int) $request->input('quantity', 1);

        // Check if ticket is available for purchase
        if (! $ticket->is_available) {
            $validation['reasons'][] = 'Ticket is not available';
            $validation['message'] = 'This ticket is no longer available for purchase.';

            return FALSE;
        }

        // Check quantity availability
        if ($ticket->available_quantity < $quantity) {
            $validation['reasons'][] = 'Not enough tickets available';
            $validation['message'] = "Only {$ticket->available_quantity} tickets available, but {$quantity} requested.";

            return FALSE;
        }

        // Check sale period
        if ($ticket->sale_starts_at && now() < $ticket->sale_starts_at) {
            $validation['reasons'][] = 'Sale has not started yet';
            $validation['message'] = 'Ticket sales have not started yet.';

            return FALSE;
        }

        if ($ticket->sale_ends_at && now() > $ticket->sale_ends_at) {
            $validation['reasons'][] = 'Sale period has ended';
            $validation['message'] = 'The sale period for this ticket has ended.';

            return FALSE;
        }

        $validation['ticket_info'] = [
            'id'                 => $ticket->id,
            'title'              => $ticket->title,
            'price'              => $ticket->price,
            'available_quantity' => $ticket->available_quantity,
            'requested_quantity' => $quantity,
        ];

        return TRUE;
    }

    /**
     * Validate purchase limits
     */
    protected function validatePurchaseLimits(User $user, Ticket $ticket, Request $request, array &$validation): bool
    {
        $quantity = (int) $request->input('quantity', 1);

        // Check maximum quantity per purchase
        $maxQuantity = config('tickets.max_quantity_per_purchase', 10);
        if ($quantity > $maxQuantity) {
            $validation['reasons'][] = 'Quantity exceeds maximum allowed';
            $validation['message'] = "Maximum {$maxQuantity} tickets allowed per purchase.";

            return FALSE;
        }

        // Check if user already purchased this ticket (if limited to one per user)
        if ($ticket->one_per_user) {
            $existingPurchase = TicketPurchase::where('user_id', $user->id)
                ->where('ticket_id', $ticket->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists();

            if ($existingPurchase) {
                $validation['reasons'][] = 'Already purchased this ticket';
                $validation['message'] = 'You have already purchased this ticket.';

                return FALSE;
            }
        }

        // Check daily purchase limits for customers
        if ($this->rbacService->hasAnyRole($user, ['customer'])) {
            $dailyLimit = config('tickets.daily_purchase_limit_customer', 50);
            $dailyPurchases = $this->getDailyPurchaseCount($user);

            if (($dailyPurchases + $quantity) > $dailyLimit) {
                $validation['reasons'][] = 'Daily purchase limit exceeded';
                $validation['message'] = "Daily purchase limit of {$dailyLimit} tickets would be exceeded.";

                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Validate security and fraud detection
     */
    protected function validateSecurity(User $user, Request $request, array &$validation): bool
    {
        // Check for suspicious activity
        $securityScore = $this->calculateSecurityScore($user, $request);
        $validation['security_score'] = $securityScore;

        if ($securityScore >= 70) {
            $validation['reasons'][] = 'Security verification required';
            $validation['message'] = 'Additional security verification is required for this purchase.';

            // Log high-risk purchase attempt
            $this->securityMonitoring->logSecurityEvent(
                'high_risk_purchase_attempt',
                $user,
                $request,
                [
                    'security_score' => $securityScore,
                    'risk_factors'   => $this->getSecurityRiskFactors($user, $request),
                ],
            );

            return FALSE;
        }

        return TRUE;
    }

    /**
     * Validate rate limiting
     */
    protected function validateRateLimit(User $user, Request $request, array &$validation): bool
    {
        $cacheKey = "purchase_attempts:{$user->id}";
        $maxAttempts = config('tickets.purchase_rate_limit', 5);
        $windowMinutes = config('tickets.purchase_rate_window', 15);

        $attempts = cache()->get($cacheKey, 0);

        if ($attempts >= $maxAttempts) {
            $validation['reasons'][] = 'Purchase rate limit exceeded';
            $validation['message'] = "Too many purchase attempts. Please wait {$windowMinutes} minutes and try again.";

            return FALSE;
        }

        // Increment attempt counter
        cache()->put($cacheKey, $attempts + 1, now()->addMinutes($windowMinutes));

        return TRUE;
    }

    /**
     * Get ticket from request parameters
     */
    protected function getTicketFromRequest(Request $request): ?Ticket
    {
        $ticketId = $request->route('ticket')?->id ?? $request->route('id') ?? $request->input('ticket_id');

        if (! $ticketId) {
            return NULL;
        }

        return Ticket::find($ticketId);
    }

    /**
     * Get monthly ticket usage for user
     */
    protected function getMonthlyTicketUsage(User $user): int
    {
        return TicketPurchase::where('user_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('status', ['confirmed', 'pending'])
            ->sum('quantity');
    }

    /**
     * Get daily purchase count for user
     */
    protected function getDailyPurchaseCount(User $user): int
    {
        return TicketPurchase::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->whereIn('status', ['confirmed', 'pending'])
            ->sum('quantity');
    }

    /**
     * Calculate security score for the purchase attempt
     */
    protected function calculateSecurityScore(User $user, Request $request): int
    {
        $score = 0;

        // Check IP reputation
        $ipScore = $this->getIpReputationScore($request->ip());
        $score += $ipScore;

        // Check user's recent security events
        $recentEvents = $this->getRecentSecurityEvents($user);
        $score += min(count($recentEvents) * 5, 30);

        // Check for unusual purchasing patterns
        $patternScore = $this->getUnusualPatternScore($user, $request);
        $score += $patternScore;

        // Check device trust
        if (! $this->isTrustedDevice($user, $request)) {
            $score += 10;
        }

        return min($score, 100);
    }

    /**
     * Get security risk factors for logging
     */
    protected function getSecurityRiskFactors(User $user, Request $request): array
    {
        return [
            'ip_reputation'          => $this->getIpReputationScore($request->ip()),
            'recent_security_events' => count($this->getRecentSecurityEvents($user)),
            'device_trusted'         => $this->isTrustedDevice($user, $request),
            'unusual_patterns'       => $this->getUnusualPatternScore($user, $request) > 0,
        ];
    }

    /**
     * Helper methods for security checks
     */
    protected function getIpReputationScore(string $ip): int
    {
        // Implement IP reputation check
        return 0; // Placeholder
    }

    protected function getRecentSecurityEvents(User $user): array
    {
        // Get recent security events for this user
        return []; // Placeholder
    }

    protected function getUnusualPatternScore(User $user, Request $request): int
    {
        // Analyze purchasing patterns for anomalies
        return 0; // Placeholder
    }

    protected function isTrustedDevice(User $user, Request $request): bool
    {
        // Check if device is trusted
        return TRUE; // Placeholder
    }

    /**
     * Deny access and log the attempt
     */
    protected function denyAccess(
        Request $request,
        string $message,
        string $reason,
        ?User $user = NULL,
        array $additionalData = [],
    ) {
        // Log the denial
        $this->securityMonitoring->logSecurityEvent(
            'ticket_purchase_denied',
            $user,
            $request,
            array_merge([
                'reason'        => $reason,
                'message'       => $message,
                'requested_uri' => $request->getRequestUri(),
            ], $additionalData),
        );

        // Return appropriate response based on request type
        if ($request->expectsJson()) {
            return response()->json([
                'success'    => FALSE,
                'message'    => $message,
                'error_code' => $reason,
                'user_info'  => $additionalData['user_info'] ?? [],
                'reasons'    => $additionalData['reasons'] ?? [],
            ], Response::HTTP_FORBIDDEN);
        }

        return redirect()->back()
            ->withErrors(['purchase' => $message])
            ->withInput();
    }
}
