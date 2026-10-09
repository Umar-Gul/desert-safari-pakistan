<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;

enum BookingStatus: string
{
    use EnumHelpers;

    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Confirmed => 'blue',
            self::Completed => 'green',
            self::Cancelled => 'red',
            self::Refunded => 'stone',
        };
    }

    /** Statuses that occupy capacity on a date. */
    public function holdsCapacity(): bool
    {
        return in_array($this, [self::Pending, self::Confirmed, self::Completed], true);
    }
}