<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentTransactionStatus;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Payment> */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'method' => PaymentMethod::BankTransfer,
            'amount' => 5000,
            'currency' => 'PKR',
            'status' => PaymentTransactionStatus::Completed,
            'transaction_id' => strtoupper(fake()->bothify('TXN########')),
            'paid_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => PaymentTransactionStatus::Pending,
            'paid_at' => null,
        ]);
    }
}