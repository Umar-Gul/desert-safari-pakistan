<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TrustBadge;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TrustBadge> */
class TrustBadgeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(3, true),
            'subtitle' => fake()->words(3, true),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}