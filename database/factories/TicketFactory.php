<?php declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid'              => Str::uuid(),
            'requester_id'      => \App\Models\User::factory(),
            'assignee_id'       => \App\Models\User::factory(),
            'category_id'       => \App\Models\Category::factory(),
            'title'             => $this->faker->sentence,
            'description'       => $this->faker->paragraph,
            'status'            => $this->faker->randomElement(['open', 'in_progress', 'pending', 'resolved', 'closed']),
            'priority'          => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'due_date'          => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'last_activity_at'  => now(),
            'platform'          => $this->faker->word,
            'external_id'       => $this->faker->unique()->numerify('EXT#####'),
            'price'             => $this->faker->randomFloat(2, 20, 300),
            'currency'          => $this->faker->currencyCode,
            'location'          => $this->faker->city,
            'venue'             => $this->faker->company,
            'event_date'        => $this->faker->dateTimeBetween('now', '+1 year'),
            'event_type'        => $this->faker->word,
            'performer_artist'  => $this->faker->name,
            'seat_details'      => $this->faker->sentence,
            'is_available'      => $this->faker->boolean,
            'ticket_url'        => $this->faker->url,
            'scraping_metadata' => json_encode(['source' => $this->faker->word, 'type' => $this->faker->word]),
        ];
    }
}
