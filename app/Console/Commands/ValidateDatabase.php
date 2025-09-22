<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use function count;

class ValidateDatabase extends Command
{
    /** The name and signature of the console command. */
    protected $signature = 'db:validate {--fix : Fix found issues}';

    /** The console command description. */
    protected $description = 'Validate database structure and content integrity';

    /**
     * Execute the console command.
     */
    /**
     * Handle
     */
    public function handle(): int
    {
        $this->info('🔍 Validating database structure and content...');

        $issues = [];

        // Check table structures
        $issues = array_merge($issues, $this->checkTableStructures());

        // Check data integrity
        $issues = array_merge($issues, $this->checkDataIntegrity());

        // Check relationships
        $issues = array_merge($issues, $this->checkRelationships());

        if ($issues === []) {
            $this->info('✅ Database validation completed successfully! No issues found.');

            return Command::SUCCESS;
        }

        $this->warn('⚠️  Found ' . count($issues) . ' issues:');
        foreach ($issues as $issue) {
            $this->line('  • ' . $issue);
        }

        if ($this->option('fix')) {
            $this->info('🔧 Attempting to fix issues...');
            $this->fixIssues();
        } else {
            $this->info('💡 Run with --fix to attempt automatic repairs');
        }

        return Command::FAILURE;
    }

    /**
     * Check required database table structures.
     *
     * @return array<int, string>
     */
    /**
     * CheckTableStructures
     */
    private function checkTableStructures(): array
    {
        $issues = [];

        $requiredTables = [
            'users'              => ['id', 'uuid', 'name', 'email', 'role', 'is_active'],
            'categories'         => ['id', 'uuid', 'name', 'slug', 'is_active', 'sort_order'],
            'tickets'            => ['id', 'uuid', 'title', 'description', 'status', 'priority', 'requester_id', 'last_activity_at'],
            'ticket_comments'    => ['id', 'uuid', 'ticket_id', 'user_id', 'content', 'type', 'is_internal'],
            'ticket_attachments' => ['id', 'uuid', 'ticket_id', 'user_id', 'filename', 'filepath'],
        ];

        foreach ($requiredTables as $table => $columns) {
            if (! Schema::hasTable($table)) {
                $issues[] = "Missing table: {$table}";

                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    $issues[] = "Missing column: {$table}.{$column}";
                }
            }
        }

        return $issues;
    }

    /**
     * Check data integrity across tables.
     *
     * @return array<int, string>
     */
    /**
     * CheckDataIntegrity
     */
    private function checkDataIntegrity(): array
    {
        $issues = [];

        // Check for missing UUIDs
        $tablesWithUuid = ['users', 'categories', 'tickets', 'ticket_comments', 'ticket_attachments'];
        foreach ($tablesWithUuid as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'uuid')) {
                $count = DB::table($table)->whereNull('uuid')->count();
                if ($count > 0) {
                    $issues[] = "{$count} records in {$table} have missing UUIDs";
                }
            }
        }

        // Check for missing required data
        if (User::where('role', User::ROLE_ADMIN)->count() === 0) {
            $issues[] = 'No admin users found';
        }

        if (Category::count() === 0) {
            $issues[] = 'No categories found';
        }

        return $issues;
    }

    /**
     * Check database relationship integrity.
     *
     * @return array<int, string>
     */
    /**
     * CheckRelationships
     */
    private function checkRelationships(): array
    {
        $issues = [];

        // Check for orphaned tickets
        $orphanedTickets = DB::table('tickets')
            ->leftJoin('users', 'tickets.requester_id', '=', 'users.id')
            ->whereNull('users.id')
            ->count();

        if ($orphanedTickets > 0) {
            $issues[] = "{$orphanedTickets} tickets have invalid requester_id";
        }

        // Check for tickets with invalid categories
        $invalidCategoryTickets = DB::table('tickets')
            ->whereNotNull('category_id')
            ->leftJoin('categories', 'tickets.category_id', '=', 'categories.id')
            ->whereNull('categories.id')
            ->count();

        if ($invalidCategoryTickets > 0) {
            $issues[] = "{$invalidCategoryTickets} tickets have invalid category_id";
        }

        return $issues;
    }

    /**
     * Attempt to fix identified issues.
     */
    /**
     * FixIssues
     */
    private function fixIssues(): void
    {
        $this->call('db:seed', ['--class' => 'DatabaseFixerSeeder']);
        $this->call('db:seed', ['--class' => 'FixCommentsAndAttachmentsSeeder']);

        $this->info('✅ Issues fixed successfully!');
    }
}
