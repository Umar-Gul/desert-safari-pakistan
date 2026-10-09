<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Vehicle> */
class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(12),
            'capacity' => fake()->randomElement([4, 6, 8, 12, 20]),
            'price_modifier' => fake()->numberBetween(0, 40) * 100,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}