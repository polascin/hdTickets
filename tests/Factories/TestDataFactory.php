<?php declare(strict_types=1);

namespace Tests\Factories;

use App\Models\Category;
use App\Models\PurchaseAttempt;
use App\Models\ScrapedTicket;
use App\Models\Ticket;
use App\Models\TicketAlert;
use App\Models\TicketSource;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestDataFactory
{
    /**
     * Create a test user with specific attributes and role
     */
    public function createUser(array $attributes = [], string $role = 'customer'): User
    {
        $defaultAttributes = [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'role'              => $role,
            'is_active'         => true,
            'preferences'       => [
                'notifications' => [
                    'email' => TRUE,
                    'sms'   => FALSE,
                    'push'  => TRUE,
                ],
                'currency' => 'USD',
                'timezone' => 'UTC',
                'theme'    => 'light',
            ],
            'two_factor_secret' => NULL,
            'remember_token'    => Str::random(10),
        ];

        $userData = array_merge($defaultAttributes, $attributes);

        return User::create($userData);
    }

    /**
     * Create a test admin user
     */
    public function createAdminUser(array $attributes = []): User
    {
        return $this->createUser($attributes, 'admin');
    }

    /**
     * Create a test premium user
     */
    public function createPremiumUser(array $attributes = []): User
    {
        // Premium is a subscription plan; role remains 'customer'
        $user = $this->createUser($attributes, 'customer');

        // Create premium subscription
        UserSubscription::create([
            'user_id'                => $user->id,
            'plan_name'              => 'premium',
            'status'                 => 'active',
            'starts_at'              => now(),
            'ends_at'                => now()->addYear(),
            'stripe_subscription_id' => 'sub_' . fake()->uuid(),
        ]);

        return $user;
    }

    /**
     * Create a test ticket
     */
    public function createTicket(array $attributes = []): Ticket
    {
        $requester = $attributes['requester_id'] ?? $this->createUser();

        $defaultAttributes = [
            'title'              => fake()->sentence(4),
            'description'        => fake()->paragraph(),
            'event_date'         => fake()->dateTimeBetween('+1 week', '+6 months'),
            'venue'              => fake()->company() . ' Stadium',
            'currency'           => 'USD',
            'available_quantity' => fake()->numberBetween(1, 1000),
            'is_available'       => true,
            'price'              => fake()->randomFloat(2, 25, 500),
            'status'             => 'open',
            'requester_id'       => $requester instanceof User ? $requester->id : $requester,
        ];

        $ticketData = array_merge($defaultAttributes, $attributes);

        return Ticket::create($ticketData);
    }

    /**
     * Create a test ticket source
     */
    public function createTicketSource(array $attributes = []): TicketSource
    {
        $defaultAttributes = [
            'name'                => fake()->randomElement(['Ticketmaster', 'StubHub', 'SeatGeek', 'Vivid Seats']),
            'base_url'            => fake()->url(),
            'api_endpoint'        => fake()->url() . '/api',
            'scraping_enabled'    => TRUE,
            'rate_limit'          => fake()->numberBetween(10, 100),
            'rate_limit_period'   => 'minute',
            'status'              => 'active',
            'priority'            => fake()->numberBetween(1, 10),
            'supported_sports'    => ['football', 'basketball', 'baseball'],
            'supported_countries' => ['US', 'CA', 'UK'],
            'configuration'       => [
                'selectors' => [
                    'title'    => '.event-title',
                    'price'    => '.price',
                    'quantity' => '.quantity',
                ],
                'headers' => [
                    'User-Agent' => 'HD Tickets Bot 1.0',
                ],
            ],
            'last_scraped_at'       => fake()->dateTimeBetween('-1 hour', 'now'),
            'success_rate'          => fake()->numberBetween(80, 100),
            'average_response_time' => fake()->numberBetween(500, 3000),
        ];

        $sourceData = array_merge($defaultAttributes, $attributes);

        return TicketSource::create($sourceData);
    }

    /**
     * Create a test category
     */
    public function createCategory(array $attributes = []): Category
    {
        $defaultAttributes = [
            'name'        => fake()->randomElement(['Football', 'Basketball', 'Baseball', 'Hockey', 'Soccer']),
            'slug'        => fake()->slug(),
            'description' => fake()->sentence(10),
            'color'       => fake()->hexColor(),
            'icon'        => fake()->randomElement(['football', 'basketball', 'baseball']),
            'is_active'   => TRUE,
            'sort_order'  => fake()->numberBetween(1, 100),
            'metadata'    => [
                'season_start'   => fake()->month(),
                'season_end'     => fake()->month(),
                'typical_venues' => ['Stadium', 'Arena', 'Field'],
            ],
        ];

        $categoryData = array_merge($defaultAttributes, $attributes);

        return Category::create($categoryData);
    }

    /**
     * Create a test purchase attempt
     */
    public function createPurchaseAttempt(array $attributes = []): PurchaseAttempt
    {
        $user = $attributes['user_id'] ?? $this->createUser();
        $ticket = $attributes['ticket_id'] ?? $this->createTicket();

        $defaultAttributes = [
            'user_id'         => $user instanceof User ? $user->id : $user,
            'ticket_id'       => $ticket instanceof Ticket ? $ticket->id : $ticket,
            'quantity'        => fake()->numberBetween(1, 4),
            'max_price'       => fake()->numberBetween(100, 500),
            'status'          => fake()->randomElement(['pending', 'processing', 'completed', 'failed', 'cancelled']),
            'priority'        => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'scheduled_at'    => fake()->dateTimeBetween('+1 minute', '+1 hour'),
            'attempt_count'   => fake()->numberBetween(0, 5),
            'last_attempt_at' => fake()->dateTimeBetween('-1 hour', 'now'),
            'next_attempt_at' => fake()->dateTimeBetween('+5 minutes', '+30 minutes'),
            'failure_reason'  => NULL,
            'purchase_data'   => [
                'payment_method'  => fake()->randomElement(['stripe', 'paypal']),
                'billing_address' => [
                    'street'  => fake()->streetAddress(),
                    'city'    => fake()->city(),
                    'state'   => fake()->state(),
                    'zip'     => fake()->postcode(),
                    'country' => 'US',
                ],
            ],
        ];

        $purchaseData = array_merge($defaultAttributes, $attributes);

        return PurchaseAttempt::create($purchaseData);
    }

    /**
     * Create a test ticket alert
     */
    public function createTicketAlert(array $attributes = []): TicketAlert
    {
        $user = $attributes['user_id'] ?? $this->createUser();

        $defaultAttributes = [
            'user_id'  => $user instanceof User ? $user->id : $user,
            'title'    => fake()->sentence(3),
            'criteria' => [
                'sport_type'   => fake()->randomElement(['football', 'basketball', 'baseball']),
                'teams'        => [fake()->words(2, TRUE)],
                'cities'       => [fake()->city()],
                'max_price'    => fake()->numberBetween(50, 300),
                'min_quantity' => fake()->numberBetween(1, 4),
            ],
            'notification_channels' => fake()->randomElements(['email', 'sms', 'push'], 2),
            'is_active'             => TRUE,
            'priority'              => fake()->randomElement(['low', 'medium', 'high']),
            'frequency_limit'       => fake()->randomElement(['instant', 'hourly', 'daily']),
            'expires_at'            => fake()->dateTimeBetween('+1 week', '+6 months'),
            'last_triggered_at'     => NULL,
            'trigger_count'         => 0,
        ];

        $alertData = array_merge($defaultAttributes, $attributes);

        return TicketAlert::create($alertData);
    }

    /**
     * Create a test scraped ticket
     */
    public function createScrapedTicket(array $attributes = []): ScrapedTicket
    {
        $source = $attributes['source_id'] ?? $this->createTicketSource();

        $defaultAttributes = [
            'source_id'          => $source instanceof TicketSource ? $source->id : $source,
            'external_id'        => fake()->uuid(),
            'title'              => fake()->sentence(4),
            'event_date'         => fake()->dateTimeBetween('+1 week', '+6 months'),
            'venue'              => fake()->company() . ' Arena',
            'city'               => fake()->city(),
            'price'              => fake()->numberBetween(25, 500),
            'currency'           => 'USD',
            'quantity_available' => fake()->numberBetween(1, 100),
            'section'            => fake()->randomElement(['Section A', 'Section B', 'VIP']),
            'row'                => fake()->randomElement(['Row 1', 'Row 2', 'Row 3']),
            'seat_numbers'       => fake()->randomElements([1, 2, 3, 4], 2),
            'source_url'         => fake()->url(),
            'raw_data'           => [
                'html_snippet' => '<div class="ticket">Sample HTML</div>',
                'json_data'    => ['price' => 100, 'available' => TRUE],
            ],
            'scraped_at' => now(),
            'status'     => 'active',
        ];

        $scrapedData = array_merge($defaultAttributes, $attributes);

        return ScrapedTicket::create($scrapedData);
    }

    /**
     * Create multiple test objects
     */
    public function createMultiple(string $type, int $count, array $attributes = []): array
    {
        $objects = [];

        for ($i = 0; $i < $count; $i++) {
            $method = 'create' . ucfirst($type);
            if (method_exists($this, $method)) {
                $objects[] = $this->$method($attributes);
            }
        }

        return $objects;
    }

    /**
     * Create a complete test scenario with related data
     */
    public function createTicketScenario(): array
    {
        $user = $this->createUser();
        $premiumUser = $this->createPremiumUser();
        $category = $this->createCategory();
        $source = $this->createTicketSource();
        $ticket = $this->createTicket(['source_id' => $source->id]);
        $alert = $this->createTicketAlert(['user_id' => $user->id]);
        $purchase = $this->createPurchaseAttempt([
            'user_id'   => $premiumUser->id,
            'ticket_id' => $ticket->id,
        ]);
        $scrapedTicket = $this->createScrapedTicket(['source_id' => $source->id]);

        return [
            'user'           => $user,
            'premium_user'   => $premiumUser,
            'category'       => $category,
            'source'         => $source,
            'ticket'         => $ticket,
            'alert'          => $alert,
            'purchase'       => $purchase,
            'scraped_ticket' => $scrapedTicket,
        ];
    }
}
