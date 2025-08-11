<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== Creating Test Sport Event Tickets ===\n\n";

try {
    // Create some sample sport event tickets
    $sportsEvents = [
        [
            'title' => 'Manchester United vs Liverpool FC',
            'sport' => 'Football',
            'venue' => 'Old Trafford',
            'location' => 'Manchester, UK',
            'event_date' => Carbon::now()->addDays(15),
            'price' => 75.00,
            'min_price' => 65.00,
            'max_price' => 150.00,
            'platform' => 'stubhub',
            'source_platform' => 'stubhub',
            'is_available' => true,
            'is_high_demand' => true,
            'scraped_at' => Carbon::now(),
            'event_name' => 'Manchester United vs Liverpool FC'
        ],
        [
            'title' => 'NBA Finals - Lakers vs Celtics',
            'sport' => 'Basketball',
            'venue' => 'Crypto.com Arena',
            'location' => 'Los Angeles, CA',
            'event_date' => Carbon::now()->addDays(22),
            'price' => 250.00,
            'min_price' => 180.00,
            'max_price' => 500.00,
            'platform' => 'ticketmaster',
            'source_platform' => 'ticketmaster',
            'is_available' => true,
            'is_high_demand' => true,
            'scraped_at' => Carbon::now(),
            'event_name' => 'NBA Finals - Lakers vs Celtics'
        ],
        [
            'title' => 'World Series - Yankees vs Dodgers',
            'sport' => 'Baseball',
            'venue' => 'Yankee Stadium',
            'location' => 'New York, NY',
            'event_date' => Carbon::now()->addDays(30),
            'price' => 125.00,
            'min_price' => 85.00,
            'max_price' => 300.00,
            'platform' => 'seatgeek',
            'source_platform' => 'seatgeek',
            'is_available' => true,
            'is_high_demand' => false,
            'scraped_at' => Carbon::now(),
            'event_name' => 'World Series - Yankees vs Dodgers'
        ],
        [
            'title' => 'UEFA Champions League Final',
            'sport' => 'Football',
            'venue' => 'Wembley Stadium',
            'location' => 'London, UK',
            'event_date' => Carbon::now()->addDays(45),
            'price' => 200.00,
            'min_price' => 150.00,
            'max_price' => 600.00,
            'platform' => 'viagogo',
            'source_platform' => 'viagogo',
            'is_available' => true,
            'is_high_demand' => true,
            'scraped_at' => Carbon::now(),
            'event_name' => 'UEFA Champions League Final'
        ],
        [
            'title' => 'Super Bowl LVIII',
            'sport' => 'American Football',
            'venue' => 'Allegiant Stadium',
            'location' => 'Las Vegas, NV',
            'event_date' => Carbon::now()->addDays(60),
            'price' => 1500.00,
            'min_price' => 800.00,
            'max_price' => 5000.00,
            'platform' => 'vivid_seats',
            'source_platform' => 'vivid_seats',
            'is_available' => true,
            'is_high_demand' => true,
            'scraped_at' => Carbon::now(),
            'event_name' => 'Super Bowl LVIII'
        ]
    ];
    
    echo "Creating sport event tickets...\n";
    
    foreach ($sportsEvents as $index => $event) {
        $ticket = ScrapedTicket::create($event);
        echo "✓ Created: {$event['title']} - {$event['sport']} (\${$event['price']})\n";
        
        // Create a few variations of each ticket with different sections/prices
        for ($i = 1; $i <= 3; $i++) {
            $variation = $event;
            $variation['title'] = $event['title'] . " - Section " . chr(65 + $i); // A, B, C
            $variation['price'] = $event['price'] + ($i * 25);
            $variation['min_price'] = $event['min_price'] + ($i * 20);
            $variation['max_price'] = $event['max_price'] + ($i * 30);
            $variation['is_high_demand'] = $i <= 2; // First 2 variations are high demand
            $variation['scraped_at'] = Carbon::now()->subMinutes(rand(5, 60));
            
            ScrapedTicket::create($variation);
        }
    }
    
    $totalTickets = ScrapedTicket::count();
    $availableTickets = ScrapedTicket::where('is_available', true)->count();
    $highDemandTickets = ScrapedTicket::where('is_high_demand', true)->count();
    
    echo "\n✓ Total sport event tickets created: $totalTickets\n";
    echo "✓ Available tickets: $availableTickets\n";
    echo "✓ High demand tickets: $highDemandTickets\n";
    
    // Create some ticket alerts for the test user
    $testUser = User::where('email', 'customer@hdtickets.test')->first();
    if ($testUser) {
        echo "\nCreating test ticket alerts for user...\n";
        
        $alerts = [
            [
                'user_id' => $testUser->id,
                'sports_event_id' => 1,
                'alert_name' => 'Manchester United Alert',
                'max_price' => 100.00,
                'status' => 'active',
                'platforms' => json_encode(['stubhub'])
            ],
            [
                'user_id' => $testUser->id,
                'sports_event_id' => 2,
                'alert_name' => 'NBA Finals Alert',
                'max_price' => 300.00,
                'status' => 'active',
                'platforms' => json_encode(['ticketmaster'])
            ]
        ];
        
        foreach ($alerts as $alert) {
            TicketAlert::create($alert);
            echo "✓ Created alert: {$alert['alert_name']}\n";
        }
    }
    
    echo "\n=== Test Data Creation Complete ===\n";
    echo "✓ Sport event tickets with realistic data created\n";
    echo "✓ Multiple sports: Football, Basketball, Baseball, American Football\n";  
    echo "✓ Multiple platforms: StubHub, Ticketmaster, SeatGeek, Viagogo, Vivid Seats\n";
    echo "✓ Ticket alerts created for test customer\n";
    echo "\nYou can now:\n";
    echo "1. Login as customer@hdtickets.test (password: password123)\n";
    echo "2. Visit /customer-dashboard to see the populated dashboard\n";
    echo "3. Test browsing tickets at /tickets/scraping\n";
    
} catch (Exception $e) {
    echo "✗ Error creating test data: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
