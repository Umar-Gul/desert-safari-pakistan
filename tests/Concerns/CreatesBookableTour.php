<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\DataTransferObjects\QuoteInput;
use App\Models\Availability;
use App\Models\Tour;

trait CreatesBookableTour
{
    protected function setUpBookingConfig(): void
    {
        config([
            'booking.currency' => 'PKR',
            'booking.tax_percent' => 0,
            'booking.same_day_cutoff' => '12:00',
            'booking.free_cancellation_hours' => 24,
            'booking.late_cancellation_fee_percent' => 50,
            'booking.max_guests_per_booking' => 50,
        ]);
    }

    /** 10,000 adult / 6,000 child, 10% tour discount. */
    protected function makeTour(array $overrides = []): Tour
    {
        return Tour::factory()->create(array_merge([
            'base_price_adult' => 10000,
            'base_price_child' => 6000,
            'discount_percent' => 10,
            'max_group_size' => 50,
            'is_active' => true,
        ], $overrides));
    }

    protected function openDate(Tour $tour, int $daysAhead = 5, int $capacity = 10, array $extra = []): Availability
    {
        return Availability::factory()->create(array_merge([
            'tour_id' => $tour->id,
            'date' => now()->addDays($daysAhead)->toDateString(),
            'capacity' => $capacity,
            'booked_count' => 0,
            'is_closed' => false,
        ], $extra));
    }

    /** Default: 2 adults + 1 child on the availability's date. */
    protected function quoteInput(Availability $availability, array $overrides = []): QuoteInput
    {
        return QuoteInput::fromArray(array_merge([
            'tour_date' => $availability->date->toDateString(),
            'adults' => 2,
            'children' => 1,
        ], $overrides));
    }

    /** @return array<string, string> */
    protected function customer(): array
    {
        return [
            'customer_name' => 'Ali Khan',
            'email' => 'ali@example.com',
            'phone' => '+92 300 1234567',
            'nationality' => 'Pakistani',
            'pickup_time' => '16:00',
            'pickup_location' => 'Hotel Grand',
            'city' => 'Bahawalpur',
        ];
    }
}