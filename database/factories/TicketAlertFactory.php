<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\TicketAlert;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<TicketAlert>
 */
class TicketAlertFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TicketAlert>
     */
    protected $model = TicketAlert::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        // Ensure we have a valid sports_event_id to satisfy FK constraints
        $sportsEventId = DB::table('sports_events')->value('id');
        if (! $sportsEventId) {
            $sportsEventId = DB::table('sports_events')->insertGetId([
                'name'              => 'Test Event',
                'venue'             => 'Test Venue',
                'city'              => 'Test City',
                'country'           => 'USA',
                'event_date'        => now()->toDateString(),
                'event_time'        => '20:00:00',
                'category'          => 'football',
                'league'            => 'NFL',
                'home_team'         => 'Home Team',
                'away_team'         => 'Away Team',
                'status'            => 'scheduled',
                'is_monitored'      => TRUE,
                'ticket_platforms'  => json_encode(['ticketmaster', 'stubhub']),
                'min_price'         => 50.00,
                'max_price'         => 200.00,
                'total_tickets'     => 1000,
                'available_tickets' => 500,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        return [
            'user_id'             => User::factory(),
            'sports_event_id'     => (int) $sportsEventId,
            'alert_name'          => fake()->sentence,
            'max_price'           => fake()->randomFloat(2, 50, 500),
            'min_price'           => fake()->randomFloat(2, 10, 40),
            'min_quantity'        => fake()->numberBetween(1, 4),
            'preferred_sections'  => json_encode(['Lower Bowl', 'Upper Bowl']),
            'platforms'           => json_encode(['stubhub', 'ticketmaster']),
            'status'              => fake()->randomElement(['active', 'paused', 'triggered', 'expired']),
            'priority_score'      => fake()->numberBetween(1, 100),
            'escalation_level'    => fake()->numberBetween(0, 3),
            'success_rate'        => fake()->randomFloat(4, 0, 1),
            'channel_preferences' => json_encode(['email' => TRUE, 'sms' => FALSE]),
            'email_notifications' => TRUE,
            'sms_notifications'   => FALSE,
            'auto_purchase'       => FALSE,
            'last_checked_at'     => fake()->dateTimeBetween('-1 week', 'now'),
            'triggered_at'        => fake()->boolean(30) ? now()->subDays(fake()->numberBetween(0, 30)) : NULL,
            'matches_found'       => fake()->numberBetween(0, 10),
        ];
    }
}
