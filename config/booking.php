<?php

declare(strict_types=1);

use App\Services\Payments\ManualPaymentGateway;

return [
    'currency' => env('BOOKING_CURRENCY', 'PKR'),
    'reference_prefix' => env('BOOKING_REFERENCE_PREFIX', 'DSP'),
    'tax_percent' => (float) env('BOOKING_TAX_PERCENT', 0),
    'max_guests_per_booking' => (int) env('BOOKING_MAX_GUESTS', 50),

    // Same-day bookings close at this time (app timezone, H:i).
    'same_day_cutoff' => env('BOOKING_SAME_DAY_CUTOFF', '12:00'),

    // Free cancellation until this many hours before the tour starts.
    'free_cancellation_hours' => (int) env('BOOKING_FREE_CANCELLATION_HOURS', 24),
    // Fee (percent of grand total) when cancelling later than that.
    'late_cancellation_fee_percent' => (float) env('BOOKING_LATE_CANCELLATION_FEE', 50),

    // PaymentMethod value => gateway class. "default" is used for anything not listed.
    'payment_gateways' => [
        'default' => ManualPaymentGateway::class,
        // 'jazzcash' => \App\Services\Payments\JazzCashGateway::class,
    ],
];