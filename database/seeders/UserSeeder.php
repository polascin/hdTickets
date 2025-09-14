<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Idempotent seed of fixed accounts
        $fixedUsers = [
            [
                'name'              => 'ticketmaster',
                'surname'           => 'admin',
                'username'          => 'ticketmaster',
                'email'             => 'ticketmaster@hdtickets.admin',
                'email_verified_at' => now(),
                'password'          => Hash::make('SecureAdminPass123!'),
                'role'              => User::ROLE_ADMIN,
                'is_active'         => TRUE,
            ],
            [
                'name'              => 'Admin',
                'surname'           => 'User',
                'username'          => 'admin.user',
                'email'             => 'admin@hdtickets.com',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'role'              => User::ROLE_ADMIN,
            ],
            [
                'name'              => 'Agent',
                'surname'           => 'User',
                'username'          => 'agent.user',
                'email'             => 'agent@hdtickets.com',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'role'              => User::ROLE_AGENT,
            ],
            [
                'name'              => 'Customer',
                'surname'           => 'User',
                'username'          => 'customer.user',
                'email'             => 'customer@hdtickets.com',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'role'              => User::ROLE_CUSTOMER,
            ],
            [
                'name'              => 'John',
                'surname'           => 'Agent',
                'username'          => 'john.agent',
                'email'             => 'john.agent@hdtickets.com',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'role'              => User::ROLE_AGENT,
            ],
            [
                'name'              => 'Jane',
                'surname'           => 'Customer',
                'username'          => 'jane.customer',
                'email'             => 'jane.customer@hdtickets.com',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'role'              => User::ROLE_CUSTOMER,
            ],
        ];

        foreach ($fixedUsers as $data) {
            User::updateOrCreate(
                ['username' => $data['username']],
                $data,
            );
        }

        // Ensure there are at least 1000 total users; create only the difference
        $targetTotal = 1000;
        $currentTotal = User::count();
        $toCreate = max(0, $targetTotal - $currentTotal);
        if ($toCreate > 0) {
            User::factory()->count($toCreate)->create();
        }
    }
}
