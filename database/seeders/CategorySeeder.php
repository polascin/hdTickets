<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Sports',
                'description' => 'All sports-related tickets including NBA, NFL, MLB, Soccer',
                'color' => '#10b981',
                'icon' => 'sports-basketball',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Concerts',
                'description' => 'Music concerts and live performances',
                'color' => '#8b5cf6',
                'icon' => 'musical-note',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Theater',
                'description' => 'Broadway shows, musicals, and theater performances',
                'color' => '#f59e0b',
                'icon' => 'mask-theater',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Comedy',
                'description' => 'Stand-up comedy shows and comedy events',
                'color' => '#ef4444',
                'icon' => 'face-smile',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Festivals',
                'description' => 'Music festivals, food festivals, and special events',
                'color' => '#06b6d4',
                'icon' => 'star',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::create(array_merge($categoryData, [
                'uuid' => Str::uuid(),
                'slug' => Str::slug($categoryData['name']),
            ]));
        }
        
        $this->command->info('Created ' . count($categories) . ' sample categories successfully!');
    }
}
