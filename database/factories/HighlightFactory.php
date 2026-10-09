<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Highlight;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Highlight> */
class HighlightFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(14),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}