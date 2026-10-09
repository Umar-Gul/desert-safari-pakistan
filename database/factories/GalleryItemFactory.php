<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GalleryItemType;
use App\Models\GalleryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GalleryItem> */
class GalleryItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'type' => GalleryItemType::Image,
            'category' => fake()->randomElement(['Dunes', 'Camp', 'Culture', 'Food']),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}