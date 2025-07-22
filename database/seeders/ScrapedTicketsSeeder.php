<?php

namespace Database\Seeders;

use App\Models\ScrapedTicket;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ScrapedTicketsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = [
            [
                'platform' => 'stubhub',
                'event_title' => 'Manchester United vs Liverpool',
                'venue' => 'Old Trafford',
                'event_date' => Carbon::now()->addDays(30),
                'section' => 'South Stand',
                'row' => '10',
                'price' => 250.00,
                'currency' => 'GBP',
                'total_price' => 280.50,
                'fees' => 30.50,
                'availability_status' => 'available',
                'quantity_available' => 2,
                'is_high_demand' => true,
                'demand_score' => 95,
                'ticket_url' => 'https://stubhub.co.uk/manchester-united-tickets/123',
                'search_keywords' => 'Manchester United Premier League Football',
                'metadata' => [
                    'category' => 'Premier League',
                    'match_type' => 'Home',
                    'importance' => 'High',
                ]
            ],
            [
                'platform' => 'ticketmaster',
                'event_title' => 'Arsenal vs Chelsea',
                'venue' => 'Emirates Stadium',
                'event_date' => Carbon::now()->addDays(45),
                'section' => 'Clock End',
                'row' => '15',
                'price' => 180.00,
                'currency' => 'GBP',
                'total_price' => 210.75,
                'fees' => 30.75,
                'availability_status' => 'available',
                'quantity_available' => 4,
                'is_high_demand' => true,
                'demand_score' => 88,
                'ticket_url' => 'https://ticketmaster.co.uk/arsenal-chelsea-456',
                'search_keywords' => 'Arsenal Chelsea Premier League London Derby',
                'metadata' => [
                    'category' => 'Premier League',
                    'match_type' => 'Home',
                    'derby' => true,
                ]
            ],
            [
                'platform' => 'viagogo',
                'event_title' => 'Real Madrid vs Barcelona',
                'venue' => 'Santiago Bernabéu',
                'event_date' => Carbon::now()->addDays(60),
                'section' => 'Lateral Alto',
                'row' => '25',
                'price' => 450.00,
                'currency' => 'EUR',
                'total_price' => 520.00,
                'fees' => 70.00,
                'availability_status' => 'available',
                'quantity_available' => 1,
                'is_high_demand' => true,
                'demand_score' => 99,
                'ticket_url' => 'https://viagogo.com/real-madrid-barcelona-789',
                'search_keywords' => 'Real Madrid Barcelona El Clasico La Liga',
                'metadata' => [
                    'category' => 'La Liga',
                    'match_type' => 'El Clasico',
                    'importance' => 'Very High',
                ]
            ],
            [
                'platform' => 'stubhub',
                'event_title' => 'Los Angeles Lakers vs Golden State Warriors',
                'venue' => 'Crypto.com Arena',
                'event_date' => Carbon::now()->addDays(20),
                'section' => 'Lower Bowl',
                'row' => '12',
                'price' => 320.00,
                'currency' => 'USD',
                'total_price' => 380.50,
                'fees' => 60.50,
                'availability_status' => 'available',
                'quantity_available' => 2,
                'is_high_demand' => true,
                'demand_score' => 92,
                'ticket_url' => 'https://stubhub.com/lakers-warriors-101112',
                'search_keywords' => 'Lakers Warriors NBA Basketball Los Angeles',
                'metadata' => [
                    'category' => 'NBA',
                    'sport' => 'Basketball',
                    'rivalry' => true,
                ]
            ],
            [
                'platform' => 'ticketmaster',
                'event_title' => 'New York Yankees vs Boston Red Sox',
                'venue' => 'Yankee Stadium',
                'event_date' => Carbon::now()->addDays(25),
                'section' => 'Field Level',
                'row' => '8',
                'price' => 125.00,
                'currency' => 'USD',
                'total_price' => 145.75,
                'fees' => 20.75,
                'availability_status' => 'available',
                'quantity_available' => 4,
                'is_high_demand' => false,
                'demand_score' => 75,
                'ticket_url' => 'https://ticketmaster.com/yankees-redsox-131415',
                'search_keywords' => 'Yankees Red Sox MLB Baseball New York',
                'metadata' => [
                    'category' => 'MLB',
                    'sport' => 'Baseball',
                    'rivalry' => 'Yankees-Red Sox',
                ]
            ],
            [
                'platform' => 'viagogo',
                'event_title' => 'Super Bowl LVIII',
                'venue' => 'State Farm Stadium',
                'event_date' => Carbon::now()->addMonths(3),
                'section' => 'Upper Deck',
                'row' => '35',
                'price' => 2500.00,
                'currency' => 'USD',
                'total_price' => 2850.00,
                'fees' => 350.00,
                'availability_status' => 'available',
                'quantity_available' => 2,
                'is_high_demand' => true,
                'demand_score' => 100,
                'ticket_url' => 'https://viagogo.com/super-bowl-161718',
                'search_keywords' => 'Super Bowl NFL Football Championship',
                'metadata' => [
                    'category' => 'NFL',
                    'sport' => 'American Football',
                    'event_type' => 'Championship',
                    'importance' => 'Highest',
                ]
            ],
            [
                'platform' => 'stubhub',
                'event_title' => 'Liverpool vs Manchester City',
                'venue' => 'Anfield',
                'event_date' => Carbon::now()->addDays(50),
                'section' => 'The Kop',
                'row' => '20',
                'price' => 200.00,
                'currency' => 'GBP',
                'total_price' => 235.00,
                'fees' => 35.00,
                'availability_status' => 'limited',
                'quantity_available' => 1,
                'is_high_demand' => true,
                'demand_score' => 90,
                'ticket_url' => 'https://stubhub.co.uk/liverpool-city-192021',
                'search_keywords' => 'Liverpool Manchester City Premier League Title Race',
                'metadata' => [
                    'category' => 'Premier League',
                    'match_type' => 'Title Decider',
                    'atmosphere' => 'Electric',
                ]
            ]
        ];

        foreach ($events as $eventData) {
            ScrapedTicket::create($eventData);
        }

        $this->command->info('✓ Successfully created ' . count($events) . ' sample scraped tickets');
    }
}
