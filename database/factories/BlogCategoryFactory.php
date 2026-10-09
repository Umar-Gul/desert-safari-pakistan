<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BlogCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BlogCategory> */
class BlogCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}