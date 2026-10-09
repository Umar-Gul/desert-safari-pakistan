<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Desert;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Desert> */
class DesertFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true).' Desert',
            'description' => fake()->sentence(16),
            'location' => fake()->randomElement([
                'Bahawalpur, Punjab', 'Tharparkar, Sindh', 'Bhakkar, Punjab', 'Kharan, Balochistan',
            ]),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}