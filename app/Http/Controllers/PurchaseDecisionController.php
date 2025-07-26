<?php

namespace App\Http\Controllers;

use App\Models\PurchaseQueue;
use App\Models\PurchaseAttempt;
use App\Models\ScrapedTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\PurchaseAnalyticsService;

class PurchaseDecisionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->canMakePurchaseDecisions()) {
                abort(403, 'You do not have permission to access purchase decisions.');
            }
            return $next($request);
        });
    }

    /**
     * Display the purchase decision dashboard
     */
public function index(Request $request)
    {
        $query = PurchaseQueue::with(['scrapedTicket', 'selectedByUser', 'purchaseAttempts'])
                    ->orderBy('priority', 'desc')
                    ->orderBy('created_at', 'asc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('platform')) {
            $query->whereHas('scrapedTicket', function ($q) use ($request) {
                $q->where('platform', $request->platform);
            });
        }

        if ($request->filled('selected_by')) {
            $query->where('selected_by_user_id', $request->selected_by);
        }

        $purchaseQueue = $query->paginate(15)->withQueryString();

        // Get summary statistics using the internal method
        $stats = $this->getPurchaseStats();

        // Get users who can make purchase decisions
        $agents = \App\Models\User::whereIn('role', ['admin', 'agent'])
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();

        return view('purchase-decisions.index', compact('purchaseQueue', 'stats', 'agents'));
    }

    /**
     * Show available tickets for selection
     */
    public function selectTickets(Request $request)
    {
        $query = ScrapedTicket::where('availability_status', 'available')
                    ->whereNotIn('id', function ($q) {
                        $q->select('scraped_ticket_id')
                          ->from('purchase_queues')
                          ->whereIn('status', ['queued', 'processing']);
                    })
                    ->orderBy('is_high_demand', 'desc')
                    ->orderBy('scraped_at', 'desc');

        // Apply filters
        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        if ($request->filled('event_title')) {
            $query->where('event_title', 'like', '%' . $request->event_title . '%');
        }

        if ($request->filled('max_price')) {
            $query->where('total_price', '<=', $request->max_price);
        }

        if ($request->filled('min_price')) {
            $query->where('total_price', '>=', $request->min_price);
        }

        if ($request->filled('high_demand_only')) {
            $query->where('is_high_demand', true);
        }

        $availableTickets = $query->paginate(20)->withQueryString();

        return view('purchase-decisions.select-tickets', compact('availableTickets'));
    }

    /**
     * Add ticket to purchase queue
     */
    public function addToQueue(Request $request, ScrapedTicket $scrapedTicket)
    {
        $request->validate([
            'priority' => 'required|in:' . implode(',', PurchaseQueue::getPriorities()),
            'max_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:1|max:10',
            'notes' => 'nullable|string|max:1000',
            'scheduled_for' => 'nullable|date|after:now',
            'expires_at' => 'nullable|date|after:scheduled_for',
            'purchase_criteria' => 'nullable|array',
        ]);

        // Check if ticket is already in queue
        $existingQueue = PurchaseQueue::where('scraped_ticket_id', $scrapedTicket->id)
                            ->whereIn('status', ['queued', 'processing'])
                            ->first();

        if ($existingQueue) {
            return redirect()->back()->with('error', 'This ticket is already in the purchase queue.');
        }

        $purchaseQueue = PurchaseQueue::create([
            'scraped_ticket_id' => $scrapedTicket->id,
            'selected_by_user_id' => Auth::id(),
            'priority' => $request->priority,
            'max_price' => $request->max_price,
            'quantity' => $request->quantity,
            'notes' => $request->notes,
            'scheduled_for' => $request->scheduled_for,
            'expires_at' => $request->expires_at,
            'purchase_criteria' => $request->purchase_criteria,
        ]);

        return redirect()->route('purchase-decisions.index')->with('success', 'Ticket added to purchase queue successfully.');
    }

    /**
     * Update queue item priority or settings
     */
    public function updateQueue(Request $request, PurchaseQueue $purchaseQueue)
    {
        $request->validate([
            'priority' => 'sometimes|in:' . implode(',', PurchaseQueue::getPriorities()),
            'max_price' => 'sometimes|nullable|numeric|min:0',
            'quantity' => 'sometimes|integer|min:1|max:10',
            'notes' => 'sometimes|nullable|string|max:1000',
            'scheduled_for' => 'sometimes|nullable|date|after:now',
            'expires_at' => 'sometimes|nullable|date|after:scheduled_for',
        ]);

        $purchaseQueue->update($request->only([
            'priority', 'max_price', 'quantity', 'notes', 'scheduled_for', 'expires_at'
        ]));

        return redirect()->back()->with('success', 'Purchase queue item updated successfully.');
    }

    /**
     * Cancel purchase queue item
     */
    public function cancelQueue(PurchaseQueue $purchaseQueue)
    {
        if (!$purchaseQueue->isActive()) {
            return redirect()->back()->with('error', 'Cannot cancel a queue item that is not active.');
        }

        $purchaseQueue->cancel();

        return redirect()->back()->with('success', 'Purchase queue item cancelled successfully.');
    }

    /**
     * Process purchase queue (manual trigger)
     */
    public function processQueue(PurchaseQueue $purchaseQueue)
    {
        if (!$purchaseQueue->status === PurchaseQueue::STATUS_QUEUED) {
            return redirect()->back()->with('error', 'Queue item is not ready for processing.');
        }

        // Mark as processing
        $purchaseQueue->markAsProcessing();

        // Create a new purchase attempt
        $attempt = PurchaseAttempt::create([
            'purchase_queue_id' => $purchaseQueue->id,
            'scraped_ticket_id' => $purchaseQueue->scraped_ticket_id,
            'platform' => $purchaseQueue->scrapedTicket->platform,
            'attempted_price' => $purchaseQueue->scrapedTicket->total_price,
            'attempted_quantity' => $purchaseQueue->quantity,
        ]);

        // TODO: Integrate with actual purchase processing service
        // For now, we'll simulate the process
        $this->simulatePurchaseProcess($attempt);

        return redirect()->back()->with('success', 'Purchase process initiated successfully.');
    }

    /**
     * Show purchase queue item details
     */
    public function show(PurchaseQueue $purchaseQueue)
    {
        $purchaseQueue->load(['scrapedTicket', 'selectedByUser', 'purchaseAttempts']);
        
        return view('purchase-decisions.show', compact('purchaseQueue'));
    }

    /**
     * Get purchase queue statistics
     */
    protected function getPurchaseStats()
    {
        return [
            'total_queued' => PurchaseQueue::byStatus(PurchaseQueue::STATUS_QUEUED)->count(),
            'processing' => PurchaseQueue::byStatus(PurchaseQueue::STATUS_PROCESSING)->count(),
            'completed_today' => PurchaseQueue::byStatus(PurchaseQueue::STATUS_COMPLETED)
                                    ->whereDate('completed_at', today())
                                    ->count(),
            'failed_today' => PurchaseQueue::byStatus(PurchaseQueue::STATUS_FAILED)
                                ->whereDate('updated_at', today())
                                ->count(),
            'success_rate' => $this->getSuccessRate(),
            'high_priority' => PurchaseQueue::highPriority()
                                ->whereIn('status', ['queued', 'processing'])
                                ->count(),
            'expired' => PurchaseQueue::expired()->count(),
        ];
    }

    /**
     * Calculate overall success rate
     */
    protected function getSuccessRate()
    {
        $totalAttempts = PurchaseAttempt::count();
        if ($totalAttempts === 0) {
            return 0;
        }

        $successfulAttempts = PurchaseAttempt::successful()->count();
        return round(($successfulAttempts / $totalAttempts) * 100, 1);
    }

    /**
     * Simulate purchase process (placeholder for actual implementation)
     */
    protected function simulatePurchaseProcess(PurchaseAttempt $attempt)
    {
        // Mark as in progress
        $attempt->markInProgress();

        // Simulate processing delay
        // In a real implementation, this would be handled by a queue job
        
        // Random success/failure for demo purposes
        $success = rand(1, 100) <= 75; // 75% success rate

        if ($success) {
            $attempt->markSuccessful(
                'TXN-' . strtoupper(Str::random(8)),
                'CONF-' . strtoupper(Str::random(8)),
                $attempt->attempted_price,
                $attempt->attempted_price * 0.15, // 15% fees
                $attempt->attempted_price * 1.15
            );
            $attempt->purchaseQueue->markAsCompleted();
        } else {
            $attempt->markFailed('Simulation failed', 'Random failure for demo purposes');
            $attempt->purchaseQueue->markAsFailed();
        }
    }

    /**
     * Bulk operations on queue items
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:cancel,update_priority,process',
            'queue_ids' => 'required|array',
            'queue_ids.*' => 'exists:purchase_queues,id',
            'priority' => 'required_if:action,update_priority|in:' . implode(',', PurchaseQueue::getPriorities()),
        ]);

        $queues = PurchaseQueue::whereIn('id', $request->queue_ids)->get();
        $processedCount = 0;

        foreach ($queues as $queue) {
            switch ($request->action) {
                case 'cancel':
                    if ($queue->isActive()) {
                        $queue->cancel();
                        $processedCount++;
                    }
                    break;
                    
                case 'update_priority':
                    $queue->update(['priority' => $request->priority]);
                    $processedCount++;
                    break;
                    
                case 'process':
                    if ($queue->status === PurchaseQueue::STATUS_QUEUED) {
                        $this->processQueue($queue);
                        $processedCount++;
                    }
                    break;
            }
        }

        return redirect()->back()->with('success', "Processed {$processedCount} queue items successfully.");
    }
}
