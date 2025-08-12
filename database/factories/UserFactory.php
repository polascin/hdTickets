<?php declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /** The current password being used by the factory. */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate more realistic user data
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        // Generate unique username
        $baseUsername = strtolower($firstName . '.' . $lastName);
        $username = $this->generateUniqueUsername($baseUsername);

        // Create varied email formats
        $emailFormats = [
            strtolower($firstName . '.' . $lastName),
            strtolower($firstName . $lastName),
            strtolower(substr($firstName, 0, 1) . $lastName),
            strtolower($firstName . '.' . substr($lastName, 0, 1)),
            strtolower($firstName) . fake()->numberBetween(10, 999),
        ];

        $emailPrefix = fake()->randomElement($emailFormats);
        $emailDomains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com', 'example.com', 'test.com'];
        // Add random number to ensure uniqueness
        $email = $emailPrefix . '.' . fake()->numberBetween(1000, 9999) . '@' . fake()->randomElement($emailDomains);

        // Assign roles with realistic distribution
        $roleWeights = [
            'customer' => 85, // 85% customers
            'agent'    => 12,    // 12% agents
            'admin'    => 3,      // 3% admins
        ];

        $role = $this->weightedRandom($roleWeights);

        return [
            'name'              => $firstName,
            'surname'           => $lastName,
            'username'          => $username,
            'email'             => $email,
            'email_verified_at' => fake()->optional(0.8)->dateTimeBetween('-1 year', 'now'), // 80% verified
            'password'          => static::$password ??= Hash::make('password123'),
            'role'              => $role,
            'is_active'         => fake()->optional(0.95, TRUE)->boolean(), // 95% active users
            'remember_token'    => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => NULL,
        ]);
    }

    /**
     * Helper method for weighted random selection
     */
    private function weightedRandom(array $weights): string
    {
        $totalWeight = array_sum($weights);
        $randomWeight = fake()->numberBetween(1, $totalWeight);

        $currentWeight = 0;
        foreach ($weights as $value => $weight) {
            $currentWeight += $weight;
            if ($randomWeight <= $currentWeight) {
                return $value;
            }
        }

        return array_key_first($weights); // fallback
    }

    /**
     * Generate a unique username by appending numbers if needed
     */
    private function generateUniqueUsername(string $baseUsername): string
    {
        $username = $baseUsername;
        $counter = 1;

        // Keep checking until we find a unique username
        while (\App\Models\User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }
}
