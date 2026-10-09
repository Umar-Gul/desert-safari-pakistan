<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\QuickLinkGroup;
use App\Models\QuickLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<QuickLink> */
class QuickLinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'section' => QuickLinkGroup::ActivityTypes,
            'label' => fake()->unique()->words(2, true),
            'url' => '/tours',
            'icon' => fake()->randomElement(['tickets', 'tour', 'transport', 'food', 'day-trip']),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }

    public function popularSearch(): static
    {
        return $this->state(fn () => ['section' => QuickLinkGroup::PopularSearches]);
    }
}