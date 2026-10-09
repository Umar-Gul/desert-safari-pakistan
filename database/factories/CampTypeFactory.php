<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AdjustmentType;
use App\Models\CampType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CampType> */
class CampTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true).' Camp',
            'description' => fake()->sentence(12),
            'price_modifier_type' => AdjustmentType::Fixed,
            'price_modifier_value' => fake()->numberBetween(0, 30) * 100,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }

    public function percent(float $value = 15): static
    {
        return $this->state(fn () => [
            'price_modifier_type' => AdjustmentType::Percent,
            'price_modifier_value' => $value,
        ]);
    }
}