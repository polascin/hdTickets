<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Events\TicketPurchaseAttempted;
use App\Events\TicketPurchaseCompleted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TicketPurchaseRequest;
use App\Http\Resources\TicketPurchaseResource;
use App\Models\Ticket;
use App\Models\TicketPurchase;
use App\Services\PaymentService;
use App\Services\TicketPurchaseService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use function in_array;

class TicketPurchaseController extends Controller
{
    public function __construct(
        private TicketPurchaseService $purchaseService,
        private PaymentService $paymentService,
    ) {
        $this->middleware('auth:sanctum');
        $this->middleware('verified');
        $this->middleware('ticket.purchase.validation')->only(['store', 'checkEligibility']);
    }

    /**
     * Check purchase eligibility for a ticket
     */
    public function checkEligibility(int $ticketId): JsonResponse
    {
        try {
            $user = Auth::user();
            $ticket = Ticket::findOrFail($ticketId);

            $eligibility = $this->purchaseService->checkPurchaseEligibility($user, $ticket);

            return response()->json([
                'success'     => TRUE,
                'eligible'    => $eligibility['can_purchase'],
                'reasons'     => $eligibility['reasons'] ?? [],
                'user_info'   => $eligibility['user_info'] ?? [],
                'ticket_info' => [
                    'available'          => $ticket->isAvailable(),
                    'price'              => $ticket->price,
                    'quantity_available' => $ticket->quantity_available,
                    'status'             => $ticket->status,
                ],
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Ticket not found.',
            ], 404);
        } catch (Exception $e) {
            Log::error('Failed to check purchase eligibility', [
                'ticket_id' => $ticketId,
                'user_id'   => Auth::id(),
                'error'     => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to check purchase eligibility.',
            ], 500);
        }
    }

    /**
     * Initiate ticket purchase
     */
    public function store(TicketPurchaseRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $data = $request->validated();
            $ticket = Ticket::findOrFail($data['ticket_id']);

            // Final eligibility check
            $eligibility = $this->purchaseService->checkPurchaseEligibility($user, $ticket, $data['quantity']);

            if (!$eligibility['can_purchase']) {
                return response()->json([
                    'success'   => FALSE,
                    'message'   => 'Purchase not allowed.',
                    'reasons'   => $eligibility['reasons'],
                    'user_info' => $eligibility['user_info'] ?? [],
                ], 422);
            }

            // Create purchase record
            $purchase = DB::transaction(function () use ($user, $ticket, $data) {
                $purchaseId = $this->generatePurchaseId();

                $purchase = TicketPurchase::create([
                    'user_id'          => $user->id,
                    'ticket_id'        => $ticket->id,
                    'purchase_id'      => $purchaseId,
                    'quantity'         => $data['quantity'],
                    'unit_price'       => $ticket->price,
                    'subtotal'         => $ticket->price * $data['quantity'],
                    'processing_fee'   => $this->calculateProcessingFee($ticket->price * $data['quantity']),
                    'service_fee'      => config('tickets.service_fee', 2.50),
                    'total_amount'     => $this->calculateTotalAmount($ticket->price, $data['quantity']),
                    'status'           => 'pending',
                    'seat_preferences' => $data['seat_preferences'] ?? NULL,
                    'special_requests' => $data['special_requests'] ?? NULL,
                ]);

                // Update ticket quantity if applicable
                if ($ticket->quantity_available !== NULL) {
                    $ticket->decrement('quantity_available', $data['quantity']);

                    // Update status if sold out
                    if ($ticket->quantity_available <= 0) {
                        $ticket->update(['status' => 'sold_out']);
                    }
                }

                return $purchase;
            });

            // Fire purchase attempted event
            event(new TicketPurchaseAttempted($purchase, $user));

            // Log the purchase attempt
            Log::info('Ticket purchase initiated', [
                'purchase_id'  => $purchase->purchase_id,
                'user_id'      => $user->id,
                'ticket_id'    => $ticket->id,
                'quantity'     => $data['quantity'],
                'total_amount' => $purchase->total_amount,
            ]);

            return response()->json([
                'success'     => TRUE,
                'message'     => 'Purchase initiated successfully.',
                'purchase'    => new TicketPurchaseResource($purchase),
                'next_step'   => 'payment',
                'payment_url' => route('tickets.purchase.payment', $purchase->purchase_id),
            ], 201);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Ticket not found.',
            ], 404);
        } catch (Exception $e) {
            Log::error('Ticket purchase failed', [
                'user_id' => Auth::id(),
                'data'    => $request->all(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Purchase failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Get purchase details
     */
    public function show(string $purchaseId): JsonResponse
    {
        try {
            $user = Auth::user();

            $purchase = TicketPurchase::with(['ticket.venue', 'user'])
                ->where('purchase_id', $purchaseId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            return response()->json([
                'success'  => TRUE,
                'purchase' => new TicketPurchaseResource($purchase),
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Purchase not found.',
            ], 404);
        } catch (Exception) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load purchase details.',
            ], 500);
        }
    }

    /**
     * Process payment for purchase
     */
    public function processPayment(string $purchaseId, Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $purchase = TicketPurchase::where('purchase_id', $purchaseId)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->firstOrFail();

            $paymentMethod = $request->get('payment_method', 'stripe');
            $paymentData = $request->get('payment_data', []);

            // Process payment through payment service
            $paymentResult = $this->paymentService->processPayment(
                $purchase,
            );

            if ($paymentResult['success']) {
                // Update purchase status
                $purchase->update([
                    'status'            => 'confirmed',
                    'payment_intent_id' => $paymentResult['payment_intent_id'] ?? NULL,
                    'payment_status'    => 'paid',
                    'confirmed_at'      => now(),
                ]);

                // Fire purchase completed event
                event(new TicketPurchaseCompleted($purchase, $user));

                // Log successful purchase
                Log::info('Ticket purchase completed', [
                    'purchase_id'    => $purchase->purchase_id,
                    'user_id'        => $user->id,
                    'payment_method' => $paymentMethod,
                    'total_amount'   => $purchase->total_amount,
                ]);

                return response()->json([
                    'success'      => TRUE,
                    'message'      => 'Payment processed successfully.',
                    'purchase'     => new TicketPurchaseResource($purchase->fresh()),
                    'redirect_url' => route('tickets.purchase.success', $purchase->purchase_id),
                ]);
            }
            // Update purchase with failure info
            $purchase->update([
                'status'              => 'failed',
                'payment_status'      => 'failed',
                'cancellation_reason' => $paymentResult['error_message'] ?? 'Payment failed',
            ]);
            // Restore ticket quantity if applicable
            $this->restoreTicketQuantity($purchase);

            return response()->json([
                'success'       => FALSE,
                'message'       => 'Payment failed.',
                'error_details' => $paymentResult['error_message'] ?? 'Unknown payment error',
            ], 422);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Purchase not found or cannot be processed.',
            ], 404);
        } catch (Exception $e) {
            Log::error('Payment processing failed', [
                'purchase_id' => $purchaseId,
                'user_id'     => Auth::id(),
                'error'       => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Payment processing failed.',
            ], 500);
        }
    }

    /**
     * Cancel a pending purchase
     */
    public function cancel(string $purchaseId, Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $purchase = TicketPurchase::where('purchase_id', $purchaseId)
                ->where('user_id', $user->id)
                ->whereIn('status', ['pending', 'processing'])
                ->firstOrFail();

            // Check if purchase can be cancelled
            if (!$this->canCancelPurchase($purchase)) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'This purchase cannot be cancelled.',
                ], 422);
            }

            $reason = $request->get('reason', 'User requested cancellation');

            DB::transaction(function () use ($purchase, $reason): void {
                $purchase->update([
                    'status'              => 'cancelled',
                    'cancelled_at'        => now(),
                    'cancellation_reason' => $reason,
                ]);

                // Restore ticket quantity
                $this->restoreTicketQuantity($purchase);
            });

            Log::info('Ticket purchase cancelled', [
                'purchase_id' => $purchase->purchase_id,
                'user_id'     => $user->id,
                'reason'      => $reason,
            ]);

            return response()->json([
                'success'  => TRUE,
                'message'  => 'Purchase cancelled successfully.',
                'purchase' => new TicketPurchaseResource($purchase->fresh()),
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Purchase not found or cannot be cancelled.',
            ], 404);
        } catch (Exception $e) {
            Log::error('Purchase cancellation failed', [
                'purchase_id' => $purchaseId,
                'user_id'     => Auth::id(),
                'error'       => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to cancel purchase.',
            ], 500);
        }
    }

    /**
     * Get user's purchase history
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $page = $request->get('page', 1);
            $perPage = min($request->get('per_page', 20), 50);
            $status = $request->get('status'); // pending, confirmed, cancelled, failed
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');

            $query = TicketPurchase::with(['ticket.venue'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            // Filter by status
            if ($status) {
                $query->where('status', $status);
            }

            // Filter by date range
            if ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->where('created_at', '<=', $dateTo);
            }

            $purchases = $query->paginate($perPage, ['*'], 'page', $page);

            // Get summary statistics
            $stats = $this->getPurchaseStats($user->id);

            return response()->json([
                'success'      => TRUE,
                'purchases'    => TicketPurchaseResource::collection($purchases->items()),
                'total'        => $purchases->total(),
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => $purchases->lastPage(),
                'has_more'     => $purchases->hasMorePages(),
                'stats'        => $stats,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to load purchase history', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load purchase history.',
            ], 500);
        }
    }

    /**
     * Export purchase history
     */
    public function exportHistory(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $format = $request->get('format', 'csv'); // csv, pdf

            if (!in_array($format, ['csv', 'pdf'], TRUE)) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Invalid export format. Use csv or pdf.',
                ], 422);
            }

            $purchases = TicketPurchase::with(['ticket.venue'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $filename = $this->purchaseService->exportPurchaseHistory($purchases, $format, $user);

            return response()->json([
                'success'         => TRUE,
                'message'         => 'Export completed successfully.',
                'download_url'    => route('tickets.purchase.download-export', ['filename' => $filename]),
                'filename'        => $filename,
                'total_purchases' => $purchases->count(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to export purchase history', [
                'user_id' => Auth::id(),
                'format'  => $request->get('format'),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to export purchase history.',
            ], 500);
        }
    }

    /**
     * Generate unique purchase ID
     */
    private function generatePurchaseId(): string
    {
        do {
            $purchaseId = 'PUR-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (TicketPurchase::where('purchase_id', $purchaseId)->exists());

        return $purchaseId;
    }

    /**
     * Calculate processing fee (3% of subtotal)
     */
    private function calculateProcessingFee(float $subtotal): float
    {
        $feeRate = config('tickets.processing_fee_rate', 0.03);

        return round($subtotal * $feeRate, 2);
    }

    /**
     * Calculate total amount including fees
     */
    private function calculateTotalAmount(float $unitPrice, int $quantity): float
    {
        $subtotal = $unitPrice * $quantity;
        $processingFee = $this->calculateProcessingFee($subtotal);
        $serviceFee = config('tickets.service_fee', 2.50);

        return round($subtotal + $processingFee + $serviceFee, 2);
    }

    /**
     * Check if purchase can be cancelled
     */
    private function canCancelPurchase(TicketPurchase $purchase): bool
    {
        // Can't cancel confirmed purchases
        if ($purchase->status === 'confirmed') {
            return FALSE;
        }

        // Check cancellation window (24 hours)
        $cancellationWindow = config('tickets.cancellation_window_hours', 24);
        $cutoffTime = $purchase->created_at->addHours($cancellationWindow);

        return now()->isBefore($cutoffTime);
    }

    /**
     * Restore ticket quantity after cancellation
     */
    private function restoreTicketQuantity(TicketPurchase $purchase): void
    {
        $ticket = $purchase->ticket;

        if ($ticket->quantity_available !== NULL) {
            $ticket->increment('quantity_available', $purchase->quantity);

            // Update status if no longer sold out
            if ($ticket->status === 'sold_out' && $ticket->quantity_available > 0) {
                $ticket->update(['status' => 'available']);
            }
        }
    }

    /**
     * Get purchase statistics for a user
     */
    private function getPurchaseStats(int $userId): array
    {
        $baseQuery = TicketPurchase::where('user_id', $userId);

        return [
            'total_purchases'      => $baseQuery->count(),
            'confirmed_purchases'  => $baseQuery->where('status', 'confirmed')->count(),
            'pending_purchases'    => $baseQuery->where('status', 'pending')->count(),
            'cancelled_purchases'  => $baseQuery->where('status', 'cancelled')->count(),
            'total_spent'          => $baseQuery->where('status', 'confirmed')->sum('total_amount'),
            'this_month_purchases' => $baseQuery->where('created_at', '>=', now()->startOfMonth())->count(),
            'this_month_spent'     => $baseQuery->where('created_at', '>=', now()->startOfMonth())
                ->where('status', 'confirmed')
                ->sum('total_amount'),
            'average_purchase_amount' => $baseQuery->where('status', 'confirmed')->avg('total_amount'),
            'last_purchase_date'      => $baseQuery->latest()->value('created_at'),
        ];
    }
}
