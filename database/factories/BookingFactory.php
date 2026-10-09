<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Booking> */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference' => 'DSP-'.date('Y').'-'.str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'tour_id' => Tour::factory(),
            'customer_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->numerify('+92 3## #######'),
            'nationality' => 'Pakistani',
            'tour_date' => now()->addDays(7)->toDateString(),
            'pickup_time' => '16:00',
            'pickup_location' => fake()->streetName(),
            'city' => fake()->randomElement(['Bahawalpur', 'Multan', 'Lahore', 'Karachi']),
            'adults' => 2,
            'children' => 0,
            'subtotal' => 10000,
            'addons_total' => 0,
            'discount_total' => 0,
            'tax' => 0,
            'grand_total' => 10000,
            'currency' => 'PKR',
            'status' => BookingStatus::Pending,
            'payment_status' => PaymentStatus::Unpaid,
            'source' => BookingSource::Web,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => BookingStatus::Confirmed]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }
}