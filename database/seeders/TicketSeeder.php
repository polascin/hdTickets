<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users for ticket assignment
        $customers = User::where('role', User::ROLE_CUSTOMER)->limit(10)->get();
        $agents = User::where('role', User::ROLE_AGENT)->limit(5)->get();

        if ($customers->isEmpty() || $agents->isEmpty()) {
            $this->command->info('No customers or agents found. Skipping ticket seeding.');

            return;
        }

        $sampleTickets = [
            [
                'title'            => 'NBA Finals 2025 - Lakers vs Celtics',
                'description'      => 'Premium tickets for Game 1 of NBA Finals',
                'status'           => Ticket::STATUS_OPEN,
                'priority'         => Ticket::PRIORITY_HIGH,
                'platform'         => 'Ticketmaster',
                'price'            => 850.00,
                'currency'         => 'USD',
                'location'         => 'Los Angeles',
                'venue'            => 'Crypto.com Arena',
                'event_type'       => 'Basketball',
                'performer_artist' => 'Lakers vs Celtics',
                'seat_details'     => json_encode([
                    'section' => '100',
                    'row'     => '5',
                    'seats'   => '15-16',
                ]),
                'is_available' => TRUE,
            ],
            [
                'title'            => 'Taylor Swift Eras Tour 2025',
                'description'      => 'VIP tickets for Taylor Swift concert',
                'status'           => Ticket::STATUS_IN_PROGRESS,
                'priority'         => Ticket::PRIORITY_URGENT,
                'platform'         => 'Ticketmaster',
                'price'            => 1200.00,
                'currency'         => 'USD',
                'location'         => 'New York',
                'venue'            => 'Madison Square Garden',
                'event_type'       => 'Concert',
                'performer_artist' => 'Taylor Swift',
                'seat_details'     => json_encode([
                    'section' => 'Floor A',
                    'row'     => '3',
                    'seats'   => '7-8',
                ]),
                'is_available' => TRUE,
            ],
            [
                'title'            => 'Super Bowl LIX 2025',
                'description'      => 'Super Bowl tickets - Championship game',
                'status'           => Ticket::STATUS_RESOLVED,
                'priority'         => Ticket::PRIORITY_URGENT,
                'platform'         => 'StubHub',
                'price'            => 3500.00,
                'currency'         => 'USD',
                'location'         => 'New Orleans',
                'venue'            => 'Caesars Superdome',
                'event_type'       => 'Football',
                'performer_artist' => 'TBD vs TBD',
                'seat_details'     => json_encode([
                    'section' => '139',
                    'row'     => '15',
                    'level'   => 'Lower',
                ]),
                'is_available' => FALSE,
            ],
            [
                'title'            => 'Broadway Hamilton Musical',
                'description'      => 'Premium orchestra seats for Hamilton',
                'status'           => Ticket::STATUS_PENDING,
                'priority'         => Ticket::PRIORITY_MEDIUM,
                'platform'         => 'Broadway.com',
                'price'            => 275.00,
                'currency'         => 'USD',
                'location'         => 'New York',
                'venue'            => 'Richard Rodgers Theatre',
                'event_type'       => 'Musical',
                'performer_artist' => 'Hamilton Cast',
                'seat_details'     => json_encode([
                    'section' => 'Orchestra',
                    'row'     => 'H',
                    'seats'   => '12-13',
                ]),
                'is_available' => TRUE,
            ],
            [
                'title'            => 'Champions League Final 2025',
                'description'      => 'UEFA Champions League Final tickets',
                'status'           => Ticket::STATUS_OPEN,
                'priority'         => Ticket::PRIORITY_HIGH,
                'platform'         => 'UEFA.com',
                'price'            => 1800.00,
                'currency'         => 'EUR',
                'location'         => 'Munich',
                'venue'            => 'Allianz Arena',
                'event_type'       => 'Soccer',
                'performer_artist' => 'TBD vs TBD',
                'seat_details'     => json_encode([
                    'category' => '1',
                    'block'    => '101',
                    'row'      => '8',
                ]),
                'is_available' => TRUE,
            ],
        ];

        foreach ($sampleTickets as $ticketData) {
            $customer = $customers->random();
            $agent = $agents->random();

            Ticket::create(array_merge($ticketData, [
                'uuid'              => Str::uuid(),
                'requester_id'      => $customer->id,
                'assignee_id'       => $agent->id,
                'due_date'          => Carbon::now()->addDays(random_int(1, 30)),
                'event_date'        => Carbon::now()->addMonths(random_int(1, 6)),
                'external_id'       => 'EXT-' . Str::random(8),
                'ticket_url'        => 'https://example.com/ticket/' . Str::random(10),
                'scraping_metadata' => json_encode([
                    'last_scraped'     => Carbon::now()->toISOString(),
                    'scrape_count'     => random_int(1, 50),
                    'last_price_check' => Carbon::now()->subHours(random_int(1, 24))->toISOString(),
                ]),
            ]));
        }

        $this->command->info('Created 5 sample tickets successfully!');
    }
}
