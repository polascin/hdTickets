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
            'name'                => fake()->sentence,
            'keywords'            => fake()->words(3, TRUE),
            'platform'            => fake()->randomElement(['stubhub', 'ticketmaster', 'viagogo']),
            'max_price'           => fake()->randomFloat(2, 50, 500),
            'currency'            => 'USD',
            'filters'             => json_encode(['criteria' => 'example']),
            'email_notifications' => TRUE,
            'sms_notifications'   => FALSE,
            'status'              => fake()->randomElement(['active', 'paused', 'triggered', 'expired']),
            'triggered_at'        => fake()->boolean(30) ? now()->subDays(fake()->numberBetween(0, 30)) : NULL,
        ];
    }
}
