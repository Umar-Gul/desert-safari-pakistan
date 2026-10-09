<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Enums\PaymentMethod;
use App\Enums\PaymentTransactionStatus;
use App\Models\Booking;

/** Bank transfer, cash, payment link: the team confirms receipt manually. */
class ManualPaymentGateway implements PaymentGateway
{
    public function charge(Booking $booking, PaymentMethod $method, float $amount): PaymentResult
    {
        return new PaymentResult(
            status: PaymentTransactionStatus::Pending,
            message: 'Awaiting manual confirmation of payment by our team ('.$method->label().').',
        );
    }
}