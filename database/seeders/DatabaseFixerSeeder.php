<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Category;
use App\Models\Ticket;

class DatabaseFixerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting database structure and content fixes...');
        
        // Fix and populate users
        $this->fixUsers();
        
        // Fix and populate categories
        $this->fixCategories();
        
        // Fix any existing tickets
        $this->fixTickets();
        
        // Create sample data if needed
        $this->createSampleData();
        
        $this->command->info('Database fixes completed successfully!');
    }
    
    private function fixUsers()
    {
        $this->command->info('Fixing users table...');
        
        // Update existing users to have proper UUIDs
        $usersWithoutUuid = DB::table('users')->whereNull('uuid')->get();
        foreach ($usersWithoutUuid as $user) {
            DB::table('users')->where('id', $user->id)->update([
                'uuid' => Str::uuid()->toString()
            ]);
        }
        
        // Ensure we have an admin user
        $adminUser = User::where('email', 'admin@hdtickets.com')->first();
        if (!$adminUser) {
            User::create([
                'uuid' => Str::uuid(),
                'name' => 'System Administrator',
                'email' => 'admin@hdtickets.com',
                'password' => Hash::make('admin123'),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $this->command->info('Created admin user: admin@hdtickets.com / admin123');
        }
        
        // Ensure we have an agent user
        $agentUser = User::where('email', 'agent@hdtickets.com')->first();
        if (!$agentUser) {
            User::create([
                'uuid' => Str::uuid(),
                'name' => 'Support Agent',
                'email' => 'agent@hdtickets.com',
                'password' => Hash::make('agent123'),
                'role' => User::ROLE_AGENT,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $this->command->info('Created agent user: agent@hdtickets.com / agent123');
        }
        
        // Ensure test user is properly configured
        $testUser = User::where('email', 'test@example.com')->first();
        if ($testUser && !$testUser->uuid) {
            $testUser->update([
                'uuid' => Str::uuid(),
                'email_verified_at' => now(),
            ]);
        }
    }
    
    private function fixCategories()
    {
        $this->command->info('Fixing categories table...');
        
        // Update existing categories to have proper UUIDs and structure
        $categoriesWithoutUuid = DB::table('categories')->whereNull('uuid')->get();
        foreach ($categoriesWithoutUuid as $category) {
            DB::table('categories')->where('id', $category->id)->update([
                'uuid' => Str::uuid()->toString(),
                'sort_order' => 0
            ]);
        }
        
        // Create default categories if none exist
        if (Category::count() === 0) {
            $categories = [
                [
                    'name' => 'Technical Support',
                    'slug' => 'technical-support',
                    'description' => 'Technical issues and troubleshooting',
                    'color' => '#dc2626',
                    'icon' => 'wrench',
                    'sort_order' => 1,
                ],
                [
                    'name' => 'Billing & Account',
                    'slug' => 'billing-account',
                    'description' => 'Billing questions and account management',
                    'color' => '#059669',
                    'icon' => 'credit-card',
                    'sort_order' => 2,
                ],
                [
                    'name' => 'Feature Request',
                    'slug' => 'feature-request',
                    'description' => 'Suggestions for new features',
                    'color' => '#7c3aed',
                    'icon' => 'light-bulb',
                    'sort_order' => 3,
                ],
                [
                    'name' => 'General Inquiry',
                    'slug' => 'general-inquiry',
                    'description' => 'General questions and information',
                    'color' => '#0ea5e9',
                    'icon' => 'question-mark-circle',
                    'sort_order' => 4,
                ],
                [
                    'name' => 'Bug Report',
                    'slug' => 'bug-report',
                    'description' => 'Report software bugs and issues',
                    'color' => '#ea580c',
                    'icon' => 'bug',
                    'sort_order' => 5,
                ]
            ];
            
            foreach ($categories as $categoryData) {
                Category::create(array_merge($categoryData, [
                    'uuid' => Str::uuid(),
                    'is_active' => true,
                ]));
            }
            
            $this->command->info('Created ' . count($categories) . ' default categories');
        }
    }
    
    private function fixTickets()
    {
        $this->command->info('Fixing tickets table...');
        
        // Update existing tickets to have proper UUIDs and last_activity_at
        $ticketsWithoutUuid = DB::table('tickets')->whereNull('uuid')->get();
        foreach ($ticketsWithoutUuid as $ticket) {
            DB::table('tickets')->where('id', $ticket->id)->update([
                'uuid' => Str::uuid()->toString()
            ]);
        }
        
        DB::table('tickets')->whereNull('last_activity_at')->update([
            'last_activity_at' => DB::raw('created_at'),
        ]);
        
        // Fix any tickets with missing required fields
        DB::table('tickets')
            ->whereNull('status')
            ->update(['status' => 'open']);
            
        DB::table('tickets')
            ->whereNull('priority')
            ->update(['priority' => 'medium']);
    }
    
    private function createSampleData()
    {
        $this->command->info('Creating sample data...');
        
        // Get users and categories
        $testUser = User::where('email', 'test@example.com')->first();
        $adminUser = User::where('role', User::ROLE_ADMIN)->first();
        $categories = Category::take(3)->get();
        
        if ($testUser && $categories->count() > 0 && Ticket::count() < 5) {
            $sampleTickets = [
                [
                    'title' => 'Login Issues with Mobile App',
                    'description' => 'I am unable to log into the mobile application. When I enter my credentials, I get an error message saying "Invalid credentials" even though I know they are correct.',
                    'status' => 'open',
                    'priority' => 'high',
                    'category_id' => $categories[0]->id ?? null,
                ],
                [
                    'title' => 'Feature Request: Dark Mode',
                    'description' => 'Would it be possible to add a dark mode theme to the application? This would be very helpful for users who work in low-light environments.',
                    'status' => 'open',
                    'priority' => 'low',
                    'category_id' => $categories[2]->id ?? null,
                ],
                [
                    'title' => 'Billing Discrepancy',
                    'description' => 'I noticed an extra charge on my account this month that I do not recognize. Can you please help me understand what this charge is for?',
                    'status' => 'in_progress',
                    'priority' => 'medium',
                    'category_id' => $categories[1]->id ?? null,
                    'assignee_id' => $adminUser->id ?? null,
                ],
            ];
            
            foreach ($sampleTickets as $ticketData) {
                Ticket::create(array_merge($ticketData, [
                    'uuid' => Str::uuid(),
                    'requester_id' => $testUser->id,
                    'last_activity_at' => now(),
                ]));
            }
            
            $this->command->info('Created ' . count($sampleTickets) . ' sample tickets');
        }
    }
}
