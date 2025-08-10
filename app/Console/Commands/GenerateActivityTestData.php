<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\TicketAlert;
use App\Models\PurchaseAttempt;
use App\Models\PurchaseQueue;
use App\Models\UserFavoriteTeam;
use App\Models\UserFavoriteVenue;
use App\Models\ScrapedTicket;
use App\Models\UserPreference;
use App\Models\LoginHistory;
use App\Models\UserSession;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateActivityTestData extends Command
{
    protected $signature = 'activity:generate-test-data {user_id? : ID of the user to generate data for}';
    
    protected $description = 'Generate sample activity data for testing the activity dashboard';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found!");
                return 1;
            }
            $users = collect([$user]);
        } else {
            // Generate for first 5 users or create test users
            $users = User::limit(5)->get();
            if ($users->isEmpty()) {
                $this->info('No users found. Creating test users...');
                $users = $this->createTestUsers();
            }
        }

        foreach ($users as $user) {
            $this->info("Generating activity data for user: {$user->name} (ID: {$user->id})");
            
            $this->generateTicketAlerts($user);
            $this->generatePurchaseData($user);
            $this->generateFavoriteTeams($user);
            $this->generateFavoriteVenues($user);
            $this->generateScrapedTickets($user);
            $this->generateUserPreferences($user);
            $this->generateLoginHistory($user);
            $this->generateUserSessions($user);
            
            $this->info("✅ Activity data generated for {$user->name}");
        }

        $this->info('🎉 Activity dashboard test data generation completed!');
        
        if (!$userId) {
            $this->info('You can now visit the activity dashboard for any of these users:');
            foreach ($users as $user) {
                $this->line("  - {$user->name}: /profile/activity");
            }
        }

        return 0;
    }

    private function createTestUsers()
    {
        $users = collect();
        
        for ($i = 1; $i <= 3; $i++) {
            $user = User::create([
                'name' => "Test User {$i}",
                'email' => "testuser{$i}@hdtickets.com",
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'role' => 'customer'
            ]);
            $users->push($user);
        }
        
        return $users;
    }

    private function generateTicketAlerts(User $user)
    {
        $events = [
            'Manchester United vs Arsenal',
            'Taylor Swift Concert',
            'NBA Finals Game 4',
            'Premier League Final',
            'Wimbledon Tennis Championship',
            'Formula 1 Monaco Grand Prix',
            'Super Bowl LVIII',
            'Champions League Final'
        ];

        for ($i = 0; $i < rand(5, 15); $i++) {
            $createdAt = Carbon::now()->subDays(rand(1, 60));
            $status = collect(['active', 'paused', 'expired'])->random();
            $matchesFound = rand(0, 50);
            
            $alert = TicketAlert::create([
                'user_id' => $user->id,
                'alert_name' => $events[array_rand($events)],
                'max_price' => rand(50, 500),
                'min_price' => rand(10, 100),
                'min_quantity' => rand(1, 4),
                'preferred_sections' => ['VIP', 'Lower Bowl', 'Upper Tier'],
                'platforms' => ['stubhub', 'ticketmaster', 'viagogo'],
                'status' => $status,
                'priority_score' => rand(1, 10),
                'matches_found' => $matchesFound,
                'email_notifications' => true,
                'sms_notifications' => rand(0, 1) == 1,
                'auto_purchase' => rand(0, 1) == 1,
                'created_at' => $createdAt,
                'updated_at' => $createdAt
            ]);

            // Generate some triggers
            if ($matchesFound > 0) {
                $alert->update([
                    'triggered_at' => $createdAt->addHours(rand(1, 24)),
                    'last_checked_at' => Carbon::now()->subMinutes(rand(5, 1440))
                ]);
            }
        }
    }

    private function generatePurchaseData(User $user)
    {
        // Create purchase queues
        for ($i = 0; $i < rand(3, 8); $i++) {
            $createdAt = Carbon::now()->subDays(rand(1, 90));
            
            $queue = PurchaseQueue::create([
                'user_id' => $user->id,
                'uuid' => Str::uuid(),
                'name' => "Purchase Queue " . ($i + 1),
                'max_total_price' => rand(200, 1000),
                'auto_purchase' => rand(0, 1) == 1,
                'status' => collect(['pending', 'processing', 'completed', 'failed'])->random(),
                'priority' => rand(1, 5),
                'created_at' => $createdAt,
                'updated_at' => $createdAt
            ]);

            // Generate purchase attempts for each queue
            for ($j = 0; $j < rand(1, 5); $j++) {
                $attemptCreatedAt = $createdAt->addHours(rand(1, 48));
                $status = collect([
                    PurchaseAttempt::STATUS_SUCCESS,
                    PurchaseAttempt::STATUS_FAILED,
                    PurchaseAttempt::STATUS_PENDING
                ])->random();

                $totalPaid = $status === PurchaseAttempt::STATUS_SUCCESS ? rand(50, 800) : 0;
                $fees = $totalPaid > 0 ? $totalPaid * 0.1 : 0;
                $finalPrice = $totalPaid - $fees;

                PurchaseAttempt::create([
                    'uuid' => Str::uuid(),
                    'purchase_queue_id' => $queue->id,
                    'status' => $status,
                    'platform' => collect(['stubhub', 'ticketmaster', 'viagogo', 'seatgeek'])->random(),
                    'attempted_price' => rand(40, 600),
                    'attempted_quantity' => rand(1, 4),
                    'transaction_id' => $status === PurchaseAttempt::STATUS_SUCCESS ? 'TXN_' . Str::random(10) : null,
                    'confirmation_number' => $status === PurchaseAttempt::STATUS_SUCCESS ? 'CONF_' . Str::random(8) : null,
                    'final_price' => $finalPrice,
                    'fees' => $fees,
                    'total_paid' => $totalPaid,
                    'started_at' => $attemptCreatedAt,
                    'completed_at' => $status !== PurchaseAttempt::STATUS_PENDING ? $attemptCreatedAt->addMinutes(rand(5, 60)) : null,
                    'retry_count' => rand(0, 3),
                    'created_at' => $attemptCreatedAt,
                    'updated_at' => $attemptCreatedAt
                ]);
            }
        }
    }

    private function generateFavoriteTeams(User $user)
    {
        $teams = [
            ['name' => 'Manchester United', 'city' => 'Manchester', 'sport' => 'soccer', 'league' => 'Premier League'],
            ['name' => 'Lakers', 'city' => 'Los Angeles', 'sport' => 'basketball', 'league' => 'NBA'],
            ['name' => 'Patriots', 'city' => 'New England', 'sport' => 'football', 'league' => 'NFL'],
            ['name' => 'Yankees', 'city' => 'New York', 'sport' => 'baseball', 'league' => 'MLB'],
            ['name' => 'Arsenal', 'city' => 'London', 'sport' => 'soccer', 'league' => 'Premier League'],
            ['name' => 'Chelsea', 'city' => 'London', 'sport' => 'soccer', 'league' => 'Premier League'],
        ];

        foreach (array_slice(array_shuffle($teams), 0, rand(3, 5)) as $team) {
            UserFavoriteTeam::create([
                'user_id' => $user->id,
                'sport_type' => $team['sport'],
                'team_name' => $team['name'],
                'team_slug' => Str::slug($team['city'] . ' ' . $team['name']),
                'league' => $team['league'],
                'team_city' => $team['city'],
                'aliases' => [$team['name'], $team['city'] . ' ' . $team['name']],
                'email_alerts' => rand(0, 1) == 1,
                'push_alerts' => rand(0, 1) == 1,
                'sms_alerts' => rand(0, 1) == 1,
                'priority' => rand(1, 5)
            ]);
        }
    }

    private function generateFavoriteVenues(User $user)
    {
        $venues = [
            ['name' => 'Old Trafford', 'city' => 'Manchester', 'country' => 'UK', 'capacity' => 74140],
            ['name' => 'Wembley Stadium', 'city' => 'London', 'country' => 'UK', 'capacity' => 90000],
            ['name' => 'Madison Square Garden', 'city' => 'New York', 'country' => 'USA', 'capacity' => 20789],
            ['name' => 'Staples Center', 'city' => 'Los Angeles', 'country' => 'USA', 'capacity' => 21000],
            ['name' => 'Emirates Stadium', 'city' => 'London', 'country' => 'UK', 'capacity' => 60704],
        ];

        foreach (array_slice(array_shuffle($venues), 0, rand(2, 4)) as $venue) {
            UserFavoriteVenue::create([
                'user_id' => $user->id,
                'venue_name' => $venue['name'],
                'venue_slug' => Str::slug($venue['name']),
                'city' => $venue['city'],
                'country' => $venue['country'],
                'capacity' => $venue['capacity'],
                'venue_types' => ['stadium', 'arena'],
                'aliases' => [$venue['name']],
                'email_alerts' => rand(0, 1) == 1,
                'push_alerts' => rand(0, 1) == 1,
                'sms_alerts' => rand(0, 1) == 1,
                'priority' => rand(1, 5)
            ]);
        }
    }

    private function generateScrapedTickets(User $user)
    {
        $events = [
            'Manchester United vs Arsenal - Old Trafford',
            'Taylor Swift Eras Tour - Wembley Stadium',
            'NBA Finals Game 4 - Madison Square Garden',
            'Champions League Final - Wembley Stadium',
            'Wimbledon Tennis Championship - All England Club'
        ];

        // Generate tickets for the last 60 days
        for ($day = 60; $day >= 0; $day--) {
            $date = Carbon::now()->subDays($day);
            
            // Generate 5-15 tickets per day
            for ($i = 0; $i < rand(5, 15); $i++) {
                $basePrice = rand(50, 400);
                $fees = $basePrice * rand(10, 25) / 100;
                
                ScrapedTicket::create([
                    'event_title' => $events[array_rand($events)],
                    'venue' => collect(['Old Trafford', 'Wembley Stadium', 'Madison Square Garden'])->random(),
                    'event_date' => $date->addDays(rand(7, 180)),
                    'section' => collect(['VIP', 'Lower Bowl', 'Upper Tier', 'General Admission'])->random(),
                    'row' => rand(1, 30),
                    'seat_numbers' => [rand(1, 20), rand(21, 40)],
                    'quantity_available' => rand(1, 8),
                    'price_per_ticket' => $basePrice,
                    'fees_per_ticket' => $fees,
                    'total_price' => ($basePrice + $fees) * rand(1, 4),
                    'platform' => collect(['stubhub', 'ticketmaster', 'viagogo', 'seatgeek'])->random(),
                    'listing_url' => 'https://example.com/ticket/' . Str::random(10),
                    'seller_notes' => 'Great seats with excellent view',
                    'delivery_method' => collect(['mobile', 'pickup', 'mail'])->random(),
                    'sale_ends_at' => $date->addHours(rand(1, 72)),
                    'scraped_at' => $date,
                    'created_at' => $date,
                    'updated_at' => $date
                ]);
            }
        }
    }

    private function generateUserPreferences(User $user)
    {
        $searchTerms = [
            'Manchester United tickets',
            'Taylor Swift concert',
            'NBA Finals',
            'Premier League',
            'Wimbledon tickets'
        ];

        // Generate search preferences
        foreach (array_slice($searchTerms, 0, rand(3, 5)) as $index => $term) {
            UserPreference::create([
                'user_id' => $user->id,
                'preference_category' => 'searches',
                'preference_key' => 'saved_search_' . ($index + 1),
                'preference_value' => $term,
                'data_type' => 'string'
            ]);
        }

        // Generate notification preferences
        UserPreference::create([
            'user_id' => $user->id,
            'preference_category' => 'notifications',
            'preference_key' => 'email_alerts',
            'preference_value' => (string) rand(0, 1),
            'data_type' => 'boolean'
        ]);

        UserPreference::create([
            'user_id' => $user->id,
            'preference_category' => 'notifications',
            'preference_key' => 'push_alerts',
            'preference_value' => (string) rand(0, 1),
            'data_type' => 'boolean'
        ]);
    }

    private function generateLoginHistory(User $user)
    {
        // Generate login history for the past 90 days
        for ($day = 90; $day >= 0; $day--) {
            $date = Carbon::now()->subDays($day);
            
            // Skip some days randomly
            if (rand(1, 4) === 1) continue;
            
            // Generate 1-3 login attempts per active day
            for ($i = 0; $i < rand(1, 3); $i++) {
                $successful = rand(1, 10) > 1; // 90% success rate
                
                LoginHistory::create([
                    'user_id' => $user->id,
                    'ip_address' => collect(['192.168.1.1', '10.0.0.1', '172.16.0.1'])->random(),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'successful' => $successful,
                    'failure_reason' => $successful ? null : 'Invalid credentials',
                    'created_at' => $date->addHours(rand(8, 22))->addMinutes(rand(0, 59)),
                ]);
            }
        }
    }

    private function generateUserSessions(User $user)
    {
        // Generate session data for the past 30 days
        for ($day = 30; $day >= 0; $day--) {
            $date = Carbon::now()->subDays($day);
            
            // Skip some days
            if (rand(1, 3) === 1) continue;
            
            $sessionStart = $date->addHours(rand(8, 22));
            $sessionDuration = rand(10, 240); // 10 minutes to 4 hours
            
            UserSession::create([
                'user_id' => $user->id,
                'session_id' => Str::random(40),
                'ip_address' => collect(['192.168.1.1', '10.0.0.1', '172.16.0.1'])->random(),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'last_activity' => $sessionStart->addMinutes($sessionDuration),
                'created_at' => $sessionStart,
                'updated_at' => $sessionStart->addMinutes($sessionDuration)
            ]);
        }
    }
}
