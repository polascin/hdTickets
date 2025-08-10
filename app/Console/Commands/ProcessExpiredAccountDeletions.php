<?php

namespace App\Console\Commands;

use App\Services\AccountDeletionProtectionService;
use Illuminate\Console\Command;

class ProcessExpiredAccountDeletions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account-deletion:process-expired
                           {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process expired account deletion requests after grace period';

    protected AccountDeletionProtectionService $deletionService;

    /**
     * Create a new command instance.
     */
    public function __construct(AccountDeletionProtectionService $deletionService)
    {
        parent::__construct();
        $this->deletionService = $deletionService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Processing expired account deletion requests...');

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No accounts will actually be deleted');
        }

        try {
            if ($this->option('dry-run')) {
                // Get expired requests without processing them
                $expiredRequests = \App\Models\AccountDeletionRequest::gracePeriodExpired()->get();
                
                $this->info("Found {$expiredRequests->count()} expired deletion requests:");
                
                foreach ($expiredRequests as $request) {
                    $user = $request->user;
                    $this->line("- User ID: {$user->id} ({$user->email}) - Grace period expired: {$request->grace_period_expires_at}");
                }
                
                return self::SUCCESS;
            }

            $processedCount = $this->deletionService->processExpiredDeletions();

            if ($processedCount > 0) {
                $this->info("Successfully processed {$processedCount} expired deletion requests.");
            } else {
                $this->info('No expired deletion requests to process.');
            }

            // Also clean up expired export files
            $cleanedExports = $this->deletionService->cleanupExpiredExports();
            
            if ($cleanedExports > 0) {
                $this->info("Cleaned up {$cleanedExports} expired data export files.");
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error processing expired deletions: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
