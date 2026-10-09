<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Review> */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tour_id' => Tour::factory(),
            'name' => fake()->name(),
            'country' => fake()->randomElement(['Pakistan', 'United Kingdom', 'Canada', 'UAE', 'Germany']),
            'rating' => fake()->numberBetween(3, 5),
            'title' => fake()->sentence(4),
            'comment' => fake()->paragraph(3),
            'status' => ReviewStatus::Pending,
            'visited_on' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => ReviewStatus::Approved]);
    }
}