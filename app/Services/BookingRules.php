<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Single read point for booking rules. Today it reads config/booking.php;
 * to move a rule into the settings table, change only the method here.
 */
class BookingRules
{
    public function currency(): string
    {
        return (string) config('booking.currency', 'PKR');
    }

    public function referencePrefix(): string
    {
        return (string) config('booking.reference_prefix', 'DSP');
    }

    public function taxPercent(): float
    {
        return max(0.0, (float) config('booking.tax_percent', 0));
    }

    public function maxGuests(): int
    {
        return (int) config('booking.max_guests_per_booking', 50);
    }

    public function sameDayCutoff(): string
    {
        return (string) config('booking.same_day_cutoff', '12:00');
    }

    public function freeCancellationHours(): int
    {
        return (int) config('booking.free_cancellation_hours', 24);
    }

    public function lateCancellationFeePercent(): float
    {
        return max(0.0, min(100.0, (float) config('booking.late_cancellation_fee_percent', 50)));
    }
}