<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\AutoPurchaseConfig;
use App\Services\AutomatedPurchasingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Retry Auto Purchase Job
 *
 * Handles delayed retry attempts for failed automated purchases
 */
class RetryAutoPurchaseJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public int $backoff = 60;

    public int $timeout = 120;

    public function __construct(
        private int $configId,
        private array $availableTickets
    ) {
    }

    public function handle(AutomatedPurchasingService $purchasingService): void
    {
        try {
            $config = AutoPurchaseConfig::find($this->configId);

            if (!$config) {
                Log::warning('Auto purchase config not found for retry', [
                    'config_id' => $this->configId,
                ]);

                return;
            }

            if (!$config->canAttemptPurchase()) {
                Log::info('Auto purchase retry skipped - config cannot attempt purchase', [
                    'config_id'     => $this->configId,
                    'is_active'     => $config->is_active,
                    'within_window' => $config->isWithinPurchaseWindow(),
                ]);

                return;
            }

            Log::info('Executing auto purchase retry', [
                'config_id'         => $this->configId,
                'available_tickets' => count($this->availableTickets),
            ]);

            $result = $purchasingService->executeAutoPurchase($config, $this->availableTickets);

            if ($result['success']) {
                Log::info('Auto purchase retry successful', [
                    'config_id'      => $this->configId,
                    'attempt_id'     => $result['attempt_id'],
                    'execution_time' => $result['execution_time'],
                ]);
            } else {
                Log::warning('Auto purchase retry failed', [
                    'config_id' => $this->configId,
                    'error'     => $result['error'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Auto purchase retry job failed', [
                'config_id' => $this->configId,
                'error'     => $e->getMessage(),
                'attempt'   => $this->attempts(),
            ]);

            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff * $this->attempts());
            } else {
                $this->fail($e);
            }
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Auto purchase retry job failed permanently', [
            'config_id' => $this->configId,
            'error'     => $exception->getMessage(),
            'attempts'  => $this->attempts(),
        ]);
    }
}
