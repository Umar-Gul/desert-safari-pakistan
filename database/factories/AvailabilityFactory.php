<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Availability;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Availability> */
class AvailabilityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tour_id' => Tour::factory(),
            'date' => now()->addDays(fake()->numberBetween(1, 90))->toDateString(),
            'capacity' => 40,
            'booked_count' => 0,
            'is_closed' => false,
            'price_override' => null,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn () => ['is_closed' => true]);
    }

    public function full(): static
    {
        return $this->state(fn (array $attrs) => ['booked_count' => $attrs['capacity']]);
    }
}