<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use App\DataTransferObjects\QuoteInput;

/** Shared rules for anything that prices a tour (quote + checkout). */
trait QuoteRules
{
    /** @return array<string, array<int, mixed>> */
    protected function quoteRules(): array
    {
        $maxGuests = (int) config('booking.max_guests_per_booking', 50);

        return [
            'tour_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'adults' => ['required', 'integer', 'min:1', 'max:'.$maxGuests],
            'children' => ['nullable', 'integer', 'min:0', 'max:'.$maxGuests],
            'camp_type_id' => ['nullable', 'integer', 'exists:camp_types,id'],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'addons' => ['nullable', 'array', 'max:20'],
            'addons.*' => ['integer', 'min:0', 'max:20'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function toQuoteInput(): QuoteInput
    {
        return QuoteInput::fromArray($this->validated());
    }
}