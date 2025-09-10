<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        fake()->words(2, TRUE);

        // Category names related to sports events
        $sportCategories = [
            'Football Events',
            'Basketball Games',
            'Baseball Matches',
            'Soccer Championships',
            'Tennis Tournaments',
            'Hockey Games',
            'Golf Tournaments',
            'Racing Events',
            'Wrestling Matches',
            'Boxing Fights',
            'MMA Events',
            'Olympics',
            'Swimming Competitions',
            'Track and Field',
            'Volleyball Games',
        ];

        $categoryName = fake()->randomElement($sportCategories);

        // Color palette for categories
        $colors = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
            '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9',
            '#F8C471', '#82E0AA', '#F1948A', '#85C1E9', '#D2B4DE',
        ];

        // Icon options for categories
        $icons = [
            'fas fa-football-ball', 'fas fa-basketball-ball', 'fas fa-baseball-ball',
            'fas fa-table-tennis', 'fas fa-hockey-puck', 'fas fa-golf-ball',
            'fas fa-car', 'fas fa-swimmer', 'fas fa-running', 'fas fa-volleyball-ball',
            'fas fa-medal', 'fas fa-trophy', 'fas fa-award', 'fas fa-crown',
            'fas fa-star',
        ];

        return [
            'uuid'        => Str::uuid(),
            'parent_id'   => NULL, // Default to root category, can be overridden
            'name'        => $categoryName,
            'slug'        => Str::slug($categoryName),
            'description' => fake()->sentence(10),
            'color'       => fake()->randomElement($colors),
            'icon'        => fake()->randomElement($icons),
            'is_active'   => fake()->boolean(90), // 90% chance of being active
            'sort_order'  => fake()->numberBetween(1, 100),
            'metadata'    => [
                'tags'     => fake()->words(3),
                'priority' => fake()->randomElement(['high', 'medium', 'low']),
                'region'   => fake()->randomElement(['North America', 'Europe', 'Asia', 'Global']),
            ],
        ];
    }

    /**
     * Create a subcategory (with parent)
     *
     * @param mixed|null $parentId
     */
    public function subcategory($parentId = NULL): static
    {
        return $this->state(fn (array $attributes): array => [
            'parent_id'  => $parentId ?: Category::factory()->create()->id,
            'sort_order' => fake()->numberBetween(1, 50),
        ]);
    }

    /**
     * Create an inactive category
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => FALSE,
        ]);
    }

    /**
     * Create a category with specific name
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => $name,
            'slug' => Str::slug($name),
        ]);
    }
}
