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
        if (!$this->canAccessTickets($user)) {
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

        return view('tickets.index', compact(
            'tickets',
            'ticketLimits',
            'remainingAllowance',
            'platforms',
            'categories',
            'user',
        ));
    }

    /**
     * Show ticket details for purchase
     */
    public function show(ScrapedTicket $ticket): View
    {
        $user = Auth::user();

        if (!$this->canAccessTickets($user)) {
            abort(403, 'You do not have access to view tickets.');
        }

        if (!$ticket->is_available || $ticket->expires_at <= now()) {
            abort(404, 'Ticket is no longer available.');
        }

        $remainingAllowance = $this->paymentService->getRemainingTicketAllowance($user);
        $canPurchase = $this->canPurchaseTicket($user, $ticket);

        return view('tickets.show', compact('ticket', 'user', 'remainingAllowance', 'canPurchase'));
    }

    /**
     * Process ticket purchase
     */
    public function purchase(Request $request, ScrapedTicket $ticket): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        // Verify user can purchase tickets
        if (!$this->canPurchaseTicket($user, $ticket)) {
            return back()->withErrors(['error' => 'You cannot purchase this ticket at this time.']);
        }

        // Check if ticket is still available
        if (!$ticket->is_available || $ticket->expires_at <= now()) {
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

        try {
            DB::beginTransaction();

            // Attempt to purchase the ticket
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

        return view('tickets.purchase.success', compact('purchaseAttempt', 'user'));
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

        return view('tickets.purchase.failed', compact('purchaseAttempt', 'user'));
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

        return view('tickets.history', compact('purchaseHistory', 'stats', 'user'));
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
        if (!$user->hasVerifiedEmail()) {
            return FALSE;
        }

        return $user->hasActiveSubscription() || $user->isOnTrial();
    }

    /**
     * Check if user can purchase specific ticket
     */
    private function canPurchaseTicket(User $user, ScrapedTicket $ticket): bool
    {
        // Basic access check
        if (!$this->canAccessTickets($user)) {
            return FALSE;
        }

        // Check if user can purchase tickets at all
        if (!$this->paymentService->canPurchaseTickets($user)) {
            return FALSE;
        }

        // Check if user has reached their limit
        if ($user->hasReachedTicketLimit()) {
            return FALSE;
        }

        // Check ticket availability
        return !(!$ticket->is_available || $ticket->expires_at <= now());
    }
}
