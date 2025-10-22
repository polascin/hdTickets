<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\TicketAlert;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'user_id'             => User::factory(),
            'sports_event_id'     => null,
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
            'channel_preferences' => json_encode(['email' => true, 'sms' => false]),
            'email_notifications' => TRUE,
            'sms_notifications'   => FALSE,
            'auto_purchase'       => FALSE,
            'last_checked_at'     => fake()->dateTimeBetween('-1 week', 'now'),
            'triggered_at'        => fake()->boolean(30) ? now()->subDays(fake()->numberBetween(0, 30)) : NULL,
            'matches_found'       => fake()->numberBetween(0, 10),
        ];
    }
}
