<?php

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
        // Create root admin user (ticketmaster)
        User::create([
            'name' => 'ticketmaster',
            'surname' => 'admin',
            'username' => 'ticketmaster',
            'email' => 'ticketmaster@hdtickets.admin',
            'email_verified_at' => now(),
            'password' => Hash::make('SecureAdminPass123!'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        // Create Admin User
        User::create([
            'name' => 'Admin',
            'surname' => 'User',
            'username' => 'admin.user',
            'email' => 'admin@hdtickets.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        // Create Agent User
        User::create([
            'name' => 'Agent',
            'surname' => 'User',
            'username' => 'agent.user',
            'email' => 'agent@hdtickets.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => User::ROLE_AGENT,
        ]);

        // Create Customer User
        User::create([
            'name' => 'Customer',
            'surname' => 'User',
            'username' => 'customer.user',
            'email' => 'customer@hdtickets.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        // Create additional sample users
        User::create([
            'name' => 'John',
            'surname' => 'Agent',
            'username' => 'john.agent',
            'email' => 'john.agent@hdtickets.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => User::ROLE_AGENT,
        ]);

        User::create([
            'name' => 'Jane',
            'surname' => 'Customer',
            'username' => 'jane.customer',
            'email' => 'jane.customer@hdtickets.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);
        // Create 1000+ fake users for testing
        User::factory()->count(1000)->create();
    }
}
