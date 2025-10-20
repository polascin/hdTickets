<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ScrapedTicket;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\TicketPurchaseService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TicketPurchaseController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private TicketPurchaseService $ticketPurchaseService,
    ) {
    }

    /**
     * Display available scraped tickets for purchase
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Check if user can access tickets
        if (! $this->canAccessTickets($user)) {
            return view('tickets.access-denied', ['user' => $user]);
        }

        // Get user's ticket limits
        $ticketLimits = $this->paymentService->calculateTicketLimits($user);
        $remainingAllowance = $this->paymentService->getRemainingTicketAllowance($user);

        // Build query for scraped tickets
        $query = ScrapedTicket::with(['platform', 'event'])
            ->where('is_available', TRUE)
            ->where('expires_at', '>', now())
            ->orderBy('scraped_at', 'desc');

        // Apply filters
        if ($request->filled('event')) {
            $query->where('event_name', 'like', '%' . $request->event . '%');
        }

        if ($request->filled('platform')) {
            $query->where('platform_name', $request->platform);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->max_price);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $tickets = $query->paginate(20);

        // Get filter options
        $platforms = ScrapedTicket::distinct()->pluck('platform_name')->filter();
        $categories = ScrapedTicket::distinct()->pluck('category')->filter();

        return view('tickets.index', ['tickets' => $tickets, 'ticketLimits' => $ticketLimits, 'remainingAllowance' => $remainingAllowance, 'platforms' => $platforms, 'categories' => $categories, 'user' => $user]);
    }

    /**
     * Show ticket details for purchase
     */
    public function show(ScrapedTicket $ticket): View
    {
        $user = Auth::user();

        if (! $this->canAccessTickets($user)) {
            abort(403, 'You do not have access to view tickets.');
        }

        if (! $ticket->is_available || $ticket->expires_at <= now()) {
            abort(404, 'Ticket is no longer available.');
        }

        $remainingAllowance = $this->paymentService->getRemainingTicketAllowance($user);
        $canPurchase = $this->canPurchaseTicket($user, $ticket);

        return view('tickets.show', ['ticket' => $ticket, 'user' => $user, 'remainingAllowance' => $remainingAllowance, 'canPurchase' => $canPurchase]);
    }

    /**
     * Process ticket purchase
     */
    public function purchase(Request $request, ScrapedTicket $ticket): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'quantity'       => ['required', 'integer', 'min:1', 'max:10'],
            'payment_method' => ['sometimes', 'string', 'in:paypal,stripe,automatic'],
        ]);

        // Verify user can purchase tickets
        if (! $this->canPurchaseTicket($user, $ticket)) {
            return back()->withErrors(['error' => 'You cannot purchase this ticket at this time.']);
        }

        // Check if ticket is still available
        if (! $ticket->is_available || $ticket->expires_at <= now()) {
            return back()->withErrors(['error' => 'This ticket is no longer available.']);
        }

        // Check quantity limits
        $quantity = (int) $request->quantity;
        if ($ticket->available_quantity && $quantity > $ticket->available_quantity) {
            return back()->withErrors(['error' => 'Requested quantity exceeds availability.']);
        }

        // Check user's remaining allowance
        $remainingAllowance = $this->paymentService->getRemainingTicketAllowance($user);
        if ($remainingAllowance !== -1 && $quantity > $remainingAllowance) {
            return back()->withErrors(['error' => 'This purchase would exceed your monthly ticket limit.']);
        }

        $paymentMethod = $request->get('payment_method', 'automatic');
        $quantity = (int) $request->quantity;

        try {
            DB::beginTransaction();

            // Handle PayPal payments separately from automatic purchase system
            if ($paymentMethod === 'paypal') {
                $paymentResult = $this->handlePayPalTicketPurchase($user, $ticket, $quantity);

                if ($paymentResult['success']) {
                    DB::commit();

                    return redirect($paymentResult['approve_url']);
                }

                throw new Exception($paymentResult['error']);
            }

            // For automatic purchases (existing system)
            $purchaseAttempt = $this->ticketPurchaseService->attemptPurchase(
                $user,
                $ticket,
                $quantity,
            );

            DB::commit();

            if ($purchaseAttempt->isSuccessful()) {
                return redirect()->route('tickets.purchase.success', $purchaseAttempt)
                    ->with('success', 'Ticket purchase completed successfully!');
            }

            return redirect()->route('tickets.purchase.failed', $purchaseAttempt)
                ->with('error', 'Ticket purchase failed: ' . $purchaseAttempt->failure_reason);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Ticket purchase failed', [
                'user_id'        => $user->id,
                'ticket_id'      => $ticket->id,
                'quantity'       => $quantity,
                'payment_method' => $paymentMethod,
                'error'          => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['error' => 'Purchase failed: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show purchase success page
     *
     * @param mixed $purchaseAttemptId
     */
    public function purchaseSuccess($purchaseAttemptId): View
    {
        $user = Auth::user();
        $purchaseAttempt = $user->purchaseAttempts()
            ->with(['scrapedTicket', 'purchaseQueue'])
            ->findOrFail($purchaseAttemptId);

        return view('tickets.purchase.success', ['purchaseAttempt' => $purchaseAttempt, 'user' => $user]);
    }

    /**
     * Show purchase failed page
     *
     * @param mixed $purchaseAttemptId
     */
    public function purchaseFailed($purchaseAttemptId): View
    {
        $user = Auth::user();
        $purchaseAttempt = $user->purchaseAttempts()
            ->with(['scrapedTicket'])
            ->findOrFail($purchaseAttemptId);

        return view('tickets.purchase.failed', ['purchaseAttempt' => $purchaseAttempt, 'user' => $user]);
    }

    /**
     * Show user's purchase history
     */
    public function history(): View
    {
        $user = Auth::user();

        $purchaseHistory = $user->purchaseAttempts()
            ->with(['scrapedTicket', 'purchaseQueue'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_attempts'       => $user->purchaseAttempts()->count(),
            'successful_purchases' => $user->purchaseAttempts()->where('status', 'completed')->count(),
            'pending_purchases'    => $user->purchaseAttempts()->where('status', 'pending')->count(),
            'failed_purchases'     => $user->purchaseAttempts()->where('status', 'failed')->count(),
        ];

        return view('tickets.history', ['purchaseHistory' => $purchaseHistory, 'stats' => $stats, 'user' => $user]);
    }

    /**
     * Handle PayPal payment return after approval
     */
    public function paypalReturn(Request $request): RedirectResponse
    {
        $orderId = $request->get('token'); // PayPal returns token as order ID
        $payerId = $request->get('PayerID');

        if (! $orderId) {
            return redirect()->route('tickets.index')
                ->withErrors(['error' => 'Invalid PayPal response.']);
        }

        try {
            // Find the purchase attempt
            $purchaseAttempt = $this->findPurchaseAttemptByPayPalOrder($orderId);

            if (! $purchaseAttempt) {
                throw new Exception('Purchase attempt not found.');
            }

            // Capture the PayPal payment
            $captureResult = $this->paymentService->capturePayPalPayment($orderId);

            if ($captureResult['success']) {
                // Update purchase attempt
                $purchaseAttempt->update([
                    'status'            => 'completed',
                    'paypal_capture_id' => $captureResult['capture_id'],
                    'total_paid'        => $captureResult['amount'],
                    'completed_at'      => now(),
                    'metadata'          => array_merge($purchaseAttempt->metadata ?? [], [
                        'paypal_capture_id' => $captureResult['capture_id'],
                        'captured_at'       => now()->toISOString(),
                        'payer_id'          => $payerId,
                    ]),
                ]);

                // Trigger payment processed event
                event(new \App\Domain\Purchase\Events\PaymentProcessed(
                    new \App\Domain\Purchase\ValueObjects\PurchaseId((string) $purchaseAttempt->id),
                    'paypal',
                    $captureResult['capture_id'],
                    (float) $captureResult['amount'],
                    $captureResult['currency'],
                    'completed',
                    now(),
                    [
                        'paypal_order_id'   => $orderId,
                        'paypal_capture_id' => $captureResult['capture_id'],
                        'payer_id'          => $payerId,
                    ],
                ));

                Log::info('PayPal ticket payment completed', [
                    'purchase_attempt_id' => $purchaseAttempt->id,
                    'paypal_order_id'     => $orderId,
                    'capture_id'          => $captureResult['capture_id'],
                ]);

                return redirect()->route('tickets.purchase.success', $purchaseAttempt->id)
                    ->with('success', 'Payment completed successfully!');
            }
            // Update purchase attempt as failed
            $purchaseAttempt->update([
                'status'         => 'failed',
                'failure_reason' => $captureResult['error'] ?? 'Payment capture failed',
                'completed_at'   => now(),
            ]);

            throw new Exception($captureResult['error'] ?? 'Payment capture failed');
        } catch (Exception $e) {
            Log::error('PayPal ticket payment return handling failed', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);

            return redirect()->route('tickets.index')
                ->withErrors(['error' => 'Payment processing failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Handle PayPal payment cancellation
     */
    public function paypalCancel(Request $request): RedirectResponse
    {
        $orderId = $request->get('token');

        if ($orderId) {
            $purchaseAttempt = $this->findPurchaseAttemptByPayPalOrder($orderId);
            if ($purchaseAttempt) {
                $purchaseAttempt->update([
                    'status'         => 'cancelled',
                    'failure_reason' => 'User cancelled PayPal payment',
                    'completed_at'   => now(),
                ]);

                Log::info('PayPal ticket payment cancelled', [
                    'purchase_attempt_id' => $purchaseAttempt->id,
                    'paypal_order_id'     => $orderId,
                ]);
            }
        }

        return redirect()->route('tickets.index')
            ->with('message', 'Payment was cancelled.');
    }

    /**
     * Check if user can access tickets
     */
    private function canAccessTickets(User $user): bool
    {
        // Admins and agents can always access
        if ($user->isAdmin() || $user->isAgent()) {
            return TRUE;
        }

        // Scrapers cannot access tickets
        if ($user->isScraper()) {
            return FALSE;
        }

        // Customers need verified email and active subscription
        if (! $user->hasVerifiedEmail()) {
            return FALSE;
        }
        if ($user->hasActiveSubscription()) {
            return TRUE;
        }

        return $user->isOnTrial();
    }

    /**
     * Check if user can purchase specific ticket
     */
    private function canPurchaseTicket(User $user, ScrapedTicket $ticket): bool
    {
        // Basic access check
        if (! $this->canAccessTickets($user)) {
            return FALSE;
        }

        // Check if user can purchase tickets at all
        if (! $this->paymentService->canPurchaseTickets($user)) {
            return FALSE;
        }

        // Check if user has reached their limit
        if ($user->hasReachedTicketLimit()) {
            return FALSE;
        }

        // Check ticket availability
        return $ticket->is_available && $ticket->expires_at > now();
    }

    /**
     * Handle PayPal ticket purchase
     */
    private function handlePayPalTicketPurchase(User $user, ScrapedTicket $ticket, int $quantity): array
    {
        try {
            $paymentResult = $this->paymentService->processPayPalPayment(
                $user,
                $ticket,
                $quantity,
                [
                    'return_url' => route('tickets.paypal.return'),
                    'cancel_url' => route('tickets.paypal.cancel'),
                ],
            );

            if (! $paymentResult['success']) {
                return ['success' => FALSE, 'error' => $paymentResult['error']];
            }

            // Create purchase attempt record for tracking
            $purchaseAttempt = $user->purchaseAttempts()->create([
                'scraped_ticket_id' => $ticket->id,
                'quantity'          => $quantity,
                'attempted_price'   => $ticket->price * $quantity,
                'status'            => 'pending_payment',
                'platform'          => $ticket->platform_name,
                'paypal_order_id'   => $paymentResult['order_id'],
                'metadata'          => [
                    'payment_method'  => 'paypal',
                    'paypal_order_id' => $paymentResult['order_id'],
                    'approve_url'     => $paymentResult['approve_url'],
                ],
            ]);

            Log::info('PayPal ticket purchase initiated', [
                'user_id'             => $user->id,
                'ticket_id'           => $ticket->id,
                'purchase_attempt_id' => $purchaseAttempt->id,
                'paypal_order_id'     => $paymentResult['order_id'],
            ]);

            return [
                'success'             => TRUE,
                'approve_url'         => $paymentResult['approve_url'],
                'order_id'            => $paymentResult['order_id'],
                'purchase_attempt_id' => $purchaseAttempt->id,
            ];
        } catch (Exception $e) {
            Log::error('PayPal ticket purchase failed', [
                'user_id'   => $user->id,
                'ticket_id' => $ticket->id,
                'error'     => $e->getMessage(),
            ]);

            return ['success' => FALSE, 'error' => $e->getMessage()];
        }
    }

    /**
     * Find purchase attempt by PayPal order ID
     */
    private function findPurchaseAttemptByPayPalOrder(string $orderId)
    {
        return \App\Models\PurchaseAttempt::where('paypal_order_id', $orderId)
            ->orWhere('metadata->paypal_order_id', $orderId)
            ->first();
    }
}
