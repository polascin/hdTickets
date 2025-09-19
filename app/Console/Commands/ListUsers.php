<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListUsers extends Command
{
    /** The name and signature of the console command. */
    protected $signature = 'users:list
                            {--role= : Filter by role (admin, agent, customer, scraper)}
                            {--active= : Filter by active status (1 or 0)}
                            {--limit=100 : Limit number of rows (0 for no limit)}
                            {--columns=id,name,username,email,role,is_active,email_verified_at,created_at : Comma-separated columns to display}
                            {--json : Output as JSON instead of a table}';

    /** The console command description. */
    protected $description = 'List users in the database';

    public function handle(): int
    {
        $columns = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('columns')))));
        if (empty($columns)) {
            $columns = ['id', 'name', 'username', 'email', 'role', 'is_active', 'email_verified_at', 'created_at'];
        }

        $query = User::query();

        if (($role = $this->option('role')) !== null && $role !== '') {
            $query->where('role', (string) $role);
        }

        if (($active = $this->option('active')) !== null && $active !== '') {
            $query->where('is_active', (bool) ((int) $active));
        }

        $query->orderBy('id');

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $query->limit($limit);
        }

        // Ensure selected columns exist; if a column doesn't exist, Eloquent will error.
        // To avoid that, fetch all and map only known attributes in presentation layer.
        $users = $query->get();

        if ($this->option('json')) {
            $data = $users->map(function (User $u) use ($columns) {
                $row = [];
                foreach ($columns as $col) {
                    $row[$col] = data_get($u, $col);
                }

                return $row;
            });
            $this->line((string) json_encode($data, JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        if ($users->isEmpty()) {
            $this->warn('No users found.');

            return self::SUCCESS;
        }

        $rows = $users->map(function (User $u) use ($columns) {
            $row = [];
            foreach ($columns as $col) {
                $val = data_get($u, $col);
                if (is_bool($val)) {
                    $val = $val ? 'YES' : 'NO';
                }
                if ($val instanceof \DateTimeInterface) {
                    $val = $val->format('Y-m-d H:i:s');
                }
                $row[$col] = $val;
            }

            return $row;
        })->toArray();

        $this->table(array_map('strtoupper', $columns), $rows);

        return self::SUCCESS;
    }
}