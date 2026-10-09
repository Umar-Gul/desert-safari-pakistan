<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BlogStatus;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Blog> */
class BlogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'blog_category_id' => BlogCategory::factory(),
            'author_id' => User::factory(),
            'title' => fake()->unique()->sentence(6),
            'excerpt' => fake()->sentence(18),
            'content' => '<p>'.implode('</p><p>', fake()->paragraphs(4)).'</p>',
            'status' => BlogStatus::Published,
            'published_at' => now()->subDays(fake()->numberBetween(1, 60)),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => BlogStatus::Draft,
            'published_at' => null,
        ]);
    }
}