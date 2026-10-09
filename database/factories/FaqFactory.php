<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FaqScope;
use App\Models\Faq;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Faq> */
class FaqFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question' => rtrim(fake()->sentence(8), '.').'?',
            'answer' => fake()->paragraph(2),
            'scope' => FaqScope::Global,
            'tour_id' => null,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }

    public function forTour(?Tour $tour = null): static
    {
        return $this->state(fn () => [
            'scope' => FaqScope::Tour,
            'tour_id' => $tour?->id ?? Tour::factory(),
        ]);
    }
}