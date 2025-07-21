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
        // Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@hdtickets.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        // Create Agent User
        User::create([
            'name' => 'Agent User',
            'email' => 'agent@hdtickets.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => User::ROLE_AGENT,
        ]);

        // Create Customer User
        User::create([
            'name' => 'Customer User',
            'email' => 'customer@hdtickets.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        // Create additional sample users
        User::create([
            'name' => 'John Agent',
            'email' => 'john.agent@hdtickets.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => User::ROLE_AGENT,
        ]);

        User::create([
            'name' => 'Jane Customer',
            'email' => 'jane.customer@hdtickets.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);
    }
}
