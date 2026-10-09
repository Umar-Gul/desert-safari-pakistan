<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Question> */
class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tour_id' => null,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'question' => rtrim(fake()->sentence(10), '.').'?',
            'status' => ReviewStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => ReviewStatus::Approved,
            'answer' => fake()->paragraph(2),
            'answered_at' => now(),
        ]);
    }
}