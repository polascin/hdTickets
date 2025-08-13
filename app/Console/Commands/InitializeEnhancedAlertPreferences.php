<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserPreference;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InitializeEnhancedAlertPreferences extends Command
{
    /** The name and signature of the console command. */
    protected $signature = 'enhanced-alerts:init-preferences 
                            {--user= : Specific user ID to initialize}
                            {--force : Force reinitialize existing preferences}
                            {--dry-run : Show what would be done without making changes}';

    /** The console command description. */
    protected $description = 'Initialize enhanced alert preferences for users';

    /**
     * Execute the console command.
     */
    /**
     * Handle
     */
    public function handle(): int
    {
        $this->info('🚀 Initializing Enhanced Alert Preferences...');

        $userIdOption = $this->option('user');
        $userId = $userIdOption ? (int) $userIdOption : NULL;
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
        }

        try {
            DB::beginTransaction();

            $users = $userId ? User::where('id', $userId)->get() : User::all();

            if ($users->isEmpty()) {
                $this->error('❌ No users found');

                return Command::FAILURE;
            }

            $progressBar = $this->output->createProgressBar($users->count());
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - Processing: %message%');

            $initialized = 0;
            $skipped = 0;
            $errors = 0;

            foreach ($users as $user) {
                $progressBar->setMessage($user->email);
                $progressBar->advance();

                try {
                    $result = $this->initializeUserPreferences($user, $force, $dryRun);

                    switch ($result) {
                        case 'initialized':
                            $initialized++;

                            break;
                        case 'skipped':
                            $skipped++;

                            break;
                    }
                } catch (Exception $e) {
                    $errors++;
                    $this->newLine();
                    $this->error("❌ Error initializing preferences for {$user->email}: " . $e->getMessage());
                }
            }

            $progressBar->finish();
            $this->newLine(2);

            // Summary
            $this->info('✅ Initialization Summary:');
            $this->table(
                ['Status', 'Count'],
                [
                    ['Initialized', $initialized],
                    ['Skipped', $skipped],
                    ['Errors', $errors],
                    ['Total', $users->count()],
                ],
            );

            if (! $dryRun) {
                DB::commit();
                $this->info('💾 Changes committed to database');
            } else {
                DB::rollBack();
                $this->info('🔄 Dry run completed - no changes made');
            }

            return Command::SUCCESS;
        } catch (Exception $e) {
            DB::rollBack();
            $this->error('❌ Failed to initialize preferences: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Initialize preferences for a specific user.
     */
    /**
     * InitializeUserPreferences
     */
    protected function initializeUserPreferences(User $user, bool $force, bool $dryRun): string
    {
        // Check if user already has preferences
        $existingPreferences = UserPreference::where('user_id', $user->id)->count();

        if ($existingPreferences > 0 && ! $force) {
            return 'skipped';
        }

        if ($dryRun) {
            if ($existingPreferences > 0) {
                $this->line("  Would reinitialize {$existingPreferences} preferences for {$user->email}");
            } else {
                $this->line("  Would initialize default preferences for {$user->email}");
            }

            return 'initialized';
        }

        // Remove existing preferences if force mode
        if ($force && $existingPreferences > 0) {
            UserPreference::where('user_id', $user->id)->delete();
        }

        // Initialize default preferences
        UserPreference::initializeDefaults($user->id);

        return 'initialized';
    }

    /**
     * Show detailed help information.
     */
    /**
     * ShowHelp
     */
    protected function showHelp(): void
    {
        $this->info('Enhanced Alert Preferences Initialization');
        $this->line('');
        $this->line('This command initializes default preferences for the enhanced alert system.');
        $this->line('');
        $this->line('Options:');
        $this->line('  --user=ID    Initialize preferences for specific user ID');
        $this->line('  --force      Reinitialize existing preferences (destructive)');
        $this->line('  --dry-run    Show what would be done without making changes');
        $this->line('');
        $this->line('Examples:');
        $this->line('  php artisan enhanced-alerts:init-preferences');
        $this->line('  php artisan enhanced-alerts:init-preferences --user=123');
        $this->line('  php artisan enhanced-alerts:init-preferences --force --dry-run');
    }
}
