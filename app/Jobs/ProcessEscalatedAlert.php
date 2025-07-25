<?php

namespace App\Jobs;

use App\Models\AlertEscalation;
use App\Services\AlertEscalationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessEscalatedAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $escalation;

    /**
     * Job configuration
     */
    public $timeout = 300; // 5 minutes
    public $tries = 1; // Let the escalation service handle retries
    public $maxExceptions = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(AlertEscalation $escalation)
    {
        $this->escalation = $escalation;
        
        // Set queue based on priority
        $this->onQueue($this->getQueueName($escalation->priority));
    }

    /**
     * Execute the job.
     */
    public function handle(AlertEscalationService $escalationService): void
    {
        try {
            Log::info('Processing escalated alert', [
                'escalation_id' => $this->escalation->id,
                'alert_id' => $this->escalation->alert_id,
                'user_id' => $this->escalation->user_id,
                'attempt' => $this->escalation->attempts + 1
            ]);

            // Refresh the escalation model to get latest data
            $this->escalation->refresh();

            // Check if escalation is still valid before processing
            if (!$this->escalation->isValid()) {
                Log::info('Escalation is no longer valid, skipping', [
                    'escalation_id' => $this->escalation->id,
                    'status' => $this->escalation->status
                ]);
                return;
            }

            // Process the escalation
            $escalationService->processEscalation($this->escalation);

        } catch (\Exception $e) {
            Log::error('Failed to process escalated alert', [
                'escalation_id' => $this->escalation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update escalation status to failed if this was the last attempt
            if ($this->escalation->hasExceededMaxAttempts()) {
                $this->escalation->update(['status' => 'failed']);
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('Escalated alert job failed permanently', [
            'escalation_id' => $this->escalation->id,
            'alert_id' => $this->escalation->alert_id,
            'user_id' => $this->escalation->user_id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Mark escalation as failed
        $this->escalation->update([
            'status' => 'failed',
            'cancellation_reason' => 'job_failed: ' . $exception->getMessage()
        ]);

        // Optionally send notification to admin about the failure
        $this->notifyAdminOfFailure($exception);
    }

    /**
     * Get the appropriate queue name based on priority
     */
    protected function getQueueName(int $priority): string
    {
        switch ($priority) {
            case 5: // Critical
                return 'alerts-critical';
            case 4: // High
                return 'alerts-high';
            case 3: // Medium
                return 'alerts-medium';
            default:
                return 'alerts-default';
        }
    }

    /**
     * Get the display name for the job
     */
    public function displayName(): string
    {
        return "Process Escalated Alert #{$this->escalation->id}";
    }

    /**
     * Get the tags for the job
     */
    public function tags(): array
    {
        return [
            'escalation',
            'alert:' . $this->escalation->alert_id,
            'user:' . $this->escalation->user_id,
            'priority:' . $this->escalation->priority
        ];
    }

    /**
     * Calculate the number of seconds to wait before retrying the job
     */
    public function backoff(): array
    {
        // Return exponential backoff times in seconds
        return [30, 60, 120]; // 30s, 1m, 2m
    }

    /**
     * Determine if the job should be retried based on the exception
     */
    public function retryUntil(): \DateTime
    {
        // Retry for up to 30 minutes
        return now()->addMinutes(30);
    }

    /**
     * Notify admin of escalation failure
     */
    protected function notifyAdminOfFailure(\Throwable $exception): void
    {
        try {
            // This could send an email, Slack message, or create an admin notification
            Log::channel('admin')->critical('Escalated alert processing failed', [
                'escalation_id' => $this->escalation->id,
                'alert_id' => $this->escalation->alert_id,
                'user_id' => $this->escalation->user_id,
                'user_email' => $this->escalation->user->email ?? 'unknown',
                'strategy' => $this->escalation->strategy,
                'attempts_made' => $this->escalation->attempts,
                'error' => $exception->getMessage(),
                'timestamp' => now()->toISOString()
            ]);

            // Could also dispatch another job to send admin notification
            // AdminNotification::dispatch('escalation_failed', [
            //     'escalation' => $this->escalation,
            //     'error' => $exception->getMessage()
            // ]);

        } catch (\Exception $e) {
            // Don't let admin notification failures crash the cleanup
            Log::error('Failed to notify admin of escalation failure', [
                'escalation_id' => $this->escalation->id,
                'notification_error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get unique ID for the job (useful for job deduplication)
     */
    public function uniqueId(): string
    {
        return "escalation:{$this->escalation->id}:{$this->escalation->attempts}";
    }

    /**
     * Handle job middleware
     */
    public function middleware(): array
    {
        return [
            // Could add rate limiting, throttling, etc.
            // new RateLimited('escalations', 10, 60), // 10 per minute
        ];
    }
}
