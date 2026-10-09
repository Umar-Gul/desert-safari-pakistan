<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Enums\PaymentMethod;
use App\Models\Booking;

/**
 * Implement this to add a gateway (JazzCash, Easypaisa, Stripe...), then
 * register the class in config/booking.php under payment_gateways.
 */
interface PaymentGateway
{
    public function charge(Booking $booking, PaymentMethod $method, float $amount): PaymentResult;
}