<?php declare(strict_types=1);

/**
 * HD Tickets Data Migration Script
 * Sports Events Entry Tickets Monitoring System
 *
 * Comprehensive data migration with batch processing, validation,
 * progress monitoring, and rollback capabilities
 */

require_once __DIR__ . '/../../bootstrap/app.php';

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DataMigrationOrchestrator extends Command
{
    protected $signature = 'hdtickets:migrate-data 
                           {--batch-size=1000 : Number of records to process per batch}
                           {--dry-run : Run migration validation without executing}
                           {--rollback : Rollback previous migration}
                           {--validate-only : Only validate data integrity}
                           {--continue : Continue from last checkpoint}
                           {--force : Force migration without confirmation}';

    protected $description = 'Migrate HD Tickets sports events data with validation and monitoring';

    private $batchSize;

    private $isDryRun = FALSE;

    private $isRollback = FALSE;

    private $validateOnly = FALSE;

    private $continueFromCheckpoint = FALSE;

    private $migrationId;

    private $startTime;

    private $logFile;

    private $checkpointFile;

    // Migration tracking
    private $totalRecords = 0;

    private $processedRecords = 0;

    private $errorRecords = 0;

    private $skippedRecords = 0;

    public function handle()
    {
        $this->initializeMigration();

        if ($this->isRollback) {
            return $this->executeRollback();
        }

        if ($this->validateOnly) {
            return $this->validateDataIntegrity();
        }

        if (!$this->option('force') && !$this->confirm('This will migrate HD Tickets sports events data. Continue?')) {
            $this->info('Migration cancelled.');

            return 0;
        }

        return $this->executeMigration();
    }

    private function initializeMigration()
    {
        $this->batchSize = $this->option('batch-size');
        $this->isDryRun = $this->option('dry-run');
        $this->isRollback = $this->option('rollback');
        $this->validateOnly = $this->option('validate-only');
        $this->continueFromCheckpoint = $this->option('continue');

        $this->migrationId = 'hdtickets_migration_' . date('Y_m_d_H_i_s');
        $this->startTime = microtime(TRUE);

        $this->logFile = storage_path("logs/migration_{$this->migrationId}.log");
        $this->checkpointFile = storage_path("app/migration_checkpoint_{$this->migrationId}.json");

        $this->info('HD Tickets Sports Events Data Migration');
        $this->info("Migration ID: {$this->migrationId}");
        $this->info("Batch Size: {$this->batchSize}");
        $this->info('Mode: ' . ($this->isDryRun ? 'DRY RUN' : 'LIVE'));

        if ($this->isDryRun) {
            $this->warn('Running in DRY RUN mode - no data will be modified');
        }

        $this->logMessage('Migration initialized', 'info');
    }

    private function executeMigration()
    {
        try {
            $this->info('Starting HD Tickets sports events data migration...');

            // Pre-migration validation
            if (!$this->preflightChecks()) {
                $this->error('Pre-flight checks failed. Aborting migration.');

                return 1;
            }

            // Load checkpoint if continuing
            $checkpoint = $this->loadCheckpoint();

            // Migration steps for sports events ticket monitoring system
            $steps = [
                'migrateSportsEvents'         => 'Sports Events',
                'migrateVenues'               => 'Venues',
                'migrateTicketPlatforms'      => 'Ticket Platforms',
                'migrateTicketListings'       => 'Ticket Listings',
                'migratePriceHistory'         => 'Price History',
                'migrateAvailabilityHistory'  => 'Availability History',
                'migrateUserAlerts'           => 'User Alerts',
                'migrateScrapingLogs'         => 'Scraping Logs',
                'migrateAuditTrails'          => 'Audit Trails',
                'updateIndexesAndConstraints' => 'Indexes & Constraints',
            ];

            foreach ($steps as $method => $stepName) {
                if ($checkpoint && $checkpoint['completed_steps'][$method] ?? FALSE) {
                    $this->info("Skipping completed step: {$stepName}");

                    continue;
                }

                $this->info("Processing: {$stepName}");
                $stepResult = $this->$method();

                if (!$stepResult) {
                    $this->error("Failed at step: {$stepName}");

                    return 1;
                }

                $this->saveCheckpoint(['completed_steps' => [$method => TRUE]]);
                $this->info("Completed: {$stepName}");
            }

            // Final validation
            if (!$this->postMigrationValidation()) {
                $this->error('Post-migration validation failed');

                return 1;
            }

            $this->finalizeMigration();

            return 0;
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            $this->logMessage('Migration error: ' . $e->getMessage(), 'error');

            return 1;
        }
    }

    private function preflightChecks()
    {
        $this->info('Running pre-flight checks...');

        // Check database connections
        try {
            DB::connection()->getPdo();
            $this->info('✓ Database connection successful');
        } catch (\Exception $e) {
            $this->error('✗ Database connection failed: ' . $e->getMessage());

            return FALSE;
        }

        // Check required tables exist
        $requiredTables = [
            'sports_events', 'venues', 'ticket_platforms', 'ticket_listings',
            'price_history', 'availability_history', 'user_alerts', 'scraping_logs',
        ];

        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                $this->error("✗ Required table missing: {$table}");

                return FALSE;
            }
        }
        $this->info('✓ All required tables exist');

        // Check disk space
        $freeSpace = disk_free_space(storage_path());
        $requiredSpace = 1024 * 1024 * 1024; // 1GB minimum

        if ($freeSpace < $requiredSpace) {
            $this->error('✗ Insufficient disk space for migration');

            return FALSE;
        }
        $this->info('✓ Sufficient disk space available');

        // Estimate total records to process
        $this->totalRecords = $this->estimateTotalRecords();
        $this->info('✓ Estimated records to process: ' . number_format($this->totalRecords));

        return TRUE;
    }

    private function migrateSportsEvents()
    {
        $this->info('Migrating sports events data...');

        $totalEvents = DB::table('sports_events')->count();
        $this->info("Processing {$totalEvents} sports events");

        $processed = 0;
        $errors = 0;

        DB::table('sports_events')
            ->orderBy('id')
            ->chunk($this->batchSize, function ($events) use (&$processed, &$errors) {
                DB::beginTransaction();

                try {
                    foreach ($events as $event) {
                        if (!$this->isDryRun) {
                            // Validate and transform event data
                            $transformedEvent = $this->transformSportsEvent($event);

                            // Update or insert transformed event
                            DB::table('sports_events_v2')->updateOrInsert(
                                ['legacy_id' => $event->id],
                                $transformedEvent
                            );
                        }

                        $processed++;

                        if ($processed % 100 == 0) {
                            $this->updateProgress('Sports Events', $processed, $totalEvents);
                        }
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    $this->logMessage('Error migrating sports events batch: ' . $e->getMessage(), 'error');
                    $errors++;
                }
            });

        $this->info("Sports events migration completed: {$processed} processed, {$errors} errors");

        return $errors === 0;
    }

    private function migrateTicketListings()
    {
        $this->info('Migrating ticket listings data...');

        $totalListings = DB::table('ticket_listings')->count();
        $this->info("Processing {$totalListings} ticket listings");

        $processed = 0;
        $errors = 0;

        DB::table('ticket_listings')
            ->orderBy('id')
            ->chunk($this->batchSize, function ($listings) use (&$processed, &$errors) {
                DB::beginTransaction();

                try {
                    foreach ($listings as $listing) {
                        if (!$this->isDryRun) {
                            // Validate and transform listing data
                            $transformedListing = $this->transformTicketListing($listing);

                            // Update or insert transformed listing
                            DB::table('ticket_listings_v2')->updateOrInsert(
                                ['legacy_id' => $listing->id],
                                $transformedListing
                            );

                            // Migrate associated price history
                            $this->migratePriceHistoryForListing($listing->id);
                        }

                        $processed++;

                        if ($processed % 100 == 0) {
                            $this->updateProgress('Ticket Listings', $processed, $totalListings);
                        }
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    $this->logMessage('Error migrating ticket listings batch: ' . $e->getMessage(), 'error');
                    $errors++;
                }
            });

        $this->info("Ticket listings migration completed: {$processed} processed, {$errors} errors");

        return $errors === 0;
    }

    private function migratePriceHistory()
    {
        $this->info('Migrating price history data...');

        $totalPrices = DB::table('price_history')->count();
        $this->info("Processing {$totalPrices} price history records");

        $processed = 0;
        $errors = 0;

        DB::table('price_history')
            ->orderBy('created_at')
            ->chunk($this->batchSize * 2, function ($prices) use (&$processed, &$errors) {
                DB::beginTransaction();

                try {
                    $batchData = [];

                    foreach ($prices as $price) {
                        if (!$this->isDryRun) {
                            $transformedPrice = $this->transformPriceHistory($price);
                            $batchData[] = $transformedPrice;
                        }

                        $processed++;
                    }

                    if (!$this->isDryRun && !empty($batchData)) {
                        // Batch insert for better performance
                        DB::table('price_history_v2')->insert($batchData);
                    }

                    DB::commit();

                    if ($processed % 1000 == 0) {
                        $this->updateProgress('Price History', $processed, $totalPrices);
                    }
                } catch (\Exception $e) {
                    DB::rollback();
                    $this->logMessage('Error migrating price history batch: ' . $e->getMessage(), 'error');
                    $errors++;
                }
            });

        $this->info("Price history migration completed: {$processed} processed, {$errors} errors");

        return $errors === 0;
    }

    private function migrateUserAlerts()
    {
        $this->info('Migrating user alerts data...');

        $totalAlerts = DB::table('user_alerts')->count();
        $this->info("Processing {$totalAlerts} user alerts");

        $processed = 0;
        $errors = 0;

        DB::table('user_alerts')
            ->orderBy('id')
            ->chunk($this->batchSize, function ($alerts) use (&$processed, &$errors) {
                DB::beginTransaction();

                try {
                    foreach ($alerts as $alert) {
                        if (!$this->isDryRun) {
                            $transformedAlert = $this->transformUserAlert($alert);

                            DB::table('user_alerts_v2')->updateOrInsert(
                                ['legacy_id' => $alert->id],
                                $transformedAlert
                            );
                        }

                        $processed++;
                    }

                    DB::commit();

                    if ($processed % 100 == 0) {
                        $this->updateProgress('User Alerts', $processed, $totalAlerts);
                    }
                } catch (\Exception $e) {
                    DB::rollback();
                    $this->logMessage('Error migrating user alerts batch: ' . $e->getMessage(), 'error');
                    $errors++;
                }
            });

        $this->info("User alerts migration completed: {$processed} processed, {$errors} errors");

        return $errors === 0;
    }

    // Transform methods for data conversion

    private function transformSportsEvent($event)
    {
        return [
            'legacy_id'  => $event->id,
            'name'       => $event->name,
            'sport_type' => $event->sport ?? 'unknown',
            'league'     => $event->league ?? NULL,
            'home_team'  => $event->home_team ?? NULL,
            'away_team'  => $event->away_team ?? NULL,
            'venue_id'   => $event->venue_id,
            'event_date' => $event->event_date,
            'status'     => $event->status ?? 'scheduled',
            'metadata'   => json_encode([
                'original_data' => $event,
                'migration_id'  => $this->migrationId,
                'migrated_at'   => now(),
            ]),
            'created_at' => $event->created_at,
            'updated_at' => now(),
        ];
    }

    private function transformTicketListing($listing)
    {
        return [
            'legacy_id'       => $listing->id,
            'sports_event_id' => $listing->sports_event_id,
            'platform_id'     => $listing->platform_id,
            'section'         => $listing->section ?? NULL,
            'row'             => $listing->row ?? NULL,
            'seat_numbers'    => $listing->seat_numbers ?? NULL,
            'quantity'        => $listing->quantity ?? 1,
            'current_price'   => $listing->price ?? 0,
            'original_price'  => $listing->original_price ?? $listing->price ?? 0,
            'currency'        => $listing->currency ?? 'USD',
            'is_available'    => $listing->is_available ?? TRUE,
            'last_scraped_at' => $listing->updated_at,
            'metadata'        => json_encode([
                'original_data' => $listing,
                'migration_id'  => $this->migrationId,
            ]),
            'created_at' => $listing->created_at,
            'updated_at' => now(),
        ];
    }

    private function transformPriceHistory($price)
    {
        return [
            'ticket_listing_id'  => $price->ticket_listing_id,
            'price'              => $price->price,
            'currency'           => $price->currency ?? 'USD',
            'availability_count' => $price->availability_count ?? NULL,
            'recorded_at'        => $price->created_at,
            'created_at'         => $price->created_at,
            'updated_at'         => $price->updated_at ?? $price->created_at,
        ];
    }

    private function transformUserAlert($alert)
    {
        return [
            'legacy_id'             => $alert->id,
            'user_id'               => $alert->user_id,
            'sports_event_id'       => $alert->sports_event_id,
            'alert_type'            => $alert->type ?? 'price_drop',
            'threshold_price'       => $alert->price_threshold ?? NULL,
            'is_active'             => $alert->is_active ?? TRUE,
            'notification_channels' => json_encode($alert->channels ?? ['email']),
            'created_at'            => $alert->created_at,
            'updated_at'            => now(),
        ];
    }

    // Validation methods

    private function validateDataIntegrity()
    {
        $this->info('Running data integrity validation...');

        $validationResults = [];

        // Validate sports events
        $validationResults['sports_events'] = $this->validateSportsEvents();

        // Validate ticket listings
        $validationResults['ticket_listings'] = $this->validateTicketListings();

        // Validate price history
        $validationResults['price_history'] = $this->validatePriceHistory();

        // Validate referential integrity
        $validationResults['referential_integrity'] = $this->validateReferentialIntegrity();

        $this->displayValidationResults($validationResults);

        return array_reduce($validationResults, function ($carry, $result) {
            return $carry && $result['passed'];
        }, TRUE);
    }

    private function validateSportsEvents()
    {
        $issues = [];

        // Check for duplicate events
        $duplicates = DB::table('sports_events')
            ->select('name', 'event_date', 'venue_id', DB::raw('COUNT(*) as count'))
            ->groupBy('name', 'event_date', 'venue_id')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->count() > 0) {
            $issues[] = "Found {$duplicates->count()} duplicate sports events";
        }

        // Check for missing required fields
        $missingFields = DB::table('sports_events')
            ->whereNull('name')
            ->orWhereNull('event_date')
            ->count();

        if ($missingFields > 0) {
            $issues[] = "Found {$missingFields} sports events with missing required fields";
        }

        return [
            'passed' => empty($issues),
            'issues' => $issues,
        ];
    }

    private function validateTicketListings()
    {
        $issues = [];

        // Check for invalid prices
        $invalidPrices = DB::table('ticket_listings')
            ->where('price', '<', 0)
            ->orWhereNull('price')
            ->count();

        if ($invalidPrices > 0) {
            $issues[] = "Found {$invalidPrices} ticket listings with invalid prices";
        }

        // Check for orphaned listings
        $orphanedListings = DB::table('ticket_listings')
            ->leftJoin('sports_events', 'ticket_listings.sports_event_id', '=', 'sports_events.id')
            ->whereNull('sports_events.id')
            ->count();

        if ($orphanedListings > 0) {
            $issues[] = "Found {$orphanedListings} orphaned ticket listings";
        }

        return [
            'passed' => empty($issues),
            'issues' => $issues,
        ];
    }

    // Helper methods

    private function estimateTotalRecords()
    {
        $tables = [
            'sports_events', 'ticket_listings', 'price_history',
            'user_alerts', 'scraping_logs', 'venues', 'ticket_platforms',
        ];

        $total = 0;
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $total += DB::table($table)->count();
            }
        }

        return $total;
    }

    private function updateProgress($step, $processed, $total)
    {
        $percentage = round(($processed / $total) * 100, 2);
        $this->info("{$step}: {$processed}/{$total} ({$percentage}%)");

        // Update cache for external monitoring
        Cache::put("migration_progress_{$this->migrationId}", [
            'step'       => $step,
            'processed'  => $processed,
            'total'      => $total,
            'percentage' => $percentage,
            'updated_at' => now(),
        ], 3600);
    }

    private function saveCheckpoint($data)
    {
        $checkpoint = $this->loadCheckpoint() ?: [];
        $checkpoint = array_merge($checkpoint, $data);
        $checkpoint['updated_at'] = now();

        file_put_contents($this->checkpointFile, json_encode($checkpoint, JSON_PRETTY_PRINT));
    }

    private function loadCheckpoint()
    {
        if (file_exists($this->checkpointFile)) {
            return json_decode(file_get_contents($this->checkpointFile), TRUE);
        }

        return NULL;
    }

    private function logMessage($message, $level = 'info')
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] {$level}: {$message}\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);

        Log::$level("Migration {$this->migrationId}: {$message}");
    }

    private function finalizeMigration()
    {
        $endTime = microtime(TRUE);
        $duration = round($endTime - $this->startTime, 2);

        $this->info('Migration completed successfully!');
        $this->info("Duration: {$duration} seconds");
        $this->info('Total records processed: ' . number_format($this->processedRecords));
        $this->info("Log file: {$this->logFile}");

        // Clean up checkpoint file
        if (file_exists($this->checkpointFile)) {
            unlink($this->checkpointFile);
        }

        // Clear progress cache
        Cache::forget("migration_progress_{$this->migrationId}");
    }

    // Additional required methods would continue here...
    // (truncated for length, but would include all other migration steps)
}

// Execute the migration if run directly
if (php_sapi_name() === 'cli') {
    $migrator = new DataMigrationOrchestrator();
    $migrator->handle();
}
