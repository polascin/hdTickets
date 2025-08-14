<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\TicketAlert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketAlert>
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
            'user_id'             => \App\Models\User::factory(),
            'name'                => $this->faker->sentence,
            'keywords'            => $this->faker->words(3, TRUE),
            'platform'            => $this->faker->randomElement(['stubhub', 'ticketmaster', 'viagogo']),
            'max_price'           => $this->faker->randomFloat(2, 50, 500),
            'currency'            => 'USD',
            'filters'             => json_encode(['criteria' => 'example']),
            'email_notifications' => TRUE,
            'sms_notifications'   => FALSE,
            'status'              => $this->faker->randomElement(['active', 'paused', 'triggered', 'expired']),
            'triggered_at'        => $this->faker->boolean(30) ? now()->subDays($this->faker->numberBetween(0, 30)) : NULL,
        ];
    }
}
