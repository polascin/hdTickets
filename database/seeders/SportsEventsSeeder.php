<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SportsEventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = [
            // NFL Games
            [
                'name' => 'Super Bowl LIX',
                'venue' => 'Caesars Superdome',
                'city' => 'New Orleans',
                'country' => 'USA',
                'event_date' => '2025-02-09',
                'event_time' => '18:30:00',
                'category' => 'football',
                'league' => 'NFL',
                'home_team' => 'TBD',
                'away_team' => 'TBD',
                'status' => 'scheduled',
                'is_monitored' => true,
                'ticket_platforms' => json_encode(['ticketmaster', 'stubhub', 'viagogo']),
                'min_price' => 2500.00,
                'max_price' => 15000.00,
                'total_tickets' => 70000,
                'available_tickets' => 45000,
            ],
            [
                'name' => 'Dallas Cowboys vs Philadelphia Eagles',
                'venue' => 'AT&T Stadium',
                'city' => 'Arlington',
                'country' => 'USA',
                'event_date' => '2025-01-12',
                'event_time' => '20:15:00',
                'category' => 'football',
                'league' => 'NFL',
                'home_team' => 'Dallas Cowboys',
                'away_team' => 'Philadelphia Eagles',
                'status' => 'scheduled',
                'is_monitored' => true,
                'ticket_platforms' => json_encode(['ticketmaster', 'stubhub', 'tickpick']),
                'min_price' => 85.00,
                'max_price' => 750.00,
                'total_tickets' => 80000,
                'available_tickets' => 25000,
            ],
            // NBA Games
            [
                'name' => 'Los Angeles Lakers vs Boston Celtics',
                'venue' => 'Crypto.com Arena',
                'city' => 'Los Angeles',
                'country' => 'USA',
                'event_date' => '2025-01-15',
                'event_time' => '22:00:00',
                'category' => 'basketball',
                'league' => 'NBA',
                'home_team' => 'Los Angeles Lakers',
                'away_team' => 'Boston Celtics',
                'status' => 'scheduled',
                'is_monitored' => true,
                'ticket_platforms' => json_encode(['ticketmaster', 'stubhub', 'viagogo', 'tickpick']),
                'min_price' => 125.00,
                'max_price' => 1200.00,
                'total_tickets' => 20000,
                'available_tickets' => 8500,
            ],
            [
                'name' => 'Golden State Warriors vs Miami Heat',
                'venue' => 'Chase Center',
                'city' => 'San Francisco',
                'country' => 'USA',
                'event_date' => '2025-01-18',
                'event_time' => '19:30:00',
                'category' => 'basketball',
                'league' => 'NBA',
                'home_team' => 'Golden State Warriors',
                'away_team' => 'Miami Heat',
                'status' => 'scheduled',
                'is_monitored' => true,
                'ticket_platforms' => json_encode(['ticketmaster', 'stubhub']),
                'min_price' => 95.00,
                'max_price' => 850.00,
                'total_tickets' => 18500,
                'available_tickets' => 12000,
            ],
            // MLB Games
            [
                'name' => 'New York Yankees vs Boston Red Sox',
                'venue' => 'Yankee Stadium',
                'city' => 'New York',
                'country' => 'USA',
                'event_date' => '2025-04-15',
                'event_time' => '19:05:00',
                'category' => 'baseball',
                'league' => 'MLB',
                'home_team' => 'New York Yankees',
                'away_team' => 'Boston Red Sox',
                'status' => 'scheduled',
                'is_monitored' => false,
                'ticket_platforms' => json_encode(['ticketmaster', 'stubhub', 'tickpick']),
                'min_price' => 25.00,
                'max_price' => 350.00,
                'total_tickets' => 54000,
                'available_tickets' => 35000,
            ],
            // Soccer/Football
            [
                'name' => 'Real Madrid vs Barcelona - El Clasico',
                'venue' => 'Santiago Bernabeu',
                'city' => 'Madrid',
                'country' => 'Spain',
                'event_date' => '2025-03-23',
                'event_time' => '21:00:00',
                'category' => 'soccer',
                'league' => 'La Liga',
                'home_team' => 'Real Madrid',
                'away_team' => 'Barcelona',
                'status' => 'scheduled',
                'is_monitored' => true,
                'ticket_platforms' => json_encode(['viagogo', 'stubhub']),
                'min_price' => 180.00,
                'max_price' => 2500.00,
                'total_tickets' => 81000,
                'available_tickets' => 15000,
            ],
            // NHL Games
            [
                'name' => 'Toronto Maple Leafs vs Montreal Canadiens',
                'venue' => 'Scotiabank Arena',
                'city' => 'Toronto',
                'country' => 'Canada',
                'event_date' => '2025-02-14',
                'event_time' => '19:00:00',
                'category' => 'hockey',
                'league' => 'NHL',
                'home_team' => 'Toronto Maple Leafs',
                'away_team' => 'Montreal Canadiens',
                'status' => 'scheduled',
                'is_monitored' => false,
                'ticket_platforms' => json_encode(['ticketmaster', 'stubhub']),
                'min_price' => 75.00,
                'max_price' => 450.00,
                'total_tickets' => 19800,
                'available_tickets' => 8900,
            ],
            // College Football
            [
                'name' => 'Alabama vs Georgia - CFP Championship',
                'venue' => 'Mercedes-Benz Stadium',
                'city' => 'Atlanta',
                'country' => 'USA',
                'event_date' => '2025-01-20',
                'event_time' => '20:00:00',
                'category' => 'football',
                'league' => 'NCAA',
                'home_team' => 'Alabama Crimson Tide',
                'away_team' => 'Georgia Bulldogs',
                'status' => 'scheduled',
                'is_monitored' => true,
                'ticket_platforms' => json_encode(['ticketmaster', 'stubhub', 'viagogo', 'tickpick']),
                'min_price' => 350.00,
                'max_price' => 3500.00,
                'total_tickets' => 75000,
                'available_tickets' => 22000,
            ],
            // Tennis
            [
                'name' => 'Australian Open Final - Men\'s Singles',
                'venue' => 'Rod Laver Arena',
                'city' => 'Melbourne',
                'country' => 'Australia',
                'event_date' => '2025-01-26',
                'event_time' => '19:30:00',
                'category' => 'tennis',
                'league' => 'Grand Slam',
                'home_team' => null,
                'away_team' => null,
                'status' => 'scheduled',
                'is_monitored' => true,
                'ticket_platforms' => json_encode(['viagogo', 'stubhub']),
                'min_price' => 450.00,
                'max_price' => 5000.00,
                'total_tickets' => 15000,
                'available_tickets' => 3500,
            ],
        ];

        foreach ($events as $event) {
            DB::table('sports_events')->insert(array_merge($event, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
