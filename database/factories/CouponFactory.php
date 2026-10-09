<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AdjustmentType;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Coupon> */
class CouponFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('SAFARI##??'),
            'type' => AdjustmentType::Percent,
            'value' => 10,
            'min_amount' => 0,
            'usage_limit' => null,
            'used_count' => 0,
            'is_active' => true,
        ];
    }

    public function fixed(float $value = 1000): static
    {
        return $this->state(fn () => ['type' => AdjustmentType::Fixed, 'value' => $value]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }
}