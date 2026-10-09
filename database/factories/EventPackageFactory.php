<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EventPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EventPackage> */
class EventPackageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(3, true).' Package',
            'time_slot' => fake()->randomElement(['9:00 AM - 2:00 PM', '3:00 PM - 9:00 PM']),
            'description' => fake()->sentence(14),
            'adult_price' => fake()->numberBetween(20, 80) * 100,
            'child_price' => fake()->numberBetween(10, 40) * 100,
            'features' => ['Private camp', 'Folk music', 'Traditional dinner'],
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}