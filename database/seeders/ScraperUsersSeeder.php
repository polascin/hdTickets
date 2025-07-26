<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class ScraperUsersSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $this->command->info('Creating 1000+ scraper users for rotation...');
        
        // Create 1200 scraper users for rotation
        for ($i = 1; $i <= 1200; $i++) {
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $username = 'scraper_' . str_pad($i, 4, '0', STR_PAD_LEFT);
            
            User::create([
                'name' => $firstName,
                'surname' => $lastName,
                'username' => $username,
                'email' => $username . '@scraper.hdtickets.fake',
                'password' => Hash::make('scraper_pass_' . $i),
                'role' => User::ROLE_SCRAPER,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            
            if ($i % 100 == 0) {
                $this->command->info("Created {$i} scraper users...");
            }
        }
        
        $this->command->info('✓ Successfully created 1200 scraper users for rotation!');
        $this->command->warn('⚠️  Note: Scraper users have NO system access - they are for rotation only.');
    }
}
