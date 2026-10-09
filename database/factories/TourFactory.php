<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TourBadge;
use App\Models\Category;
use App\Models\Desert;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Tour> */
class TourFactory extends Factory
{
    public function definition(): array
    {
        $adult = fake()->numberBetween(25, 150) * 100; // PKR, round hundreds

        return [
            'category_id' => Category::factory(),
            'desert_id' => Desert::factory(),
            'title' => fake()->unique()->words(3, true).' Desert Safari',
            'short_description' => fake()->sentence(14),
            'description' => '<p>'.fake()->paragraph(5).'</p>',
            'duration_hours' => fake()->randomElement([3, 4, 5, 6, 8, 24]),
            'base_price_adult' => $adult,
            'base_price_child' => (int) round($adult * 0.6 / 100) * 100,
            'discount_percent' => fake()->randomElement([0, 0, 10, 15, 20]),
            'price_label' => 'per person',
            'has_dinner' => fake()->boolean(),
            'has_breakfast' => fake()->boolean(30),
            'has_guide' => true,
            'has_transfer' => fake()->boolean(70),
            'has_itinerary' => true,
            'max_group_size' => fake()->randomElement([10, 20, 50]),
            'min_age' => fake()->randomElement([0, 3, 5]),
            'badges' => [fake()->randomElement(TourBadge::cases())],
            'cancellation_policy' => '<p>Free cancellation up to 24 hours before the tour.</p>',
            'is_featured' => fake()->boolean(25),
            'is_best_selling' => fake()->boolean(20),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function discounted(int $percent = 20): static
    {
        return $this->state(fn () => ['discount_percent' => $percent]);
    }
}