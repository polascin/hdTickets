<?php declare(strict_types=1);

namespace App\Jobs;

use App\Models\DataExportRequest;
use App\Services\AccountDeletionProtectionService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessDataExportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected DataExportRequest $exportRequest;

    /**
     * Create a new job instance.
     */
    public function __construct(DataExportRequest $exportRequest)
    {
        $this->exportRequest = $exportRequest;
    }

    /**
     * Execute the job.
     */
    public function handle(AccountDeletionProtectionService $deletionService): void
    {
        Log::info('Processing data export request', [
            'export_request_id' => $this->exportRequest->id,
            'user_id'           => $this->exportRequest->user_id,
        ]);

        try {
            $success = $deletionService->processDataExport($this->exportRequest);

            if ($success) {
                Log::info('Data export processed successfully', [
                    'export_request_id' => $this->exportRequest->id,
                ]);
            } else {
                Log::error('Failed to process data export', [
                    'export_request_id' => $this->exportRequest->id,
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error processing data export', [
                'export_request_id' => $this->exportRequest->id,
                'error'             => $e->getMessage(),
                'trace'             => $e->getTraceAsString(),
            ]);

            $this->exportRequest->markAsFailed($e->getMessage());

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Data export job failed', [
            'export_request_id' => $this->exportRequest->id,
            'error'             => $exception->getMessage(),
        ]);

        $this->exportRequest->markAsFailed($exception->getMessage());
    }
}
