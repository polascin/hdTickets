<?php declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use function count;

class MonitorDomainEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'domain-events:monitor 
                           {--check-integrity : Check for constraint violations and gaps}
                           {--check-performance : Check for performance issues}
                           {--fix-gaps : Attempt to fix version gaps}
                           {--aggregate-type= : Filter by specific aggregate type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor domain events for constraint violations and integrity issues';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Domain Events Health Monitor');
        $this->info('==================================');

        if ($this->option('check-integrity')) {
            $this->checkIntegrity();
        }

        if ($this->option('check-performance')) {
            $this->checkPerformance();
        }

        if ($this->option('fix-gaps')) {
            $this->fixVersionGaps();
        }

        if (!$this->option('check-integrity') && !$this->option('check-performance') && !$this->option('fix-gaps')) {
            $this->runFullHealthCheck();
        }

        return 0;
    }

    private function checkIntegrity(): void
    {
        $this->info('📊 Checking Domain Events Integrity...');

        $aggregateType = $this->option('aggregate-type') ?: NULL;
        $whereClause = $aggregateType ? "WHERE aggregate_type = '{$aggregateType}'" : '';

        // Check for duplicate aggregate versions
        $duplicates = DB::select("
            SELECT aggregate_type, aggregate_id, aggregate_version, COUNT(*) as count
            FROM domain_events 
            {$whereClause}
            GROUP BY aggregate_type, aggregate_id, aggregate_version 
            HAVING COUNT(*) > 1
        ");

        if (empty($duplicates)) {
            $this->info('✅ No duplicate aggregate versions found');
        } else {
            $this->error('❌ Found ' . count($duplicates) . ' duplicate aggregate versions:');
            foreach ($duplicates as $dup) {
                $this->line("   - {$dup->aggregate_type}-{$dup->aggregate_id}-{$dup->aggregate_version} ({$dup->count} duplicates)");
            }
        }

        // Check for version gaps
        $gaps = DB::select('
            SELECT DISTINCT
                de1.aggregate_type,
                de1.aggregate_id,
                de1.aggregate_version + 1 as missing_version
            FROM domain_events de1
            LEFT JOIN domain_events de2 ON 
                de1.aggregate_type = de2.aggregate_type 
                AND de1.aggregate_id = de2.aggregate_id 
                AND de1.aggregate_version + 1 = de2.aggregate_version
            WHERE de2.id IS NULL 
                AND de1.aggregate_version < (
                    SELECT MAX(aggregate_version) 
                    FROM domain_events de3 
                    WHERE de3.aggregate_type = de1.aggregate_type 
                        AND de3.aggregate_id = de1.aggregate_id
                )
                ' . ($aggregateType ? "AND de1.aggregate_type = '{$aggregateType}'" : '') . '
            ORDER BY de1.aggregate_type, de1.aggregate_id, missing_version
            LIMIT 20
        ');

        if (empty($gaps)) {
            $this->info('✅ No version gaps found');
        } else {
            $this->warn('⚠️  Found ' . count($gaps) . ' version gaps:');
            foreach ($gaps as $gap) {
                $this->line("   - {$gap->aggregate_type}-{$gap->aggregate_id}: Missing version {$gap->missing_version}");
            }
        }

        // Check aggregate version consistency
        $inconsistencies = DB::select("
            SELECT 
                aggregate_type, 
                aggregate_id,
                COUNT(*) as event_count,
                MAX(aggregate_version) as max_version
            FROM domain_events
            {$whereClause}
            GROUP BY aggregate_type, aggregate_id
            HAVING COUNT(*) != MAX(aggregate_version)
        ");

        if (empty($inconsistencies)) {
            $this->info('✅ All aggregate versions are consistent');
        } else {
            $this->warn('⚠️  Found ' . count($inconsistencies) . ' version inconsistencies:');
            foreach ($inconsistencies as $inc) {
                $this->line("   - {$inc->aggregate_type}-{$inc->aggregate_id}: {$inc->event_count} events but max version is {$inc->max_version}");
            }
        }
    }

    private function checkPerformance(): void
    {
        $this->info('⚡ Checking Domain Events Performance...');

        // Check table size
        $tableSize = DB::selectOne('
            SELECT 
                COUNT(*) as total_events,
                AVG(LENGTH(event_data)) as avg_event_size,
                MAX(occurred_at) as latest_event,
                MIN(occurred_at) as earliest_event
            FROM domain_events
        ');

        $this->info('📊 Table Statistics:');
        $this->line('   - Total events: ' . number_format($tableSize->total_events));
        $this->line('   - Average event size: ' . round($tableSize->avg_event_size) . ' bytes');
        $this->line("   - Event date range: {$tableSize->earliest_event} to {$tableSize->latest_event}");

        // Check for slow aggregates (many events)
        $heavyAggregates = DB::select('
            SELECT 
                aggregate_type, 
                aggregate_id,
                COUNT(*) as event_count,
                MAX(aggregate_version) as max_version
            FROM domain_events
            GROUP BY aggregate_type, aggregate_id
            HAVING COUNT(*) > 100
            ORDER BY event_count DESC
            LIMIT 10
        ');

        if (!empty($heavyAggregates)) {
            $this->warn('⚠️  Aggregates with high event counts (potential performance impact):');
            foreach ($heavyAggregates as $heavy) {
                $this->line("   - {$heavy->aggregate_type}-{$heavy->aggregate_id}: {$heavy->event_count} events (version {$heavy->max_version})");
            }
        }

        // Check recent event creation rate
        $recentEvents = DB::selectOne('
            SELECT 
                COUNT(*) as recent_count,
                COUNT(*) / 24 as events_per_hour
            FROM domain_events
            WHERE occurred_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ');

        $this->info('🕒 Recent Activity (last 24h):');
        $this->line('   - Events created: ' . $recentEvents->recent_count);
        $this->line('   - Events per hour: ' . round($recentEvents->events_per_hour, 2));
    }

    private function fixVersionGaps(): void
    {
        if (!$this->confirm('This will attempt to fix version gaps by renumbering events. Continue?')) {
            return;
        }

        $this->warn('🔧 Attempting to fix version gaps...');
        $this->warn('⚠️  This feature is not yet implemented for safety reasons.');
        $this->info('   To fix gaps manually, you would need to:');
        $this->info('   1. Identify the affected aggregate');
        $this->info('   2. Lock the aggregate for updates');
        $this->info('   3. Renumber the aggregate_version sequence');
        $this->info('   4. Update all events atomically');
        $this->info('   This should only be done during maintenance windows.');
    }

    private function runFullHealthCheck(): void
    {
        $this->info('🏥 Running Full Domain Events Health Check...');
        $this->newLine();

        // Check if the function exists
        $functionExists = DB::selectOne("
            SELECT COUNT(*) as count 
            FROM information_schema.ROUTINES 
            WHERE ROUTINE_SCHEMA = DATABASE() 
                AND ROUTINE_NAME = 'GetNextAggregateVersion'
                AND ROUTINE_TYPE = 'FUNCTION'
        ");

        if ($functionExists->count > 0) {
            $this->info('✅ GetNextAggregateVersion function exists');
        } else {
            $this->error('❌ GetNextAggregateVersion function is missing');
        }

        // Check if the trigger exists
        $triggerExists = DB::selectOne("
            SELECT COUNT(*) as count 
            FROM information_schema.TRIGGERS 
            WHERE TRIGGER_SCHEMA = DATABASE() 
                AND TRIGGER_NAME = 'log_user_changes'
        ");

        if ($triggerExists->count > 0) {
            $this->info('✅ log_user_changes trigger exists');
        } else {
            $this->error('❌ log_user_changes trigger is missing');
        }

        $this->newLine();
        $this->checkIntegrity();
        $this->newLine();
        $this->checkPerformance();

        $this->newLine();
        $this->info('🎯 Health Check Complete');
    }
}
