<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Addon;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Addon> */
class AddonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(10),
            'price' => fake()->numberBetween(5, 60) * 100,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}