<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Enquiry> */
class EnquiryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->numerify('+92 3075484089'),
            'subject' => fake()->sentence(5),
            'message' => fake()->paragraph(3),
            'status' => EnquiryStatus::New,
            'ip' => fake()->ipv4(),
        ];
    }
}