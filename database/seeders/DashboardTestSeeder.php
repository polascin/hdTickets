<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PaymentPlan;
use App\Models\Ticket;
use App\Models\TicketAlert;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\UserSubscription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use function chr;

class DashboardTestSeeder extends Seeder
{
    /**
     * Run the database seeds for comprehensive dashboard testing
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting comprehensive dashboard seeding...');

        DB::transaction(function (): void {
            $this->createTestCustomers();
            $this->createTicketsWithVariedData();
            $this->createTicketAlerts();
            $this->createUserPreferences();
            $this->createSubscriptions();
        });

        $this->command->info('✅ Dashboard test data seeded successfully!');
    }

    /**
     * Create test customer accounts with varied subscription statuses
     */
    private function createTestCustomers(): void
    {
        $testCustomers = [
            [
                'name'              => 'Dashboard',
                'surname'           => 'Customer',
                'username'          => 'dashboard.customer',
                'email'             => 'dashboard@customer.test',
                'password'          => Hash::make('password123'),
                'role'              => User::ROLE_CUSTOMER,
                'email_verified_at' => now(),
                'is_active'         => TRUE,
            ],
            [
                'name'              => 'Premium',
                'surname'           => 'User',
                'username'          => 'premium.user',
                'email'             => 'premium@customer.test',
                'password'          => Hash::make('password123'),
                'role'              => User::ROLE_CUSTOMER,
                'email_verified_at' => now(),
                'is_active'         => TRUE,
            ],
            [
                'name'              => 'Trial',
                'surname'           => 'User',
                'username'          => 'trial.user',
                'email'             => 'trial@customer.test',
                'password'          => Hash::make('password123'),
                'role'              => User::ROLE_CUSTOMER,
                'email_verified_at' => now(),
                'is_active'         => TRUE,
            ],
        ];

        foreach ($testCustomers as $customerData) {
            User::updateOrCreate(
                ['email' => $customerData['email']],
                array_merge($customerData, [
                    'created_at' => now()->subDays(rand(30, 90)),
                    'updated_at' => now(),
                ]),
            );
        }

        $this->command->info('Created test customer accounts');
    }

    /**
     * Create tickets with varied statuses and prices for dashboard
     */
    private function createTicketsWithVariedData(): void
    {
        $customers = User::where('role', User::ROLE_CUSTOMER)
            ->whereIn('email', ['dashboard@customer.test', 'premium@customer.test', 'trial@customer.test'])
            ->get();

        if ($customers->isEmpty()) {
            $this->command->warn('No test customers found for ticket creation');

            return;
        }

        $sampleTickets = [
            // Recent hot tickets
            [
                'title'            => 'NBA Finals 2025 - Game 1',
                'description'      => 'Lakers vs Celtics - Premium lower bowl seats',
                'platform'         => 'Ticketmaster',
                'price'            => 450.00,
                'venue'            => 'Crypto.com Arena',
                'location'         => 'Los Angeles, CA',
                'event_type'       => 'Basketball',
                'performer_artist' => 'Lakers vs Celtics',
                'event_date'       => now()->addDays(15),
                'status'           => 'open',
                'priority'         => 'high',
                'is_available'     => TRUE,
                'created_at'       => now()->subHours(2),
            ],
            [
                'title'            => 'Super Bowl LIX 2025',
                'description'      => 'Championship game - Upper level seats',
                'platform'         => 'StubHub',
                'price'            => 2800.00,
                'venue'            => 'Caesars Superdome',
                'location'         => 'New Orleans, LA',
                'event_type'       => 'Football',
                'performer_artist' => 'TBD vs TBD',
                'event_date'       => now()->addDays(45),
                'status'           => 'open',
                'priority'         => 'urgent',
                'is_available'     => TRUE,
                'created_at'       => now()->subHours(1),
            ],
            [
                'title'            => 'Taylor Swift Eras Tour',
                'description'      => 'VIP package with meet & greet',
                'platform'         => 'Vivid Seats',
                'price'            => 890.00,
                'venue'            => 'SoFi Stadium',
                'location'         => 'Los Angeles, CA',
                'event_type'       => 'Concert',
                'performer_artist' => 'Taylor Swift',
                'event_date'       => now()->addDays(30),
                'status'           => 'open',
                'priority'         => 'high',
                'is_available'     => TRUE,
                'created_at'       => now()->subHours(5),
            ],
            // Older tickets for history
            [
                'title'            => 'Yankees vs Red Sox',
                'description'      => 'Classic rivalry game - Green Monster seats',
                'platform'         => 'SeatGeek',
                'price'            => 125.00,
                'venue'            => 'Fenway Park',
                'location'         => 'Boston, MA',
                'event_type'       => 'Baseball',
                'performer_artist' => 'Yankees vs Red Sox',
                'event_date'       => now()->addDays(60),
                'status'           => 'open',
                'priority'         => 'medium',
                'is_available'     => TRUE,
                'created_at'       => now()->subDays(5),
            ],
            [
                'title'            => 'Golden State Warriors vs Lakers',
                'description'      => 'California Classic - Court side available',
                'platform'         => 'Ticketmaster',
                'price'            => 1200.00,
                'venue'            => 'Chase Center',
                'location'         => 'San Francisco, CA',
                'event_type'       => 'Basketball',
                'performer_artist' => 'Warriors vs Lakers',
                'event_date'       => now()->addDays(25),
                'status'           => 'open',
                'priority'         => 'high',
                'is_available'     => TRUE,
                'created_at'       => now()->subDays(3),
            ],
            // Sold out / unavailable tickets
            [
                'title'            => 'Harry Styles World Tour',
                'description'      => 'SOLD OUT - Pit tickets were available',
                'platform'         => 'Ticketmaster',
                'price'            => 350.00,
                'venue'            => 'Madison Square Garden',
                'location'         => 'New York, NY',
                'event_type'       => 'Concert',
                'performer_artist' => 'Harry Styles',
                'event_date'       => now()->addDays(20),
                'status'           => 'closed',
                'priority'         => 'high',
                'is_available'     => FALSE,
                'created_at'       => now()->subDays(10),
            ],
        ];

        foreach ($sampleTickets as $ticketData) {
            $customer = $customers->random();

            $ticketData = array_merge($ticketData, [
                'uuid'         => Str::uuid(),
                'requester_id' => $customer->id,
                'external_id'  => 'DASH-' . Str::random(8),
                'ticket_url'   => 'https://example.com/ticket/' . Str::random(10),
                'currency'     => 'USD',
                'seat_details' => json_encode([
                    'section' => rand(100, 400),
                    'row'     => chr(rand(65, 90)), // A-Z
                    'seats'   => rand(1, 20) . '-' . (rand(1, 20) + 1),
                ]),
                'scraping_metadata' => json_encode([
                    'last_scraped'  => now()->subMinutes(rand(5, 120)),
                    'scrape_count'  => rand(10, 100),
                    'price_changes' => rand(2, 15),
                ]),
                'updated_at' => now(),
            ]);

            Ticket::create($ticketData);
        }

        $this->command->info('Created varied tickets for dashboard testing');
    }

    /**
     * Create ticket alerts for price monitoring
     */
    private function createTicketAlerts(): void
    {
        $customers = User::where('role', User::ROLE_CUSTOMER)
            ->whereIn('email', ['dashboard@customer.test', 'premium@customer.test', 'trial@customer.test'])
            ->get();

        // Get some sports events for alerts
        $sportsEvents = DB::table('sports_events')->limit(10)->get();

        if ($customers->isEmpty() || $sportsEvents->isEmpty()) {
            $this->command->warn('No test customers or sports events found for alert creation');

            return;
        }

        foreach ($customers as $customer) {
            // Create 2-3 alerts per customer
            $alertCount = rand(2, 3);

            for ($i = 0; $i < $alertCount; $i++) {
                $event = $sportsEvents->random();

                TicketAlert::create([
                    'user_id'             => $customer->id,
                    'sports_event_id'     => $event->id,
                    'alert_name'          => 'Price Alert for ' . $event->name,
                    'max_price'           => rand(100, 500),
                    'min_price'           => rand(50, 99),
                    'min_quantity'        => rand(1, 4),
                    'preferred_sections'  => json_encode(['Lower Bowl', 'Upper Deck']),
                    'platforms'           => json_encode(['ticketmaster', 'stubhub', 'vivid_seats']),
                    'status'              => collect(['active', 'paused'])->random(),
                    'email_notifications' => TRUE,
                    'sms_notifications'   => rand(0, 1) === 1,
                    'auto_purchase'       => FALSE,
                    'triggered_at'        => rand(0, 1) === 1 ? now()->subDays(rand(1, 7)) : NULL,
                    'created_at'          => now()->subDays(rand(1, 30)),
                ]);
            }
        }

        $this->command->info('Created ticket alerts for customers');
    }

    /**
     * Create user preferences for dashboard customization
     */
    private function createUserPreferences(): void
    {
        $customers = User::where('role', User::ROLE_CUSTOMER)
            ->whereIn('email', ['dashboard@customer.test', 'premium@customer.test', 'trial@customer.test'])
            ->get();

        foreach ($customers as $customer) {
            // Create separate preference records for each category
            $preferences = [
                [
                    'key'      => 'dashboard_theme',
                    'value'    => json_encode(collect(['light', 'dark'])->random()),
                    'category' => 'dashboard',
                    'type'     => 'string',
                ],
                [
                    'key'      => 'dashboard_items_per_page',
                    'value'    => json_encode(rand(10, 50)),
                    'category' => 'dashboard',
                    'type'     => 'integer',
                ],
                [
                    'key'      => 'dashboard_auto_refresh',
                    'value'    => json_encode(TRUE),
                    'category' => 'dashboard',
                    'type'     => 'boolean',
                ],
                [
                    'key'      => 'alerts_email_notifications',
                    'value'    => json_encode(TRUE),
                    'category' => 'alerts',
                    'type'     => 'boolean',
                ],
                [
                    'key'      => 'alerts_frequency',
                    'value'    => json_encode(collect(['immediate', 'hourly', 'daily'])->random()),
                    'category' => 'alerts',
                    'type'     => 'string',
                ],
                [
                    'key'      => 'preferred_platforms',
                    'value'    => json_encode(collect(['Ticketmaster', 'StubHub', 'Vivid Seats'])->random(2)->toArray()),
                    'category' => 'tickets',
                    'type'     => 'array',
                ],
                [
                    'key'   => 'price_range',
                    'value' => json_encode([
                        'min' => rand(50, 200),
                        'max' => rand(500, 2000),
                    ]),
                    'category' => 'tickets',
                    'type'     => 'json',
                ],
            ];

            foreach ($preferences as $pref) {
                UserPreference::create(array_merge($pref, [
                    'user_id' => $customer->id,
                ]));
            }
        }

        $this->command->info('Created user preferences');
    }

    /**
     * Create subscriptions with varied statuses
     */
    private function createSubscriptions(): void
    {
        $customers = User::where('role', User::ROLE_CUSTOMER)
            ->whereIn('email', ['dashboard@customer.test', 'premium@customer.test', 'trial@customer.test'])
            ->get();

        $paymentPlans = PaymentPlan::where('is_active', TRUE)->get();

        if ($paymentPlans->isEmpty()) {
            $this->command->warn('No payment plans found, creating basic ones for testing');
            $this->createBasicPaymentPlans();
            $paymentPlans = PaymentPlan::where('is_active', TRUE)->get();
        }

        foreach ($customers as $index => $customer) {
            $plan = $paymentPlans->random();

            // Create varied subscription statuses
            $subscriptionData = [
                'user_id'                => $customer->id,
                'payment_plan_id'        => $plan->id,
                'stripe_subscription_id' => 'sub_' . Str::random(24),
                'status'                 => collect(['active', 'trial', 'expired'])->random(),
                'starts_at'              => now()->subDays(rand(1, 28)),
                'ends_at'                => now()->addDays(rand(1, 32)),
                'trial_ends_at'          => $index === 2 ? now()->addDays(rand(1, 14)) : NULL, // Trial user
                'created_at'             => now()->subDays(rand(30, 90)),
            ];

            UserSubscription::create($subscriptionData);
        }

        $this->command->info('Created customer subscriptions');
    }

    /**
     * Create basic payment plans if none exist
     */
    private function createBasicPaymentPlans(): void
    {
        $plans = [
            [
                'name'             => 'Basic Plan',
                'slug'             => 'basic',
                'description'      => 'Basic ticket monitoring',
                'price'            => 9.99,
                'currency'         => 'USD',
                'billing_interval' => 'monthly',
                'stripe_price_id'  => 'price_basic_monthly',
                'features'         => json_encode([
                    'ticket_alerts'    => 10,
                    'price_monitoring' => TRUE,
                    'basic_support'    => TRUE,
                ]),
                'is_active' => TRUE,
            ],
            [
                'name'             => 'Premium Plan',
                'slug'             => 'premium',
                'description'      => 'Advanced ticket monitoring',
                'price'            => 19.99,
                'currency'         => 'USD',
                'billing_interval' => 'monthly',
                'stripe_price_id'  => 'price_premium_monthly',
                'features'         => json_encode([
                    'ticket_alerts'      => 50,
                    'price_monitoring'   => TRUE,
                    'advanced_analytics' => TRUE,
                    'priority_support'   => TRUE,
                ]),
                'is_active' => TRUE,
            ],
        ];

        foreach ($plans as $planData) {
            PaymentPlan::create($planData);
        }
    }
}
